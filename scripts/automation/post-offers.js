import { fileURLToPath } from "url";
import { chromium } from "playwright";
import {
    isLoggedIn,
    loadAuthState,
    loginWithOTP,
    saveAuthState,
} from "./utils/auth.js";
import { rl } from "./utils/index.js";
import {
    navigateToAccountsSection,
    clickContinueButton,
} from "./utils/sell.js";
import {
    fillOfferForm,
    submitFormAndAddNew,
    submitForm,
} from "./utils/form-filler.js";

// Configuration populated from the base64 JSON argument passed by the Laravel job
let CONFIG = {
    authFile: "",
    baseUrl: "https://www.g2g.com",
    credentials: { email: "", password: "" },
    headless: false,
    slowMo: 120,
    debug: true,
};

async function main() {
    let templatesData = [];
    try {
        const encodedData = process.argv[2];
        if (!encodedData) {
            throw new Error("No data provided from Laravel");
        }

        const decodedData = Buffer.from(encodedData, "base64").toString("utf8");
        templatesData = JSON.parse(decodedData);

        console.log(`📥 Received ${templatesData.length} templates from Laravel`);

        if (templatesData.length === 0) {
            throw new Error("No templates to process");
        }

        const firstTemplate = templatesData[0];
        CONFIG.credentials.email    = firstTemplate.user_email;
        CONFIG.credentials.password = firstTemplate.password;
        CONFIG.authFile             = firstTemplate.cookies;
    } catch (e) {
        console.error("❌ Failed to parse input from Laravel:", e.message);
        process.exit(1);
    }

    let browser = null;
    let context  = null;
    let page     = null;

    try {
        browser = await chromium.launch({
            headless: CONFIG.headless,
            slowMo: CONFIG.slowMo,
            args: ["--start-maximized"],
        });

        context = await browser.newContext({ viewport: { width: 1366, height: 768 } });
        page    = await context.newPage();

        console.log("🔐 Checking authentication state...");
        const hasAuthState = await loadAuthState(context, CONFIG.authFile);

        let loggedIn = hasAuthState && (await isLoggedIn(page, CONFIG.baseUrl));

        if (!loggedIn) {
            await context.clearCookies();
            const loginSuccess = await loginWithOTP(
                page,
                CONFIG.baseUrl,
                CONFIG.credentials.email,
                CONFIG.credentials.password
            );
            if (!loginSuccess) throw new Error("Login process failed");
            await saveAuthState(context, CONFIG.authFile);
        } else {
            console.log("✅ Using existing session");
        }

        for (let i = 0; i < templatesData.length; i++) {
            const templateData = templatesData[i];
            console.log(`\n🔄 Processing template ${i + 1}/${templatesData.length}: ${templateData.Title}`);

            if (i === 0) {
                const navSuccess = await navigateToAccountsSection(page);
                if (!navSuccess) throw new Error("Failed to navigate to Accounts section");
                const continueSuccess = await clickContinueButton(page);
                if (!continueSuccess) {
                    console.log("⚠️ Could not click Continue. Stopping.");
                    break;
                }
            }

            await fillOfferForm(page, templateData);

            if (i < templatesData.length - 1) {
                const success = await submitFormAndAddNew(page);
                if (!success) {
                    console.log("⚠️ Could not proceed to next offer, stopping.");
                    break;
                }
            } else {
                console.log("🚀 Submitting final offer...");
                await submitForm(page);
                await page.waitForTimeout(5000);
                console.log("✅ Final offer submitted!");
            }
        }

        console.log(`✅ All ${templatesData.length} templates processed!`);
    } catch (error) {
        console.error("❌ Process failed:", error.message);
        process.exitCode = 1;
    } finally {
        rl.close();
        if (!CONFIG.debug && browser) {
            await browser.close();
        } else {
            console.log("🛑 Debug mode — browser kept open.");
        }
    }
}

// Only run when invoked directly (not when imported)
if (process.argv[1] === fileURLToPath(import.meta.url)) {
    main();
}
