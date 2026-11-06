/*
Cart page scenes, loaded as their own chunk and built only under prefers-reduced-motion: no-preference.
The stylesheet holds the finished page; every start state is created here and every end state is the markup.
The flag on <html> (data-cart) is set in the head of the two Cart pages, so the first paint already shows the faint
title, the bags not yet set down and the empty places where the prices are stamped, and on the empty cart the faint note.

data-motion="cart-sign"     the title on paper through the shared signScene(): the line above written and the name
                            lettered stroke by stroke over its ghost in dark ink; as the page scrolls on it lifts as the
                            nearest plane

data-motion="cart-counter"  each line of the cart as a bag set down on the counter: the counter's edge is ruled under it,
                            the bag is set down onto it and rocks square, its label is lettered stroke by stroke over
                            its ghost, the line under it written by the pen and the prices stamped, the line total by a
                            numbering stamp. Lines on the first screen are set down on load, the others with the
                            scroll. The quantity field and the remove button never move or fade, so they work at every
                            moment
data-motion="cart-receipt"  the totals as a till receipt: each row is ruled as it is printed, its label written by the
                            print head, its figure counted up to its value as on a till, and the total to pay stamped;
                            the checkout button and the way back to the shop never move or fade.
                            data-receipt-struck names the row struck by the numbering stamp (".lv__row--due" by default)
data-motion="cart-note"     the empty cart as a lettered note on the empty counter: the line above written, the name
                            lettered over its ghost, the line under it written and light crosses the photo of the
                            counter; the way back to the shop keeps its ink. As the page scrolls on, the note lifts as
                            the nearest plane and the camera moves in on the bags
*/

import { gsap, ScrollTrigger } from "./gsap.js";
import { rule, pen, stamp, figure } from "./paint.js";
import { letterSign, signScene, endOnResize } from "./lettering.js";
import { inView, relative, layer, redrawOnRefresh, onScroll, swing, freshSign, signLettered } from "./motion-utils.js";

// the bags on the counter, in empty.jpg: the camera moves in on them
const BAGS = "66% 70%";
const shown = (el) => el && el.offsetWidth > 0;

// Bags on the counter -------------------------------------------------------------------------------------

// One line's parts, placed on tl from `at`: the counter's edge ruled under the bag, the bag set down onto it and
// rocking square on its base, its label lettered, the line under it written, the prices stamped
function setDown(line, tl, at, cleanups, parts, { scrub }) {
	const thumb = line.querySelector(".cart-line__thumb");
	const text = line.querySelector(".cart-line__text");
	const name = line.querySelector(".cart-line__name");
	const details = line.querySelector(".cart-line__details");
	const each = line.querySelector(".cart-line__each");
	const total = line.querySelector(".cart-line__total-value");
	relative(line, cleanups);
	relative(text, cleanups);
	const edge = layer(line, "cart-counter", cleanups);
	// under the bag and its label, clear of the quantity field; a line with no photo has only its label
	const base = thumb || text;
	const place = () => Object.assign(edge.style, { left: "0px", top: `${base.offsetTop + base.offsetHeight}px`, width: `${Math.max(thumb ? thumb.offsetWidth : 0, text.offsetLeft + text.offsetWidth)}px` });
	place();
	redrawOnRefresh(place, cleanups);
	rule(tl, edge, at, { duration: 0.35, ease: "power2.inOut", start: { opacity: 0.7 } });
	if (thumb) {
		tl.fromTo(thumb, { opacity: 0 }, { opacity: 1, duration: 0.12, ease: "none" }, at + 0.1)
			.fromTo(thumb, { y: -26 }, { y: 0, duration: 0.26, ease: "power2.in" }, at + 0.1);
		swing(tl, thumb, at + 0.1, { axis: "rotation", from: -7, steps: [[2.2, 0.26, "power2.in"], [0, 0.18, "sine.out"]], hinge: { transformOrigin: "50% 100%" } });
	}
	const letters = letterSign(name, cleanups, { perChar: 0.045, dip: 0.08, wet: "darker", ghost: 0.3 });
	tl.add(letters.tl, at + 0.4);
	parts.push(letters);
	const written = at + 0.4 + Math.min(0.7, letters.tl.duration() * 0.45);
	if (scrub) {
		pen(tl, details, written, cleanups, { ink: "own", exact: true, scrub: true, duration: 0.4 });
	} else {
		const p = pen(tl, details, written, cleanups, { pace: 2.2, ink: "own", exact: true });
		if (p) parts.push(p);
	}
	const struck = written + 0.35;
	if (shown(each)) stamp(tl, each, struck, line, { size: 1.6, tilt: -5, press: 0.24, ease: "power4.in" });
	if (shown(total)) stamp(tl, total, struck + 0.16, line, { size: 1.7, tilt: 4, press: 0.26, ease: "power4.in", count: { cleanups } });
	tl.to(edge, { opacity: 0, duration: 0.25 }, struck + 0.3);
	cleanups.push(() => gsap.set([thumb, details, each, total].filter(Boolean), { clearProps: "opacity,transform,transformOrigin" }));
}

