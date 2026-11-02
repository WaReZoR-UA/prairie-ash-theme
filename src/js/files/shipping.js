/*
Shipping, pickup and returns scenes, loaded as their own chunk and built only under prefers-reduced-motion: no-preference.
The stylesheet holds the finished page; every start state is created here and every end state is the markup.
The flag on <html> (data-shipping) is set in the head of the page, so the first paint already shows the faint title.

data-motion="shipping-sign"        the title on paper through the shared signScene(): the line above written, the name
                                   lettered stroke by stroke over its ghost in dark ink, the line under it written and light
                                   crosses the photo of the box being packed; as the page scrolls on, the lettering lifts as
                                   the nearest plane and the camera moves in on the box. The jump links never move or fade,
                                   so they scroll the page at every moment
data-motion="shipping-board"       the rates as a lettered rate board through the shared board(): under each row a guide is
                                   ruled and the row stands up off it; the price is stamped a little off its column by the
                                   hand and pushed square, so the column lies aligned as the board ends, and the free
                                   shipping threshold is stamped after it
data-motion="shipping-photo"       the pickup shelf is uncovered from the edge facing its text while the picture settles
                                   into its crop
data-motion="shipping-note"        the pickup part as a note written by the pen: the heading, the line under it and each
                                   row's name and what it says, in reading order with the scroll; the way to the cafe slides
                                   in from its edge
data-motion="shipping-correction"  the damaged or wrong bags part as a correction note: its heading written in with a rule
                                   struck under it, lifted once it is written, then the line under it and each row written by
                                   the pen; the way to write to us slides in from its edge. Links in it work at every moment
*/

import { gsap, ScrollTrigger } from "./gsap.js";
import { rule, pen, stamp, board } from "./paint.js";
import { signScene } from "./lettering.js";
import { inView, relative, layer, redrawOnRefresh, onScroll, uncover, facing, riseIn } from "./motion-utils.js";

// the box being packed, in title.jpg: the camera moves in on it
const BOX = "56% 56%";
// the bags on the shelf, in pickup.jpg: the picture settles onto them
const SHELF = "60% 36%";
// a row's line: the eye band of running text
const LINE = { start: "top 92%", end: "top 62%" };

// once the faces are in, since the pen and the stamps measure the type
const measured = ({ ctx, alive, fontsReady }, fn) =>
	fontsReady.then(() => {
		if (!alive()) return;
		ctx.add(() => {
			fn();
			ScrollTrigger.refresh();
		});
	});

// A line below the window written in by the pen with the scroll, on its own band, or on tl at `at` for `duration`
function written(el, cleanups, { tl = null, at = 0, duration = 1, band = LINE } = {}) {
	if (!el || inView(el)) return;
	relative(el.parentElement, cleanups);
	const own = tl || gsap.timeline({ defaults: { ease: "none" } });
	pen(own, el, at, cleanups, { ink: "own", exact: true, scrub: true, duration });
	cleanups.push(() => gsap.set(el, { clearProps: "opacity" }));
	if (!tl) onScroll(el, own, band);
}

// Each row of a fact list: its name written, then what it says
function rowsWritten(lv, cleanups) {
	lv.querySelectorAll(":scope > .lv__row").forEach((row) => {
		if (inView(row)) return;
		const tl = gsap.timeline({ defaults: { ease: "none" } });
		written(row.querySelector(".lv__label"), cleanups, { tl, at: 0, duration: 0.45 });
		written(row.querySelector(".lv__value"), cleanups, { tl, at: 0.35, duration: 0.65 });
		onScroll(row, tl, LINE);
	});
}

// A control slides in from the column edge it ends on, at full ink so it works throughout
function slid(el, cleanups) {
	if (!el || inView(el)) return;
	riseIn(el, { opacity: 0, x: -24 });
	cleanups.push(() => gsap.set(el, { clearProps: "opacity,transform" }));
}

// The title sign -------------------------------------------------------------------------------------------

function signIn(region, scene) {
	const text = region.querySelector(".shipping-title__text");
	const media = region.querySelector(".shipping-title__media");
	const intro = text && text.querySelector(".shipping-title__intro");
	signScene(region, scene, {
		flag: "shipping",
		title: text && text.querySelector("h1"),
		eyebrow: text && text.querySelector(".shipping-title__eyebrow"),
		media,
		img: media && media.querySelector("img"),
		plane: text,
		held: [text, media],
		focus: BOX,
		// ink on paper keeps its paint as the page scrolls on
		weather: false,
		exact: true,
		// dark ink on paper: the wet coat reads deeper than the dry paint
		letters: { perChar: [0.064, 0.07], wet: "darker" },
		// the line under the name is written as the brush lifts, then the light crosses the box; under the fold, by the scroll
		then: [["write", intro, 1, 1.2], ["sheen", 1.2, 0.9]],
		story: { written: [intro].filter(Boolean) },
	});
}

// The rate board -------------------------------------------------------------------------------------------

