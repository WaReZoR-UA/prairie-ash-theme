/*
Motion layer, hand-off section "Motion". Hooks are data attributes, so copy edits keep their motion.

data-motion-scope        page region whose headings, text, rows, controls, rules and photos reveal on scroll
data-motion="sign"       title sign: lettered by a brush, line by line, side block after
  data-motion-plate      the sign hangs from a hinge and swings into place first (door plate, trade sign)
data-motion="stripe"     a pinstripe, ruled from the left
data-motion="card"       a coffee sign, swings down from its hook (batched)
data-motion="price"      price lettering, stamped
data-motion="count"      a figure counts up to its value (cart and order totals count too)
data-motion="plate"      date plate: swings down like a calendar leaf, the days are torn off to the date
data-motion="wall"       painted wall advert, lines painted in while scrolling
data-motion="scene"      pinned scene on desktop: camera pushes in, heat and steam rise behind the glass
data-motion="map"        map stands up, streets drawn, then the walk from the bus stop is traced
data-motion="wordmark"   footer wordmark: letters drawn and filled, then lit by a torch
data-motion="reveal"     any other block that should fade up with its neighbours
data-motion-parallax     photo slot whose image drifts while in view
data-motion-skip         opt out, the subtree keeps its static look
data-motion="roast-*"    Roastery scenes, their own chunk (js/files/roastery.js)

Sections of a page take turns with distinct scrubbed entrances (tilt, swing, depth, counter-motion), and
on a wide screen a few bands stay pinned while the next one slides over them.

The stylesheet always holds the finished page. Every start state is set here, inside
gsap.matchMedia() under prefers-reduced-motion: no-preference, so no JS or reduced motion
gives the settled page.
*/

import { gsap, ScrollTrigger } from "./gsap.js";
import { CustomEase } from "gsap/CustomEase";
import { ScrollSmoother } from "gsap/ScrollSmoother";
import { Flip } from "gsap/Flip";
import { MotionPathPlugin } from "gsap/MotionPathPlugin";
import { letterSign, splitLines } from "./lettering.js";
import { rule, stamp, stampPivot, figure } from "./paint.js";
import { layer, onScroll, swing, uncover, sheen, platesIn, tipUp, riseIn, signLettered } from "./motion-utils.js";

gsap.registerPlugin(ScrollSmoother, Flip, MotionPathPlugin);
ScrollTrigger.config({ ignoreMobileResize: true });

// Tokens live on :root as --motion-*, so CSS transitions and GSAP share one set of values
const rootStyle = getComputedStyle(document.documentElement);
const token = (name, fallback) => {
	const value = parseFloat(rootStyle.getPropertyValue(`--motion-${name}`));
	return Number.isFinite(value) ? value : fallback;
};
const D = {
	fast: token("duration-fast", 160) / 1000,
	base: token("duration-base", 280) / 1000,
	brush: token("duration-brush", 420) / 1000,
	slow: token("duration-slow", 600) / 1000,
	showpiece: token("duration-showpiece", 1200) / 1000,
};
const STAGGER = { line: 0.16, item: 0.04, card: 0.08 };
const RISE = token("distance-rise", 16);
CustomEase.create("brush", "M0,0 C0.7,0 0.2,1 1,1");
const OUT = "expo.out";
const IN_OUT = "power3.inOut";
const START = "clamp(top 95%)";
// fires just before the element enters, so nothing in view ever waits for its entrance
const EARLY = "clamp(top bottom+=100)";
const SVG_NS = "http://www.w3.org/2000/svg";

// A page carrying data-motion-scrub drives its motion by the scroll position, through onScroll
const SCRUB_PAGE = !!document.querySelector("[data-motion-scrub]");

const REDUCED = "(prefers-reduced-motion: reduce)";
// The motion measures the type once the web fonts are in, or after a second and a half at most. Faces that arrive
// later than that (font-display: swap) set the type again, so the layer is then rebuilt on them, see the end.
const fontsIn = document.fonts ? document.fonts.ready : Promise.resolve();
let fontsLanded = false;
let fontsLate = false;
fontsIn.then(() => (fontsLanded = true));
const fontsReady = Promise.race([fontsIn, new Promise((resolve) => setTimeout(() => {
	fontsLate = !fontsLanded;
	resolve();
}, 1500))]);

const TOTALS = ".lv__row--due .lv__value, .lv__row--paid .lv__value, .checkout-title__total-value";
const STAMPS = '[data-motion="price"], .roastery__fact';
const LEAF = 'h1, h2, h3, h4, p, li, tr, dl > div, dl > dt, dl > dd, figcaption, address, blockquote, legend, .field, .checkbox, .options__item, .text-link, .button, [data-motion="reveal"]';
const BLOCKER = 'img, picture, svg, table, dl, ul, ol, form, fieldset, h1, h2, h3, .field, .checkbox, [data-motion]:not([data-motion="price"]):not([data-motion="count"]):not([data-motion="stripe"]), [data-motion-skip]';
const OWNED = `[data-motion="hero"], [data-motion="sign"], [data-motion="card"], [data-motion="plate"], [data-motion="map"], [data-motion="wall"], [data-motion="wordmark"], [data-motion="roast-sign"], [data-motion="roast-curve"], [data-motion="roast-plate"], [data-motion^="shop-"], [data-motion^="product-"], [data-motion^="visit-"], [data-motion^="about-"], [data-motion^="menu-"], [data-motion^="wholesale-"], [data-motion^="contact-"], [data-motion^="signup-"], [data-motion^="cart-"], [data-motion^="checkout-"], [data-motion^="order-"], [data-motion^="shipping-"], [data-motion^="privacy-"], [data-motion^="accessibility-"], ${STAMPS}, [data-motion="count"], [data-motion-skip], [hidden], .visually-hidden, [data-error-summary-box], header`;
const isHeading = (el) => /^H[1-4]$/.test(el.tagName);
const isRow = (el) => /^(TR|DT|DD)$/.test(el.tagName) || (el.tagName === "DIV" && el.parentElement.tagName === "DL");

// Text, rows and controls of a region, outermost first, never an element inside another one
function collectLeaves(root, owned, exclude = []) {
	const out = [];
	root.querySelectorAll(LEAF).forEach((el) => {
		if (exclude.some((x) => x === el || x.contains(el))) return;
		const owner = el.closest(owned);
		if (owner && owner !== root) return;
		if (el.querySelector(BLOCKER)) return;
		if (out.some((o) => o.contains(el))) return;
		out.push(el);
	});
	return out;
}

// Wipe: the element is hidden by its own clip and opened. A wall-coloured cover laid over a photo
// leaks a device-pixel row of the photo wherever the slot edge lands between pixels.
const WIPES = [
	"inset(-12% -12% 112% -12%)", // from the top
	"inset(-12% 112% -12% -12%)", // from the left
	"inset(50% 50% 50% 50%)", // from the centre out
	"inset(-12% -12% -12% 112%)", // from the right
];
const WIPE_OPEN = "inset(-12% -12% -12% -12%)";
function wipeShut(el, kind = 0) {
	gsap.set(el, { clipPath: WIPES[kind % WIPES.length] });
}
function wipeOpen(el, vars = {}) {
	return gsap.to(el, { clipPath: WIPE_OPEN, duration: D.brush, ease: "brush", clearProps: "clipPath", ...vars });
}


// Title sign, storyboard "sign paint-in" (794:4944), lettered by a brush
function signIn(sign, ctx, cleanups, alive) {
	const stripes = [...sign.querySelectorAll('[data-motion="stripe"]')];
	const title = sign.querySelector("h1, h2");
	if (!title) return;
	const side = collectLeaves(sign, OWNED, [title]);
	const plate = sign.hasAttribute("data-motion-plate");

	gsap.set(stripes, { scaleX: 0, transformOrigin: (i) => (i % 2 ? "100% 50%" : "0% 50%") });
	gsap.set(title, { opacity: 0 });
	gsap.set(side, { opacity: 0, y: RISE, rotationX: -35, transformPerspective: 700, transformOrigin: "50% 0%" });
	// a hung plate swings down from its hinge before it is lettered
	if (plate) gsap.set(sign, { rotationX: -82, transformPerspective: 1400, transformOrigin: "50% 0%", opacity: 0 });

	const sideStagger = Math.min(STAGGER.item * 1.5, 0.6 / Math.max(side.length, 1));
	let played = false;
	const play = () => {
		if (played) return;
		played = true;
		fontsReady.then(() => {
			if (!alive()) return;
			ctx.add(() => {
				// lettering starts while the plate is still swinging, so the sign is never a blank board
				const t0 = plate ? 0.12 : 0;
				let lettering = null;
				const tl = gsap.timeline({
					onComplete: () => {
						gsap.set(stripes, { clearProps: "transform" });
						lettering.remove();
					},
				});
				if (plate) {
					tl.to(sign, { opacity: 1, duration: 0.18 }, 0);
					swing(tl, sign, 0, { steps: [[0, 1.3, "elastic.out(1, 0.45)"]], first: { clearProps: "transform,opacity" } });
				}
				if (stripes[0]) rule(tl, stripes[0], t0, { held: true, duration: D.brush * 1.4, ease: "expo.inOut" });
				// measured flat by the lettering, while the plate still hangs from its hinge
				lettering = letterSign(title, cleanups, { mode: "lines", pass: D.brush * 1.7, gap: STAGGER.line * 1.5, ease: "brush", settle: D.slow });
				tl.add(lettering.tl, t0 + 0.05);
				stripes.slice(1).forEach((stripe, i) => rule(tl, stripe, t0 + 0.75 + i * 0.08, { held: true, duration: D.brush * 1.4, ease: "expo.inOut" }));
				tl.to(side, { opacity: 1, y: 0, rotationX: 0, duration: D.slow * 1.3, ease: OUT, stagger: sideStagger, clearProps: "opacity,transform" }, t0 + 0.3);
			});
		});
	};
	// on the first screen or below it is read once the web fonts have set the page above it
	fontsReady.then(() => {
		if (!alive()) return;
		ctx.add(() => {
			if (sign.getBoundingClientRect().top < window.innerHeight * 0.95) play();
			else ScrollTrigger.create({ trigger: sign, start: START, once: true, onEnter: play });
		});
	});
}

