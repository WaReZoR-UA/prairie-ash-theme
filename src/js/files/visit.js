/*
Visit page scenes, loaded as their own chunk and built only under prefers-reduced-motion: no-preference.
The stylesheet holds the finished page; every start state is created here and every end state is the markup.
The flag on <html> (data-visit) is set in the head of visit.html, so the first paint already shows the door
plate ajar with its faint sign and the storefront partly open.

data-motion="visit-sign"   the enamel door plate swings shut on its hinge while its name is lettered over the
                           ghost, the address and the intro written and the rules ruled; the storefront is
                           opened from the side that faces the plate and light crosses its glass. As the page
                           scrolls on, the plate lifts as the nearest plane and the camera moves in on the door
data-motion="visit-board"  the opening hours as a departure board: a rule is ruled under each row and the row
                           turns over on its axle like a flap and comes to rest on it; the roast days are
                           stamped on once their row lies flat
data-motion="visit-facts"  getting here and accessibility as lettered boards: row by row the brush letters the
                           label in its own colour and the pen writes the value after it
*/

import { gsap } from "./gsap.js";
import { rule, pen, board } from "./paint.js";
import { letterSign, endOnResize } from "./lettering.js";
import { smooth, relative, redrawOnRefresh, onScroll, swing, uncover, sheen, freshSign, signLettered } from "./motion-utils.js";

// Title ---------------------------------------------------------------------------------------------

// the door the camera moves in on, in title.jpg: the arched glass door right of centre
const DOOR = "74% 58%";

function titleScene(sign, { ctx, cleanups, alive, fontsReady }) {
	const later = (fn) => () => alive() && ctx.add(fn);
	const root = document.documentElement;
	const section = sign.closest("section") || sign;
	const plate = sign.querySelector(".visit-title__plate");
	const title = plate && plate.querySelector("h1");
	const eyebrow = plate && plate.querySelector(".visit-title__eyebrow");
	const intro = plate && plate.querySelector(".visit-title__intro");
	const stripes = plate ? [...plate.querySelectorAll(":scope > .pinstripe")] : [];
	const media = sign.querySelector(".visit-title__media");
	const img = media && media.querySelector("img");
	if (!title || !img) {
		delete root.dataset.visit;
		return;
	}
	// beside the photo the plate hangs on the hinge at its left edge, under it on the hinge at its top edge
	const beside = plate.getBoundingClientRect().left >= media.getBoundingClientRect().right - 1;
	const fresh = freshSign("visit", section);
	relative(plate, cleanups);

	const state = { p: 0, settle: 1 };
	let swinging = false;
	let st = null;
	const apply = () => {
		const scrolled = st ? state.p * (st.end - st.start) : 0;
		const e = smooth(0, 0.8, state.p);
		// at the top of the page nothing is left on a layer of its own, so the type renders exactly as drawn
		if (scrolled < 0.5 && state.settle === 1) {
			gsap.set(img, { clearProps: "transform" });
			if (!swinging) gsap.set(plate, { clearProps: "transform" });
			return;
		}
		gsap.set(img, { scale: state.settle * (1 + 0.08 * e), transformOrigin: DOOR, force3D: true });
		// the plate is the nearest plane: it lifts faster than the page
		gsap.set(plate, { y: -scrolled * (beside ? 0.1 : 0.05), force3D: true });
	};
	redrawOnRefresh(apply, cleanups);
	cleanups.push(() => {
		gsap.set([img, plate, media], { clearProps: "transform,transformOrigin,clipPath" });
		[title, eyebrow, intro, ...stripes].forEach((t) => t && (t.style.opacity = ""));
	});
	st = gsap.to(state, {
		p: 1,
		ease: "none",
		onUpdate: apply,
		scrollTrigger: { trigger: section, start: 0, end: "bottom top", scrub: beside ? true : 0.6, invalidateOnRefresh: true },
	}).scrollTrigger;

	if (!fresh) {
		signLettered("visit");
		apply();
		return;
	}

	// the first paint's ajar plate, faint sign and part-open storefront, now held inline
	const faint = [title, eyebrow, intro, ...stripes].filter(Boolean);
	const shut = beside ? "inset(0% 0% 0% 62%)" : "inset(62% 0% 0% 0%)";
	const hinge = beside ? { rotationY: 24, transformPerspective: 1600, transformOrigin: "0% 50%" } : { rotationX: -20, transformPerspective: 1600, transformOrigin: "50% 0%" };
	gsap.set(faint, { opacity: 0.3 });
	gsap.set(plate, hinge);
	gsap.set(media, { clipPath: shut });
	swinging = true;
	root.dataset.visit = "letter";
	state.settle = 1.06;
	apply();
	cleanups.push(() => {
		root.dataset.visit = "live";
		gsap.set(faint, { clearProps: "opacity" });
	});

	fontsReady.then(later(() => {
		const act = gsap.timeline();
		const parts = [];
		// the door plate swings shut onto the wall, a little past and back
		const axis = beside ? "rotationY" : "rotationX";
		const past = beside ? -2.5 : 2;
		swing(act, plate, 0.05, { axis, steps: [[past, 0.8, "power2.inOut"], [0, 0.4, "sine.out"]] });
		act.call(() => {
			swinging = false;
			gsap.set(plate, { clearProps: "transform,transformOrigin" });
			apply();
		}, null, 1.25);
		// the storefront opens from the side that faces the plate while the camera settles on its crop
		uncover(media, img, { shut, from: {}, to: {}, tl: act, at: 0.1 });
		act.to(state, { settle: 1, duration: 1.8, ease: "expo.out", onUpdate: apply }, 0.1);
		sheen(media, cleanups, { tl: act, at: 1 });
		if (stripes[0]) parts.push(rule(act, stripes[0], 0.2, { liner: plate, cleanups, duration: beside ? 0.75 : 0.6 }));
		const write = (el, at, pace) => {
			const p = pen(act, el, at, cleanups, { pace, ink: "own" });
			if (p) parts.push(p);
		};
		write(eyebrow, 0.35, 1.3);
		// dark ink on the enamel: the wet coat reads deeper than the dry paint
		const letters = letterSign(title, cleanups, { perChar: beside ? 0.064 : 0.07, dip: 0.09, wet: "darker", flat: true });
		act.add(letters.tl, 0.55);
		parts.push(letters);
		const lettersEnd = 0.55 + letters.tl.duration();
		write(intro, Math.max(1, lettersEnd - 1.4), 2.2);
		if (stripes[1]) parts.push(rule(act, stripes[1], Math.max(1.4, lettersEnd - 0.9), { liner: plate, cleanups, duration: beside ? 0.7 : 0.55 }));
		act.eventCallback("onComplete", () => {
			signLettered("visit");
			gsap.set(faint, { clearProps: "opacity" });
			gsap.set(media, { clearProps: "clipPath" });
			parts.forEach((part) => part.remove());
			apply();
		});
		endOnResize(act, cleanups);
		cleanups.push(() => act.kill());
	}));
}

