// scripts/automation/post-offers.js
import { chromium } from "playwright";
import readline from "readline";

(async () => {
    const browser = await chromium.launch({
        headless: false,
        slowMo: 80,
        args: ["--disable-blink-features=AutomationControlled"],
    });

    const context = await browser.newContext({
        userAgent:
            "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 " +
            "(KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36",
        viewport: { width: 1366, height: 768 },
    });

    const page = await context.newPage();

    // helpers
    const delay = (ms) => new Promise((res) => setTimeout(res, ms));
    async function humanType(selector, text) {
        for (const char of text) {
            await page.type(selector, char);
            await delay(Math.floor(Math.random() * 150) + 50); // 50–200ms delay
        }
    }

    // Go to homepage
    await page.goto("https://www.g2g.com", { waitUntil: "domcontentloaded" });

    // Human-like scroll
    await page.mouse.wheel(0, 200);
    await delay(1200);

    // Click login button
    await page.click("a[href*='login']");

    // Wait for login form
    await page.waitForSelector('form button[type="submit"]');

    // Credentials
    const EMAIL = "recoveryth0000@gmail.com";
    const PASSWORD = "@#aocy123%&";

    // Fill login form (with human typing)
    await humanType('input[data-attr="username-input"]', EMAIL);
    await humanType('input[type="password"]', PASSWORD);

    // Submit
    await Promise.all([
        page.waitForNavigation({ waitUntil: "networkidle" }),
        page.click('form button[type="submit"]')
    ]);
    // Step 4: Navigate to Create Offer page
    await page.goto("https://www.g2g.com/sell/create", { waitUntil: "domcontentloaded" });
    console.log("✅ On Create Offer page.");

    // Step 5: Select Category = Accounts
    await page.waitForSelector('select[name="category"]');
    await page.selectOption('select[name="category"]', { label: "Accounts" });
    console.log("✅ Category selected: Accounts");

    // Step 6: Wait a little before selecting Brand
    await page.waitForTimeout(2000);

    // Step 7: Select Brand = Clash of Clans (Global)
    await page.waitForSelector('select[name="brand"]');
    await page.selectOption('select[name="brand"]', { label: "Clash Of Clans (Global)" });
    console.log("✅ Brand selected: Clash Of Clans (Global)");


    // Keep browser open for debugging
    await browser.close();
})();