// Section headings: h2 lettered by the brush on a wide screen; h3 and h4 tip up off the wall line by line
function headingIn(el, delay, ctx, cleanups, alive, wide) {
	fontsReady.then(() => {
		if (!alive()) return;
		ctx.add(() => {
			gsap.set(el, { opacity: 1 });
			if (el.tagName === "H2" && wide) {
				const lettering = letterSign(el, cleanups, { mode: "lines", pass: D.brush * 1.7, gap: STAGGER.line * 1.5, ease: "brush", settle: D.slow });
				lettering.tl.delay(delay).eventCallback("onComplete", lettering.remove);
				return;
			}
			tipUp(gsap.timeline({ delay }), el, 0, cleanups, { rise: RISE, duration: D.slow * 1.2, ease: OUT, stagger: STAGGER.line, revert: true });
		});
	});
}

// Scroll-driven headings: an h2 is lettered by a brush loaded with its own colour, an h3 or h4
// tips up off the wall line by line; both are complete when the heading reaches the eye band's end
function headingOnScroll(el, ctx, cleanups, alive, wide) {
	gsap.set(el, { opacity: 0 });
	fontsReady.then(() => {
		if (!alive()) return;
		ctx.add(() => {
			gsap.set(el, { opacity: 1 });
			if (el.tagName === "H2" && wide) {
				onScroll(el, letterSign(el, cleanups, { mode: "lines", split: true, pass: D.brush * 1.7, gap: STAGGER.line * 1.5, ease: "power2.inOut", settle: D.slow, paint: true }).tl, { end: "top 50%" });
				return;
			}
			onScroll(el, tipUp(gsap.timeline(), el, 0, cleanups, { rise: RISE, duration: 1, stagger: 0.25 }), { end: "top 50%" });
		});
	});
}

// Running text rises, table rows flip down like a lettered board, controls slide in from the side
function leavesIn(leaves, ctx, cleanups, alive, wide) {
	if (!leaves.length) return;
	if (SCRUB_PAGE) {
		leaves.filter(isHeading).forEach((el) => headingOnScroll(el, ctx, cleanups, alive, wide));
		leaves = leaves.filter((el) => !isHeading(el));
		// a row of a lettered board flips down from its top edge and lies flat on its own rule line
		leaves.filter(isRow).forEach((el) => {
			const tl = swing(gsap.timeline(), el, 0, { from: -80, steps: [[0, 1, "power2.out"]], hinge: { transformPerspective: 900, transformOrigin: "50% 0%" }, start: { opacity: 0 }, first: { opacity: 1 } });
			onScroll(el, tl, { start: "top 88%", end: "top 62%" });
		});
		leaves = leaves.filter((el) => !isRow(el));
		// running text rises into its line, a control slides in from the column edge it ends on
		leaves.forEach((el) => {
			riseIn(el, el.matches(".button, .text-link") ? { opacity: 0, x: -24 } : { opacity: 0, y: RISE });
		});
		return;
	}
	const control = (el) => el.matches(".button, .text-link");
	leaves.forEach((el) => {
		if (isHeading(el)) gsap.set(el, { opacity: 0 });
		else if (isRow(el)) gsap.set(el, { opacity: 0, rotationX: -88, transformPerspective: 900, transformOrigin: "50% 0%" });
		else if (control(el)) gsap.set(el, { opacity: 0, x: -24 });
		else gsap.set(el, { opacity: 0, y: RISE });
	});
	ScrollTrigger.batch(leaves, {
		start: EARLY,
		once: true,
		onEnter: (batch) => {
			batch.forEach((el, i) => {
				const delay = i * STAGGER.item;
				if (isHeading(el)) headingIn(el, delay, ctx, cleanups, alive, wide);
				else if (isRow(el)) swing(gsap.timeline({ delay: i * 0.07 }), el, 0, { steps: [[0, D.slow * 1.2, "back.out(1.4)"]], first: { opacity: 1, clearProps: "opacity,transform" } });
				else gsap.to(el, { opacity: 1, x: 0, y: 0, duration: D.slow, ease: OUT, delay, clearProps: "opacity,transform" });
			});
		},
	});
}

function stripesIn(stripes) {
	if (!stripes.length) return;
	if (SCRUB_PAGE) {
		// a rule is drawn the way a painter pulls it, left to right, to the full measure of the column
		stripes.forEach((el) => onScroll(el, rule(gsap.timeline(), el, 0, { duration: 1, ease: "power2.inOut" }).tl, { start: "top 90%", end: "top 60%" }));
		return;
	}
	gsap.set(stripes, { scaleX: 0, transformOrigin: (i) => (i % 2 ? "100% 50%" : "0% 50%") });
	ScrollTrigger.batch(stripes, {
		start: EARLY,
		once: true,
		onEnter: (batch) => {
			const tl = gsap.timeline();
			batch.forEach((el, i) => rule(tl, el, i * STAGGER.card, { held: true, duration: D.brush * 1.6, ease: "expo.inOut", clear: "transform" }));
		},
	});
}

// Coffee signs hang from a hook: each swings down from the wall, overshoots and settles
function cardsIn(cards) {
	if (!cards.length) return;
	if (SCRUB_PAGE) {
		// the rule at the top of a sign is its batten: it is ruled first, then the lettered board
		// swings down from that line and comes to rest flat on the wall, the rule never moving
		const tops = new Map();
		cards.forEach((card) => {
			const stripe = card.querySelector('[data-motion="stripe"]');
			// the body is display: contents, so the whole sign turns, about its top edge, which is the rule
			const body = card;
			const top = Math.round(card.getBoundingClientRect().top);
			const col = tops.get(top) || 0;
			tops.set(top, col + 1);
			const tl = gsap.timeline();
			if (stripe) rule(tl, stripe, col * 0.12, { duration: 0.35, ease: "power2.inOut" });
			const at = stripe ? 0.2 + col * 0.12 : col * 0.12;
			swing(tl, body, at, { from: -75, steps: [[9, 0.45, "power2.in"], [-3.5, 0.2, "sine.inOut"], [0, 0.2, "sine.out"]], hinge: { transformPerspective: 1100, transformOrigin: "50% 0%" }, start: { opacity: 0 }, first: { opacity: 1 } });
			onScroll(card, tl, { start: "top 88%", end: "top 45%" });
		});
		return;
	}
	const stripes = cards.map((card) => card.querySelector('[data-motion="stripe"]')).filter(Boolean);
	gsap.set(cards, { opacity: 0, rotationX: -78, rotation: (i) => (i % 2 ? 3 : -3), transformPerspective: 1100, transformOrigin: "50% 0%" });
	gsap.set(stripes, { scaleX: 0, transformOrigin: "0% 50%" });
	ScrollTrigger.batch(cards, {
		start: EARLY,
		once: true,
		onEnter: (batch) => {
			gsap.to(batch, { opacity: 1, duration: 0.25, stagger: STAGGER.card * 1.5 });
			const settle = gsap.timeline();
			batch.forEach((card, i) => swing(settle, card, i * STAGGER.card * 1.5, { steps: [[0, 1.6, "elastic.out(1, 0.5)"]], first: { rotation: 0, clearProps: "opacity,transform,transformOrigin" } }));
			const own = batch.map((card) => card.querySelector('[data-motion="stripe"]')).filter(Boolean);
			const ruled = gsap.timeline({ delay: 0.4 });
			own.forEach((stripe, i) => rule(ruled, stripe, i * STAGGER.card, { held: true, duration: D.brush, ease: "brush", clear: "transform,transformOrigin" }));
		},
	});
}

function settle(base, parallax) {
	const vars = { scale: base, duration: D.showpiece, ease: OUT };
	if (!parallax) vars.clearProps = "transform";
	return vars;
}

// A page's scenes come in their own chunk. When it cannot be fetched (a deploy replaced the hashed files under an
// open tab) or its scene fails to build, the page is left as the stylesheet draws it: the head flag is handed
// back at once, rather than the faint first paint waiting for the stylesheet's own fallback
let chunkFailed = false;
function pageChunk(load, start, ctx, alive) {
	load.then((module) => alive() && ctx.add(() => start(module))).catch((error) => {
		const flags = document.documentElement.dataset;
		Object.keys(flags).forEach((flag) => {
			if (flags[flag] === "pre" || flags[flag] === "letter") signLettered(flag);
		});
		if (chunkFailed) return;
		chunkFailed = true;
		console.warn("Motion: a page scene could not start.", error);
	});
}

// Home hero: the sign repainted, the roastery alive behind the glass, the camera pushed in by the scroll
// the scene is its own chunk, so it never weighs on the first paint
function heroIn(hero, ctx, cleanups, alive) {
	pageChunk(import(/* webpackChunkName: "hero" */ "./hero.js"), ({ heroScene }) => heroScene(hero, { ctx, cleanups, alive, fontsReady }), ctx, alive);
}

// Roastery: its scenes are their own chunk too
function roasteryIn(ctx, cleanups, alive, wide) {
	pageChunk(import(/* webpackChunkName: "roastery" */ "./roastery.js"), ({ roasteryMotion }) => roasteryMotion({ ctx, cleanups, alive, fontsReady, wide }), ctx, alive);
}

// Shop: the title label and the coffee shelf are their own chunk
function shopIn(ctx, cleanups, alive, wide) {
	pageChunk(import(/* webpackChunkName: "shop" */ "./shop.js"), ({ shopMotion }) => shopMotion({ ctx, cleanups, alive, fontsReady, wide }), ctx, alive);
}

