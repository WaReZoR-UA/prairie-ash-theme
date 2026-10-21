# Performance decisions

Each decision with its reason. Targets, from web.dev because WordPress publishes no Core Web Vitals
guidance of its own: LCP 2.5 s or less, CLS 0.1 or less, INP 200 ms or less, at the 75th percentile.
No number is claimed here: the claim is a measurement on the deployed page, recorded below with its
date and URL once the site is deployed.

## Assets

- One global stylesheet, `assets/css/main.css`, and one script, `assets/js/main.js`, both enqueued
  in `inc/enqueue.php`. Nothing is printed as a raw tag.
- The design's compiled stylesheet is split at build time (`tools/css-split.mjs`): a rule that can
  only match where a given block or WooCommerce template renders goes to a sheet of its own in
  `assets/css/split/`, listed with its blocks and templates in `manifest.json`; everything else
  stays in `main.css`. 163,694 bytes became a 59,382 byte global sheet plus 36 sheets of 0.8 to
  11.8 KB. Block sheets load through `wp_enqueue_block_style()`, which with block assets on demand
  enqueues a sheet when one of its blocks renders and lets WordPress move it into the head;
  template sheets are enqueued on the product, cart and checkout templates. Each sheet carries its
  `path`, so WordPress prints a small one inline (`styles_inline_size_limit`, 40 KB a page). Every
  sheet depends on `main.css` and prints after it. The editor canvas gets all of them through
  `add_editor_style()`.
- The split is accepted by measurement, not by the script: the computed style of every element and
  of its `::before` and `::after`, on all 23 pages and products at 1440, 768 and 390, was identical
  before and after (69 of 69), and full-page renders were pixel-identical (69 of 69), on 2026-10-20.
  The first build split only the outer `@media` level and moved the shop card's base rule after
  its nested `@supports` grid rule, which broke the shop grid; the split now descends into nested
  groups. A static pass over every moved rule and every later global rule with the same
  specificity, a shared subject class and a shared property found 11 pairs, all between the shop
  card and the home coffee row, which never render inside each other.
- Both are versioned on the file's modification time (`prairie_ash_asset_version()`), so an edit is
  served at once. An omitted version would append the WordPress core version and serve the old file
  until core updates. This is a house decision, not a documented WordPress one.
- The script loads in the footer with the `defer` strategy and no dependency. It uses no jQuery.
- GSAP and the motion layer are not in `main.js`: they are separate chunks (`assets/js/motion.*.js` and
  the GSAP vendor chunk), fetched in idle time once four things hold (`src/js/files/motion-loader.js`):
  the page is shown (a prerendered page waits for activation), the `load` event has fired, the page
  transition has finished (`window.paReveal`, 2.5 s backstop), and the largest paint is on screen (the
  page's `fetchpriority="high"` image is complete, a frame has been presented since, and the browser has
  reported a `largest-contentful-paint` entry, 2.5 s backstop). The last gate is what keeps the chunks
  out of Home's LCP window: on a fast connection `load` and idle time can come before the first frame.
  Content already on screen when they arrive keeps its painted state.
- The stylesheet build (`tools/css-slim.mjs`) drops the `@supports not (clamp)` fallbacks the
  template's `adaptiveValue` writes and folds its three media blocks per fluid value into the one
  `clamp()` that already holds both pins: 477,740 bytes to 163,694 (gzip 32.5 KB to 17.1 KB), with
  full-page renders of six pages at five widths pixel-identical before and after.
- Page transitions are a cross-document view transition (`@view-transition` and the
  `::view-transition-*` animations in `src/scss/motion.scss`, none under reduced motion). `header.php`
  prints a render-blocking `<link rel="expect" href="#site-footer" blocking="render">`, so the new page's
  first frame waits until the whole document is parsed, and a small inline script that resolves
  `window.paReveal` when the transition has finished. A browser without view transitions simply
  navigates.
- Speculative loading is prerender at moderate eagerness (hover or pointer down), through core's
  `wp_speculation_rules_configuration` filter (`inc/performance.php`), so a page transition starts from a
  finished page. Core still turns it off for a logged-in visitor and for plain permalinks. The cart, the
  checkout and the account pages are excluded through `wp_speculation_rules_href_exclude_paths`; core
  already excludes every URL with a query string, which covers the add-to-cart links. A prerendered page
  loads no motion until it is shown, and the theme sends no analytics beacon, so a prerender that is
  never visited costs the server one page render and nothing else.
- WooCommerce's add-to-cart, cookie, block UI and notice scripts, and jQuery with them, are dequeued
  on every page that is not the shop, a product, the cart, the checkout or the account
  (`prairie_ash_dequeue_shop_scripts()` in `inc/performance.php`). The coffee cards on content pages
  add to the cart through the theme's own script.
