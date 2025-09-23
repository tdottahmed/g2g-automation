import {CONFIG} from "../post-offers.js";
import fs from "fs";
import {rl} from "./index.js";
import {delay} from "./index.js";

export async function saveAuthState(context) {
    try {
        // Use Playwright's built-in method to save the complete state
        await context.storageState({ path: CONFIG.authFile });
        console.log("✅ Authentication state saved to", CONFIG.authFile);

        // Verify the file was created
        if (fs.existsSync(CONFIG.authFile)) {
            const stats = fs.statSync(CONFIG.authFile);
            console.log("✅ Auth file created with size:", stats.size, "bytes");
            return true;
        } else {
            console.log("❌ Auth file was not created");
            return false;
        }
    } catch (error) {
        console.error("❌ Failed to save authentication state:", error.message);
        return false;
    }
}

export async function loadAuthState(context) {
    try {
        if (fs.existsSync(CONFIG.authFile)) {
            // Use Playwright's built-in method to load the complete state
            const storageState = fs.readFileSync(CONFIG.authFile, 'utf8');
            await context.addCookies(JSON.parse(storageState).cookies);

            // Also set localStorage if available
            const state = JSON.parse(storageState);
            if (state.origins && state.origins.length > 0) {
                for (const origin of state.origins) {
                    if (origin.localStorage && origin.localStorage.length > 0) {
                        await context.addInitScript((storage) => {
                            for (const item of storage) {
                                window.localStorage.setItem(item.name, item.value);
                            }
                        }, origin.localStorage);
                    }
                }
            }

            console.log("✅ Authentication state loaded from", CONFIG.authFile);
            return true;
        }
        return false;
    } catch (error) {
        console.error("❌ Failed to load authentication state:", error.message);
        return false;
    }
}

export async function isLoggedIn(page) {
    try {
        await page.goto(`${CONFIG.baseUrl}/`, {waitUntil: "domcontentloaded", timeout: 10000});
        const loginLink = await page.$('a[href*="login"]');
        return !loginLink;
    } catch (error) {
        return false;
    }
}

export async function loginWithOTP(page) {
    console.log("🔐 Starting login process...");
    // Go to login page
    await page.goto(`${CONFIG.baseUrl}/login`, { waitUntil: "domcontentloaded" });
    await delay(1000);

    // Fill login form
    await page.fill('input[type="email"], input[data-attr="username-input"]', CONFIG.credentials.email);
    await delay(500);
    await page.fill('input[type="password"]', CONFIG.credentials.password);
    await delay(500);

    // Submit login form
    await page.click('form button[type="submit"]');
    console.log("✅ Submitted login, waiting for OTP...");

    // Wait for OTP input
    try {
        await page.waitForSelector("input.otp-input", { timeout: 30000 });
        console.log("📧 OTP required. Please check your email.");

        const otp = await new Promise((resolve) => {
            rl.question("Enter the 6-digit OTP from your email: ", (answer) => {
                resolve(answer.trim());
            });
        });

        // Fill OTP inputs
        const otpInputs = await page.$$("input.otp-input");
        for (let i = 0; i < otpInputs.length; i++) {
            await otpInputs[i].fill(otp[i]);
            await delay(100);
        }

        console.log("✅ OTP entered. Waiting for login to complete...");

        // Wait for login to complete (check for avatar)
        await page.waitForSelector('#btnMenu', { timeout: 15000 });
        console.log("✅ Login successful!");
        return true;
    } catch (error) {
        console.log("❌ OTP process failed:", error.message);
        return false;
    }
}

// Add this function to your auth.js or utils
export async function clearProblematicStorage(page) {
    try {
        // Clear localStorage and sessionStorage
        await page.evaluate(() => {
            localStorage.clear();
            sessionStorage.clear();
        });

        // Clear specific cookies that might cause issues
        const cookies = await page.context().cookies();
        const problematicCookies = cookies.filter(cookie =>
            cookie.name.includes('track') ||
            cookie.name.includes('session') ||
            cookie.name.includes('auth')
        );

        for (const cookie of problematicCookies) {
            await page.context().clearCookies({
                name: cookie.name,
                domain: cookie.domain
            });
        }

        console.log("✅ Cleared problematic storage and cookies");
        return true;
    } catch (error) {
        console.error("❌ Failed to clear storage:", error.message);
        return false;
    }
}


