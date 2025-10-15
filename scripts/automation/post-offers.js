import { chromium } from 'playwright'
import {
  isLoggedIn,
  loadAuthState,
  loginWithOTP,
  saveAuthState,
} from './utils/auth.js'
import { rl, humanDelay } from './utils/index.js'
import { navigateToAccountsSection, clickContinueButton } from './utils/sell.js'
import fs from 'fs'

// Dynamic configuration
let CONFIG = {
  authFile: '',
  baseUrl: 'https://www.g2g.com',
  credentials: {
    email: '',
    password: '',
  },
  headless: false,
  slowMo: 100, // Reduced slightly for large batches
  debug: false, // Set to false for production
}

import formStructure from './templates/offer.js'

function getSelector(obj, index, defaultValue) {
  let selector = defaultValue
  if (obj && obj.selector) {
    selector = obj.selector
  }
  return selector.replace(':NUMBER:', index + 1)
}

async function main() {
  let templatesData = []
  try {
    const filePath = process.argv[2]
    if (!filePath) {
      throw new Error('No file path provided from Laravel')
    }

    console.log('📁 Reading large batch data from file:', filePath)

    if (!fs.existsSync(filePath)) {
      throw new Error(`File not found: ${filePath}`)
    }

    const fileData = fs.readFileSync(filePath, 'utf8')
    templatesData = JSON.parse(fileData)

    console.log(
      '📥 Received large batch from Laravel:',
      `Found ${templatesData.length} templates to process`
    )

    if (templatesData.length === 0) {
      throw new Error('No templates to process')
    }

    // Use first template for auth configuration
    const firstTemplate = templatesData[0]
    CONFIG.credentials.email = firstTemplate.user_email
    CONFIG.credentials.password = firstTemplate.password
    CONFIG.authFile = firstTemplate.cookies

    console.log('🔧 Using config for large batch:', {
      email: CONFIG.credentials.email,
      templates_count: templatesData.length,
    })
  } catch (e) {
    console.error('❌ Failed to parse large batch input:', e.message)
    process.exit(1)
  }

  let browser = null
  let context = null
  let page = null

  try {
    browser = await chromium.launch({
      headless: CONFIG.headless,
      slowMo: CONFIG.slowMo,
      args: ['--start-maximized', '--disable-dev-shm-usage', '--no-sandbox'],
    })

    context = await browser.newContext({
      viewport: { width: 1366, height: 768 },
    })

    page = await context.newPage()

    // Load session with proper error handling
    console.log('🔐 Checking authentication state for large batch...')
    const hasAuthState = await loadAuthState(context, CONFIG.authFile)

    let loggedIn = false
    if (hasAuthState) {
      console.log('📁 Auth file exists, checking login status...')
      loggedIn = await isLoggedIn(page, CONFIG.baseUrl)
      console.log('🔍 Login status after loading auth:', loggedIn)
    }

    if (!loggedIn) {
      console.log('🔑 Performing fresh login for large batch...')
      await context.clearCookies()

      const loginSuccess = await loginWithOTP(
        page,
        CONFIG.baseUrl,
        CONFIG.credentials.email,
        CONFIG.credentials.password
      )

      if (loginSuccess) {
        console.log('✅ Login successful! Saving authentication state...')
        await saveAuthState(context, CONFIG.authFile)
      } else {
        throw new Error('❌ Login process failed')
      }
    } else {
      console.log('✅ Using existing session for large batch')
    }

    // Process each template with better error handling
    let successCount = 0
    let failCount = 0

    for (let i = 0; i < templatesData.length; i++) {
      const templateData = templatesData[i]
      console.log(
        `\n🔄 [${i + 1}/${templatesData.length}] Processing: ${
          templateData.Title
        }`
      )

      try {
        if (i === 0) {
          // First template - navigate to accounts section
          const navSuccess = await navigateToAccountsSection(page)
          if (!navSuccess) {
            throw new Error('Failed to navigate to Accounts section')
          }
          const continueSuccess = await clickContinueButton(page)
          if (!continueSuccess) {
            throw new Error("Could not click 'Continue' button")
          }
        }

        // Fill the form
        await fillOfferForm(page, templateData)

        if (i < templatesData.length - 1) {
          // Submit and add new offer
          const success = await submitFormAndAddNew(page)
          if (!success) {
            console.log('⚠️ Could not proceed to next offer, stopping...')
            failCount += templatesData.length - i
            break
          }
        } else {
          // Final template
          console.log('🚀 Submitting final form...')
          await submitForm(page)
          await page.waitForTimeout(5000)
          console.log('✅ Final offer submitted!')
        }

        successCount++
        console.log(
          `✅ [${i + 1}/${templatesData.length}] Success: ${templateData.Title}`
        )
      } catch (error) {
        failCount++
        console.error(
          `❌ [${i + 1}/${templatesData.length}] Failed: ${
            templateData.Title
          } - ${error.message}`
        )

        // Try to recover and continue with next template
        if (i < templatesData.length - 1) {
          console.log('🔄 Attempting recovery for next template...')
          try {
            // Try to go back to the offer creation page
            await page.goto(`${CONFIG.baseUrl}/sell/index`, {
              waitUntil: 'domcontentloaded',
              timeout: 15000,
            })
            await page.waitForTimeout(3000)

            // Try to click continue button again
            const continueSuccess = await clickContinueButton(page)
            if (!continueSuccess) {
              console.log('❌ Failed to recover navigation, stopping...')
              failCount += templatesData.length - i - 1
              break
            }
          } catch (navError) {
            console.error('❌ Failed to recover, stopping completely...')
            failCount += templatesData.length - i - 1
            break
          }
        }
      }

      // Add small delay between templates to avoid rate limiting
      if (i < templatesData.length - 1) {
        await page.waitForTimeout(1000)
      }
    }

    const successRate =
      templatesData.length > 0 ? (successCount / templatesData.length) * 100 : 0
    console.log(
      `\n📊 LARGE BATCH COMPLETED: ${successCount}/${
        templatesData.length
      } successful (${successRate.toFixed(1)}%)`
    )

    // Send results back to PHP in specific format
    console.log(`RESULTS:${successCount}:${failCount}`)
  } catch (error) {
    console.error('❌ Large batch process failed completely:', error.message)
    process.exit(1)
  } finally {
    rl.close()
    if (browser) {
      await browser.close()
    }
  }
}
main()

