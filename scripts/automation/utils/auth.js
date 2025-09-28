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
            const storageState = fs.readFileSync(authFile, "utf8");
            await context.addCookies(JSON.parse(storageState).cookies);

            const state = JSON.parse(storageState);
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
            }

            console.log("✅ Authentication state loaded from", authFile);
            return true;
        }
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
            timeout: 10000,
        });
        const loginLink = await page.$('a[href*="login"]');
        return !loginLink;
    } catch (error) {
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
    console.log("🔐 Starting login process...");
    await page.goto(`${baseUrl}/login`, { waitUntil: "domcontentloaded" });
    await delay(1000);

    await page.fill(
        'input[type="email"], input[data-attr="username-input"]',
        email
    );
    await delay(500);
    await page.fill('input[type="password"]', password);
    await delay(500);

    await page.click('form button[type="submit"]');
    console.log("✅ Submitted login, waiting for OTP...");

    try {
        await page.waitForSelector("input.otp-input", { timeout: 30000 });
        console.log("📧 OTP required. Please check your email.");

        const otp = await new Promise((resolve) => {
            rl.question("Enter the 6-digit OTP from your email: ", (answer) => {
                resolve(answer.trim());
            });
        });

        const otpInputs = await page.$$("input.otp-input");
        for (let i = 0; i < otpInputs.length; i++) {
            await otpInputs[i].fill(otp[i]);
            await delay(100);
        }

        console.log("✅ OTP entered. Waiting for login to complete...");
        await page.waitForSelector("#btnMenu", { timeout: 15000 });
        console.log("✅ Login successful!");
        return true;
    } catch (error) {
        console.log("❌ OTP process failed:", error.message);
        return false;
    }
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
