// scripts/automation/auth-main.js
import { chromium } from "playwright";
import { isLoggedIn, loadAuthState, loginWithOTP, saveAuthState } from "./utils/auth.js";
import { rl } from "./utils/index.js";
import {navigateToAccounts} from "./utils/sell.js";

export const CONFIG = {
    authFile: "g2g_auth_state.json",
    baseUrl: "https://www.g2g.com",
    credentials: {
        email: "recoveryth0000@gmail.com",
        password: "@#aocy123%&",
    },
    headless: false,
    slowMo: 50,
};

async function main() {
    let browser = null;
    let context = null;

    try {
        browser = await chromium.launch({
            headless: CONFIG.headless,
            slowMo: CONFIG.slowMo,
        });
        context = await browser.newContext();

        const hasAuthState = await loadAuthState(context);
        console.log("Auth States:", hasAuthState);
        const page = await context.newPage();

        let loggedIn = false;
        if (hasAuthState) {
            loggedIn = await isLoggedIn(page);
        }

        if (loggedIn) {
            console.log("✅ Using existing authentication session");
            const navSuccess = await navigateToAccounts(page);
            if (!navSuccess) {
                console.log("❌ Failed to navigate to Accounts section");
            }
        } else {
            console.log("❌ No valid session found, performing login...");
            const loginSuccess = await loginWithOTP(page);

            if (loginSuccess) {
                const saveSuccess = await saveAuthState(context);
                if (saveSuccess) {
                    console.log("✅ Session saved successfully!");
                } else {
                    console.log("❌ Failed to save session");
                }

                // After login, navigate to sell offers page
                console.log("🌐 Navigating to sell offers page...");
                await page.goto('https://www.g2g.com/offers/sell', {
                    waitUntil: 'networkidle',
                    timeout: 30000
                });

                // Click on Accounts category
                console.log("🖱️ Clicking on Accounts category...");
                const accountsButton = page.locator('div.g-nav-btn:has-text("Accounts")');

                if (await accountsButton.count() > 0) {
                    await accountsButton.click();

                    // Wait for the page to fully load
                    await page.waitForLoadState('networkidle');
                    console.log("✅ Successfully navigated to Accounts section");
                } else {
                    console.log("❌ Could not find Accounts button after login");
                }
            } else {
                throw new Error("Login failed");
            }
        }

        console.log("✅ Authentication and navigation process completed successfully!");
    } catch (error) {
        console.error("❌ Process failed:", error.message);
    } finally {
        rl.close();

        if (browser) {
            console.log("⚠️ Browser kept open for debugging. Close it manually when done.");
        }
    }
}

main();