function counterIn(list, { ctx, cleanups, alive, fontsReady }) {
	const lines = [...list.querySelectorAll(".cart-line")];
	const root = document.documentElement;
	const fresh = freshSign("cart", list.closest("section"));
	// a title sign on the page owns the flag: it is lettered first and lets the flag go when its act is over
	const signed = !!document.querySelector('[data-motion="cart-sign"]');
	const later = (fn) => () => alive() && ctx.add(fn);
	// the label, the line under it and the stamps measure the type, so the counter waits for the faces; until then a
	// line keeps the first paint's look, and its quantity field and remove button work as they stand
	fontsReady.then(later(() => {
		const now = fresh ? lines.filter(inView) : [];
		lines.filter((line) => !inView(line)).forEach((line) => {
			const tl = gsap.timeline({ defaults: { ease: "none" } });
			setDown(line, tl, 0, cleanups, [], { scrub: true });
			onScroll(line, tl, { start: "top 90%", end: "top 50%" });
		});
		if (!now.length) {
			if (!signed && (fresh || root.dataset.cart === "pre")) signLettered("cart");
			ScrollTrigger.refresh();
			return;
		}
		// set down on load after the title is lettered, one bag after another
		if (!signed) root.dataset.cart = "letter";
		const act = gsap.timeline({ delay: Math.max(0, 1.05 - performance.now() / 1000) });
		const parts = [];
		// the first paint's faint lines, now held inline while the pen writes over them
		gsap.set(now.map((line) => line.querySelector(".cart-line__details")), { opacity: 0.3 });
		now.forEach((line, i) => setDown(line, act, i * 0.45, cleanups, parts, { scrub: false }));
		act.eventCallback("onComplete", () => {
			if (!signed) signLettered("cart");
			gsap.set(now.flatMap((line) => [...line.querySelectorAll(".cart-line__thumb, .cart-line__details, .cart-line__each, .cart-line__total-value")]), { clearProps: "opacity,transform,transformOrigin" });
			parts.forEach((part) => part.remove());
		});
		endOnResize(act, cleanups);
		cleanups.push(() => {
			act.kill();
			if (!signed) signLettered("cart");
		});
		ScrollTrigger.refresh();
	}));
}

// The till receipt ----------------------------------------------------------------------------------------

// A figure counted up to its value as on a till, on the caller's timeline; drawn again after the trigger measures
function counted(tl, el, at, duration, cleanups) {
	const fig = figure(el, cleanups, { read: "till" });
	if (!fig || !fig.target) return false;
	const till = { v: 0 };
	const draw = () => fig.show(till.v);
	tl.fromTo(till, { v: 0 }, { v: fig.target, duration, ease: "power2.out", onUpdate: draw }, at);
	draw();
	redrawOnRefresh(draw, cleanups);
	return true;
}