async function fillOfferForm(page, inputData) {
  console.log(`📝 Starting to fill form for template: ${inputData.Title}`)

  await page.waitForLoadState('domcontentloaded')
  await page.waitForTimeout(2000)

  const { selector: cardSelector, items } = formStructure

  for (let [cardIndex, cardObj] of items.entries()) {
    if (!cardObj) continue // Skip null items

    const cardSel = getSelector(cardObj, cardIndex, cardSelector)
    const cardEl = page.locator(cardSel).first()

    if ((await cardEl.count()) === 0) {
      console.log(`❌ Could not find card in the dom: ${cardSel}`)
      continue
    }

    const { items: sectionItems, selector: defaultSectionSelector } =
      cardObj.sections

    for (let [sectionIndex, sectionObj] of sectionItems.entries()) {
      if (!sectionObj) continue // Skip null items

      const sectionSel = getSelector(
        sectionObj,
        sectionIndex,
        defaultSectionSelector
      )

      const sectionEl = cardEl.locator(sectionSel).first()

      if ((await sectionEl.count()) === 0) {
        console.log(`❌ Could not find section in the dom: ${sectionSel}`)
        continue
      }

      console.log(
        `Processing section: ${
          sectionObj.name
        }, selector: ${sectionSel}, classes: ${await sectionEl.getAttribute(
          'class'
        )}`
      )

      const {
        items: fieldItems,
        selector: defaultFieldSelector,
        type: defaultFieldType,
      } = sectionObj.fields

      for (let [fieldIndex, fieldObj] of fieldItems.entries()) {
        if (!fieldObj) continue // Skip null items

        const label = fieldObj.label
        const fieldSel = getSelector(fieldObj, fieldIndex, defaultFieldSelector)
        const fieldEl = sectionEl.locator(fieldSel).nth(fieldIndex)

        if ((await fieldEl.count()) === 0) {
          console.log(`❌ Could not find field in the dom: ${fieldSel}`)
          continue
        }

        const fieldType = fieldObj.type || defaultFieldType
        if (!fieldType) {
          console.log(`❌ Field type not specified: ${fieldSel}`)
          continue
        }

        const value = inputData[label]

        switch (fieldType) {
          case 'dropdown':
            await selectDropdownOption(page, fieldEl, value)
            await page.waitForTimeout(500)
            break
          case 'text':
            await fillInput(page, fieldEl, value, label)
            await page.waitForTimeout(500)
            break
          default:
            console.log(`❌ Unsupported field type: ${fieldType}`)
            break
        }
      }
    }
  }

  await fillPricingSection(page, inputData['Default price (unit)'])

  const mediaData = inputData.mediaData || []
  await fillMediaGallery(page, mediaData)

  // Call the delivery functions with page parameter
  await selectManualDelivery(page)
  await page.waitForTimeout(1000)

  // Make sure these values exist in your inputData
  const deliveryHour = inputData['Delivery hour']
  await setDeliveryHour(page, deliveryHour)
  await page.waitForTimeout(1000)
  await setDeliveryMinute(page)

  console.log(`✅ Form filled successfully for template: ${inputData.Title}`)
}
async function submitFormAndAddNew(page) {
  console.log('🚀 Submitting form...')
  await submitForm(page)

  // Wait for the success dialog
  const successDialog = page.locator(
    '.q-dialog__inner .q-card:has-text("Your offer has been published")'
  )

  try {
    await successDialog.waitFor({ state: 'visible', timeout: 30000 })
    console.log('✅ Success dialog appeared')
  } catch (error) {
    console.log('⚠️ Success dialog not found, but continuing...')
  }

  console.log("🔍 Looking for 'Add new offer' button...")

  // Click "Add new offer" button
  const addNewOfferBtn = page.locator('button:has-text("Add new offer")')

  try {
    await addNewOfferBtn.waitFor({ state: 'visible', timeout: 1500 })
    await addNewOfferBtn.click()
    console.log("✅ Clicked 'Add new offer' button")

    await page.waitForSelector('a:has-text("Continue")', {
      timeout: 20000,
    })
    const continueButton = page.locator('a:has-text("Continue")')
    await continueButton.click()

    console.log('✅ Continue button clicked successfully')
    return true
  } catch (error) {
    console.log("❌ 'Add new offer' button not found or not clickable")
    return false
  }

  return true
}

