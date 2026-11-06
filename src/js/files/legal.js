/*
The two reading pages, the privacy policy and the accessibility statement, as one module, loaded as its own chunk and
built only under prefers-reduced-motion: no-preference. The motion is calm, every piece is finished before the eye
reaches it, and anything already on the screen when the motion arrives keeps its finished look. The stylesheet holds
the finished page; every start state is created here and every end state is the markup. The page's flag on <html>
(data-privacy or data-accessibility) is set in the head, so the first paint already shows the faint title.

legalMotion(scene, { flag, guide, lastWritten }), flag naming the page's hooks:
data-motion="<flag>-sign"      the title on the wall through the shared signScene(): the line above written, the name
                               lettered stroke by stroke over its ghost in cream paint, the line under it written; as
                               the page scrolls on, the sign lifts as the nearest plane and weathers back into a ghost
data-motion="<flag>-contents"  the contents never fade or move while on the screen, so their links scroll the page at
                               every moment; a link below the window slides in from the column edge at full use
data-motion="<flag>-part"      each part: its heading lettered by the section brush of the scroll pages (tipped up off
                               its line on a narrow screen), its running text risen into its lines, a line after a list
                               written in by the pen, and a list as a lettered board through the shared board(): under
                               each row a guide (the class named by guide) is ruled and the row stands up off it. Links
                               in a row turn with it and work at every moment. lastWritten: the closing part is a dated
                               note, written by the pen throughout (the accessibility statement)
*/

import { gsap, ScrollTrigger } from "./gsap.js";
import { pen, board } from "./paint.js";
import { signScene, letterSign } from "./lettering.js";
import { inView, relative, onScroll, riseIn, tipUp } from "./motion-utils.js";

// the section brush of the scroll pages, the shared letterSign in its line mode
const BRUSH = { mode: "lines", split: true, pass: 0.714, gap: 0.24, ease: "power2.inOut", settle: 0.6, paint: true };
// a written line's band: the eye band of running text
const LINE = { start: "top 90%", end: "top 62%" };

// once the faces are in, since the brush, the pen and the board measure the type
const measured = ({ ctx, alive, fontsReady }, fn) =>
	fontsReady.then(() => {
		if (!alive()) return;
		ctx.add(() => {
			fn();
			ScrollTrigger.refresh();
		});
	});

// The title sign -------------------------------------------------------------------------------------------

function signIn(region, scene, flag) {
	const intro = region.querySelector(".legal-title__intro");
	signScene(region, scene, {
		flag,
		title: region.querySelector("h1"),
		eyebrow: region.querySelector(".eyebrow"),
		plane: region,
		held: [region],
		// the heading is shrunk to its text: the pen and the weathered copies take its fractional width
		exact: true,
		// cream paint on the wall: the wet coat reads lighter than the dry paint
		letters: { perChar: [0.058, 0.064] },
		// the line under the name is written as the brush lifts; under the fold, by the scroll
		then: [["write", intro, 1, 1.2]],
		story: { written: [intro].filter(Boolean) },
	});
}

// The contents ---------------------------------------------------------------------------------------------

function contentsIn(nav, { cleanups }) {
	nav.querySelectorAll("a[href]").forEach((link) => {
		if (inView(link)) return;
		riseIn(link, { opacity: 0, x: -24 });
		cleanups.push(() => gsap.set(link, { clearProps: "opacity,transform" }));
	});
}

// A part ---------------------------------------------------------------------------------------------------

function headingIn(h, { cleanups, wide }) {
	if (!h || inView(h)) return;
	if (wide) onScroll(h, letterSign(h, cleanups, BRUSH).tl, { end: "top 50%" });
	else onScroll(h, tipUp(gsap.timeline(), h, 0, cleanups, { rise: 16, duration: 1, stagger: 0.25 }), { end: "top 50%" });
}

function textIn(p, { cleanups }) {
	if (inView(p)) return;
	riseIn(p);
	cleanups.push(() => gsap.set(p, { clearProps: "opacity,transform" }));
}

// a line written in by the pen with the scroll
function writtenIn(p, { cleanups }) {
	if (inView(p)) return;
	relative(p.parentElement, cleanups);
	const tl = gsap.timeline({ defaults: { ease: "none" } });
	pen(tl, p, 0, cleanups, { ink: "own", exact: true, scrub: true, duration: 1 });
	cleanups.push(() => gsap.set(p, { clearProps: "opacity" }));
	onScroll(p, tl, LINE);
}

function boardIn(lv, { cleanups }, guide) {
	board(lv, cleanups, {
		guide,
		edge: "bottom",
		ruled: { duration: 0.32, opacity: 0.5 },
		// a calm turn: up off the guide, a little past square and back
		turn: { at: 0.18, from: 70, steps: [[-4, 0.42, "power2.in"], [1.2, 0.14, "sine.inOut"], [0, 0.14, "sine.out"]], perspective: 900 },
		ink: 0.24,
		fade: [0.74, 0.22],
		band: { start: "top 90%", end: "top 60%" },
		// a row already on the screen when the motion arrives keeps its finished look
		onScreen: () => {},
	});
}

// a line after a list closes its part and is written by the pen; so is every line of a closing dated note
function partIn(part, scene, guide, written) {
	measured(scene, () => {
		headingIn(part.querySelector(".legal__heading"), scene);
		part.querySelectorAll(":scope > .legal__text").forEach((p) => {
			const prev = p.previousElementSibling;
			if (written || (prev && prev.matches(".lv"))) writtenIn(p, scene);
			else textIn(p, scene);
		});
		part.querySelectorAll(":scope > .lv").forEach((lv) => boardIn(lv, scene, guide));
	});
}

export function legalMotion({ ctx, cleanups, alive, fontsReady, wide }, { flag, guide, lastWritten = false }) {
	const scene = { ctx, cleanups, alive, fontsReady, wide };
	const sign = document.querySelector(`[data-motion="${flag}-sign"]`);
	if (sign) signIn(sign, scene, flag);
	const contents = document.querySelector(`[data-motion="${flag}-contents"]`);
	if (contents) measured(scene, () => contentsIn(contents, scene));
	const parts = [...document.querySelectorAll(`[data-motion="${flag}-part"]`)];
	parts.forEach((part, i) => partIn(part, scene, guide, lastWritten && i === parts.length - 1));
}
