import { chromium } from "playwright";
import {
    isLoggedIn,
    loadAuthState,
    loginWithOTP,
    saveAuthState,
} from "./utils/auth.js";
import { rl, humanDelay } from "./utils/index.js";
import {
    navigateToAccountsSection,
    clickContinueButton,
} from "./utils/sell.js";

export const CONFIG = {
    authFile: "g2g_auth_state.json",
    baseUrl: "https://www.g2g.com",
    credentials: {
        email: "recoveryth0000@gmail.com",
        password: "@#aocy123%&",
    },
    headless: true,
    slowMo: 120,
};

import formStructure from "./templates/offer.js";

/**
 *
 * @param {Object} obj
 * @param {number} index
 * @param {string} defaultValue
 * @returns
 */
function getSelector(obj, index, defaultValue) {
    let selector = defaultValue;
    if (obj && obj.selector) {
        selector = obj.selector;
    }
    return selector.replace(":NUMBER:", index + 1);
}

async function main() {
    console.log("Raw args:", process.argv);

    /**
     * @type {Values} inputData ↓ 9
     */
    let inputData = {};
    try {
        // inputData = JSON.parse(process.argv[2] || "{}");
        inputData = {
            Title: "Test Title",
            mediaData: [
                {
                    title: "Test Media 1",
                    Link: "https://www.dropbox.com/scl/fi/1ox7w331djf9fh1jkhlom/InCollage_20250826_170111747.jpg?rlkey=ah2ar9dqcpko9gsw5qs5gkcza&st=q0vbgmo1&raw=1",
                },
                {
                    title: "Test Media 2",
                    Link: "https://www.dropbox.com/scl/fi/1ox7w331djf9fh1jkhlom/InCollage_20250826_170111747.jpg?rlkey=ah2ar9dqcpko9gsw5qs5gkcza&st=q0vbgmo1&raw=1",
                },
            ],
            Description:
                "t is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout. The point of using Lorem Ipsum is that it has a more-or-less normal distribution of letters",
            "Town Hall Level": "17",
            "King Level": "85+",
            "Queen Level": "95+",
            "Warden Level": "65+",
            "Champion Level": "45+",
            "Default price (unit)": "150.00",
            "Minimum purchase quantity": null,
            "Media gallery": null,
            "Instant delivery": 1,
        };
        console.log("📥 Parsed inputData:", inputData);
    } catch (e) {
        console.error("❌ Failed to parse input:", e.message);
        process.exit(1);
    }

    let browser = null;
    try {
        browser = await chromium.launch({
            headless: CONFIG.headless,
            slowMo: CONFIG.slowMo,
            args: ["--start-maximized"],
        });

        const context = await browser.newContext({
            viewport: { width: 1366, height: 768 },
        });

        const page = await context.newPage();

        // Load session
        const hasAuthState = await loadAuthState(context);
        console.log("Auth States:", hasAuthState);

        let loggedIn = hasAuthState && (await isLoggedIn(page));
        if (!loggedIn) {
            console.log("❌ Performing fresh login...");
            await page.goto(`${CONFIG.baseUrl}/login`, {
                waitUntil: "networkidle",
            });
            await page.type('input[name="email"]', CONFIG.credentials.email);
            await page.type(
                'input[name="password"]',
                CONFIG.credentials.password
            );
            await page.keyboard.press("Enter");
            await loginWithOTP(page);
            await saveAuthState(context);
        } else {
            console.log("✅ Using existing session");
        }

        // Navigate Accounts **before Continue**
        const navSuccess = await navigateToAccountsSection(page);
        if (!navSuccess)
            throw new Error("Failed to navigate to Accounts section");

        // Click Continue button → now we are on Offer creation page
        const continueClicked = await clickContinueButton(page);
        if (!continueClicked)
            throw new Error("Failed to click Continue button");

        await page.waitForLoadState("domcontentloaded");
        await page.waitForTimeout(5000); // lazy-render buffer

        const { selector: cardSelector, items } = formStructure;

        for (let [cardIndex, cardObj] of items.entries()) {
            if (!cardObj) continue; // Skip null items

            const cardSel = getSelector(cardObj, cardIndex, cardSelector);
            const cardEl = page.locator(cardSel).first();

            if ((await cardEl.count()) === 0) {
                console.log(`❌ Could not find card in the dom: ${cardSel}`);
                continue;
            }

            const { items: sectionItems, selector: defaultSectionSelector } =
                cardObj.sections;

            for (let [sectionIndex, sectionObj] of sectionItems.entries()) {
                if (!sectionObj) continue; // Skip null items

                const sectionSel = getSelector(
                    sectionObj,
                    sectionIndex,
                    defaultSectionSelector
                );

                const sectionEl = cardEl.locator(sectionSel).first();

                if ((await sectionEl.count()) === 0) {
                    console.log(
                        `❌ Could not find section in the dom: ${sectionSel}`
                    );
                    continue;
                }

                console.log(
                    `Processing section: ${
                        sectionObj.name
                    }, selector: ${sectionSel}, classes: ${await sectionEl.getAttribute(
                        "class"
                    )}`
                );

                const {
                    items: fieldItems,
                    selector: defaultFieldSelector,
                    type: defaultFieldType,
                } = sectionObj.fields;

                for (let [fieldIndex, fieldObj] of fieldItems.entries()) {
                    if (!fieldObj) continue; // Skip null items

                    const label = fieldObj.label;
                    const fieldSel = getSelector(
                        fieldObj,
                        fieldIndex,
                        defaultFieldSelector
                    );
                    const fieldEl = sectionEl.locator(fieldSel).nth(fieldIndex);

                    if ((await fieldEl.count()) === 0) {
                        console.log(
                            `❌ Could not find field in the dom: ${fieldSel}`
                        );
                        continue;
                    }

                    const fieldType = fieldObj.type || defaultFieldType;
                    if (!fieldType) {
                        console.log(`❌ Field type not specified: ${fieldSel}`);
                        continue;
                    }

                    const value = inputData[label];

                    switch (fieldType) {
                        case "dropdown":
                            await selectDropdownOption(page, fieldEl, value);
                            await page.waitForTimeout(500); // 👈 Now this works!
                            break;
                        case "text":
                            await fillInput(page, fieldEl, value, label);
                            await page.waitForTimeout(500); // 👈 Add delay here too
                            break;
                        default:
                            console.log(
                                `❌ Unsupported field type: ${fieldType}`
                            );
                            break;
                    }
                }
            }
        }
        await fillPricingSection(page, inputData["Default price (unit)"]);

        const mediaData = inputData.mediaData || [];
        await fillMediaGallery(page, mediaData);

        console.log("✅ Full flow completed!");
    } catch (error) {
        console.error("❌ Process failed:", error.message);
    } finally {
        rl.close();
        if (browser) console.log("⚠️ Browser kept open for debugging.");
    }
}