// Product: the summary, its boards and the origin photo are their own chunk
function productIn(ctx, cleanups, alive, wide) {
	pageChunk(import(/* webpackChunkName: "product" */ "./product.js"), ({ productMotion }) => productMotion({ ctx, cleanups, alive, fontsReady, wide }), ctx, alive);
}

// Visit: the door plate, the hours board and the facts boards are their own chunk
function visitIn(ctx, cleanups, alive, wide) {
	pageChunk(import(/* webpackChunkName: "visit" */ "./visit.js"), ({ visitMotion }) => visitMotion({ ctx, cleanups, alive, fontsReady, wide }), ctx, alive);
}

// About: the sign on the dark wall and the people are their own chunk
function aboutIn(ctx, cleanups, alive, wide) {
	pageChunk(import(/* webpackChunkName: "about" */ "./about.js"), ({ aboutMotion }) => aboutMotion({ ctx, cleanups, alive, fontsReady, wide }), ctx, alive);
}

// Menu: the bar board, the price boards, the pastry case and the notes are their own chunk
function menuIn(ctx, cleanups, alive, wide) {
	pageChunk(import(/* webpackChunkName: "menu" */ "./menu.js"), ({ menuMotion }) => menuMotion({ ctx, cleanups, alive, fontsReady, wide }), ctx, alive);
}

// Wholesale: the trade sign, the account board, the inquiry ticket and the sent ticket are their own chunk
function wholesaleIn(ctx, cleanups, alive, wide) {
	pageChunk(import(/* webpackChunkName: "wholesale" */ "./wholesale.js"), ({ wholesaleMotion }) => wholesaleMotion({ ctx, cleanups, alive, fontsReady, wide }), ctx, alive);
}

// Contact: its scenes are their own chunk too
function contactIn(ctx, cleanups, alive, wide) {
	pageChunk(import(/* webpackChunkName: "contact" */ "./contact.js"), ({ contactMotion }) => contactMotion({ ctx, cleanups, alive, fontsReady, wide }), ctx, alive);
}

// Signup confirmation: its sign is its own chunk too
function signupIn(ctx, cleanups, alive, wide) {
	pageChunk(import(/* webpackChunkName: "signup" */ "./signup.js"), ({ signupMotion }) => signupMotion({ ctx, cleanups, alive, fontsReady, wide }), ctx, alive);
}

// Cart: the bags on the counter, the receipt and the empty cart's note are their own chunk too
function cartIn(ctx, cleanups, alive, wide) {
	pageChunk(import(/* webpackChunkName: "cart" */ "./cart.js"), ({ cartMotion }) => cartMotion({ ctx, cleanups, alive, fontsReady, wide }), ctx, alive);
}

// Checkout: the title sign and the order form in ink are their own chunk too
function checkoutIn(ctx, cleanups, alive, wide) {
	pageChunk(import(/* webpackChunkName: "checkout" */ "./checkout.js"), ({ checkoutMotion }) => checkoutMotion({ ctx, cleanups, alive, fontsReady, wide }), ctx, alive);
}

// Order confirmation: the sign, the struck order number and the printed receipt are their own chunk too
function orderIn(ctx, cleanups, alive, wide) {
	pageChunk(import(/* webpackChunkName: "order" */ "./order.js"), ({ orderMotion }) => orderMotion({ ctx, cleanups, alive, fontsReady, wide }), ctx, alive);
}

// Shipping, pickup and returns: the sign, the rate board and the notes are their own chunk too
function shippingIn(ctx, cleanups, alive, wide) {
	pageChunk(import(/* webpackChunkName: "shipping" */ "./shipping.js"), ({ shippingMotion }) => shippingMotion({ ctx, cleanups, alive, fontsReady, wide }), ctx, alive);
}

// Privacy policy: the sign, the contents and the parts are one chunk with the accessibility statement (legal.js)
function privacyIn(ctx, cleanups, alive, wide) {
	pageChunk(import(/* webpackChunkName: "legal" */ "./legal.js"), ({ legalMotion }) => legalMotion({ ctx, cleanups, alive, fontsReady, wide }, { flag: "privacy", guide: "privacy-rule" }), ctx, alive);
}

// Accessibility statement: the same reading-page chunk, its closing dated note written by the pen
function accessibilityIn(ctx, cleanups, alive, wide) {
	pageChunk(import(/* webpackChunkName: "legal" */ "./legal.js"), ({ legalMotion }) => legalMotion({ ctx, cleanups, alive, fontsReady, wide }, { flag: "accessibility", guide: "accessibility-rule", lastWritten: true }), ctx, alive);
}

function mediaIn(imgs, wide, cleanups) {
	imgs.forEach((img, n) => {
		const slot = img.closest('[class*="-ibg"]') || img.parentElement;
		const parallax = slot.hasAttribute("data-motion-parallax");
		const base = parallax ? 1.1 : 1;
		if (img.getAttribute("fetchpriority") === "high") {
			// the photo above the fold settles in place, then light crosses its glass
			gsap.fromTo(img, { scale: base + 0.06 }, settle(base, parallax));
			sheen(slot, cleanups, { at: 0.9 });
		} else if (SCRUB_PAGE) {
			// uncovered from the edge that faces its text, so the light comes from the copy it
			// illustrates; a full-width photo is uncovered from the top. Nothing turns in its frame
			uncover(slot, img, { from: { scale: base + 0.12 }, to: { scale: base } });
		} else {
			// opened from a side that changes photo by photo, so the part in view is never an empty block
			wipeShut(slot, n);
			gsap.set(img, { scale: base + 0.14, rotation: n % 2 ? 1.5 : -1.5 });
			ScrollTrigger.create({
				trigger: slot,
				start: "clamp(top 98%)",
				once: true,
				onEnter: () => {
					wipeOpen(slot, { duration: 1.1, ease: "power3.out" });
					gsap.to(img, { ...settle(base, parallax), rotation: 0 });
				},
			});
		}
		if (parallax) {
			const amp = wide ? 4.5 : 3;
			gsap.fromTo(img, { yPercent: -amp }, { yPercent: amp, ease: "none", scrollTrigger: { trigger: slot, start: "top bottom", end: "bottom top", scrub: true } });
		}
	});
}

// Prices are stamped onto the board. A scroll page presses them straight down onto their line as the page is read;
// elsewhere each is struck as it comes into view, set down crooked by the hand and shaken square
function stampsIn(stamps) {
	if (!stamps.length) return;
	if (SCRUB_PAGE) {
		stamps.forEach((el) => onScroll(el, stamp(gsap.timeline(), el, 0, null, { press: 1, inkAt: 0.45, inkFor: 0.55, screen: true }), { start: "top 88%", end: "top 62%" }));
		return;
	}
	const struck = { size: 1.9, tilt: -6 };
	const pivots = new Map(stamps.map((el) => {
		const pivot = stampPivot(el, null, { ...struck, screen: true });
		gsap.set(el, { opacity: 0, scale: pivot.s, rotation: struck.tilt, transformOrigin: `${pivot.cx.toFixed(1)}px 50%` });
		return [el, pivot];
	}));
	ScrollTrigger.batch(stamps, {
		start: EARLY,
		once: true,
		onEnter: (batch) => {
			const tl = gsap.timeline();
			batch.forEach((el, i) => stamp(tl, el, i * 0.07, null, { ...struck, from: 0, press: 0.3, ease: "power4.in", inkAt: 0, inkFor: 0.3, inkEase: "power4.in", land: i % 3 === 1 ? 1.2 : -0.8, wobble: [0.5, "elastic.out(1.2, 0.4)"], pivot: pivots.get(el) }));
		},
	});
}

// Figures count up to their value, decimals and currency kept, so a total ticks up like a till; the figure is read and
// shown by the numbering stamp's own wheels, reading the nearest value as a till does
function countsIn(counts, cleanups) {
	counts.forEach((el) => {
		const fig = figure(el, cleanups, { read: "till" });
		if (!fig || !fig.target) return;
		fig.show(0);
		const state = { value: 0 };
		ScrollTrigger.create({
			trigger: el,
			start: START,
			once: true,
			onEnter: () => gsap.to(state, {
				value: fig.target,
				duration: fig.decimals ? 1.3 : 0.9,
				ease: "power3.out",
				delay: 0.15,
				onUpdate: () => fig.show(state.value),
				onComplete: fig.restore,
			}),
		});
	});
}


