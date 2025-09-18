// Default offer data structure
export const DEFAULT_OFFER_DATA = {
    townHallLevel: 12,
    kingLevel: 65,
    queenLevel: 65,
    wardenLevel: 40,
    championLevel: 25,
    title: "Max TH12 with 65/65/40/25 Heroes",
    description: "This is a fully maxed Town Hall 12 account with high-level heroes. Perfect for competitive play!\n\n- Fully maxed defenses and walls\n- All troops and spells upgraded\n- Plenty of resources\n- Never been banned or flagged\n- Full access to email\n\nReady for immediate delivery!",
    price: 50.00,
    stock: 5,
    deliveryMethod: "Auto delivery",
    mediaUrls: [
        "https://imgur.com/a/example1",
        "https://imgur.com/a/example2",
        "https://imgur.com/a/example3"
    ],
    mediaTitles: [
        "Town Hall Overview",
        "Hero Levels Showcase",
        "Defense Layout"
    ]
};

// Wait for the offer form to load
export async function waitForOfferForm(page) {
    try {
        console.log("⏳ Waiting for offer form to load...");
        await page.waitForSelector('div:has-text("Offer details")', { timeout: 15000 });
        console.log("✅ Offer form loaded successfully");
        return true;
    } catch (error) {
        console.error("❌ Offer form failed to load:", error.message);
        return false;
    }
}

// Select a level from dropdown (fixed)
export async function selectDropdownValue(page, labelText, value) {
    try {
        console.log(`🎯 Selecting ${labelText}: ${value}`);

        // Locate label container
        const container = page.locator(
            `//div[contains(@class,"text-font-2nd") and normalize-space(text())="${labelText}"]/ancestor::div[contains(@class,"col-12")]`
        );

        // Locate button inside that container
        const dropdown = container.locator('button[role="button"]');
        await dropdown.waitFor({ state: "visible", timeout: 10000 });
        await dropdown.click();
        console.log(`✅ Opened dropdown for ${labelText}`);

        // Wait for dropdown panel
        await page.waitForSelector('.g-shadow.q-card', { timeout: 5000 });

        // Search by typing
        const searchInput = page.locator('input[placeholder="Type to filter"]');
        await searchInput.fill(String(value));
        console.log(`🔍 Searching for: ${value}`);

        await page.waitForTimeout(1500);

        // Try exact match
        const exactOption = page.locator(`.q-item:has-text("${value}")`);
        if (await exactOption.count() > 0) {
            await exactOption.first().click();
            console.log(`✅ Selected: ${value} for ${labelText}`);
        } else {
            // Try fuzzy match
            const allOptions = await page.$$('.q-item');
            let matched = false;
            for (const option of allOptions) {
                const optionText = await option.textContent();
                if (optionText && optionText.includes(String(value))) {
                    await option.click();
                    console.log(`✅ Selected: ${optionText.trim()} for ${labelText}`);
                    matched = true;
                    break;
                }
            }
            if (!matched) {
                console.log(`❌ Could not find option for ${labelText} = ${value}`);
                await page.keyboard.press('Escape');
                return false;
            }
        }

        await page.waitForSelector('.g-shadow.q-card', { state: 'detached', timeout: 5000 });
        return true;
    } catch (error) {
        console.error(`❌ Failed to select ${labelText}:`, error.message);
        await page.keyboard.press('Escape').catch(() => {});
        return false;
    }
}

// Fill text field
async function fillTextField(page, fieldName, value) {
    try {
        console.log(`📝 Filling ${fieldName}: ${value}`);

        let fieldSelector;
        if (fieldName === "Title") {
            fieldSelector = 'input[placeholder="Offer title"]';
        } else if (fieldName === "Description") {
            fieldSelector = 'textarea[placeholder="Type offer description here"]';
        } else if (fieldName === "Default price") {
            fieldSelector = 'input[id*="price"], input[placeholder*="price"]';
        } else if (fieldName === "Minimum purchase quantity") {
            fieldSelector = 'input[placeholder*="quantity"], input[name*="quantity"]';
        }

        if (fieldSelector) {
            const field = page.locator(fieldSelector);
            await field.fill(value.toString());
            console.log(`✅ Filled ${fieldName}`);
            return true;
        } else {
            console.log(`❌ Field selector for ${fieldName} not defined`);
            return false;
        }
    } catch (error) {
        console.error(`❌ Failed to fill ${fieldName}:`, error.message);
        return false;
    }
}