async function selectDropdownOption(page, fieldEl, value) {
    const btn = fieldEl.locator("div:nth-child(2) .g-btn-select").first();
    const labelEl = fieldEl.locator("div:nth-child(1) .text-font-2nd");
    const labelText = (await labelEl.first().innerText()).trim();
    try {
        console.log(`Selecting ${labelText} = ${value}`);
        await humanDelay(800, 1500); // 👈 Now delay works!
        await btn.click({ force: true });
        console.log(`🖱️ Clicked ${labelText} dropdown button`);
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
        await page.waitForTimeout(500);
        console.log(`✅ Selected ${labelText}: ${value}`);
        return true;
    } catch (error) {
        console.error(`❌ Failed to select ${labelText}:`, error.message);
        await page.keyboard.press("Escape").catch(() => {});
        return false;
    }
}

async function fillInput(page, fieldEl, value, label = "Input") {
    try {
        const input = fieldEl.locator(".q-field__native").first();

        if ((await input.count()) === 0) {
            return false;
        }
        await humanDelay(800, 1500); // 👈 Now delay works!
        await input.fill("");
        await input.type(value, { delay: 100 });
        console.log(`🖱️ Filled ${label} input with ${value}`);
        await page.waitForTimeout(500);
        return true;
    } catch (error) {
        console.error(`❌ Failed to fill input ${label}:`, error.message);
        return false;
    }
}

/**
 * Fill Pricing section manually (only price input)
 * @param {import('playwright').Page} page
 * @param {string|number} price - e.g., "150.00"
 */
async function fillPricingSection(page, price) {
    try {
        const pricingSection = page
            .locator(".g-cu-form-card__section:has-text('Pricing')")
            .first();

        if ((await pricingSection.count()) === 0) {
            console.log("❌ Could not find Pricing section");
            return false;
        }

        const priceInput = pricingSection
            .locator("input.q-field__native")
            .first();
        if ((await priceInput.count()) === 0) {
            console.log("❌ Could not find Default price input");
            return false;
        }

        await priceInput.fill("");
        await priceInput.type(price.toString(), { delay: 100 });
        console.log(`🖱️ Filled Default price (unit) with: ${price}`);

        return true;
    } catch (error) {
        console.error("❌ Failed to fill Pricing section:", error.message);
        return false;
    }
}

/**
 * Fill Media Gallery
 * @param {import('playwright').Page} page
 * @param {Array<{title: string, Link: string}>} medias
 */
async function fillMediaGallery(page, medias = []) {
    if (!medias.length) return;

    try {
        const mediaSection = page
            .locator(".g-cu-form-card__section:has-text('Media gallery')")
            .first();

        if ((await mediaSection.count()) === 0) {
            console.log("❌ Could not find Media gallery section");
            return false;
        }

        // Loop through each media item in JSON
        for (let i = 0; i < medias.length; i++) {
            const { title, Link } = medias[i];

            // Click 'Add media' button if not first item
            if (i > 0) {
                const addBtn = mediaSection
                    .locator("button:has-text('Add media')")
                    .first();
                if ((await addBtn.count()) > 0) {
                    await addBtn.click();
                    await page.waitForTimeout(500); // wait for new input to render
                }
            }

            // Fill Media Title
            const titleInput = mediaSection
                .locator(`input[placeholder="Media title"]`)
                .nth(i);
            if ((await titleInput.count()) > 0) {
                await titleInput.fill("");
                await titleInput.type(title, { delay: 100 });
                console.log(`🖱️ Filled media title: ${title}`);
            } else {
                console.log(
                    `❌ Could not find media title input for item ${i}`
                );
            }

            // Fill Link
            const linkInput = mediaSection
                .locator(`input[placeholder="https://"]`)
                .nth(i);
            if ((await linkInput.count()) > 0) {
                await linkInput.fill("");
                await linkInput.type(Link, { delay: 100 });
                console.log(`🖱️ Filled media link: ${Link}`);
            } else {
                console.log(`❌ Could not find media link input for item ${i}`);
            }

            await page.waitForTimeout(300); // small delay for stability
        }

        return true;
    } catch (error) {
        console.error("❌ Failed to fill Media gallery:", error.message);
        return false;
    }
}

main();
