# Changelog

## 1.2.1

Shop and checkout:

- Enter in a cart quantity updates the cart once, and a quantity changed just before leaving the cart is kept.
- The checkout asks for an address only when the order ships, honours the terms box, shows a coupon or fee
  row on the cart, the checkout and the receipt, and fires the WooCommerce hooks payment and express checkout
  plugins rely on.
- A failed or unpaid order gets its own confirmation, with a way to pay again and the total still to pay.
- The product page prints its structured data, takes every kind of product attribute, and confirms an add
  only after the cart accepted it.
- A cached page shows the current cart count.
- Email addresses with a plus sign and longer domain endings are accepted, error messages are read with
  their fields, and the error summary heads the form below the page title.

Blocks and content:

- Section headings and legal anchors keep unique ids on any page, a photo no longer repeats its caption as
  alt text, section text colours and background images reach the page, and the roast chart's words are
  fields.
- New Site settings for the moving roastery's pause button, the roast chart's stage names, the search
  results, the lists of entries and the missing page.
- Search results and the missing page are drawn in the design.

Images and demo content:

- The demo import asks before it changes a site with content of its own, runs one import or repair at a
  time, records a copy that dies mid-write, and exports only the attachments its records use.
- Image copies are counted the same way by the writer and the screen, and copies of edited images are
  deleted with the attachment.

Motion, accessibility and build:

- Keyboard focus finishes any entrance animation it lands on, the page is shown finished when a theme script
  fails to load, the motion follows a late font and a resized window, and the "Open today" line follows the
  site's time zone.
- Forced colours and print get their own styles.
- Line endings are LF throughout, unused partials and packages are removed, and the GSAP licence is stated
  in LICENSE.
- The editor no longer raises a notice for the block stylesheets.

Phones:

- The first header link starts on the same edge as the wordmark and the sign.
- The analytics notice is compact and covers less than a third of the first screen.
- The window frame in the home photo has equal gutters, and the photo fills the width in WebKit at 414 and 430.
- Scroll effects are measured again once the web fonts are laid out, so they start where they should in WebKit.