async function selectDropdownOption(page, fieldEl, value) {
  const btn = fieldEl.locator('div:nth-child(2) .g-btn-select').first()
  const labelEl = fieldEl.locator('div:nth-child(1) .text-font-2nd')
  const labelText = (await labelEl.first().innerText()).trim()
  try {
    console.log(`Selecting ${labelText} = ${value}`)
    await humanDelay(300, 500)
    await btn.click({ force: true })
    console.log(`🖱️ Clicked ${labelText} dropdown button`)

    // Locate wrapper relative to this button
    const dropdownWrapper = btn.locator(
      ' + div.relative-position > div:not(.g-input-error)'
    )
    if ((await dropdownWrapper.count()) === 0) {
      console.log(`❌ Could not find ${labelText} dropdown wrapper`)
      return false
    }

    // Filter input
    const filterInput = dropdownWrapper.locator(
      'label input[placeholder="Type to filter"]'
    )
    if ((await filterInput.count()) === 0) return false

    await filterInput.first().fill(value)
    await page.waitForTimeout(700)

    // Dropdown menu
    const dropdownMenu = dropdownWrapper.locator(
      'div:nth-child(2) .q-virtual-scroll__content'
    )
    if ((await dropdownMenu.count()) === 0) return false

    const option = dropdownMenu.locator(
      `.q-item .q-item__section:has-text("${value}")`
    )
    if ((await option.count()) === 0) {
      await page.keyboard.press('Escape').catch(() => {})
      return false
    }

    const firstOption = option.first()
    const innerHTML = await firstOption.innerHTML()
    if (!innerHTML.toLowerCase().includes(value.toLowerCase())) {
      await page.keyboard.press('Escape').catch(() => {})
      return false
    }

    await firstOption.click({ force: true })
    await page.waitForTimeout(600)
    console.log(`✅ Selected ${labelText}: ${value}`)
    return true
  } catch (error) {
    console.error(`❌ Failed to select ${labelText}:`, error.message)
    await page.keyboard.press('Escape').catch(() => {})
    return false
  }
}

