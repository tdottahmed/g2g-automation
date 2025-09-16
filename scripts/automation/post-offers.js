// scripts/automation/g2g-automation.js
import { chromium } from "playwright";
import fs from "fs";
import readline from "readline";

// Create readline interface for user input
const rl = readline.createInterface({
    input: process.stdin,
    output: process.stdout
});

// Configuration
const CONFIG = {
    authFile: 'g2g_auth_state.json',
    baseUrl: 'https://www.g2g.com',
    credentials: {
        email: "developertanbir1@gmail.com",
        password: "newpass@123"
    },
    userAgent: "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36",
    viewport: { width: 1366, height: 768 },
    headless: false,
    slowMo: 80
};

// Helper functions
const delay = (ms) => new Promise((res) => setTimeout(res, ms));

async function humanType(page, selector, text) {
    for (const char of text) {
        await page.type(selector, char);
        await delay(Math.floor(Math.random() * 150) + 50); // 50–200ms delay
    }
}

async function saveAuthState(context) {
    await context.storageState({ path: CONFIG.authFile });
    console.log("✅ Authentication state saved successfully");
}

async function loadAuthState(context) {
    if (fs.existsSync(CONFIG.authFile)) {
        await context.addInitScript((storage) => {
            if (window && window.localStorage) {
                for (const [key, value] of Object.entries(storage)) {
                    window.localStorage.setItem(key, value);
                }
            }
        }, require(CONFIG.authFile));

        // Also set cookies
        const authState = JSON.parse(fs.readFileSync(CONFIG.authFile, 'utf8'));
        await context.addCookies(authState.cookies);

        console.log("✅ Authentication state loaded successfully");
        return true;
    }
    return false;
}

async function checkLoginState(page) {
    try {
        // Check multiple indicators of being logged in
        const loggedInSelectors = [
            'a[href*="logout"]',
            '[data-attr="user-avatar"]',
            '.user-profile',
            '.account-menu'
        ];

        for (const selector of loggedInSelectors) {
            const element = await page.$(selector).catch(() => null);
            if (element) {
                console.log("✅ Verified logged in state");
                return true;
            }
        }

        // Additional check by visiting account page
        await page.goto(`${CONFIG.baseUrl}/account`, { waitUntil: 'domcontentloaded', timeout: 10000 })
            .catch(() => {});

        if (!page.url().includes('login')) {
            console.log("✅ Verified logged in via account page access");
            return true;
        }

        return false;
    } catch (error) {
        console.log("❌ Could not verify login state:", error.message);
        return false;
    }
}

async function loginWithOTP(page) {
    console.log("🔐 Starting login process...");

    // Go to homepage
    await page.goto(CONFIG.baseUrl, { waitUntil: "domcontentloaded" });
    await delay(1000);

    // Human-like scroll
    await page.mouse.wheel(0, 200);
    await delay(1200);

    // Click login button
    const loginLink = await page.$('a[href*="login"]');
    if (!loginLink) {
        console.log("❌ Login link not found");
        return false;
    }

    await loginLink.click();
    await delay(1500);

    // Wait for login form
    try {
        await page.waitForSelector('input[data-attr="username-input"]', { timeout: 10000 });
    } catch (error) {
        console.log("❌ Login form not found");
        return false;
    }

    // Fill login form with human-like typing
    await humanType(page, 'input[data-attr="username-input"]', CONFIG.credentials.email);
    await delay(800);
    await humanType(page, 'input[type="password"]', CONFIG.credentials.password);
    await delay(500);

    // Submit login form
    await page.click('form button[type="submit"]');

    // Wait for either OTP input or successful login
    try {
        await Promise.race([
            page.waitForSelector('input[type="text"][name="otp"], input[data-attr="otp-input"]', { timeout: 15000 }),
            page.waitForNavigation({ waitUntil: "networkidle", timeout: 15000 }),
            page.waitForSelector('a[href*="logout"]', { timeout: 15000 })
        ]);
    } catch (error) {
        console.log("❌ Login submission timed out");
        return false;
    }

    // Check if OTP is required
    const otpInput = await page.$('input[type="text"][name="otp"], input[data-attr="otp-input"]').catch(() => null);

    if (otpInput) {
        console.log("📧 OTP required. Please check your email.");

        // Wait for OTP input manually
        const otp = await new Promise((resolve) => {
            rl.question("Enter the OTP from your email: ", (answer) => {
                resolve(answer);
            });
        });

        // Enter OTP
        await otpInput.fill(otp);
        await page.click('button[type="submit"]');

        // Wait for login to complete
        try {
            await Promise.race([
                page.waitForNavigation({ waitUntil: "networkidle", timeout: 15000 }),
                page.waitForSelector('a[href*="logout"]', { timeout: 15000 })
            ]);
        } catch (error) {
            console.log("❌ OTP verification timed out");
            return false;
        }
    }

    // Verify login was successful
    const isLoggedIn = await checkLoginState(page);
    if (isLoggedIn) {
        console.log("✅ Login successful!");
        return true;
    } else {
        console.log("❌ Login failed");
        return false;
    }
}

async function createOffer(page) {
    console.log("🚀 Starting offer creation process...");

    // Navigate to Create Offer page
    await page.goto(`${CONFIG.baseUrl}/sell/create`, { waitUntil: "domcontentloaded" });
    console.log("✅ On Create Offer page.");

    // Select Category = Accounts
    await page.waitForSelector('select[name="category"]', { timeout: 10000 });
    await page.selectOption('select[name="category"]', { label: "Accounts" });
    console.log("✅ Category selected: Accounts");

    // Wait a moment for brand options to load
    await delay(2000);

    // Select Brand = Clash of Clans (Global)
    await page.waitForSelector('select[name="brand"]', { timeout: 10000 });
    await page.selectOption('select[name="brand"]', { label: "Clash Of Clans (Global)" });
    console.log("✅ Brand selected: Clash Of Clans (Global)");

    // Add more offer creation steps here as needed

    return true;
}

async function main() {
    let browser = null;
    let context = null;
    let page = null;

    try {
        // Launch browser
        browser = await chromium.launch({
            headless: CONFIG.headless,
            slowMo: CONFIG.slowMo,
            args: ["--disable-blink-features=AutomationControlled"],
        });

        // Create context
        context = await browser.newContext({
            userAgent: CONFIG.userAgent,
            viewport: CONFIG.viewport,
        });

        // Try to load existing auth state
        const authLoaded = await loadAuthState(context);

        // Create page
        page = await context.newPage();

        // Check if we're already logged in
        let isLoggedIn = false;
        if (authLoaded) {
            isLoggedIn = await checkLoginState(page);

            if (isLoggedIn) {
                console.log("✅ Using existing authentication session");
            } else {
                console.log("❌ Saved session expired, need to login again");
            }
        }

        // Login if needed
        if (!isLoggedIn) {
            isLoggedIn = await loginWithOTP(page);

            if (isLoggedIn) {
                // Save the new auth state
                await saveAuthState(context);
            } else {
                throw new Error("Login failed");
            }
        }

        // Proceed with offer creation
        await createOffer(page);

        console.log("✅ Automation completed successfully!");

    } catch (error) {
        console.error("❌ Automation failed:", error.message);
    } finally {
        rl.close();

        // Keep browser open if in debug mode
        if (browser && CONFIG.headless) {
            await browser.close();
        } else if (browser) {
            console.log("⚠️  Browser kept open for debugging. Close it manually when done.");
        }
    }
}

// Run the automation
main();
