export async function selectDropdownOption(page, labelText, value) {
    try {
        console.log(`🎯 Selecting ${labelText}: ${value}`);

        await page.waitForLoadState("domcontentloaded");
        await page.waitForTimeout(1500);

        // Find the label
        const label = page.locator(
            `div.text-font-2nd:has-text("${labelText}")`
        );
        await label.waitFor({ timeout: 20000 });

        // Find the dropdown button in the same row as label
        const dropdownButton = label.locator(
            'xpath=ancestor::div[contains(@class,"col-12")]//button[contains(@class,"g-btn-select")]'
        );
        if ((await dropdownButton.count()) === 0) {
            console.log(`❌ Could not find ${labelText} dropdown button`);
            return false;
        }

        // Click the button
        await dropdownButton.first().click({ force: true });
        console.log(`✅ Clicked ${labelText} dropdown button`);
        await page.waitForTimeout(500);

        // Now locate any visible dropdown menu globally
        const dropdownMenu = page.locator(".q-menu:visible");
        await dropdownMenu
            .first()
            .waitFor({ timeout: 10000 })
            .catch(() => {
                console.log(`❌ Dropdown menu did not appear for ${labelText}`);
                return false;
            });

        // Search box inside dropdown (if exists)
        const searchInput = dropdownMenu.locator(
            'input[placeholder="Type to filter"]'
        );
        if ((await searchInput.count()) > 0) {
            await searchInput.fill(value);
            await page.waitForTimeout(500);
        }

        // Select the option
        const option = dropdownMenu.locator(`.q-item:has-text("${value}")`);
        if ((await option.count()) > 0) {
            await option.first().click({ force: true });
            console.log(`✅ Selected ${labelText}: ${value}`);
            return true;
        }

        console.log(`❌ No option found for ${labelText} ${value}`);
        await page.keyboard.press("Escape").catch(() => {});
        return false;
    } catch (error) {
        console.error(`❌ Failed to select ${labelText}:`, error.message);
        await page.keyboard.press("Escape").catch(() => {});
        return false;
    }
}

// 🔹 Convenience wrappers
export const selectTownHallLevel = (page, level) =>
    selectDropdownOption(page, "Town Hall Level", level);

export const selectKingLevel = (page, level) =>
    selectDropdownOption(page, "King Level", level);

export const selectQueenLevel = (page, level) =>
    selectDropdownOption(page, "Queen Level", level);

export const selectWardenLevel = (page, level) =>
    selectDropdownOption(page, "Warden Level", level);

export const selectChampionLevel = (page, level) =>
    selectDropdownOption(page, "Champion Level", level);