async function fillInput(page, fieldEl, value, label = 'Input') {
  try {
    const input = fieldEl.locator('.q-field__native').first()

    if ((await input.count()) === 0) {
      console.log(`❌ Could not find ${label} input`)
      return false
    }
    await humanDelay(800, 1500)

    await input.click({ clickCount: 3 })
    await input.fill(value)

    console.log(`✅ Filled ${label} input: ${value}`)
    await page.waitForTimeout(500)

    return true
  } catch (error) {
    console.error(`❌ Failed to fill input ${label}:`, error.message)
    return false
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
      .first()

    if ((await pricingSection.count()) === 0) {
      console.log('❌ Could not find Pricing section')
      return false
    }

    const priceInput = pricingSection.locator('input.q-field__native').first()
    if ((await priceInput.count()) === 0) {
      console.log('❌ Could not find Default price input')
      return false
    }

    await priceInput.fill('')
    await priceInput.type(price.toString(), { delay: 100 })
    console.log(`🖱️ Filled Default price (unit) with: ${price}`)

    return true
  } catch (error) {
    console.error('❌ Failed to fill Pricing section:', error.message)
    return false
  }
}

/**
 * Fill Media Gallery
 * @param {import('playwright').Page} page
 * @param {Array<{title: string, Link: string}>} medias
 */
async function fillMediaGallery(page, medias = []) {
  if (!medias.length) return

  try {
    const mediaSection = page
      .locator(".g-cu-form-card__section:has-text('Media gallery')")
      .first()

    if ((await mediaSection.count()) === 0) {
      console.log('❌ Could not find Media gallery section')
      return false
    }

    // Loop through each media item in JSON
    for (let i = 0; i < medias.length; i++) {
      const { title, Link } = medias[i]

      // Click 'Add media' button if not first item
      if (i > 0) {
        const addBtn = mediaSection
          .locator("button:has-text('Add media')")
          .first()
        if ((await addBtn.count()) > 0) {
          await addBtn.click()
          await page.waitForTimeout(500) // wait for new input to render
        }
      }

      // --- Fill Media Title ---
      const titleInput = mediaSection
        .locator(`input[placeholder="Media title"]`)
        .nth(i)
      if ((await titleInput.count()) > 0) {
        await titleInput.fill('') // clear first
        await titleInput.click()

        // Copy to clipboard
        await page.evaluate(async (text) => {
          await navigator.clipboard.writeText(text)
        }, title)

        // Paste
        await page.keyboard.press('Control+V') // or "Meta+V" on macOS
        console.log(`📋 Pasted media title: ${title}`)
      } else {
        console.log(`❌ Could not find media title input for item ${i}`)
      }

      // --- Fill Link ---
      const linkInput = mediaSection
        .locator(`input[placeholder="https://"]`)
        .nth(i)
      if ((await linkInput.count()) > 0) {
        await linkInput.fill('') // clear first
        await linkInput.click()

        await page.evaluate(async (text) => {
          await navigator.clipboard.writeText(text)
        }, Link)

        await page.keyboard.press('Control+V') // or "Meta+V" on macOS
        console.log(`📋 Pasted media link: ${Link}`)
      } else {
        console.log(`❌ Could not find media link input for item ${i}`)
      }

      await page.waitForTimeout(300) // small delay for stability
    }

    return true
  } catch (error) {
    console.error('❌ Failed to fill Media gallery:', error.message)
    return false
  }
}