// Hours --------------------------------------------------------------------------------------------

function hoursBoard(lv, { ctx, cleanups, alive, fontsReady }) {
	// the stamp is measured on the type, so the board waits for the faces
	fontsReady.then(() => {
		if (!alive()) return;
		// a departure board: each flap turns over on its axle, a little past flat and back, and lies on the rule
		// ruled under it; the roast days are stamped on once their row lies flat
		ctx.add(() => board(lv, cleanups, {
			guide: "visit-rule",
			turn: { at: 0.12, from: -86, steps: [[10, 0.38, "power2.in"], [-4, 0.14, "sine.inOut"], [0, 0.14, "sine.out"]], origin: "50% 50%" },
			struck: (row) => (row.classList.contains("visit-hours__roast") ? row.querySelector(".lv__value") : null),
			strike: { at: 0.84, size: 1.7, tilt: -5 },
		}));
	});
}

// Facts --------------------------------------------------------------------------------------------

function factsBoard(lv, { ctx, cleanups, alive, fontsReady }) {
	const rows = [...lv.querySelectorAll(":scope > .lv__row")];
	const parts = rows.flatMap((row) => [...row.querySelectorAll(".lv__label, .lv__value")]);
	if (!parts.length) return;
	// the brush and the pen measure the type, so the board waits for the faces
	gsap.set(parts, { opacity: 0 });
	cleanups.push(() => gsap.set(parts, { clearProps: "opacity" }));
	fontsReady.then(() => {
		if (!alive()) return;
		ctx.add(() => rows.forEach((row) => {
			const label = row.querySelector(".lv__label");
			const value = row.querySelector(".lv__value");
			if (!label || !value) return gsap.set([label, value], { clearProps: "opacity" });
			gsap.set([label, value], { clearProps: "opacity" });
			relative(row, cleanups);
			const tl = gsap.timeline();
			tl.add(letterSign(label, cleanups, { mode: "lines", split: true, pass: 0.5, gap: 0.18, ease: "power2.inOut", settle: 0.5, paint: true }).tl, 0);
			pen(tl, value, 0.35, cleanups, { ink: "line", scrub: true, duration: 0.55 });
			onScroll(row, tl, { start: "top 88%", end: "top 58%" });
		}));
	});
}

export function visitMotion({ ctx, cleanups, alive, fontsReady }) {
	const scene = { ctx, cleanups, alive, fontsReady };
	const sign = document.querySelector('[data-motion="visit-sign"]');
	if (sign) titleScene(sign, scene);
	const hours = document.querySelector('[data-motion="visit-board"]');
	if (hours) hoursBoard(hours, scene);
	document.querySelectorAll('[data-motion="visit-facts"]').forEach((lv) => factsBoard(lv, scene));
}