// Painted wall advert on Home: each line is painted in as it comes into view. Played, not scrubbed,
// so a visitor who stops scrolling never looks at a half-painted line
function wallsIn(walls, ctx, cleanups, alive) {
	if (SCRUB_PAGE) {
		// painted, not slid: each line is lettered by the same brush as the signs, in the advert's
		// own cream, left to right as a painter works, between its rules
		walls.forEach((wall) => {
			const stripes = [...wall.querySelectorAll('[data-motion="stripe"]')];
			const rows = [...wall.querySelectorAll("li")];
			stripes.forEach((stripe) => onScroll(stripe, rule(gsap.timeline(), stripe, 0, { duration: 1, ease: "power2.inOut" }).tl, { start: "top 90%", end: "top 60%" }));
			gsap.set(rows, { opacity: 0 });
			fontsReady.then(() => {
				if (!alive()) return;
				ctx.add(() => {
					gsap.set(rows, { opacity: 1 });
					rows.forEach((row) => onScroll(row, letterSign(row, cleanups, { mode: "lines", split: true, pass: D.brush * 1.7, gap: 0.2, ease: "power2.inOut", settle: D.slow, paint: true }).tl, { start: "top 85%", end: "top 50%" }));
				});
			});
		});
		return;
	}
	walls.forEach((wall) => {
		const stripes = [...wall.querySelectorAll('[data-motion="stripe"]')];
		const rows = [...wall.querySelectorAll("li")];
		gsap.set(stripes, { scaleX: 0, transformOrigin: "0% 50%" });
		gsap.set(rows, { opacity: 0 });
		const ruleOn = (el, delay = 0) => ScrollTrigger.create({ trigger: el, start: START, once: true, onEnter: () => rule(gsap.timeline({ delay }), el, 0, { held: true, duration: D.brush, ease: "brush", clear: "transform" }) });
		fontsReady.then(() => {
			if (!alive()) return;
			ctx.add(() => {
				stripes.forEach((stripe, i) => ruleOn(stripe, i === 0 ? 0 : 0.3));
				rows.forEach((row) => {
					// a line set to the right of the wall is painted from the right
					const fromRight = getComputedStyle(row).justifyContent === "flex-end";
					const dir = fromRight ? -1 : 1;
					const parts = [...row.children].map((part) => splitLines(part, cleanups));
					const masks = parts.flatMap((p) => p.masks);
					const lines = parts.flatMap((p) => p.lines);
					gsap.set(masks, { xPercent: -101 * dir });
					gsap.set(lines, { xPercent: 101 * dir, opacity: 0.35 });
					gsap.set(row, { opacity: 1 });
					ScrollTrigger.create({
						trigger: row,
						start: START,
						once: true,
						onEnter: () => gsap.timeline({ defaults: { duration: D.brush * 1.6, ease: "power2.out", stagger: 0.12 }, onComplete: () => parts.forEach((p) => p.split.revert()) })
							.to(masks, { xPercent: 0 }, 0)
							.to(lines, { xPercent: 0, opacity: 1 }, 0),
					});
				});
			});
		});
	});
}

// Steam off the hot beans: wisps travel up their own paths while the scene is pinned
const STEAM = '<svg viewBox="0 0 200 300" preserveAspectRatio="none" focusable="false"><path d="M40 300C20 240 70 210 45 160S60 80 40 20"/><path d="M90 300C110 250 60 200 90 150S70 70 95 10"/><path d="M140 300C120 245 165 205 140 150S160 75 135 15"/><path d="M175 300C190 255 160 215 180 170S165 100 180 50"/></svg>';
function steam(slot, cleanups) {
	const cloud = layer(slot, "motion-steam", cleanups, { html: STEAM });
	const wisps = [...cloud.querySelectorAll("path")];
	gsap.set(wisps, { drawSVG: "0% 0%" });
	return { cloud, wisps };
}

// Pinned scene, desktop only. The photograph is whole and the text is set from the first pixel:
// on the way in the camera eases towards the glass and the lines close up into their places,
// then, pinned, it keeps pushing in while the drum's heat warms the pane, steam rises and a reflection crosses it
function sceneLayer(slot, cleanups, style) {
	return layer(slot, "", cleanups, { style: { position: "absolute", pointerEvents: "none", zIndex: "1", ...style } });
}
function scenesIn(scenes, cleanups) {
	scenes.forEach((scene) => {
		const img = scene.querySelector("img");
		const slot = img ? img.closest('[class*="-ibg"]') || img.parentElement : null;
		const text = collectLeaves(scene, OWNED);
		const pinned = gsap.timeline({
			defaults: { ease: "none" },
			scrollTrigger: {
				trigger: scene,
				start: () => (scene.offsetHeight > window.innerHeight * 0.9 ? "top top" : "center center"),
				end: "+=70%",
				pin: true,
				pinSpacing: true, // a flex parent would turn it off by default
				scrub: 0.6,
				anticipatePin: 1,
				invalidateOnRefresh: true,
			},
		});
		const approach = gsap.timeline({
			defaults: { ease: "none" },
			scrollTrigger: { trigger: scene, start: "top bottom", end: () => pinned.scrollTrigger.start, scrub: 0.6, invalidateOnRefresh: true },
		});
		if (img && slot) {
			// the scale always exceeds twice the drift, so the photo never uncovers its slot
			approach.fromTo(img, { scale: 1.24, yPercent: 6 }, { scale: 1.14, yPercent: 3, duration: 1 }, 0);
			approach.fromTo(slot, { rotationY: -12, transformPerspective: 1600, transformOrigin: "0% 50%" }, { rotationY: 0, duration: 1 }, 0);
			// on Home the push ends on the photo's own crop in its slot, the frame the design draws
			pinned.fromTo(img, { scale: 1.14, yPercent: 3 }, { scale: SCRUB_PAGE ? 1 : 1.02, yPercent: 0, duration: 1 }, 0);
			// on Home the light and the vapour take their colours from the photograph itself: the warm
			// of the beans in the tray, the daylight of its window, the blue-grey of its own steam,
			// and every layer fades out inside its own box, so no edge of it ever shows
			const photo = SCRUB_PAGE;
			const heat = sceneLayer(slot, cleanups, {
				inset: "0",
				mixBlendMode: "soft-light",
				background: photo
					? "radial-gradient(ellipse 60% 55% at 47% 68%, rgba(158, 126, 101, 0.9) 0%, rgba(158, 126, 101, 0.35) 50%, rgba(158, 126, 101, 0) 100%)"
					: "radial-gradient(ellipse 70% 80% at 45% 70%, rgba(217, 164, 65, 0.95) 0%, rgba(201, 148, 46, 0.45) 45%, rgba(94, 36, 23, 0) 75%)",
			});
			const sheenLayer = sceneLayer(slot, cleanups, photo
				? { top: "-10%", bottom: "-10%", left: "0", width: "30%", transform: "skewX(-14deg)", background: "radial-gradient(closest-side, rgba(242, 240, 238, 0.2), rgba(242, 240, 238, 0))" }
				: {
					top: "-20%",
					bottom: "-20%",
					left: "0",
					width: "35%",
					background: "linear-gradient(100deg, rgba(255, 248, 235, 0) 0%, rgba(255, 248, 235, 0.16) 45%, rgba(255, 248, 235, 0.26) 50%, rgba(255, 248, 235, 0) 60%)",
				});
			const { cloud, wisps } = steam(slot, cleanups);
			if (photo) cloud.classList.add("motion-steam--photo");
			pinned.fromTo(heat, { opacity: 0 }, { keyframes: [{ opacity: 1, duration: 0.6 }, { opacity: 0.6, duration: 0.4 }] }, 0);
			sheen(slot, cleanups, { tl: pinned, at: 0.15, glint: sheenLayer, travel: [-120, 320], duration: 0.6, ease: null, stay: true });
			pinned.fromTo(cloud, { yPercent: 20 }, { yPercent: -25, duration: 1 }, 0);
			wisps.forEach((wisp, i) => {
				pinned.fromTo(wisp, { drawSVG: "0% 0%" }, { drawSVG: "35% 100%", duration: 0.7, ease: "power1.inOut" }, 0.05 + i * 0.08);
				pinned.fromTo(wisp, { opacity: 0 }, { keyframes: [{ opacity: 0.85, duration: 0.25 }, { opacity: 0.6, duration: 0.25 }, { opacity: 0, duration: 0.2 }] }, 0.05 + i * 0.08);
			});
		}
		// lines close up from a slightly open spacing, opaque throughout
		if (text.length) approach.fromTo(text, { y: (i) => 14 + i * 10 }, { y: 0, duration: 0.8, ease: "power1.out" }, 0.2);
	});
}

