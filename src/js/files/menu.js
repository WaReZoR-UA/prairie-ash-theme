/*
Menu page scenes, loaded as their own chunk and built only under prefers-reduced-motion: no-preference.
The stylesheet holds the finished page; every start state is created here and every end state is the markup.
The flag on <html> (data-menu) is set in the head of menu.html, so the first paint already shows the faint
board on the soot wall.

data-motion="menu-sign"   the bar board on the soot wall: the rules are ruled, the line above written and the name
                          lettered stroke by stroke over its ghost, the line under it written, and light crosses the
                          photo of the empty bar. As the page scrolls on, the lettering lifts as the nearest plane and
                          weathers back into a ghost while the camera moves in on the pastry case
data-motion="menu-board"  a price list as a lettered menu board: row by row a guide is ruled, the row stands up off it
                          (the drinks) or drops down onto it (the pastries), its price is stamped on a little off the
                          column by the hand and pushed square, so the column of prices is aligned as the board ends
data-motion="menu-case"   the photo of the pastry case is uncovered from the side that faces its text while it settles
                          into its crop, and light crosses the glass of the case as it opens
data-motion="menu-note"   the milk note and the allergens note are written in by the pen with the scroll
*/

import { gsap, ScrollTrigger } from "./gsap.js";
import { pen, board } from "./paint.js";
import { signScene } from "./lettering.js";
import { relative, onScroll, uncover, sheen, facing } from "./motion-utils.js";

// the pastry case on the bar, in title.jpg: the camera moves in on it
const CASE = "76% 62%";

// Boards ---------------------------------------------------------------------------------------------

// where the hand sets each price down: a little short of the column and off the square, never the same twice in a row
const HAND = [[-14, -2.2], [-8, 1.6], [-18, -1.2], [-10, 2.4], [-16, 1]];

function boardIn(lv, { ctx, cleanups, alive, fontsReady }) {
	// the drinks stand up off the rule under them; the pastries, on the brick, drop down onto the rule above them
	const hung = !!lv.closest(".menu-pastries");
	// the stamp is measured on the type, so the board waits for the faces
	fontsReady.then(() => {
		if (!alive()) return;
		ctx.add(() => {
			board(lv, cleanups, {
				guide: "menu-rule",
				edge: hung ? "top" : "bottom",
				ruled: { duration: 0.32, opacity: 0.5 },
				turn: hung
					? { at: 0.18, from: -84, steps: [[8, 0.4, "power2.in"], [-2.6, 0.14, "sine.inOut"], [0, 0.14, "sine.out"]], perspective: 900 }
					: { at: 0.18, from: 80, steps: [[-7, 0.4, "power2.in"], [2.4, 0.14, "sine.inOut"], [0, 0.14, "sine.out"]], perspective: 900 },
				ink: 0.22,
				fade: [0.74, 0.22],
				struck: (row) => row.querySelector(".lv__value"),
				strike: { at: 0.86, size: 1.6, tilt: hung ? 5 : -5, set: (i) => ({ x: HAND[i % HAND.length][0], rotation: HAND[i % HAND.length][1] }), square: [0.34, "power3.inOut"] },
				band: { start: "top 90%", end: "top 58%" },
				// a row already on the screen when the motion arrives keeps its finished look
				onScreen: () => {},
			});
			ScrollTrigger.refresh();
		});
	});
}

// Pastry case --------------------------------------------------------------------------------------

// the glass case on the bar, in pastries.jpg: the picture settles onto it
const GLASS = "40% 66%";

function caseIn(slot, { cleanups }) {
	const img = slot.querySelector("img");
	const text = slot.parentElement.querySelector(".menu-pastries__text");
	if (!img) return;
	relative(slot, cleanups);
	uncover(slot, img, {
		shut: facing(slot, text),
		from: { scale: 1.12, transformOrigin: GLASS },
		to: { scale: 1 },
		also: (tl) => sheen(slot, cleanups, { tl, at: 0.45, duration: 0.75, stay: true }),
	});
	cleanups.push(() => gsap.set([slot, img], { clearProps: "clipPath,transform,transformOrigin" }));
}

// Notes --------------------------------------------------------------------------------------------

function noteIn(note, { ctx, cleanups, alive, fontsReady }) {
	// the pen measures the lines, so the note waits for the faces
	fontsReady.then(() => {
		if (!alive()) return;
		ctx.add(() => {
			relative(note.parentElement, cleanups);
			const tl = gsap.timeline();
			pen(tl, note, 0, cleanups, { ink: "own", scrub: true, duration: 1 });
			cleanups.push(() => gsap.set(note, { clearProps: "opacity" }));
			onScroll(note, tl, { start: "top 90%", end: "top 60%" });
			ScrollTrigger.refresh();
		});
	});
}

export function menuMotion({ ctx, cleanups, alive, fontsReady, wide }) {
	const scene = { ctx, cleanups, alive, fontsReady, wide };
	const sign = document.querySelector('[data-motion="menu-sign"]');
	if (sign) {
		const text = sign.querySelector(".menu-title__text");
		const media = sign.querySelector(".menu-title__media");
		const intro = text && text.querySelector(".menu-title__intro");
		signScene(sign, scene, {
			flag: "menu",
			title: text && text.querySelector("h1"),
			eyebrow: text && text.querySelector(".menu-title__eyebrow"),
			media,
			img: media && media.querySelector("img"),
			plane: text,
			stripes: [...sign.querySelectorAll(":scope > .pinstripe")],
			held: [sign, text, media],
			focus: CASE,
			// cream paint on the soot: the wet coat reads lighter than the dry paint
			letters: { perChar: [0.06, 0.066] },
			// the line under the name is written, the rule under the board closes it, the light crosses the bar
			then: [["write", intro, 1, 1.1], ["rule", 1, 1.3, 0.8], ["sheen", 1.2, 0.9]],
		});
	}
	document.querySelectorAll('[data-motion="menu-board"]').forEach((lv) => boardIn(lv, scene));
	document.querySelectorAll('[data-motion="menu-case"]').forEach((slot) => caseIn(slot, scene));
	document.querySelectorAll('[data-motion="menu-note"]').forEach((note) => noteIn(note, scene));
}