function receiptIn(totals, { ctx, cleanups, alive, fontsReady }) {
	const lv = totals.querySelector(".lv");
	if (!lv) return;
	fontsReady.then(() => {
		if (!alive()) return;
		ctx.add(() => {
			relative(lv, cleanups);
			const rows = [...lv.querySelectorAll(":scope > .lv__row")];
			const struck = totals.dataset.receiptStruck || ".lv__row--due";
			rows.forEach((row, i) => {
				// a row already on the screen when the motion arrives keeps its finished look
				if (inView(row)) return;
				const label = row.querySelector(".lv__label");
				const value = row.querySelector(".lv__value");
				const due = row.matches(struck);
				const tear = layer(lv, "cart-tear", cleanups);
				const place = () => Object.assign(tear.style, { left: `${row.offsetLeft}px`, top: `${row.offsetTop + row.offsetHeight}px`, width: `${row.offsetWidth}px` });
				place();
				redrawOnRefresh(place, cleanups);
				const tl = gsap.timeline({ defaults: { ease: "none" } });
				rule(tl, tear, 0, { duration: 0.3, ease: "power2.inOut", start: { opacity: 0.55 } });
				pen(tl, label, 0.05, cleanups, { ink: "own", exact: true, scrub: true, duration: 0.45 });
				if (due) stamp(tl, value, 0.55, lv, { size: 1.6, tilt: -6, press: 0.3, ease: "power4.in", inkAt: 0.13, inkFor: 0.15, count: { cleanups } });
				else if (!counted(tl, value, 0.3, 0.6, cleanups)) pen(tl, value, 0.4, cleanups, { ink: "own", exact: true, scrub: true, duration: 0.35 });
				tl.to(tear, { opacity: 0, duration: 0.15 }, 0.9);
				cleanups.push(() => gsap.set([label, value], { clearProps: "opacity,transform,transformOrigin" }));
				// printed one row after another down the receipt
				onScroll(row, tl, { start: `top ${92 - 3 * i}%`, end: `top ${60 - 3 * i}%` });
			});
			ScrollTrigger.refresh();
		});
	});
}

// The title sign --------------------------------------------------------------------------------------------

function signIn(region, scene) {
	signScene(region, scene, {
		flag: "cart",
		title: region.querySelector("h1"),
		eyebrow: region.querySelector(".cart-items__eyebrow"),
		plane: region,
		held: [region],
		// ink on paper keeps its paint as the page scrolls on
		weather: false,
		exact: true,
		// dark ink on paper: the wet coat reads deeper than the dry paint
		letters: { perChar: [0.064, 0.07], wet: "darker" },
	});
}

// The empty cart ------------------------------------------------------------------------------------------

function noteIn(region, scene) {
	const text = region.querySelector(".cart-empty__text");
	const media = region.querySelector(".cart-empty__media");
	const body = text && text.querySelector(".cart-empty__body");
	signScene(region, scene, {
		flag: "cart",
		title: text && text.querySelector("h1"),
		eyebrow: text && text.querySelector(".cart-empty__eyebrow"),
		media,
		img: media && media.querySelector("img"),
		plane: text,
		held: [text, media],
		focus: BAGS,
		// ink on paper keeps its paint as the page scrolls on
		weather: false,
		exact: true,
		// dark ink on paper: the wet coat reads deeper than the dry paint
		letters: { perChar: [0.064, 0.07], wet: "darker" },
		// the line under the name is written as the brush lifts, then the light crosses the counter
		then: [["write", body, 1, 1.2], ["sheen", 1.2, 0.9]],
	});
}

export function cartMotion({ ctx, cleanups, alive, fontsReady, wide }) {
	const scene = { ctx, cleanups, alive, fontsReady, wide };
	// the counter reads the head flag before the sign takes it over
	document.querySelectorAll('[data-motion="cart-counter"]').forEach((list) => counterIn(list, scene));
	const sign = document.querySelector('[data-motion="cart-sign"]');
	if (sign) signIn(sign, scene);
	const printed = new WeakSet();
	const receipts = () => document.querySelectorAll('[data-motion="cart-receipt"]').forEach((totals) => {
		if (printed.has(totals)) return;
		printed.add(totals);
		receiptIn(totals, scene);
	});
	receipts();
	// on the checkout WooCommerce replaces the order review, receipt and all, when the order changes: the new receipt
	// is printed by the scroll in its turn, its rows on the screen already finished
	const $ = window.jQuery;
	if ($ && document.querySelector("form.woocommerce-checkout")) {
		const reprint = () => alive() && ctx.add(receipts);
		$(document.body).on("updated_checkout", reprint);
		cleanups.push(() => $(document.body).off("updated_checkout", reprint));
	}
	const note = document.querySelector('[data-motion="cart-note"]');
	if (note) noteIn(note, scene);
}