// Route from the bus stop to the door, in the map's own coordinates. Built for every visitor:
// the finished drawing is the walk already traced, the motion only draws it
const VISIT_ROUTES = {
	wide: { box: [1280, 960], points: [[316, 760], [316, 708], [278, 708], [278, 360], [496, 360], [496, 330]] },
	narrow: { box: [684, 512], points: [[31, 428], [31, 386], [152, 386], [152, 198], [264, 198], [264, 176]] },
};
let routeSeq = 0;
function routeSvg(host) {
	const id = `route-${++routeSeq}`;
	const svg = document.createElementNS(SVG_NS, "svg");
	svg.setAttribute("class", "motion-route");
	svg.setAttribute("aria-hidden", "true");
	svg.setAttribute("focusable", "false");
	svg.setAttribute("preserveAspectRatio", "none");
	svg.innerHTML = `<defs><mask id="${id}" maskUnits="userSpaceOnUse"><path class="motion-route__reveal" fill="none" stroke="#fff"/></mask></defs><path class="motion-route__path" fill="none" mask="url(#${id})"/><circle class="motion-route__walker" r="1"/><circle class="motion-route__ring" r="1" fill="none"/>`;
	host.append(svg);
	return svg;
}
function drawRoute(svg, box, points) {
	const d = points.map(([x, y], i) => `${i ? "L" : "M"}${x} ${y}`).join("");
	const unit = Math.max(box[0], box[1]) / 200;
	svg.setAttribute("viewBox", `0 0 ${box[0]} ${box[1]}`);
	svg.querySelectorAll("path").forEach((p) => p.setAttribute("d", d));
	svg.querySelector(".motion-route__reveal").setAttribute("stroke-width", unit * 6);
	const path = svg.querySelector(".motion-route__path");
	path.setAttribute("stroke-width", unit * 0.9);
	path.setAttribute("stroke-dasharray", `${unit * 0.1} ${unit * 2}`);
	const [x, y] = points[points.length - 1];
	const walker = svg.querySelector(".motion-route__walker");
	// placed by its transform, so the walk can move it along the path in the same coordinates
	walker.setAttribute("transform", `translate(${x} ${y})`);
	walker.dataset.start = points[0].join(" ");
	walker.setAttribute("r", unit * 1.6);
	const ring = svg.querySelector(".motion-route__ring");
	ring.setAttribute("cx", x);
	ring.setAttribute("cy", y);
	ring.setAttribute("r", unit * 3);
	ring.setAttribute("stroke-width", unit * 0.5);
}
const routes = [];
function buildRoutes() {
	document.querySelectorAll(".visit-route__map").forEach((host) => {
		if (!host.querySelector("picture source")) return;
		host.classList.add("has-route");
		const svg = routeSvg(host);
		const wideMap = window.matchMedia("(min-width: 48em)");
		const sync = () => {
			const r = wideMap.matches ? VISIT_ROUTES.wide : VISIT_ROUTES.narrow;
			drawRoute(svg, r.box, r.points);
		};
		sync();
		wideMap.addEventListener("change", sync);
		routes.push({ host, svg });
	});
	document.querySelectorAll('.map[data-motion="map"]').forEach((host) => {
		const q = (s) => host.querySelector(s);
		const bus = q(".map__bus"), main = q(".map__street--main"), avenue = q(".map__street--avenue"), building = q(".map__building");
		const crosses = [...host.querySelectorAll('[class*="map__street--cross"]')];
		if (!bus || !main || !avenue || !building || !crosses.length) return;
		host.classList.add("has-route");
		const svg = routeSvg(host);
		const sync = () => {
			// layout boxes, not painted ones: the map may be standing up in 3D while this runs
			const h = { width: host.offsetWidth, height: host.offsetHeight };
			if (!h.width) return;
			const rel = (el) => {
				let x = 0, y = 0;
				for (let n = el; n && n !== host; n = n.offsetParent) {
					x += n.offsetLeft;
					y += n.offsetTop;
					if (n.offsetParent && !host.contains(n.offsetParent) && n.offsetParent !== host) break;
				}
				const w = el.offsetWidth, hh = el.offsetHeight;
				return { x, y, w, h: hh, cx: x + w / 2, cy: y + hh / 2 };
			};
			const b = rel(bus), m = rel(main), a = rel(avenue), house = rel(building);
			const cross = crosses.map(rel).sort((p, q2) => Math.abs(p.cx - b.cx) - Math.abs(q2.cx - b.cx))[0];
			drawRoute(svg, [h.width, h.height], [[b.cx, b.y], [b.cx, m.cy], [cross.cx, m.cy], [cross.cx, a.cy], [house.cx, a.cy], [house.cx, house.y + house.h + 4]].map(([x, y]) => [Math.round(x), Math.round(y)]));
		};
		sync();
		new ResizeObserver(sync).observe(host);
		routes.push({ host, svg });
	});
}
// the walker waits at the bus stop, so the map is never missing its marker
function routeStart(svg) {
	const walker = svg.querySelector(".motion-route__walker");
	const [x, y] = (walker.dataset.start || "0 0").split(" ").map(Number);
	gsap.set(svg.querySelector(".motion-route__reveal"), { drawSVG: "0% 0%" });
	gsap.set(svg.querySelector(".motion-route__ring"), { opacity: 0 });
	gsap.set(walker, { x, y });
}
function routeTimeline(svg) {
	const reveal = svg.querySelector(".motion-route__reveal");
	const path = svg.querySelector(".motion-route__path");
	const walker = svg.querySelector(".motion-route__walker");
	const ring = svg.querySelector(".motion-route__ring");
	const cx = ring.getAttribute("cx"), cy = ring.getAttribute("cy");
	return gsap.timeline()
		.fromTo(reveal, { drawSVG: "0% 0%" }, { drawSVG: "0% 100%", duration: 1.5, ease: "power1.inOut" }, 0)
		.to(walker, { motionPath: { path, start: 0, end: 1 }, duration: 1.5, ease: "power1.inOut" }, 0)
		.fromTo(ring, { scale: 0.2, opacity: 0, svgOrigin: `${cx} ${cy}` }, { scale: 1, opacity: 1, duration: 0.6, ease: "back.out(3)" }, 1.45);
}

// The map stands up off the table already drawn, the pins drop onto it, then the walk is traced
function mapsIn(maps) {
	maps.forEach((map) => {
		const building = map.querySelectorAll(".map__building");
		const markers = map.querySelectorAll(".map__marker");
		const route = routes.find((r) => r.host === map);
		gsap.set(map, { rotationX: 50, transformPerspective: 1200, transformOrigin: "50% 100%" });
		gsap.set(building, { rotationY: 90, transformPerspective: 400 });
		gsap.set(markers, { opacity: 0, y: -40, rotation: -8 });
		if (route) routeStart(route.svg);
		if (SCRUB_PAGE) {
			// the map lies down on the page, the house turns to face the street, the pins are set
			// down without a bounce, and the walk from the bus stop is traced as the page is read
			const tl = gsap.timeline({ defaults: { ease: "none" } })
				.to(map, { rotationX: 0, duration: 0.35, ease: "power2.out" }, 0)
				.to(building, { rotationY: 0, duration: 0.3, ease: "power2.out" }, 0.1)
				.to(markers, { opacity: 1, y: 0, rotation: 0, duration: 0.2, ease: "power3.in", stagger: 0.08 }, 0.2);
			if (route) {
				// the walker waits at the bus stop until the walk begins
				const walker = route.svg.querySelector(".motion-route__walker");
				const [sx, sy] = (walker.dataset.start || "0 0").split(" ").map(Number);
				tl.set(walker, { x: sx, y: sy }, 0);
				tl.add(routeTimeline(route.svg).duration(0.5), 0.45);
			}
			onScroll(map, tl, { start: "top 85%", end: "top 30%" });
			return;
		}
		ScrollTrigger.create({
			trigger: map,
			start: EARLY,
			once: true,
			onEnter: () => {
				const tl = gsap.timeline({ defaults: { ease: OUT } })
					.to(map, { rotationX: 0, duration: 1, ease: "power3.out", clearProps: "transform" }, 0)
					.to(building, { rotationY: 0, duration: D.slow * 1.4, clearProps: "transform" }, 0.2)
					.to(markers, { opacity: 1, y: 0, rotation: 0, duration: D.slow * 1.3, ease: "bounce.out", stagger: 0.12, clearProps: "opacity,transform" }, 0.25);
				if (route) tl.add(routeTimeline(route.svg), 0.6);
			},
		});
	});
}
function visitRoutesIn() {
	routes.filter((r) => r.host.classList.contains("visit-route__map")).forEach(({ host, svg }) => {
		routeStart(svg);
		gsap.set(host, { rotationX: 40, rotationZ: -2, transformPerspective: 1400, transformOrigin: "50% 100%" });
		if (SCRUB_PAGE) {
			// as the Home map: it stands up off the table, then the walk from the bus stop is traced as the page is read
			const walker = svg.querySelector(".motion-route__walker");
			const [sx, sy] = (walker.dataset.start || "0 0").split(" ").map(Number);
			const tl = gsap.timeline({ defaults: { ease: "none" } })
				.to(host, { rotationX: 0, rotationZ: 0, duration: 0.35, ease: "power2.out" }, 0)
				.set(walker, { x: sx, y: sy }, 0)
				.add(routeTimeline(svg).duration(0.5), 0.45);
			onScroll(host, tl, { start: "top 85%", end: "top 30%" });
			return;
		}
		ScrollTrigger.create({
			trigger: host,
			start: EARLY,
			once: true,
			onEnter: () => gsap.timeline()
				.to(host, { rotationX: 0, rotationZ: 0, duration: 1.1, ease: "power3.out", clearProps: "transform" })
				.add(routeTimeline(svg), 0.5),
		});
	});
}