// Set delivery method
async function setDeliveryMethod(page, method) {
    try {
        console.log(`🚚 Setting delivery method: ${method}`);

        let methodSelector;
        if (method === "Auto delivery") {
            methodSelector = 'button:has-text("Auto delivery")';
        } else if (method === "Manual delivery") {
            methodSelector = 'button:has-text("Manual delivery")';
        }

        if (methodSelector) {
            const methodButton = page.locator(methodSelector);
            if (await methodButton.count() > 0) {
                await methodButton.click();
                console.log(`✅ Set delivery method to: ${method}`);
                return true;
            } else {
                console.log(`❌ Delivery method button not found: ${method}`);
                return false;
            }
        } else {
            console.log(`❌ Delivery method selector not defined for: ${method}`);
            return false;
        }
    } catch (error) {
        console.error(`❌ Failed to set delivery method:`, error.message);
        return false;
    }
}

// Add media to gallery
async function addMediaToGallery(page, mediaUrl, mediaTitle, index = 0) {
    try {
        console.log(`🖼️ Adding media ${index + 1}: ${mediaTitle}`);

        const titleInputs = await page.$$('input[placeholder*="title" i], input[placeholder*="Title" i]');
        if (titleInputs.length > index) {
            await titleInputs[index].fill(mediaTitle);
            console.log(`✅ Added title for media ${index + 1}`);
        }

        const urlInputs = await page.$$('input[placeholder*="URL" i], input[placeholder*="url" i], input[placeholder*="link" i]');
        if (urlInputs.length > index) {
            await urlInputs[index].fill(mediaUrl);
            console.log(`✅ Added URL for media ${index + 1}`);
        }

        const addButtons = await page.$$('button:has-text("Add Media"), button:has-text("Add media")');
        if (addButtons.length > index) {
            await addButtons[index].click();
            console.log(`✅ Clicked add media button ${index + 1}`);
            await page.waitForTimeout(1000);
        }

        return true;
    } catch (error) {
        console.error(`❌ Failed to add media ${index + 1}:`, error.message);
        return false;
    }
}

// Fill pricing information
async function fillPricing(page, priceData) {
    try {
        console.log(`💰 Setting price: ${priceData.price}`);

        const priceInput = page.locator('input[id*="price"], input[placeholder*="price"]');
        if (await priceInput.count() > 0) {
            await priceInput.fill(priceData.price.toString());
            console.log(`✅ Set price to: ${priceData.price}`);
            return true;
        } else {
            console.log("❌ Price input field not found");
            return false;
        }
    } catch (error) {
        console.error("❌ Failed to set price:", error.message);
        return false;
    }
}

// Fill stock quantity
async function fillStockQuantity(page, quantity) {
    try {
        console.log(`📦 Setting stock quantity: ${quantity}`);

        const stockInput = page.locator('input[placeholder*="quantity"], input[name*="quantity"]');
        if (await stockInput.count() > 0) {
            await stockInput.fill(quantity.toString());
            console.log(`✅ Set stock quantity to: ${quantity}`);
            return true;
        } else {
            console.log("❌ Stock quantity input field not found");
            return false;
        }
    } catch (error) {
        console.error("❌ Failed to set stock quantity:", error.message);
        return false;
    }
}

// Main function to fill the offer form
export async function fillOfferForm(page, offerData = DEFAULT_OFFER_DATA) {
    try {
        const formLoaded = await waitForOfferForm(page);
        if (!formLoaded) return false;

        // Select all the level dropdowns
        const levelSelectors = [
            { type: "Town Hall Level", value: offerData.townHallLevel },
            { type: "King Level", value: offerData.kingLevel },
            { type: "Queen Level", value: offerData.queenLevel },
            { type: "Warden Level", value: offerData.wardenLevel },
            { type: "Champion Level", value: offerData.championLevel }
        ];

        for (const level of levelSelectors) {
            const success = await selectDropdownValue(page, level.type, level.value);
            if (!success) {
                console.log(`⚠️ Warning: Could not select ${level.type} ${level.value}`);
            }
        }

        await fillTextField(page, "Title", offerData.title);
        await fillTextField(page, "Description", offerData.description);

        await fillPricing(page, offerData);
        await fillStockQuantity(page, offerData.stock);

        if (offerData.mediaUrls && offerData.mediaUrls.length > 0) {
            for (let i = 0; i < offerData.mediaUrls.length; i++) {
                const title = offerData.mediaTitles?.[i] ?? `Media ${i + 1}`;
                await addMediaToGallery(page, offerData.mediaUrls[i], title, i);
            }
        }

        await setDeliveryMethod(page, offerData.deliveryMethod);

        console.log("✅ Offer form filled successfully!");
        return true;
    } catch (error) {
        console.error("❌ Failed to fill offer form:", error.message);
        return false;
    }
}
