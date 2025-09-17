
// Default offer data structure
export const DEFAULT_OFFER_DATA = {
    townHallLevel: "12",
    kingLevel: "65",
    queenLevel: "65",
    wardenLevel: "40",
    championLevel: "25",
    title: "Max TH12 with 65/65/40/25 Heroes",
    description: "This is a fully maxed Town Hall 12 account with high-level heroes. Perfect for competitive play!\n\n- Fully maxed defenses and walls\n- All troops and spells upgraded\n- Plenty of resources\n- Never been banned or flagged\n- Full access to email\n\nReady for immediate delivery!",
    price: "50.00",
    stock: "5",
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

// Select a level from dropdown
async function selectLevel(page, levelType, levelValue) {
    try {
        console.log(`📋 Selecting ${levelType}: ${levelValue}`);

        // Find the dropdown button for the specific level type
        const dropdownXPath = `//div[contains(text(), "${levelType}")]/ancestor::div[contains(@class, "row")]//button[contains(text(), "Please select") or contains(@class,"q-field")]`;
        const dropdownButton = page.locator(`xpath=${dropdownXPath}`);

        if (await dropdownButton.count() > 0) {
            await dropdownButton.first().click();

            // Wait for dropdown/options to appear
            await page.waitForSelector('.q-menu .q-item, .q-dialog .q-item, .q-virtual-scroll__content .q-item', { timeout: 7000 }).catch(() => {});

            // Prefer selecting by value input (type and Enter) for Quasar selects
            let optionSelected = false;
            try {
                await page.keyboard.type(String(levelValue), { delay: 50 });
                await page.keyboard.press('Enter');
                optionSelected = true;
            } catch (_) {
                // ignore and fallback
            }

            // If still open or not selected, try role/attributes/text strategies
            const isMenuOpen = async () => {
                const menu = page.locator('.q-menu, .q-dialog');
                return (await menu.count()) > 0;
            };
            const ensureClosed = async () => {
                await page.waitForSelector('.q-menu, .q-dialog', { state: 'detached', timeout: 3000 }).catch(() => {});
            };

            if (await isMenuOpen()) {
                // Strategy 2: ARIA role=option by name
                const byRole = page.getByRole('option', { name: new RegExp(`^\\s*${String(levelValue).replace(/[.*+?^${}()|[\]\\]/g, '\\$&')}\\s*$`, 'i') }).first();
                if (await byRole.count()) {
                    await byRole.click();
                    optionSelected = true;
                }
            }

            if (await isMenuOpen() && !optionSelected) {
                // Strategy 3: Attributes carrying the value
                const attrSelector = [
                    `[data-value="${levelValue}"]`,
                    `[data-val="${levelValue}"]`,
                    `[aria-label="${levelValue}"]`
                ].join(', ');
                const byAttr = page.locator(attrSelector).first();
                if (await byAttr.count()) {
                    await byAttr.click();
                    optionSelected = true;
                }
            }

            if (await isMenuOpen() && !optionSelected) {
                // Strategy 4: Exact text match inside .q-item
                const exactText = page.locator(`.q-item:has-text("${levelValue}")`).first();
                if (await exactText.count()) {
                    await exactText.click();
                    optionSelected = true;
                } else {
                    // Strategy 5: Iterate all options and compare trimmed text
                    const allOptions = await page.$$('.q-item');
                    for (const opt of allOptions) {
                        const text = (await opt.textContent())?.trim();
                        if (text && text.toLowerCase() === String(levelValue).trim().toLowerCase()) {
                            await opt.click();
                            optionSelected = true;
                            break;
                        }
                    }
                }
            }

            // Ensure menu is closed if something was picked
            if (optionSelected) {
                await ensureClosed();
            } else {
                // Close dropdown if not selected
                await page.keyboard.press('Escape').catch(() => {});
            }

            // Verify selection by checking the control now displays the value
            const valueShownXPath = `//div[contains(text(), "${levelType}")]/ancestor::div[contains(@class, "row")]//*[self::button or self::div][contains(normalize-space(.), "${levelValue}")]`;
            const valueShown = page.locator(`xpath=${valueShownXPath}`);
            const verified = await valueShown.first().isVisible().catch(() => false);

            if (optionSelected && verified) {
                console.log(`✅ Selected ${levelType}: ${levelValue}`);
                return true;
            } else {
                console.log(`❌ Option ${levelValue} not found/verified for ${levelType}`);
                return false;
            }
        } else {
            console.log(`❌ Dropdown for ${levelType} not found`);
            return false;
        }
    } catch (error) {
        console.error(`❌ Failed to select ${levelType}:`, error.message);
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
            await field.fill(value);
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

        // Find all media title inputs
        const titleInputs = await page.$$('input[placeholder*="title" i], input[placeholder*="Title" i]');
        if (titleInputs.length > index) {
            await titleInputs[index].fill(mediaTitle);
            console.log(`✅ Added title for media ${index + 1}`);
        }

        // Find all media URL inputs
        const urlInputs = await page.$$('input[placeholder*="URL" i], input[placeholder*="url" i], input[placeholder*="link" i]');
        if (urlInputs.length > index) {
            await urlInputs[index].fill(mediaUrl);
            console.log(`✅ Added URL for media ${index + 1}`);
        }

        // Click the add media button if it exists and we're not on the last item
        const addButtons = await page.$$('button:has-text("Add Media"), button:has-text("Add media")');
        if (addButtons.length > index) {
            await addButtons[index].click();
            console.log(`✅ Clicked add media button ${index + 1}`);
            await page.waitForTimeout(1000); // Wait for new fields to appear
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

        // Find the price input field
        const priceInput = page.locator('input[id*="price"], input[placeholder*="price"]');
        if (await priceInput.count() > 0) {
            await priceInput.fill(priceData.price);
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

        // Find the stock quantity input field
        const stockInput = page.locator('input[placeholder*="quantity"], input[name*="quantity"]');
        if (await stockInput.count() > 0) {
            await stockInput.fill(quantity);
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
        // Wait for the form to load
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
            const success = await selectLevel(page, level.type, level.value);
            if (!success) {
                console.log(`⚠️ Warning: Could not select ${level.type} ${level.value}`);
            }
        }

        // Fill text fields
        await fillTextField(page, "Title", offerData.title);
        await fillTextField(page, "Description", offerData.description);

        // Fill pricing
        await fillPricing(page, offerData);

        // Fill stock quantity
        await fillStockQuantity(page, offerData.stock);

        // Add media to gallery
        if (offerData.mediaUrls && offerData.mediaUrls.length > 0) {
            for (let i = 0; i < offerData.mediaUrls.length; i++) {
                const title = offerData.mediaTitles && offerData.mediaTitles[i]
                    ? offerData.mediaTitles[i]
                    : `Media ${i + 1}`;

                await addMediaToGallery(page, offerData.mediaUrls[i], title, i);
            }
        }

        // Set delivery method
        await setDeliveryMethod(page, offerData.deliveryMethod);

        console.log("✅ Offer form filled successfully!");
        return true;
    } catch (error) {
        console.error("❌ Failed to fill offer form:", error.message);
        return false;
    }
}
