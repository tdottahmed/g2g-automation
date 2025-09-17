import { chromium } from "playwright";
import { isLoggedIn, loadAuthState, loginWithOTP, saveAuthState } from "./utils/auth.js";
import { rl } from "./utils/index.js";
import { navigateToAccounts } from "./utils/sell.js";
import { fillOfferForm } from "./utils/offerDetails.js"; // Import from the new file

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
            } else {
                throw new Error("Login failed");
            }
        }

        // Navigate to accounts section
        const navSuccess = await navigateToAccounts(page);
        if (!navSuccess) {
            throw new Error("Failed to navigate to accounts section");
        }

        // Fill the offer form
        const formFilled = await fillOfferForm(page);
        if (!formFilled) {
            throw new Error("Failed to fill offer form");
        }

        console.log("✅ Offer creation process completed successfully!");

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
