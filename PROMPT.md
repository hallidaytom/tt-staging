Build a complete single-page static website for "Snooty Tooty" — a luxury portable restroom hire business in South Australia.

OUTPUT FILES:
- index.html (root)
- assets/css/styles.css
- assets/js/app.js

Also run: cp "/root/.openclaw/workspace/snooty-tooty-brief/ST_HTerms_022025.pdf" assets/ST_HTerms_022025.pdf

BRAND & DESIGN:
- Colours: #1a1a1a (near-black bg), #C9A84C (gold), #FFFFFF (white), #f5f0e8 (warm cream for cards/sections)
- Fonts via Google Fonts: "Playfair Display" (headings, weights 400/700/900) + "Lato" (body, weights 300/400/700)
- Aesthetic: Art Deco / 1920s luxury. Gold accents on dark backgrounds for hero; light cream backgrounds for content sections.
- Pure HTML/CSS/vanilla JS only — no frameworks, no jQuery, no analytics.

IMAGES (already in assets/img/):
- logo.png — gold Art Deco wordmark — use in nav + hero (max-width ~300px in hero)
- event.png — exterior event shot (trailer at wedding) — hero background
- interior.png — interior bathroom shot — "Introducing" section
- interior-bench.png — vanity/mirror closeup — amenities section
- dragonfly-water.jpg — Dragonfly water bottles — cross-promo section
- supply-nation.png — certification logo — footer (small)
- sa-logo.png — SA logo — footer (small)

SECTION STRUCTURE:

## NAV (sticky, dark #1a1a1a)
- Left: logo.png (~140px wide)
- Right: Home | Features | Reviews | FAQs | Get a Quote (smooth scroll)
- Gold hover colour on links
- Mobile hamburger menu

