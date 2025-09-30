import { humanDelay } from "./index.js";

export async function navigateToSellOffers(page) {
    try {
        console.log("🌐 Navigating to sell offers page...");
        await page.goto("https://www.g2g.com/offers/sell", {
            waitUntil: "networkidle",
        });
        return true;
    } catch (error) {
        console.error("❌ Failed to navigate to sell offers:", error.message);
        return false;
    }
}

export async function clickAccountsCategory(page) {
    try {
        console.log("🖱️ Clicking on Accounts category...");

        // Wait for nav buttons area to appear
        await page.waitForSelector(".g-nav-btn, .g-card-no-deco", {
            timeout: 10000,
        });

        // Inspect all .g-nav-btn nodes and match visible text
        const navButtons = page.locator(".g-nav-btn");
        const navCount = await navButtons.count();
        let accountsButton = null;

        for (let i = 0; i < navCount; i++) {
            const btn = navButtons.nth(i);
            let text = "";
            try {
                text = (await btn.innerText()).trim().replace(/\s+/g, " ");
            } catch (e) {
                // ignore and continue
            }
            if (text && text.toLowerCase().includes("accounts")) {
                accountsButton = btn;
                break;
            }
        }

        // Fallback: other common selectors if the .g-nav-btn loop didn't find it
        if (!accountsButton) {
            const fallbacks = [
                page.locator('button:has-text("Accounts")'),
                page.locator('a:has-text("Accounts")'),
                page.locator("text=Accounts"),
            ];
            for (const loc of fallbacks) {
                if ((await loc.count()) > 0) {
                    accountsButton = loc.first();
                    break;
                }
            }
        }

        if (!accountsButton) {
            console.log("❌ Could not find Accounts button");
            return false;
        }

        // Prefer a clearly clickable child (cursor-pointer, role=button, <button>, <a>)
        let clickable = accountsButton.locator(
            '.cursor-pointer, [role="button"], button, a'
        );
        if ((await clickable.count()) === 0) clickable = accountsButton;

        await clickable.scrollIntoViewIfNeeded();
        await humanDelay(150, 350);

        // Make sure it's visible (best-effort) and interact
        await clickable
            .waitFor({ state: "visible", timeout: 5000 })
            .catch(() => {});
        await clickable.hover().catch(() => {});
        await humanDelay(120, 250);

        await clickable.click({ force: true });
        console.log("✅ Successfully clicked Accounts");

        return true;
    } catch (error) {
        console.error("❌ Failed to click Accounts category:", error.message);
        return false;
    }
}

export async function handleProtectAccountPopup(page) {
    try {
        console.log("🔍 Checking for 'Protect Your Account' popup...");

        await page.waitForTimeout(1000);

        const popupSelector = '.q-card:has-text("Protect Your Account")';
        const popupExists = await page
            .waitForSelector(popupSelector, {
                timeout: 5000,
                state: "attached",
            })
            .catch(() => false);

        if (popupExists) {
            console.log("🛡️ Popup detected");

            const checkbox = page.locator(
                '.q-checkbox:has-text("Do not remind me again")'
            );
            if ((await checkbox.count()) > 0) {
                await checkbox.click();
                await humanDelay(100, 300);
            }

            const understoodButton = page.locator(
                'button:has-text("Understood")'
            );
            if ((await understoodButton.count()) > 0) {
                await understoodButton.click();
                console.log("✅ Clicked 'Understood' button");

                await page.waitForSelector(popupSelector, {
                    state: "detached",
                    timeout: 5000,
                });
                console.log("✅ Popup dismissed successfully");
            }
        } else {
            console.log("✅ No popup detected");
        }

        return true;
    } catch (error) {
        console.error("❌ Failed to handle popup:", error.message);
        return false;
    }
}

export async function selectGameBrand(
    page,
    gameName = "Clash of Clans (Global)"
) {
    try {
        console.log(`🎮 Selecting game brand: ${gameName}`);

        await page.waitForTimeout(4000);

        await page.waitForSelector('button:has-text("Select brand")', {
            timeout: 5000,
        });
        const selectButton = page.locator('button:has-text("Select brand")');
        await selectButton.click();
        await page.waitForSelector(".g-shadow.q-card", { timeout: 5000 });

        const searchInput = page.locator('input[placeholder="Type to filter"]');
        await searchInput.fill(gameName);
        await page.waitForTimeout(6000);

        let gameSelected = false;
        const exactGameOption = page.locator(`text=/${gameName}/i`);
        if ((await exactGameOption.count()) > 0) {
            await exactGameOption.click();
            gameSelected = true;
        } else {
            const allOptions = await page.$$(".q-item");
            for (const option of allOptions) {
                const optionText = await option.textContent();
                if (
                    optionText &&
                    optionText.toLowerCase().includes(gameName.toLowerCase())
                ) {
                    await option.click();
                    gameSelected = true;
                    break;
                }
            }
        }

        if (!gameSelected) {
            console.log(`❌ Could not find game: ${gameName}`);
            await page.keyboard.press("Escape");
            return false;
        }

        await page.waitForSelector(".g-shadow.q-card", {
            state: "detached",
            timeout: 5000,
        });
        await page.waitForTimeout(1000);

        console.log(`✅ Selected game: ${gameName}`);
        return true;
    } catch (error) {
        console.error("❌ Failed to select game brand:", error.message);
        await page.keyboard.press("Escape").catch(() => {});
        return false;
    }
}

export async function clickContinueButton(page) {
    try {
        console.log("➡️ Clicking Continue button...");

        await page.waitForSelector('a:has-text("Continue")', {
            timeout: 5000,
        });
        const continueButton = page.locator('a:has-text("Continue")');
        await continueButton.click();

        console.log("✅ Continue button clicked successfully");
        return true;
    } catch (error) {
        console.error("❌ Failed to click Continue button:", error.message);
        return false;
    }
}

/**
 * Navigate to Accounts section **before** clicking Continue
 */
export async function navigateToAccountsSection(page) {
    const navSuccess = await navigateToSellOffers(page);
    if (!navSuccess) return false;

    const clickSuccess = await clickAccountsCategory(page);
    if (!clickSuccess) return false;

    const popupHandled = await handleProtectAccountPopup(page);
    if (!popupHandled) return false;

    const gameSelected = await selectGameBrand(page);
    if (!gameSelected) return false;

    console.log(
        "✅ Successfully navigated to Accounts section (before Continue)"
    );
    return true;
}