// Footer wall wordmark: letters rise and are drawn from the centre, then filled, then a torch lights the paint
function wordmarksIn(marks, cleanups, fine) {
	marks.forEach((svg) => {
		const letters = [...svg.querySelectorAll("path")];
		if (!letters.length) return;
		// the lit copies are taken before the drawing touches the letters
		const light = torch(svg, letters, cleanups, fine);
		if (SCRUB_PAGE) {
			// painted where it stands: each letter is outlined by the brush from the middle out, then
			// flooded with paint; it does not travel, because paint on a wall does not
			gsap.set(letters, { attr: { stroke: "currentColor", "stroke-width": 3 } });
			// the torch passes every time the drawing reads as finished, not on the scrub's last eased
			// frame, and goes out once the paint is taken back well below that
			const tl = gsap.timeline({
				onUpdate: () => {
					const p = tl.progress();
					if (p >= 0.92) light.sweep();
					else if (p < 0.8) light.out();
				},
			})
				.fromTo(letters, { drawSVG: "50% 50%" }, { drawSVG: "0% 100%", duration: 0.6, ease: "power1.inOut", stagger: { each: 0.03, from: "center" } }, 0)
				.fromTo(letters, { fillOpacity: 0 }, { fillOpacity: 1, duration: 0.4, ease: "power1.in", stagger: { each: 0.03, from: "center" } }, 0.35)
				.to(letters, { attr: { "stroke-width": 0 }, duration: 0.2 }, ">-0.2");
			onScroll(svg, tl, { start: "top 92%", end: "top 45%" });
			return;
		}
		gsap.set(letters, { attr: { stroke: "currentColor", "stroke-width": 3 }, drawSVG: "50% 50%", y: 90, rotationX: -60, transformOrigin: "50% 100%", fillOpacity: 0 });
		ScrollTrigger.create({
			trigger: svg,
			start: START,
			once: true,
			onEnter: () => gsap.timeline({
				onComplete: () => {
					letters.forEach((path) => {
						path.removeAttribute("stroke");
						path.removeAttribute("stroke-width");
					});
					gsap.set(letters, { clearProps: "transform,fillOpacity,strokeDasharray,strokeDashoffset" });
					light.sweep();
				},
			})
				.to(letters, { drawSVG: "0% 100%", y: 0, rotationX: 0, duration: D.slow * 1.6, ease: OUT, stagger: { each: 0.05, from: "center" } }, 0)
				.to(letters, { fillOpacity: 1, duration: D.slow, ease: "power1.inOut", stagger: { each: 0.04, from: "center" } }, 0.45),
		});
	});
}
let torchSeq = 0;
// One pass of light across the finished paint, then on a mouse the light follows the pointer over the
// wordmark. Only one of them writes the light at a time: while the pass runs the pointer is only
// remembered. The pass always runs off the wall, and after a short rest the light flies back to the pointer.
function torch(svg, letters, cleanups, fine) {
	const id = `torch-${++torchSeq}`;
	const defs = document.createElementNS(SVG_NS, "defs");
	defs.innerHTML = `<radialGradient id="${id}" gradientUnits="userSpaceOnUse" cx="-400" cy="171" r="300"><stop offset="0" stop-color="#f1e4c8" stop-opacity="0.55"/><stop offset="0.35" stop-color="#d9a441" stop-opacity="0.5"/><stop offset="1" stop-color="#d9a441" stop-opacity="0"/></radialGradient>`;
	const lit = document.createElementNS(SVG_NS, "g");
	lit.setAttribute("fill", `url(#${id})`);
	lit.style.opacity = "0";
	letters.forEach((path) => lit.append(path.cloneNode()));
	svg.append(defs, lit);
	const grad = defs.firstChild;
	const box = svg.viewBox.baseVal;
	// the light's edge touches the first letter on its first frame and is clear of the last on its final one
	const from = -300;
	const to = box.width + 400;
	const pos = { x: from, y: box.height / 2 };
	const apply = () => {
		grad.setAttribute("cx", pos.x.toFixed(1));
		grad.setAttribute("cy", pos.y.toFixed(1));
	};
	let state = "out";
	let pass = null;
	let pointer = null;
	// the light is off the wall: it comes back by flying in from where it waits, never by appearing at the pointer
	let parked = false;
	let flight = null;
	let goal = null;
	const toX = fine ? gsap.quickTo(pos, "x", { duration: 0.2, ease: "power3", onUpdate: apply }) : null;
	const toY = fine ? gsap.quickTo(pos, "y", { duration: 0.2, ease: "power3", onUpdate: apply }) : null;
	// the pointer in the wordmark's own units, or null when it is not over the wordmark
	const target = () => {
		const m = pointer && svg.getScreenCTM();
		if (!m) return null;
		const p = new DOMPoint(pointer.x, pointer.y).matrixTransform(m.inverse());
		return p.x > -150 && p.x < box.width + 150 && p.y > -150 && p.y < box.height + 150 ? p : null;
	};
	const land = () => {
		if (!flight) return;
		flight.kill();
		flight = null;
	};
	// an eased flight from the light's own place to the pointer; a pointer that moves meanwhile moves the goal,
	// so the path bends towards it instead of starting over, and the follow takes over where the flight lands
	const fly = (p) => {
		goal = { x: p.x, y: p.y };
		if (flight) return;
		toX.tween.pause();
		toY.tween.pause();
		const sx = pos.x;
		const sy = pos.y;
		const k = { v: 0 };
		flight = gsap.to(k, {
			v: 1,
			duration: 0.65,
			ease: "sine.out",
			onUpdate: () => {
				pos.x = sx + (goal.x - sx) * k.v;
				pos.y = sy + (goal.y - sy) * k.v;
				apply();
			},
			onComplete: () => {
				flight = null;
				toX(pos.x, pos.x);
				toY(pos.y, pos.y);
			},
		});
	};
	// over the wordmark the light sits under the pointer, anywhere else it waits off the nearer end
	const aim = () => {
		if (state !== "follow" || !toX) return;
		const p = target();
		if (p && (parked || flight)) {
			parked = false;
			fly(p);
			return;
		}
		if (!p && parked) return;
		if (!p) {
			parked = true;
			land();
		}
		// started from the light's own place, so nothing that moved it before is undone
		toX(p ? p.x : pos.x < box.width / 2 ? from - 200 : to + 200, pos.x);
		toY(p ? p.y : pos.y, pos.y);
	};
	// the pass has left the wall: a short rest, then the pointer leads
	const follow = () => {
		parked = true;
		state = "rest";
		pass = gsap.delayedCall(0.3, () => {
			pass = null;
			state = "follow";
			aim();
		});
	};
	const sweep = () => {
		if (state !== "out") return;
		state = "sweep";
		parked = false;
		pos.x = from;
		pos.y = box.height / 2;
		apply();
		gsap.set(lit, { opacity: 1, overwrite: true });
		pass = gsap.to(pos, { x: to, duration: 1.6, ease: "power1.inOut", onUpdate: apply, onComplete: follow });
	};
	const out = () => {
		if (state === "out") return;
		state = "out";
		if (pass) pass.kill();
		pass = null;
		land();
		if (toX) {
			toX.tween.pause();
			toY.tween.pause();
		}
		gsap.to(lit, { opacity: 0, duration: 0.3, ease: "power1.out", overwrite: true });
	};
	const footer = svg.closest("footer") || svg.parentElement;
	// pointerover carries the place of a pointer that rests while the page scrolls under it
	const track = (e) => {
		if (e.pointerType === "touch") return;
		pointer = { x: e.clientX, y: e.clientY };
		aim();
	};
	const leave = () => {
		pointer = null;
		aim();
	};
	if (fine) {
		footer.addEventListener("pointerover", track, { passive: true });
		footer.addEventListener("pointermove", track, { passive: true });
		footer.addEventListener("pointerleave", leave);
	}
	cleanups.push(() => {
		if (pass) pass.kill();
		land();
		if (toX) {
			toX.tween.kill();
			toY.tween.kill();
		}
		footer.removeEventListener("pointerover", track);
		footer.removeEventListener("pointermove", track);
		footer.removeEventListener("pointerleave", leave);
		defs.remove();
		lit.remove();
	});
	return { sweep, out };
}

// A gold pinstripe along the top edge of the window, ruled as the page is read
// whether the page is long enough for it is read once the web fonts are in, since they set the page's length
function progressIn(ctx, cleanups, alive) {
	fontsReady.then(() => alive() && ctx.add(() => progressBar(cleanups)));
}
function progressBar(cleanups) {
	if (document.documentElement.scrollHeight < window.innerHeight * 2.2) return;
	const bar = document.createElement("div");
	bar.className = "scroll-stripe";
	bar.setAttribute("aria-hidden", "true");
	document.body.append(bar);
	cleanups.push(() => bar.remove());
	gsap.fromTo(bar, { scaleX: 0 }, { scaleX: 1, ease: "none", scrollTrigger: { start: 0, end: "max", scrub: 0.3 } });
}

// Sections take turns with distinct entrances, so no two neighbours arrive the same way.
// Scrubbed transforms only: a band is fully painted and readable at every scroll position
const CHOREO = {
	tilt: (c, wide) => [{ rotationX: wide ? 10 : 6, y: wide ? 80 : 40, transformPerspective: 1400, transformOrigin: "50% 0%" }, { rotationX: 0, y: 0 }],
	// hinged on its left edge, so nothing swings out of the window
	swing: (c, wide) => [{ rotationY: wide ? -9 : -5, transformPerspective: 1800, transformOrigin: "0% 50%" }, { rotationY: 0 }],
	depth: (c, wide) => [{ scale: wide ? 0.88 : 0.94, y: wide ? 90 : 40, transformOrigin: "50% 0%" }, { scale: 1, y: 0 }],
};
// neighbours always differ in family and axis: depth on X, lateral, scale toward the viewer, depth on Y
const ORDER = ["tilt", "counter", "depth", "swing", "counter", "depth"];
const freeToMove = (k) => !k.matches(LEAF) && k.offsetHeight > 0 && !k.matches("[data-motion], .visit-route__map, .has-route, [data-motion-skip]") && !k.querySelector('[data-motion="scene"]');
function counterTargets(container) {
	let kids = [...container.children].filter(freeToMove);
	if (kids.length < 2 && kids[0]) kids = [...kids[0].children].filter(freeToMove);
	return kids.length >= 2 ? kids : null;
}
// Which block of each band takes its entrance, and which bands stack, is read from the layout. The web fonts change
// that layout, so the sizes are read again once they are in, and the bands are set up again if the answer changed: a
// visit with the fonts already cached and a visit that waits for them end with the same choreography. What the bands
// are made of is read once, at the start, before any page module has added its own layers to them.
function sectionsShape(main) {
	const busy = (el) => !!el.querySelector('[data-motion="scene"], [data-motion="sign"], [data-motion="map"], form, [data-motion="roast-scene"]');
	return [...main.children].filter((s) => s.tagName === "SECTION" && s.offsetHeight > 0).map((section, i) => {
		const skip = i === 0 || !!section.querySelector('[data-motion="scene"], [data-motion="sign"], [data-motion="roast-scene"], [data-motion="roast-sign"]');
		const container = section.querySelector(':scope > [class*="__container"]') || section.firstElementChild;
		const heads = container ? [...container.children].filter((k) => !k.matches('[data-motion="stripe"]')).map((k) => ({ el: k, motion: k.matches("[data-motion]"), kids: counterTargets(k) })) : [];
		return { section, skip, quiet: !!section.querySelector("form"), container, heads, busy: busy(section), opaque: opaque(section), kids: container ? counterTargets(container) : null };
	});
}
function sectionsPlan(shape, wide) {
	const kinds = new Map();
	const bands = [];
	let turn = 0;
	shape.forEach(({ section, skip, quiet, container: box, heads, kids: boxKids }) => {
		if (skip) return;
		// a form is quiet on purpose: its fields only rise, the band stays still
		if (quiet) {
			kinds.set(section, "quiet");
			bands.push({ section, kind: "quiet" });
			return;
		}
		let container = box;
		let kids = boxKids;
		// a tall block turned in depth would mislead every trigger inside it, so only its head moves
		if (container && container.offsetHeight > window.innerHeight * 1.3) {
			const head = heads.find((k) => k.el.offsetHeight > 0);
			container = head && head.el.offsetHeight <= window.innerHeight && !head.motion ? head.el : null;
			kids = container ? head.kids : null;
		}
		if (!container) return;
		let kind = ORDER[turn++ % ORDER.length];
		if (kind === "counter" && !kids) kind = "depth";
		if (kind !== "counter") kids = null;
		kinds.set(section, kind);
		bands.push({ section, container, kind, kids });
	});
	const sections = shape.map((x) => x.section);
	const stacks = wide ? stacksPlan(shape, kinds) : [];
	const index = (el) => [...el.parentElement.children].indexOf(el);
	const key = bands.map((b) => `${sections.indexOf(b.section)}:${b.kind}:${b.container ? b.container.className : ""}:${b.kids ? b.kids.map(index).join(".") : ""}`).join("|") + "/" + stacks.map((x) => sections.indexOf(x.s)).join(".");
	return { sections, bands, stacks, key };
}
function sectionsApply({ sections, bands, stacks }, wide, cleanups) {
	// on Home the band settles while it crosses the part of the viewport the eye is on
	const scrollFor = (section) => (SCRUB_PAGE
		? { trigger: section, start: "clamp(top 92%)", end: wide ? "clamp(top 40%)" : "clamp(top 50%)", scrub: 0.8 }
		: { trigger: section, start: "top bottom", end: wide ? "top 35%" : "top 55%", scrub: 0.8 });
	bands.forEach(({ section, container, kind, kids }) => {
		section.dataset.motionChoreo = kind;
		if (kind === "quiet") return;
		const st = scrollFor(section);
		if (kind === "counter") {
			kids.forEach((kid, k) => {
				const dir = k % 2 ? 1 : -1;
				gsap.fromTo(kid, { x: dir * (wide ? 90 : 28), rotationY: dir * -8, transformPerspective: 1600, transformOrigin: dir < 0 ? "100% 50%" : "0% 50%" }, { x: 0, rotationY: 0, ease: "none", scrollTrigger: st });
			});
			return;
		}
		const [from, to] = CHOREO[kind](container, wide);
		gsap.fromTo(container, from, { ...to, ease: "none", scrollTrigger: st });
	});
	cleanups.push(() => sections.forEach((s) => delete s.dataset.motionChoreo));
	stacks.forEach((pair) => stackIn(pair, cleanups));
}
function sectionsIn(main, wide, cleanups, alive) {
	let current = null;
	const build = (plan) => {
		const own = [];
		const sub = gsap.context(() => sectionsApply(plan, wide, own));
		let done = false;
		current = {
			key: plan.key,
			revert: () => {
				if (done) return;
				done = true;
				sub.revert();
				own.reverse().forEach((fn) => fn());
			},
		};
	};
	const shape = sectionsShape(main);
	build(sectionsPlan(shape, wide));
	cleanups.push(() => current.revert());
	const replan = () => {
		if (!alive()) return false;
		const plan = sectionsPlan(shape, wide);
		if (plan.key === current.key) return false;
		current.revert();
		build(plan);
		return true;
	};
	fontsReady.then(replan);
	// the window's size decides which blocks move and which bands stack, so a new size is planned again: a new
	// width always, a new height on a wide screen, where the stacks are (a phone's toolbar changes its height
	// with every scroll and plans nothing new there)
	let size = [window.innerWidth, window.innerHeight];
	let wait = null;
	const onResize = () => {
		window.clearTimeout(wait);
		wait = window.setTimeout(() => {
			const now = [window.innerWidth, window.innerHeight];
			const moved = now[0] !== size[0] || (wide && now[1] !== size[1]);
			size = now;
			if (moved && replan()) ScrollTrigger.refresh();
		}, 250);
	};
	window.addEventListener("resize", onResize);
	cleanups.push(() => {
		window.clearTimeout(wait);
		window.removeEventListener("resize", onResize);
	});
}