## HERO (id="home", full viewport height)
- Background: event.png with dark overlay (rgba 0,0,0,0.62)
- Centred content:
  - Eyebrow: "SOUTH AUSTRALIA'S MOST LUXURIOUS"
  - H1: "LUXURY PORTABLE RESTROOM HIRE"
  - Subtext: "Snooty Tooty offers a VIP experience that will amaze your guests and help them feel at home."
  - Two buttons: "CALL NOW — 0488 036 717" (gold fill, tel:0488036717) and "GET A QUOTE" (gold outline, scroll to #quote)
- Scroll-down chevron at bottom

## INTRODUCING SNOOTY TOOTY (id="about", cream #f5f0e8)
- Two-column: text left, interior.png right (on desktop; stacked on mobile)
- H2: "Introducing Snooty Tooty"
- Intro paragraph: "Are you looking for a luxury portable restroom to hire in South Australia? Snooty Tooty offers a VIP experience that will amaze your guests and help them feel at home."
- Three feature items (icon + bold title + description):
  1. Luxurious Features — "Our ultra-modern twin suites feature marble vanities and quality fittings, designed for elegance and comfort."
  2. Perfect for Every Occasion — "Whether it's a wedding, birthday, or corporate event, Snooty Tooty caters for up to 150 guests, ensuring an unforgettable experience."
  3. Comprehensive Service — "Enjoy a hassle-free experience with our full 'drop off, set up, and collect' service. We deliver clean, sanitised units ready for use!"
- Italic pull-quote below: "Snooty Tooty really is Australia's most luxurious portable restroom. Nothing out-shines our beautifully clean, well-presented first-class amenities!"

## PREMIUM AMENITIES (id="features", dark #1a1a1a)
- H2: "Explore Our Premium Amenities" (gold)
- Sub: "Snooty Tooty is equipped with:"
- 8-item grid (2 cols mobile, 4 cols desktop) — each card: icon + bold title + description:
  1. Spacious Cubicles & Elegant Vanities — "Enjoy large mirrors and ample space for your comfort."
  2. Handwashing Facilities — "Equipped with running water for easy and effective handwashing."
  3. Pedal-Flush Porcelain Toilets — "Modern and hygienic facilities for your convenience."
  4. Eco-Friendly Lighting — "Solar-powered interior and exterior lighting enhances your experience."
  5. Premium Hygiene Supplies — "Soft liquid soap, hand sanitiser and paper towels available."
  6. Sanitary Bins — "Discreetly maintained for your comfort."
  7. Bluetooth Sound System — "Create the perfect ambiance for your occasion!"
  8. Internal Power Points — "Perfect for hair dryers, straighteners, and other styling tools."
- interior-bench.png below grid (full-width, max 900px, rounded corners)
- Gold-border card — "Hosting an Event?":
  - "In addition to Snooty Tooty, we're proud to offer event solutions from Andy's Water — premium quality spring water in mobile solutions (1,500–2,000 litres). Check out Andy's Water." Link to https://andyswater.com.au target _blank.
- Dragonfly row (dragonfly-water.jpg left, text right):
  - Bold: "Make Your Event Even More Memorable!"
  - "Consider adding the refreshing taste of Dragonfly Springwater to your menu. Perfectly sourced and delivered to your location." Link "Visit Dragonfly Springwater" to https://www.dragonflyspringwater.com.au/ target _blank.

## REVIEW (id="reviews", cream #f5f0e8, centred)
- Large gold open-quote mark
- Italic: "We used Snooty Tooty for our son's garden engagement party recently and the communication, delivery and collection was excellent. The toilets were very modern, clean and had all the supplies needed including a Bluetooth sound system. The feedback from our guests was very positive and we would not hesitate to use this friendly local company again."
- Attribution: "— Catherine, Port Elliot" + 5 gold stars

## FAQs (id="faqs", white bg)
- H2: "Frequently Asked Questions"
- Accordion (click to expand, smooth max-height transition, plus rotates to x):
  1. How many guests can Snooty Tooty accommodate? — "Designed to cater to up to 150 guests over a 6-hour period, handling approximately 300 flushes. Our team will discuss your specific needs."
  2. How is sanitation managed during events? — "In most instances sanitation is not a concern due to ample capacity. For events over 6 hours or multi-day, we arrange a team visit to replenish water and restock essentials."
  3. What types of events are suitable? — "Weddings, corporate events, festivals and more!"
  4. Where do I collect Snooty Tooty from? — "No collection needed — we provide full delivery, setup and pickup with every hire."
  5. Why would I need an external power source? — "Lighting and Bluetooth are solar-powered; air conditioning and power points need external power. We can supply a generator — speak to us at booking."
  6. Are there any placement considerations? — "Yes — we consider access, solar exposure, and power access. We discuss all of this at booking as every event is unique."
  7. Is an upfront deposit required? — "Yes, 50% of the full hire cost is required at booking to secure your reservation."

## GET A QUOTE (id="quote", gold #C9A84C bg, dark text)
- H2: "Ready to Elevate Your Next Event?"
- "We are dedicated to providing top-tier services that make your events memorable. Contact us today and let's make your event extraordinary!"
- Phone: 0488 036 717 (tel: link, large)
- Email: sales@snootytooty.com.au (mailto: link, large)
- Social: Facebook (https://www.facebook.com/SnootyTootySA/) | Instagram (https://www.instagram.com/snootytootysa/)

## FOOTER (dark #1a1a1a)
- Logo (small, ~120px)
- Aboriginal acknowledgement: "We acknowledge Aboriginal and Torres Strait Islander Peoples as the traditional custodians of our land. We recognise the Kaurna people as the traditional custodians of this place we now call Adelaide and pay our respects to Elders past, present and future."
- supply-nation.png + sa-logo.png (small logos, ~80px each, inline)
- Links: Terms & Conditions (href="assets/ST_HTerms_022025.pdf" target="_blank") | Privacy Policy
- Copyright: "© Dragonfly Group (SA) 2026. All Rights Reserved."

CSS REQUIREMENTS:
- Mobile-first, breakpoints: 480px / 768px / 1024px
- html { scroll-behavior: smooth }
- Sticky nav — JS adds class "scrolled" on scroll, CSS applies backdrop-filter blur + slight shadow
- Scroll-reveal: IntersectionObserver adds "visible" class to elements with class "reveal" — fade up from 30px, opacity 0 to 1, 0.6s ease
- FAQ accordion: max-height 0 to auto trick using max-height transition; chevron rotates 180deg when open
- .btn-primary: gold bg #C9A84C, dark text, hover darken to #b8943f
- .btn-secondary: transparent bg, gold border + text, hover: gold fill
- All external links: target="_blank" rel="noopener noreferrer"
- Section padding: 80px top/bottom desktop, 48px mobile

When completely finished, run: openclaw system event --text "Done: Snooty Tooty static site built" --mode now
