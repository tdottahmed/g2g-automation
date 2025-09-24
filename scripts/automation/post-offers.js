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
    headless: false,
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

// function Input(element, value, type) {

// }
async function main() {
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
        items.forEach(async (cardObj, index) => {
            if (!cardObj) return; // Skip null items

            const cardSel = getSelector(cardObj, index, cardSelector);

            const cardEl = page.locator(cardSel).first();

            if ((await cardEl.count()) === 0) {
                console.log(`❌ Could not find card in the dom: ${cardSel}`);
                return;
            }
            const { items: sectionItems, selector: defaultSectionSelector } =
                cardObj.sections;
            sectionItems.forEach(async (sectionObj, index) => {
                if (!sectionObj) return; // Skip null items

                const sectionSel = getSelector(
                    sectionObj,
                    index,
                    defaultSectionSelector
                );

                const sectionEl = cardEl.locator(sectionSel).first();

                if ((await sectionEl.count()) === 0) {
                    console.log(
                        `❌ Could not find section in the dom: ${sectionSel}`
                    );
                    return;
                }

                console.log(
                    `Processing section: ${
                        sectionObj.name
                    }, selector: ${sectionSel}, classes: ${await sectionEl.getAttribute(
                        "class"
                    )}`
                );

                const levels = {
                    "Town Hall Level": "17",
                    "King Level": "95+",
                    "Queen Level": "90+",
                    "Warden Level": "60+",
                    "Champion Level": "30+",
                };

                const {
                    items: fieldItems,
                    selector: defaultFieldSelector,
                    type: defaultFieldType,
                } = sectionObj.fields;
                for (let [index, fieldObj] of fieldItems.entries()) {
                    if (!fieldObj) continue; // Skip null items

                    const label = fieldObj.label;
                    const fieldSel = getSelector(
                        fieldObj,
                        index,
                        defaultFieldSelector
                    );
                    const fieldEl = sectionEl.locator(fieldSel).nth(index);

                    if ((await fieldEl.count()) === 0) {
                        console.log(
                            `❌ Could not find field in the dom: ${fieldSel}`
                        );
                        return;
                    }

                    const fieldType = fieldObj.type || defaultFieldType;
                    if (!fieldType) {
                        console.log(`❌ Field type not specified: ${fieldSel}`);
                        return;
                    }

                    switch (fieldType) {
                        case "dropdown":
                            const value = levels[label];
                            await selectDropdownOption(page, fieldEl, value);
                            break;

                        default:
                            console.log(
                                `❌ Unsupported field type: ${fieldType}`
                            );
                            break;
                    }
                }
            });
        });
        console.log("✅ Full flow completed!");
    } catch (error) {
        console.error("❌ Process failed:", error.message);
    } finally {
        rl.close();
        if (browser) console.log("⚠️ Browser kept open for debugging.");
    }
}

async function selectDropdownOption(page, fieldEl, value) {
    try {
        const btn = fieldEl.locator("div:nth-child(2) .g-btn-select").first();
        const labelEl = fieldEl.locator("div:nth-child(1) .text-font-2nd");
        const labelText = (await labelEl.first().innerText()).trim();

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
        "form > .row >.col-12:nth-of-type(1) .g-cu-form-card .g-cu-form-card__section:nth-of-type(2) > div:nth-of-type(2) > div > .row"
    );
    const itemCount = await items.count();
    console.log(`Found ${itemCount} form rows`);

    for (let i = 0; i < itemCount; i++) {
        console.log(`Processing row ${i}...`);
        const row = items.nth(i);

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
        // Find label in this row
        const dropdownButton = row.locator("div:nth-child(2) .g-btn-select");
        if ((await dropdownButton.count()) === 0) {
            console.log(`❌ Could not find ${labelText} dropdown button`);
            return false;
        }

        await selectDropdownOption(
            page,
            dropdownButton.first(),
            value,
            labelText
        );
    }
}

main();