// Stack: a band stays pinned and sinks back while the next band slides over it, twice a page at most
function opaque(el) {
	const bg = getComputedStyle(el).backgroundColor;
	return bg && bg !== "transparent" && !/rgba\(.*,\s*0\)$/.test(bg);
}
function stacksPlan(shape, kinds) {
	const pairs = [];
	for (let i = 1; i < shape.length - 1 && pairs.length < 2; i++) {
		const s = shape[i].section, next = shape[i + 1].section;
		// the next band must cover the whole travel, or the band after it would pass under the pinned one
		if (shape[i].busy || shape[i + 1].busy || !shape[i + 1].opaque || s.offsetHeight > window.innerHeight * 1.4 || next.offsetHeight < Math.min(s.offsetHeight, window.innerHeight) + 2) continue;
		if (kinds.get(s) === "depth") continue;
		pairs.push({ s, next });
		i++;
	}
	return pairs;
}
function stackIn({ s, next }, cleanups) {
	const container = s.querySelector(':scope > [class*="__container"]') || s.firstElementChild;
	gsap.set(s, { position: "relative" });
	const shade = layer(s, SCRUB_PAGE ? "motion-shade motion-shade--brick" : "motion-shade", cleanups);
	gsap.set(next, { position: "relative", zIndex: 2 });
	cleanups.push(() => gsap.set([s, next], { clearProps: "position,zIndex" }));
	const travel = () => Math.min(s.offsetHeight, window.innerHeight);
	gsap.timeline({
		defaults: { ease: "none" },
		scrollTrigger: {
			trigger: s,
			start: () => (s.offsetHeight > window.innerHeight ? "bottom bottom" : "top top"),
			end: () => `+=${travel()}`,
			pin: true,
			pinSpacing: false,
			scrub: true,
			invalidateOnRefresh: true,
		},
	})
		// scale only: the band's own entrance owns its rotation and offset
		.fromTo(container, { scale: 1 }, { scale: 0.9, duration: 1, immediateRender: false }, 0)
		.fromTo(shade, { opacity: 0 }, { opacity: 0.55, duration: 1 }, 0);
}

// ScrollSmoother on a wide screen with a fine pointer; touch keeps native scrolling
function smoothIn(cleanups) {
	const content = document.querySelector(".wrapper");
	if (!content || ScrollSmoother.get()) return null;
	const outer = document.createElement("div");
	outer.className = "smooth-wrapper";
	content.before(outer);
	outer.append(content);
	// fixed layers cannot live inside the transformed content
	const fixed = [...content.querySelectorAll("[data-analytics-notice]")].map((el) => ({ el, parent: el.parentNode, next: el.nextSibling }));
	fixed.forEach(({ el }) => document.body.append(el));
	const smoother = ScrollSmoother.create({ wrapper: outer, content, smooth: 1, smoothTouch: false, effects: false });
	// a press stops the glide where the content stands, so nothing moves under the pointer before the release
	const settle = (e) => {
		if (e.button !== 0 || !content.contains(e.target)) return;
		const y = smoother.scrollTop();
		if (Math.abs(y - window.scrollY) < 1 && !gsap.isTweening(smoother)) return;
		gsap.killTweensOf(smoother, "scrollTop");
		smoother.scrollTop(y);
	};
	window.addEventListener("pointerdown", settle, true);
	cleanups.push(() => {
		window.removeEventListener("pointerdown", settle, true);
		smoother.kill();
		outer.before(content);
		outer.remove();
		fixed.forEach(({ el, parent, next }) => parent.insertBefore(el, next));
	});
	return smoother;
}

// Wrapping the page moves .wrapper, and a pressed element that is moved loses its click, so a press under
// way when the motion layer arrives is released first
function afterPress(start) {
	const content = document.querySelector(".wrapper");
	if (!content || !content.matches(":active")) {
		start();
		return;
	}
	let done = false;
	const go = () => {
		if (done) return;
		done = true;
		window.removeEventListener("pointerup", go, true);
		window.removeEventListener("pointercancel", go, true);
		window.removeEventListener("blur", go);
		// the click is dispatched with the release, so the wrap waits one task
		setTimeout(start, 0);
	};
	window.addEventListener("pointerup", go, true);
	window.addEventListener("pointercancel", go, true);
	window.addEventListener("blur", go);
}

let smoother = null;
buildRoutes();

const mm = gsap.matchMedia();
const CONDITIONS = { motion: "(prefers-reduced-motion: no-preference)", wide: "(min-width: 62em)", fine: "(pointer: fine)" };
let motionAdded = false;
afterPress(() => {
	motionAdded = true;
	mm.add(CONDITIONS, motionLayer);
});
// Faces that came after the wait: every act still running is ended in its finished state, and the layer is built
// again on the real type, so no clip, mask or split line stays cut to the fallback face. Signs already lettered
// stay lettered, since their head flags read "live" by then.
fontsIn.then(() => {
	if (!fontsLate || !motionAdded) return;
	mm.revert();
	mm.add(CONDITIONS, motionLayer);
});

