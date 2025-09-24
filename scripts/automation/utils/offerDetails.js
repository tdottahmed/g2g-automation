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

        // Now Locate next sibling of the dropdown button which has the input field for the filter and options
        const dropdownWrapper = dropdownButton.locator(
            " + div.relative-position > div:not(.g-input-error)"
        );

        if ((await dropdownWrapper.count()) === 0) {
            console.log(`❌ Could not find ${labelText} dropdown wrapper`);
            return false;
        }

        const filterInput = dropdownWrapper.locator(
            'label input[placeholder="Type to filter"]'
        );
        if ((await filterInput.count()) == 0) {
            console.log(`❌ Could not find ${labelText} filter input`);
            return false;
        }
        await filterInput.first().fill(value);
        await page.waitForTimeout(500);

        const dropdownMenu = dropdownWrapper.locator(
            "div:nth-child(2) .q-virtual-scroll__content"
        );
        if ((await dropdownMenu.count()) === 0) {
            console.log(`❌ Could not find ${labelText} dropdown menu`);
            return false;
        }
        const option = dropdownMenu.locator(
            `.q-item .q-item__section:has-text("${value}")`
        );
        if ((await option.count()) === 0) {
            console.log(`❌ No option found for ${labelText} ${value}`);
            await page.keyboard.press("Escape").catch(() => {});
            return false;
        }

        const firstOption = await option.first();

        // Check First option inerthtml includes the value (case insensitive)
        const innerHTML = await firstOption.innerHTML();
        if (!innerHTML.toLowerCase().includes(value.toLowerCase())) {
            console.log(
                `❌ No matching option found for ${labelText} ${value}`
            );
            await page.keyboard.press("Escape").catch(() => {});
            return false;
        }

        await firstOption.click({ force: true });
        console.log(`✅ Selected ${labelText}: ${value}`);
        return true;
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
