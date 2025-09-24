
async function selectDropdownOption(page, btn, value) {
    try {
        await btn.click({ force: true });
        console.log(`🖱️ Clicked ${labelText} dropdown button in row ${i}`);
        await page.waitForTimeout(800);

        // Locate wrapper relative to this button
        const dropdownWrapper = btn.locator(
            " + div.relative-position > div:not(.g-input-error)"
        );
        if ((await dropdownWrapper.count()) === 0) {
            console.log(`❌ Could not find ${labelText} dropdown wrapper`);
            return false;
        }

        // Filter input
        const filterInput = dropdownWrapper.locator(
            'label input[placeholder="Type to filter"]'
        );
        if ((await filterInput.count()) === 0) return false;

        await filterInput.first().fill(value);
        await page.waitForTimeout(500);

        // Dropdown menu
        const dropdownMenu = dropdownWrapper.locator(
            "div:nth-child(2) .q-virtual-scroll__content"
        );
        if ((await dropdownMenu.count()) === 0) return false;

        const option = dropdownMenu.locator(
            `.q-item .q-item__section:has-text("${value}")`
        );
        if ((await option.count()) === 0) {
            await page.keyboard.press("Escape").catch(() => {});
            return false;
        }

        const firstOption = option.first();
        const innerHTML = await firstOption.innerHTML();
        if (!innerHTML.toLowerCase().includes(value.toLowerCase())) {
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

/**
 *
 * @param {*} page
 * @param {Levels} levels
 */
export async function selectLevels(page, levels) {
    await page.waitForLoadState("domcontentloaded");
    await page.waitForTimeout(1500);

    // Get all form rows
    const items = page.locator(
        ".g-cu-form-card>.g-cu-form-card__section:nth-child(2) .col-12 .row"
    );
    const itemCount = await items.count();
    console.log(`Found ${itemCount} form rows`);

    for (let i = 0; i < itemCount; i++) {
        console.log(`Processing row ${i}...`);

        const label = row.locator("div:nth-child(1) .text-font-2nd");
        const labelText = (await label.first().innerText()).trim();
        if ((await label.count()) === 0) {
            console.log(`Skipping row ${i}, label not found`);
            return false;
        }
        const value = levels[labelText];
        if (!value) {
            console.log(`Skipping row ${i}, no value found for ${labelText}`);
            return false;
        }
        console.log(
            `Processing row ${i} with label ${labelText} and value ${value}`
        );
        const row = items.nth(i);
        // Find label in this row
        const dropdownButton = row.locator("div:nth-child(2) .g-btn-select");
        if ((await dropdownButton.count()) === 0) {
            console.log(`❌ Could not find ${labelText} dropdown button`);
            return false;
        }

        await selectDropdownOption(page, dropdownButton.first(), value);
    }
}
