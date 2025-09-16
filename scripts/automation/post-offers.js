import { chromium } from "playwright";
import fs from "fs";
import path from "path";
import { fileURLToPath } from "url";

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const cookiesPath = path.join(__dirname, "cookies.json");

// Your login credentials (only used if cookie login fails)
const EMAIL = "developertanbir1@gmail.com";
const PASSWORD = "newpass@123";

(async () => {
    const browser = await chromium.launch({ headless: false, slowMo: 100 });
    const context = await browser.newContext();
    const page = await context.newPage();

    let loggedIn = false;

    // 1️⃣ If cookies.json exists → try load them
    if (fs.existsSync(cookiesPath)) {
        const cookies = JSON.parse(fs.readFileSync(cookiesPath, "utf-8"));
        await context.addCookies(cookies);
        console.log("🍪 Cookies loaded.");
    }

    // 2️⃣ Go to G2G
    await page.goto("https://www.g2g.com", { waitUntil: "domcontentloaded" });

// 3️⃣ Detect login state properly
//     if (await page.isVisible('a[href*="login"]')) {
        console.log("🔐 Login required, filling credentials...");

        await page.click('a[href*="login"]');
        await page.waitForSelector('form button[type="submit"]');

        await page.fill('input[data-attr="username-input"]', EMAIL);
        await page.fill('input[type="password"]', PASSWORD);

        await Promise.all([
            page.waitForNavigation({ waitUntil: "networkidle" }),
            page.click('form button[type="submit"]')
        ]);

        console.log("✅ Logged in via credentials.");

        // Save cookies
        const newCookies = await context.cookies();
        fs.writeFileSync(cookiesPath, JSON.stringify(newCookies, null, 2));
        console.log("💾 Cookies saved to cookies.json");

        loggedIn = true;
    // } else {
    //     console.log("✅ Already logged in using cookies.");
    //     loggedIn = true;
    // }
    //
    // if (!loggedIn) {
    //     console.error("❌ Login failed. Check credentials or OTP.");
    // } else {
    //     console.log("🚀 Ready for next steps (like create offer).");
    // }

    // await browser.close();
})();