async function selectManualDelivery(page) {
  try {
    console.log('🔧 Selecting Manual delivery...')
    const manualDeliveryRadio = page.locator(
      'div[role="radio"][aria-label="Manual delivery"]'
    )

    if ((await manualDeliveryRadio.count()) > 0) {
      const isChecked = await manualDeliveryRadio.getAttribute('aria-checked')
      if (isChecked !== 'true') {
        await manualDeliveryRadio.click()
        console.log('✅ Selected Manual delivery')
        await page.waitForTimeout(1000)
      } else {
        console.log('ℹ️ Manual delivery already selected')
      }
    } else {
      console.log('❌ Manual delivery radio not found')
      return false
    }
    return true
  } catch (error) {
    console.error('❌ Failed to select Manual delivery:', error.message)
    return false
  }
}

async function setDeliveryHour(page, hourValue) {
  try {
    // Determine singular or plural text
    const hourText =
      hourValue == 1 || hourValue == 0
        ? `${hourValue} hour`
        : `${hourValue} hours`
    console.log(`🔧 Setting delivery hour to: ${hourText}`)

    // Locate the hour dropdown button
    const hourDropdown = page
      .locator('div.g-select-text-input .left button')
      .last()
    if ((await hourDropdown.count()) === 0) {
      console.log('❌ Hour dropdown button not found')
      return false
    }

    await hourDropdown.click()
    console.log('✅ Clicked hour dropdown')
    await page.waitForTimeout(500)

    // Locate the dropdown menu
    const dropdownMenu = page.locator('.q-virtual-scroll__content')
    if ((await dropdownMenu.count()) === 0) {
      console.log('❌ Hour dropdown menu not found')
      return false
    }

    // Locate the option by exact text
    const option = dropdownMenu
      .locator('.q-item__section', { hasText: hourText })
      .first()
    if ((await option.count()) === 0) {
      console.log(`❌ Hour option "${hourText}" not found`)
      await page.keyboard.press('Escape').catch(() => {})
      return false
    }

    await option.scrollIntoViewIfNeeded()
    await option.click({ force: true })
    console.log(`✅ Set Delivery Hour: ${hourText}`)
    await page.waitForTimeout(500)

    return true
  } catch (error) {
    console.error('❌ Failed to set delivery hour:', error.message)
    await page.keyboard.press('Escape').catch(() => {})
    return false
  }
}

async function setDeliveryMinute(page) {
  try {
    // Determine singular or plural text
    const minText = '10 mins'
    console.log(`🔧 Setting delivery minute to: ${minText}`)

    // Locate the minute dropdown button
    const minDropdown = page
      .locator('div.g-select-text-input .right button')
      .first()
    if ((await minDropdown.count()) === 0) {
      console.log('❌ Minute dropdown button not found')
      return false
    }

    await minDropdown.click()
    console.log('✅ Clicked minute dropdown')
    await page.waitForTimeout(500)

    // Locate the dropdown menu
    const dropdownMenu = page.locator('.q-virtual-scroll__content')
    if ((await dropdownMenu.count()) === 0) {
      console.log('❌ Minute dropdown menu not found')
      return false
    }

    // Locate the option by exact text
    const option = dropdownMenu
      .locator('.q-item__section', { hasText: minText })
      .first()
    if ((await option.count()) === 0) {
      console.log(`❌ Minute option "${minText}" not found`)
      await page.keyboard.press('Escape').catch(() => {})
      return false
    }

    await option.scrollIntoViewIfNeeded()
    await option.click({ force: true })
    console.log(`✅ Set Delivery Minute: ${minText}`)
    await page.waitForTimeout(500)

    return true
  } catch (error) {
    console.error('❌ Failed to set delivery minute:', error.message)
    await page.keyboard.press('Escape').catch(() => {})
    return false
  }
}

async function submitForm(page) {
  try {
    console.log('🔧 Attempting to click Publish button...')

    // Locate the Publish button by its text
    const publishBtn = page.locator('button:has-text("Publish")').first()

    if ((await publishBtn.count()) === 0) {
      console.log('❌ Publish button not found')
      return false
    }

    await publishBtn.scrollIntoViewIfNeeded()
    await publishBtn.click({ force: true })
    console.log('✅ Publish button clicked successfully')

    // optional wait for form submission / navigation
    await page.waitForTimeout(1000)

    return true
  } catch (error) {
    console.error('❌ Failed to click Publish button:', error.message)
    return false
  }
}