// where the hand sets the price down: a little short of its column and off the square
const HAND = { x: -14, rotation: -2.2 };
// the board's row timeline: the price is struck at 0.86 and pushed square by 1.44
const STRUCK = 0.86;
const SQUARE = 1.44;

function boardIn(lv, scene) {
	const { cleanups } = scene;
	measured(scene, () => {
		const rows = board(lv, cleanups, {
			guide: "shipping-rule",
			edge: "bottom",
			ruled: { duration: 0.32, opacity: 0.5 },
			turn: { at: 0.18, from: 80, steps: [[-7, 0.4, "power2.in"], [2.4, 0.14, "sine.inOut"], [0, 0.14, "sine.out"]], perspective: 900 },
			ink: 0.22,
			fade: [0.74, 0.22],
			struck: (row) => row.querySelector(".shipping-rate"),
			strike: { at: STRUCK, size: 1.6, tilt: -5, set: () => HAND, square: [SQUARE - STRUCK - 0.24, "power3.inOut"] },
			band: { start: "top 90%", end: "top 58%" },
			// a row already on the screen when the motion arrives keeps its finished look
			onScreen: () => {},
		});
		// the free shipping threshold is struck after the price, on the same band as its row
		rows.forEach((row) => {
			const rates = [...row.querySelectorAll(".shipping-rate")];
			if (rates.length < 2 || inView(row)) return;
			const tl = gsap.timeline({ defaults: { ease: "none" } });
			rates.slice(1).forEach((rate, i) => {
				tl.set(rate, { opacity: 0 }, 0);
				stamp(tl, rate, STRUCK + 0.3 + 0.2 * i, lv, { size: 1.6, tilt: 6, from: 0 });
			});
			tl.set({}, {}, SQUARE);
			cleanups.push(() => gsap.set(rates, { clearProps: "opacity,transform,transformOrigin" }));
			onScroll(row, tl, { start: "top 90%", end: "top 58%" });
		});
	});
}

// The pickup photo and note ----------------------------------------------------------------------------

function photoIn(slot, { cleanups }) {
	const img = slot.querySelector("img");
	const text = slot.parentElement.querySelector(".shipping-part__main");
	// a photo already on the screen when the motion arrives is left as it is
	if (!img || inView(slot)) return;
	relative(slot, cleanups);
	uncover(slot, img, { shut: facing(slot, text), from: { scale: 1.12, transformOrigin: SHELF }, to: { scale: 1 } });
	cleanups.push(() => gsap.set([slot, img], { clearProps: "clipPath,transform,transformOrigin" }));
}

function noteIn(main, scene) {
	const { cleanups } = scene;
	measured(scene, () => {
		written(main.querySelector(".shipping-part__heading"), cleanups, { duration: 1 });
		written(main.querySelector(".shipping-part__body"), cleanups, { duration: 1 });
		main.querySelectorAll(".lv").forEach((lv) => rowsWritten(lv, cleanups));
		slid(main.querySelector(":scope > .text-link"), cleanups);
	});
}

// The correction note --------------------------------------------------------------------------------------

function correctionIn(region, scene) {
	const { cleanups } = scene;
	measured(scene, () => {
		const heading = region.querySelector(".shipping-part__heading");
		if (heading && !inView(heading)) {
			const host = heading.parentElement;
			relative(host, cleanups);
			const tl = gsap.timeline({ defaults: { ease: "none" } });
			written(heading, cleanups, { tl, at: 0, duration: 0.7 });
			// the rule struck under the heading, lifted once the note stands corrected
			const strike = layer(host, "shipping-strike", cleanups);
			const place = () => Object.assign(strike.style, { left: `${heading.offsetLeft}px`, top: `${heading.offsetTop + heading.offsetHeight}px`, width: `${heading.offsetWidth}px` });
			place();
			redrawOnRefresh(place, cleanups);
			rule(tl, strike, 0.4, { duration: 0.45, ease: "power2.in", start: { opacity: 1 } });
			tl.to(strike, { opacity: 0, duration: 0.25 }, 0.95);
			onScroll(heading, tl, { start: "top 90%", end: "top 55%" });
		}
		written(region.querySelector(".shipping-part__body"), cleanups, { duration: 1 });
		region.querySelectorAll(".lv").forEach((lv) => rowsWritten(lv, cleanups));
		slid(region.querySelector(".shipping-part__main > .text-link"), cleanups);
	});
}

export function shippingMotion({ ctx, cleanups, alive, fontsReady, wide }) {
	const scene = { ctx, cleanups, alive, fontsReady, wide };
	const sign = document.querySelector('[data-motion="shipping-sign"]');
	if (sign) signIn(sign, scene);
	document.querySelectorAll('[data-motion="shipping-board"]').forEach((lv) => boardIn(lv, scene));
	document.querySelectorAll('[data-motion="shipping-photo"]').forEach((slot) => photoIn(slot, scene));
	document.querySelectorAll('[data-motion="shipping-note"]').forEach((main) => noteIn(main, scene));
	document.querySelectorAll('[data-motion="shipping-correction"]').forEach((region) => correctionIn(region, scene));
}