- Formidable enqueues jQuery and its own script only on the pages that print a form (home, contact,
  wholesale; `FrmFormsController::footer_js()` checks `forms_loaded`). Both stay, because the
  plugin does not document which of its features work without its own front-end script, but they
  are deferred (`prairie_ash_defer_form_scripts()` in `inc/performance.php`), as is jQuery in the
  head of the WooCommerce pages. Formidable binds on document ready and every WooCommerce script
  that depends on jQuery is deferred already. WordPress keeps a script blocking by itself when a
  dependent has no strategy or an inline script after it (`WP_Scripts::filter_eligible_strategies()`).
  The theme's bundle reads `window.jQuery` when it needs it rather than when it loads, since on a
  form page it now runs before jQuery.
- Formidable's own stylesheet is off ("Load form styling", none). Every form rule is the theme's.

## Fonts

- All three faces are `font-display: optional` (hand-edited `src/scss/fonts/fonts.scss`), so no page
  ever swaps a face in after its first paint, including mid-transition. All three are preloaded through
  `wp_preload_resources` with `crossorigin`, since a font is a CORS-mode fetch; the italic is preloaded
  too, because under `optional` a face that is not there by the first paint is not used on that page.
- The fonts are served from the theme. No font host, so no preconnect.
- Licences, checked 2026-10-20. All three files are subsets (Latin; 220 code points for League
  Gothic, 203 for each Instrument Serif face) and keep the upstream names. SIL OFL 1.1 forbids a
  Reserved Font Name on a Modified Version ("No Modified Version of the Font Software may use the
  Reserved Font Name(s) unless explicit written permission is granted"), so the question is whether
  either family declares one. Instrument Serif: the copyright line of the upstream `OFL.txt`
  (github.com/Instrument/instrument-serif, identical to the Google Fonts copy) is "Copyright 2022
  The Instrument Serif Project Authors" with no Reserved Font Name. League Gothic: the shipped file
  is version 2.001 with the copyright "Copyright 2020 The League Gothic Project Authors"; that build
  comes from github.com/sursly/league-gothic (the source pinned in Google Fonts' `METADATA.pb`),
  whose `OFL.txt` reads "Copyright 2010 The League Gothic Project Authors" with no Reserved Font
  Name. The older 1.x line in github.com/theleagueof/league-gothic does declare one ("with Reserved
  Font Name: "League Gothic""), but that is not the build in the theme. No rename is needed. OFL
  condition 2 asks that each copy carry the copyright notice and the licence, so both licence
  texts ship beside the fonts as `assets/fonts/OFL-LeagueGothic.txt` and
  `assets/fonts/OFL-InstrumentSerif.txt`.

## Images

- One image per template is the Largest Contentful Paint candidate, marked `lcp-image` in its render
  template and printed by `prairie_ash_lcp_image()` with `loading` off and `fetchpriority="high"`:
  home hero, the title photo of menu, roastery, visit, about, contact, wholesale, shipping and shop,
  the photo of the sent and signup confirmation pages, and the bag photo of a product page.
- Every other image is lazy. Core writes the attribute itself, through its documented
  `wp_get_loading_optimization_attributes` filter (`prairie_ash_lazy_unless_lcp()` in
  `inc/images.php`). Core's own rule is a guess at the LCP: the first three content images stay
  eager, and every image printed before the main loop counts as in view, which made the whole
  shop grid eager. Every template here names its LCP image, and on a phone the rest sit below
  the first screen. No `loading="lazy"` is written by hand.
- `sizes` comes from one table, `prairie_ash_slot_sizes()` in `inc/images.php`, keyed by the slot's
  class. Every value is the width the picture is drawn at, measured in a browser at thirteen
  viewport widths from 360 to 1920 and fitted per breakpoint. A slot that crops with object-fit
  gets the width of the cropped picture, which is what the file has to cover.

## Image sizes and format

- Sub-sizes: one width-only ladder, `prairie-ash-160`, `-320`, `-480`, `-720`, `-960`, `-1280` and
  `-1600`, plus the full image. Chosen against every measured slot width at one and two device
  pixels: 160 serves the cart, checkout and receipt thumbnails, 320 to 720 the phone, the cards
  and the desktop columns, 960 to 1600 the tablet column and two-pixel desktops. Core never
  writes a rung wider than the upload.
- Removed: core's `thumbnail`, `medium`, `medium_large`, `large`, `1536x1536` and `2048x2048`, and
  WooCommerce's `woocommerce_thumbnail`, `woocommerce_single` and `woocommerce_gallery_thumbnail`.
  The registry is filtered on `intermediate_image_sizes`, which also reaches the block editor's
  in-browser upload processing (WordPress 7.1 reads the registry for it, never
  `intermediate_image_sizes_advanced`). The plugin sizes are also unregistered, because
  WooCommerce otherwise writes a missing size to disk the first time one is requested. Admin
  screens that ask for a thumbnail now receive the full image.
- `big_image_size_threshold` stays at core's 2560: the widest slot is the 1312 pixel hero, 2624
  at two device pixels, and no upload in the design exceeds 2624.
- Formats: three per size. Core writes the upload's own format, JPEG or PNG, for the full image
  and every size. `prairie_ash_add_image_formats()` (`inc/images.php`, on
  `wp_update_attachment_metadata`) writes an AVIF and a WebP copy of each, resized from the full
  image so each is compressed once, named with the source extension kept
  (`photo-720x480-jpg.avif`) so a copy never collides with another upload, and recorded under a
  `sources` key in the metadata. The hook runs on every metadata write, so it covers an upload, a
  regeneration and the sizes the block editor uploads after processing in the browser. A copy
  newer than its source is reused. GIF and WebP uploads get no copies.
- Deletion: core deletes only the files its own metadata names, so
  `prairie_ash_delete_image_formats()` deletes the copies on `delete_attachment`. Measured: an
  image uploaded and then deleted through the admin screens left no file behind.
- Markup: every slot prints `<picture>` with an AVIF `<source>`, a WebP `<source>`, each with the
  same srcset widths and sizes as the img, and the img in the upload's format as the fallback,
  carrying srcset, sizes, width, height, alt, loading and fetchpriority. The browser takes the
  first type it decodes and downloads one file. Art-directed slots (cupping, visit map) print
  their media-query sources in all three formats first. `fetchpriority` on the img applies to
  whichever source the browser picks. No image is preloaded, so no preload has to match a format.
- Quality: AVIF at 75 (`wp_editor_set_quality`). Measured on three of the design's photographs at
  800 pixels: SSIM 0.978, 0.974 and 0.979 against core's JPEG at 82 at 0.977, 0.973 and 0.981,
  at 43 to 56 percent of the JPEG's bytes. WebP stays at core's 86, JPEG at core's 82.
- Fallback, host side: the copies are written only in the formats the image editor reports it can
  write. AVIF needs GD built with AVIF or Imagick with an AVIF delegate. A host without it gets
  WebP and JPEG, without WebP the upload's own format alone, and the picture then prints only the
  sources it has. Check a host with `wp eval 'var_dump( wp_image_editor_supports( array(
  "mime_type" => "image/avif" ) ) );'`, and run `wp media regenerate` after a host change.
- Fallback, browser side: a browser without AVIF (before Chrome 85, Edge 121, Firefox 93, Safari
  16.1, per MDN) takes WebP, and one without either takes the JPEG or PNG.
- Encoding cost: GD writes AVIF slowly. A whole regeneration of one attachment, JPEG sizes plus
  both copies, took about seven seconds here. A host with a 30 second limit copes with one upload;
  a bulk regeneration runs one attachment per process.
- `wp media regenerate` on Windows: run through wp-cli the install's paths use forward slashes,
  which core's `path_is_absolute()` does not accept after a drive letter, so deleting an
  attachment from the command line leaves core's own JPEG sizes behind. Through the admin the
  paths use backslashes and everything is deleted. This is core and the local path layout, not
  the theme.

## What the theme switches off

- Emoji detection script and styles: the design and the copy carry no emoji.
- Remote block patterns: the editor offers the theme's own sections only.
- Core block styles load only where a core block renders (`should_load_separate_core_block_assets`).
  Pages are stacks of the theme's own sections.
- WooCommerce order attribution (WooCommerce, Settings, Advanced, Features): it loads sourcebuster.js
  on every page and writes `sbjs_` cookies, and the design calls for cookieless analytics. The demo
  import switches it off.

## Left at the core default, with the reason

- Resource hints: none added. The pages make no request to a third-party origin. The map is a link
  out, not an embed.
- Embeds: the design contains none, so oEmbed is left as core ships it and no embed script loads.

## Measurements

None yet: there is no deployed page. Lighthouse cannot produce INP, so that number comes from DevTools
by hand or from field data.

Local lab only, 2026-10-20, Lighthouse 13.5.0, median of three runs, a local server, with the
local gzip toggle on (production-like). Before the motion port: mobile Home 93, Shop 99, Huila Pink
Bourbon 99, Roastery 99, Contact 99; Home's mobile LCP (3.15 s) was held back by the motion chunks, which
started after `load` but before Home's first paint. After the port, with the motion chunks gated on the
largest paint: mobile Home 99 (LCP 2.05 s), Shop 99, Huila Pink Bourbon 99, Roastery 100, Contact 99;
desktop 100 on all five; Accessibility, Best Practices and SEO 100 on all ten. In the Home mobile trace
`load` fired at 242 ms, the observed LCP at 262 ms and both motion chunks were requested at 282 ms.

| Date | URL | LCP | CLS | INP | LCP element |
|---|---|---|---|---|---|
