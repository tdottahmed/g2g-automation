
// Select Town Hall Level
export async function selectTownHallLevel(page, level = "12") {
    try {
        console.log(`🎯 Selecting Town Hall Level: ${level}`);

        // Wait for the form to load
        await page.waitForSelector('div:has-text("Town Hall Level")', { timeout: 10000 });

        // Find the dropdown button for Town Hall Level
        // Using a more direct approach based on the HTML structure
        const dropdownButton = page.locator('div:has-text("Town Hall Level") + div button:has-text("Please select")');

        if (await dropdownButton.count() > 0) {
            await dropdownButton.click();
            console.log("✅ Opened Town Hall Level dropdown");

            // Wait for the dropdown to appear
            await page.waitForSelector('input[placeholder="Type to filter"]', { timeout: 5000 });

            // Type the level in the search field
            const searchInput = page.locator('input[placeholder="Type to filter"]');
            await searchInput.fill(level);
            console.log(`🔍 Typed level: ${level}`);

            // Wait a moment for the search results to filter
            await page.waitForTimeout(1000);

            // Try to find and click the option
            // Using a more specific selector for the option
            const option = page.locator(`.q-item .q-item__section:has-text("${level}")`);

            if (await option.count() > 0) {
                await option.click();
                console.log(`✅ Selected Town Hall Level: ${level}`);

                // Wait for the dropdown to close
                await page.waitForSelector('input[placeholder="Type to filter"]', { state: 'hidden', timeout: 3000 });
                return true;
            } else {
                console.log(`❌ Option ${level} not found`);

                // Debug: List all available options
                const allOptions = await page.$$eval('.q-item', options =>
                    options.map(option => option.textContent.trim())
                );
                console.log("Available options:", allOptions);

                await page.keyboard.press('Escape');
                return false;
            }
        } else {
            console.log("❌ Town Hall Level dropdown button not found");

            // Alternative approach: Try to find any dropdown button
            const allDropdowns = await page.$$('button:has-text("Please select")');
            console.log(`Found ${allDropdowns.length} dropdown buttons total`);

            if (allDropdowns.length > 0) {
                console.log("Trying first dropdown button...");
                await allDropdowns[0].click();

                // Continue with the selection process
                await page.waitForSelector('input[placeholder="Type to filter"]', { timeout: 5000 });
                const searchInput = page.locator('input[placeholder="Type to filter"]');
                await searchInput.fill(level);
                await page.waitForTimeout(1000);

                const option = page.locator(`.q-item .q-item__section:has-text("${level}")`);
                if (await option.count() > 0) {
                    await option.click();
                    console.log(`✅ Selected Town Hall Level: ${level}`);
                    return true;
                }
            }

            return false;
        }
    } catch (error) {
        console.error("❌ Failed to select Town Hall Level:", error.message);
        await page.keyboard.press('Escape').catch(() => {});
        return false;
    }
}

// Simple test function
export async function testTownHallSelection(page, level = "12") {
    try {
        const success = await selectTownHallLevel(page, level);
        if (success) {
            console.log("✅ Town Hall Level selection successful!");
            return true;
        } else {
            console.log("❌ Town Hall Level selection failed");
            return false;
        }
    } catch (error) {
        console.error("❌ Town Hall Level selection test failed:", error.message);
        return false;
    }
}
