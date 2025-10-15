import fs from "fs";
import { rl } from "./index.js";
import { delay } from "./index.js";

/**
 * Save authentication state to a specified file
 * @param {BrowserContext} context
 * @param {string} authFile
 */
export async function saveAuthState(context, authFile) {
    try {
        await context.storageState({ path: authFile });
        console.log("✅ Authentication state saved to", authFile);

        if (fs.existsSync(authFile)) {
            const stats = fs.statSync(authFile);
            console.log("✅ Auth file size:", stats.size, "bytes");

            // Read and log the cookies count for verification
            const storageState = JSON.parse(fs.readFileSync(authFile, "utf8"));
            console.log(
                "📊 Cookies saved:",
                storageState.cookies ? storageState.cookies.length : 0
            );

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

/**
 * Load authentication state from a specified file
 * @param {BrowserContext} context
 * @param {string} authFile
 */
export async function loadAuthState(context, authFile) {
    try {
        if (fs.existsSync(authFile)) {
            console.log("📁 Loading auth state from:", authFile);
            const storageState = fs.readFileSync(authFile, "utf8");
            const state = JSON.parse(storageState);

            // Clear existing cookies first
            await context.clearCookies();

            // Add cookies from storage state
            if (state.cookies && state.cookies.length > 0) {
                await context.addCookies(state.cookies);
                console.log("✅ Loaded cookies:", state.cookies.length);
            } else {
                console.log("❌ No cookies found in auth file");
                return false;
            }

            // Handle localStorage if present
            if (state.origins && state.origins.length > 0) {
                for (const origin of state.origins) {
                    if (origin.localStorage && origin.localStorage.length > 0) {
                        await context.addInitScript((storage) => {
                            for (const item of storage) {
                                window.localStorage.setItem(
                                    item.name,
                                    item.value
                                );
                            }
                        }, origin.localStorage);
                    }
                }
                console.log("✅ Loaded localStorage data");
            }

            console.log("✅ Authentication state loaded successfully");
            return true;
        }
        console.log("❌ Auth file does not exist:", authFile);
        return false;
    } catch (error) {
        console.error("❌ Failed to load authentication state:", error.message);
        return false;
    }
}

/**
 * Check if the user is logged in
 * @param {Page} page
 * @param {string} baseUrl
 */
export async function isLoggedIn(page, baseUrl) {
    try {
        await page.goto(`${baseUrl}/`, {
            waitUntil: "domcontentloaded",
            timeout: 15000,
        });

        // Check multiple indicators of being logged in
        const currentUrl = page.url();

        // If redirected to login page, not logged in
        if (currentUrl.includes("/login") || currentUrl.includes("/sign-in")) {
            console.log("❌ Not logged in - on login page");
            return false;
        }

        // Check for login button/link
        const loginLink = await page
            .locator('a[href*="login"], a[href*="sign-in"]')
            .first();
        if ((await loginLink.count()) > 0) {
            console.log("❌ Not logged in - login link found");
            return false;
        }

        // Check for user profile elements
        const profileElements = await page
            .locator(
                '[href*="account"], [href*="profile"], .user-avatar, .user-name'
            )
            .first();
        if ((await profileElements.count()) > 0) {
            console.log("✅ Logged in - user profile elements found");
            return true;
        }

        // If we're on the main page without login redirect, assume logged in
        console.log("✅ Logged in - on main page without login redirect");
        return true;
    } catch (error) {
        console.error("❌ Error checking login status:", error.message);
        return false;
    }
}

/**
 * Login with OTP
 * @param {Page} page
 * @param {string} baseUrl
 * @param {string} email
 * @param {string} password
 */
export async function loginWithOTP(page, baseUrl, email, password) {
    const maxRetries = 2;

    for (let attempt = 1; attempt <= maxRetries; attempt++) {
        try {
            console.log(
                `🔐 Starting login process (Attempt ${attempt}/${maxRetries})...`
            );

            // Try different waitUntil strategies
            try {
                await page.goto(`${baseUrl}/login`, {
                    waitUntil: "domcontentloaded",
                    timeout: 45000,
                });
            } catch (navError) {
                console.log(
                    `⚠️ Navigation timeout, trying with 'load' strategy...`
                );
                await page.goto(`${baseUrl}/login`, {
                    waitUntil: "load",
                    timeout: 45000,
                });
            }

            await delay(3000);

            // Check if we're actually on the login page
            const currentUrl = page.url();
            if (!currentUrl.includes("/login")) {
                console.log(
                    `ℹ️ Already redirected from login page to: ${currentUrl}`
                );

                const loggedIn = await isLoggedIn(page, baseUrl);
                if (loggedIn) {
                    console.log("✅ Already logged in!");
                    return true;
                } else {
                    console.log(
                        "❌ Not logged in but not on login page, retrying..."
                    );
                    continue;
                }
            }

            // Wait for email input
            console.log("⌛ Waiting for login form...");
            try {
                await page.waitForSelector(
                    'input[type="email"], input[name="email"], input[data-attr="username-input"], input[placeholder*="email" i]',
                    { timeout: 15000 }
                );
            } catch {
                console.log("❌ Login form not found, retrying...");
                continue;
            }

            // Fill credentials
            await page.fill(
                'input[type="email"], input[name="email"], input[data-attr="username-input"], input[placeholder*="email" i]',
                email
            );
            await delay(1000);

            await page.fill(
                'input[type="password"], input[name="password"], input[placeholder*="password" i]',
                password
            );
            await delay(1000);

            // Submit form
            await page.click('form button[type="submit"]');
            console.log("✅ Submitted login credentials...");

            // Wait for either OTP input or successful login
            try {
                // If OTP appears
                await page.waitForSelector("input.otp-input", {
                    timeout: 30000,
                });
                console.log(
                    "📧 OTP required. Please enter it in the browser..."
                );

                // Instead of CLI input, just wait for login success
                await page.waitForSelector("#btnMenu", { timeout: 120000 }); // give user 2 min
                console.log("✅ OTP entered manually. Login successful!");
                return true;
            } catch {
                // If OTP never shows but login succeeds directly
                const loggedIn = await isLoggedIn(page, baseUrl);
                if (loggedIn) {
                    console.log("✅ Logged in without OTP");
                    return true;
                }

                console.log("❌ OTP/login process failed.");
                return false;
            }
        } catch (error) {
            console.error(
                `❌ Login process failed (Attempt ${attempt}):`,
                error.message
            );

            if (attempt < maxRetries) {
                console.log(
                    `🔄 Retrying login... (${
                        maxRetries - attempt
                    } attempts left)`
                );
                await delay(5000);
            } else {
                console.error("❌ All login attempts failed");
                return false;
            }
        }
    }

    return false;
}

/**
 * Clear localStorage, sessionStorage and specific cookies
 * @param {Page} page
 */
export async function clearProblematicStorage(page) {
    try {
        await page.evaluate(() => {
            localStorage.clear();
            sessionStorage.clear();
        });

        const cookies = await page.context().cookies();
        const problematicCookies = cookies.filter(
            (cookie) =>
                cookie.name.includes("track") ||
                cookie.name.includes("session") ||
                cookie.name.includes("auth")
        );

        for (const cookie of problematicCookies) {
            await page.context().clearCookies({
                name: cookie.name,
                domain: cookie.domain,
            });
        }

        console.log("✅ Cleared problematic storage and cookies");
        return true;
    } catch (error) {
        console.error("❌ Failed to clear storage:", error.message);
        return false;
    }
}
