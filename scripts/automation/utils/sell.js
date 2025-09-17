export async function navigateToSellOffers(page) {
    try {
        console.log("🌐 Navigating to sell offers page...");
        await page.goto('https://www.g2g.com/offers/sell', {
            waitUntil: 'networkidle',
            timeout: 30000
        });

        // Wait for the page to load completely
        console.log("⏳ Waiting for page to load...");
        await page.waitForSelector('.g-nav-btn', { timeout: 15000 });

        return true;
    } catch (error) {
        console.error("❌ Failed to navigate to sell offers:", error.message);
        return false;
    }
}

export async function clickAccountsCategory(page) {
    try {
        console.log("🖱️ Clicking on Accounts category...");

        // Using a more reliable selector approach
        const accountsButton = page.locator('div.g-nav-btn:has-text("Accounts")');

        if (await accountsButton.count() > 0) {
            await accountsButton.click();

            // Wait for the page to fully load after clicking
            console.log("⏳ Waiting for Accounts page to load...");
            await page.waitForLoadState('networkidle');
            await page.waitForTimeout(2000); // Additional wait for any dynamic content

            console.log("✅ Successfully navigated to Accounts section");
            return true;
        } else {
            console.log("❌ Could not find Accounts button");
            return false;
        }
    } catch (error) {
        console.error("❌ Failed to click Accounts category:", error.message);
        return false;
    }
}

export async function handleProtectAccountPopup(page) {
    try {
        console.log("🔍 Checking for 'Protect Your Account' popup...");

        // Wait for the popup to appear with a reasonable timeout
        const popupSelector = '.q-card:has-text("Protect Your Account")';
        const popupExists = await page.waitForSelector(popupSelector, {
            timeout: 5000,
            state: 'attached'
        }).catch(() => false);

        if (popupExists) {
            console.log("🛡️ 'Protect Your Account' popup detected");

            const checkboxSelector = '.q-checkbox:has-text("Do not remind me again")';
            const checkbox = page.locator(checkboxSelector);

            if (await checkbox.count() > 0) {
                await checkbox.click();
                console.log("✅ Checked 'Do not remind me again'");
                await page.waitForTimeout(500);
            }

            // Click the "Understood" button
            const understoodButton = page.locator('button:has-text("Understood")');

            if (await understoodButton.count() > 0) {
                await understoodButton.click();
                console.log("✅ Clicked 'Understood' button");

                // Wait for the popup to disappear
                await page.waitForSelector(popupSelector, { state: 'detached', timeout: 5000 });
                console.log("✅ Popup dismissed successfully");

                return true;
            } else {
                console.log("❌ Could not find 'Understood' button");
                return false;
            }
        } else {
            console.log("ℹ️ No 'Protect Your Account' popup found");
            return true; // No popup is also a success case
        }
    } catch (error) {
        console.error("❌ Failed to handle popup:", error.message);
        return false;
    }
}

export async function selectGameBrand(page, gameName = "Clash of Clans (Global)") {
    try {
        console.log(`🎮 Selecting game brand: ${gameName}`);

        // Wait for the select button to be available
        await page.waitForSelector('button:has-text("Select brand")', { timeout: 10000 });

        // Click the select button to open the dropdown
        const selectButton = page.locator('button:has-text("Select brand")');
        await selectButton.click();
        console.log("✅ Opened game selection dropdown");

        // Wait for the dropdown to appear
        await page.waitForSelector('.g-shadow.q-card', { timeout: 5000 });

        // Type the game name in the search field
        const searchInput = page.locator('input[placeholder="Type to filter"]');
        await searchInput.fill(gameName);
        console.log(`🔍 Searching for: ${gameName}`);

        // Wait a moment for the search results to filter
        await page.waitForTimeout(2000);

        // Try to find and click the game option using multiple strategies
        let gameSelected = false;

        // Strategy 1: Look for exact text match
        const exactGameOption = page.locator(`text=/${gameName}/i`);
        if (await exactGameOption.count() > 0) {
            await exactGameOption.click();
            console.log(`✅ Selected: ${gameName}`);
            gameSelected = true;
        }

        // Strategy 2: If exact match not found, try case-insensitive search
        if (!gameSelected) {
            const allOptions = await page.$$('.q-item');
            for (const option of allOptions) {
                const optionText = await option.textContent();
                if (optionText && optionText.toLowerCase().includes(gameName.toLowerCase())) {
                    await option.click();
                    console.log(`✅ Selected: ${optionText.trim()}`);
                    gameSelected = true;
                    break;
                }
            }
        }

        if (!gameSelected) {
            console.log(`❌ Could not find game: ${gameName}`);
            // Try to close the dropdown if we couldn't select a game
            await page.keyboard.press('Escape');
            return false;
        }

        // Wait for the dropdown to close and selection to be applied
        await page.waitForSelector('.g-shadow.q-card', { state: 'detached', timeout: 5000 });

        return true;
    } catch (error) {
        console.error("❌ Failed to select game brand:", error.message);
        // Try to close the dropdown if it's still open
        await page.keyboard.press('Escape').catch(() => {});
        return false;
    }
}

export async function clickContinueButton(page) {
    try {
        console.log("🖱️ Looking for Continue button...");

        // Wait for the Continue button to be available
        await page.waitForSelector('a:has-text("Continue")', { timeout: 10000 });

        // Click the Continue button
        const continueButton = page.locator('a:has-text("Continue")');
        await continueButton.click();
        console.log("✅ Clicked Continue button");

        // Wait for navigation to complete
        await page.waitForLoadState('networkidle');
        console.log("✅ Navigation after Continue completed");

        return true;
    } catch (error) {
        console.error("❌ Failed to click Continue button:", error.message);
        return false;
    }
}

export async function navigateToAccounts(page) {
    try {
        const navSuccess = await navigateToSellOffers(page);
        if (!navSuccess) return false;

        const clickSuccess = await clickAccountsCategory(page);
        if (!clickSuccess) return false;

        // Handle the protect account popup if it appears
        const popupHandled = await handleProtectAccountPopup(page);
        if (!popupHandled) return false;

        // Select the game brand
        const gameSelected = await selectGameBrand(page);
        if (!gameSelected) return false;

        // Click the Continue button
        const continueClicked = await clickContinueButton(page);

        return continueClicked;
    } catch (error) {
        console.error("❌ Failed to navigate to Accounts:", error.message);
        return false;
    }
}
