import { chromium } from "playwright";
import {
    isLoggedIn,
    loadAuthState,
    loginWithOTP,
    saveAuthState,
} from "./utils/auth.js";
import { rl, humanDelay } from "./utils/index.js";
import {
    navigateToAccountsSection,
    clickContinueButton,
} from "./utils/sell.js";
import {
    selectChampionLevel,
    selectKingLevel,
    selectQueenLevel,
    selectTownHallLevel,
    selectWardenLevel,
} from "./utils/offerDetails.js";

export const CONFIG = {
    authFile: "g2g_auth_state.json",
    baseUrl: "https://www.g2g.com",
    credentials: {
        email: "recoveryth0000@gmail.com",
        password: "@#aocy123%&",
    },
    headless: false,
    slowMo: 120,
};

async function main() {
    let browser = null;

    try {
        browser = await chromium.launch({
            headless: CONFIG.headless,
            slowMo: CONFIG.slowMo,
            args: ["--start-maximized"],
        });

        const context = await browser.newContext({
            viewport: { width: 1366, height: 768 },
        });

        const page = await context.newPage();

        // Load session
        const hasAuthState = await loadAuthState(context);
        console.log("Auth States:", hasAuthState);

        let loggedIn = hasAuthState && (await isLoggedIn(page));
        if (!loggedIn) {
            console.log("❌ Performing fresh login...");
            await page.goto(`${CONFIG.baseUrl}/login`, {
                waitUntil: "networkidle",
            });
            await page.type('input[name="email"]', CONFIG.credentials.email);
            await page.type(
                'input[name="password"]',
                CONFIG.credentials.password
            );
            await page.keyboard.press("Enter");
            await loginWithOTP(page);
            await saveAuthState(context);
        } else {
            console.log("✅ Using existing session");
        }

        // Navigate Accounts **before Continue**
        const navSuccess = await navigateToAccountsSection(page);
        if (!navSuccess)
            throw new Error("Failed to navigate to Accounts section");

        // Click Continue button → now we are on Offer creation page
        const continueClicked = await clickContinueButton(page);
        if (!continueClicked)
            throw new Error("Failed to click Continue button");

        await page.waitForLoadState("domcontentloaded");
        await page.waitForTimeout(2000); // lazy-render buffer

        await selectTownHallLevel(page, "12");
        await selectKingLevel(page, "65");
        await selectQueenLevel(page, "65");
        await selectWardenLevel(page, "40");
        await selectChampionLevel(page, "25");

        console.log("✅ Full flow completed!");
    } catch (error) {
        console.error("❌ Process failed:", error.message);
    } finally {
        rl.close();
        if (browser) console.log("⚠️ Browser kept open for debugging.");
    }
}

main();