function motionLayer(ctx) {
	const { motion, wide, fine } = ctx.conditions;
	if (!motion) return undefined;
	const cleanups = [];
	let live = true;
	const alive = () => live;

	smoother = wide && fine ? smoothIn(cleanups) : null;

	const owned = wide ? `${OWNED}, [data-motion="scene"], [data-motion="roast-scene"]` : OWNED;
	const scopes = [...document.querySelectorAll("[data-motion-scope]")];
	const inScopes = (selector) => scopes.flatMap((scope) => [...scope.querySelectorAll(selector)]);
	const scenes = wide ? inScopes('[data-motion="scene"]') : [];
	const insideScene = (el) => scenes.some((scene) => scene.contains(el));

	// pins first, so every trigger below is measured with their spacing in place
	scenesIn(scenes, cleanups);
	const main = document.querySelector("main.page");
	if (main) sectionsIn(main, wide, cleanups, alive);
	inScopes('[data-motion="hero"]').forEach((hero) => heroIn(hero, ctx, cleanups, alive, wide));
	if (inScopes('[data-motion^="roast-"]').length) roasteryIn(ctx, cleanups, alive, wide);
	if (inScopes('[data-motion^="shop-"]').length) shopIn(ctx, cleanups, alive, wide);
	if (inScopes('[data-motion^="product-"]').length) productIn(ctx, cleanups, alive, wide);
	if (inScopes('[data-motion^="visit-"]').length) visitIn(ctx, cleanups, alive, wide);
	if (inScopes('[data-motion^="about-"]').length) aboutIn(ctx, cleanups, alive, wide);
	if (inScopes('[data-motion^="menu-"]').length) menuIn(ctx, cleanups, alive, wide);
	if (inScopes('[data-motion^="wholesale-"]').length) wholesaleIn(ctx, cleanups, alive, wide);
	if (inScopes('[data-motion^="contact-"]').length) contactIn(ctx, cleanups, alive, wide);
	if (inScopes('[data-motion^="signup-"]').length) signupIn(ctx, cleanups, alive, wide);
	if (inScopes('[data-motion^="cart-"]').length) cartIn(ctx, cleanups, alive, wide);
	if (inScopes('[data-motion^="checkout-"]').length) checkoutIn(ctx, cleanups, alive, wide);
	if (inScopes('[data-motion^="order-"]').length) orderIn(ctx, cleanups, alive, wide);
	if (inScopes('[data-motion^="shipping-"]').length) shippingIn(ctx, cleanups, alive, wide);
	if (inScopes('[data-motion^="privacy-"]').length) privacyIn(ctx, cleanups, alive, wide);
	if (inScopes('[data-motion^="accessibility-"]').length) accessibilityIn(ctx, cleanups, alive, wide);
	inScopes('[data-motion="sign"]').filter((sign) => !sign.closest('[data-motion="hero"], [data-motion^="wholesale-"], [data-motion^="contact-"], [data-motion^="signup-"], [data-motion^="cart-"], [data-motion^="checkout-"], [data-motion^="order-"], [data-motion^="shipping-"], [data-motion^="privacy-"], [data-motion^="accessibility-"]')).forEach((sign) => signIn(sign, ctx, cleanups, alive));
	wallsIn(inScopes('[data-motion="wall"]'), ctx, cleanups, alive);
	cardsIn(inScopes('[data-motion="card"]').filter((card) => !card.closest('[data-motion^="shop-"], [data-motion^="product-"], [data-motion^="visit-"], [data-motion^="about-"], [data-motion^="menu-"], [data-motion^="wholesale-"], [data-motion^="contact-"], [data-motion^="signup-"], [data-motion^="cart-"], [data-motion^="checkout-"], [data-motion^="order-"], [data-motion^="shipping-"], [data-motion^="privacy-"], [data-motion^="accessibility-"]')));
	platesIn(inScopes('[data-motion="plate"]'), cleanups);
	mapsIn(inScopes('[data-motion="map"]'));
	visitRoutesIn();
	countsIn([...inScopes('[data-motion="count"]'), ...inScopes(TOTALS)].filter((el) => !el.closest('[data-motion^="cart-"], [data-motion^="checkout-"], [data-motion^="order-"], [data-motion^="shipping-"], [data-motion^="privacy-"], [data-motion^="accessibility-"]')), cleanups);
	stampsIn(inScopes(STAMPS).filter((el) => !el.closest('[data-motion-skip], [data-motion^="shop-"], [data-motion^="product-"], [data-motion^="visit-"], [data-motion^="about-"], [data-motion^="menu-"], [data-motion^="wholesale-"], [data-motion^="contact-"], [data-motion^="signup-"], [data-motion^="cart-"], [data-motion^="checkout-"], [data-motion^="order-"], [data-motion^="shipping-"], [data-motion^="privacy-"], [data-motion^="accessibility-"]') && !insideScene(el)));
	stripesIn(inScopes('[data-motion="stripe"]').filter((el) => !el.closest(`[data-motion="sign"], [data-motion="card"], [data-motion="wall"], [data-motion="roast-sign"], [data-motion^="shop-"], [data-motion^="product-"], [data-motion^="visit-"], [data-motion^="about-"], [data-motion^="menu-"], [data-motion^="wholesale-"], [data-motion^="contact-"], [data-motion^="signup-"], [data-motion^="cart-"], [data-motion^="checkout-"], [data-motion^="order-"], [data-motion^="shipping-"], [data-motion^="privacy-"], [data-motion^="accessibility-"], [data-motion-skip]`) && !insideScene(el)));
	mediaIn(inScopes("img").filter((img) => !insideScene(img) && !img.closest('[data-motion-skip], .visit-route__map, [data-motion="hero"], [data-motion^="roast-"], [data-motion^="shop-"], [data-motion^="product-"], [data-motion^="visit-"], [data-motion^="about-"], [data-motion^="menu-"], [data-motion^="wholesale-"], [data-motion^="contact-"], [data-motion^="signup-"], [data-motion^="cart-"], [data-motion^="checkout-"], [data-motion^="order-"], [data-motion^="shipping-"], [data-motion^="privacy-"], [data-motion^="accessibility-"]')), wide, cleanups);
	leavesIn(scopes.flatMap((scope) => collectLeaves(scope, owned)).filter((el) => !insideScene(el) && !el.matches(TOTALS)), ctx, cleanups, alive, wide);
	wordmarksIn(inScopes('[data-motion="wordmark"]'), cleanups, fine && wide);
	progressIn(ctx, cleanups, alive);

	// measure again with the final fonts and splits
	fontsReady.then(() => {
		if (!alive()) return;
		ScrollTrigger.refresh();
		// a clamped start lands on exactly 0 for anything in view at load, and ScrollTrigger enters only past its start
		ScrollTrigger.getAll().forEach((t) => {
			if (t.vars.once && t.vars.onEnter && !t.isActive && Math.abs(t.start - t.scroll()) < 1) {
				t.vars.onEnter(t);
				t.kill();
			}
		});
	});
	if (smoother && location.hash) {
		const target = document.getElementById(decodeURIComponent(location.hash.slice(1)));
		if (target) fontsReady.then(() => alive() && smoother.scrollTo(target, false, "top 24px"));
	}

	return () => {
		live = false;
		smoother = null;
		cleanups.reverse().forEach((fn) => fn());
	};
}

// In-page links glide to their target, then hand it focus
function focusTarget(target) {
	if (!target.matches("a[href], button, input, select, textarea, [tabindex]")) target.setAttribute("tabindex", "-1");
	target.focus({ preventScroll: true });
}
document.addEventListener("click", (e) => {
	if (e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
	const link = e.target.closest("a[href*='#']");
	if (!link) return;
	const url = new URL(link.href, location.href);
	if (url.origin !== location.origin || url.pathname !== location.pathname || url.search !== location.search || url.hash.length < 2) return;
	const target = document.getElementById(decodeURIComponent(url.hash.slice(1)));
	if (!target) return;
	e.preventDefault();
	history.pushState(null, "", url.hash);
	if (window.matchMedia(REDUCED).matches) {
		target.scrollIntoView();
		focusTarget(target);
	} else if (smoother) {
		gsap.to(smoother, { scrollTop: smoother.offset(target, "top 24px"), duration: 1, ease: IN_OUT, onComplete: () => focusTarget(target) });
	} else {
		gsap.to(window, { scrollTo: { y: target, offsetY: 24, autoKill: true }, duration: 0.9, ease: IN_OUT, onComplete: () => focusTarget(target) });
	}
});

// Shop filter: leaving cards turn away, then the rest slide into their new places with Flip.
// Under reduced motion the cards are simply hidden and shown.
const filter = document.querySelector("[data-shop-filter]");
if (filter) {
	const cards = [...document.querySelectorAll("[data-brew]")];
	const grid = cards.length ? cards[0].parentElement : null;
	const count = document.querySelector("[data-shop-count]");
	let running = null;
	let recount = null;
	// the count line follows the filter, its text swapped while it is faded out
	const setCount = (value, still) => {
		if (!count) return;
		const text = count.getAttribute(`data-count-${value}`);
		if (!text || text === count.textContent) return;
		if (recount) recount.kill();
		recount = null;
		if (still) {
			count.textContent = text;
			gsap.set(count, { clearProps: "opacity" });
			return;
		}
		recount = gsap.timeline()
			.to(count, { opacity: 0, duration: D.fast, ease: IN_OUT, onComplete: () => (count.textContent = text) })
			.to(count, { opacity: 1, duration: D.base, ease: OUT, clearProps: "opacity" });
	};
	filter.addEventListener("change", (e) => {
		if (!grid || e.target.type !== "radio") return;
		if (running) running.progress(1);
		const value = e.target.value;
		const wanted = (card) => value === "all" || card.dataset.brew.split(" ").includes(value);
		const apply = () => cards.forEach((card) => (card.hidden = !wanted(card)));
		const still = window.matchMedia(REDUCED).matches;
		// with motion allowed the shop shelf (js/files/shop.js) moves the cards and the count line itself
		if (!still && document.querySelector('[data-motion="shop-shelf"]')) return;
		setCount(value, still);
		if (still) {
			apply();
			ScrollTrigger.refresh();
			return;
		}
		const leaving = cards.filter((card) => !card.hidden && !wanted(card));
		const reflow = () => {
			gsap.set(leaving, { clearProps: "opacity,transform" });
			const state = Flip.getState([...grid.children]);
			apply();
			running = Flip.from(state, {
				duration: D.slow,
				ease: OUT,
				onEnter: (els) => gsap.fromTo(els, { opacity: 0, rotationY: -90, transformPerspective: 1000 }, { opacity: 1, rotationY: 0, duration: D.slow * 1.4, ease: OUT, stagger: 0.06, clearProps: "opacity,transform" }),
				onComplete: () => {
					running = null;
					ScrollTrigger.refresh();
				},
			});
		};
		if (leaving.length) running = gsap.to(leaving, { opacity: 0, rotationY: 90, transformPerspective: 1000, duration: D.base * 1.3, ease: "power2.in", stagger: 0.03, onComplete: reflow });
		else reflow();
	});
}

export { gsap, ScrollTrigger, D as motionDurations };
