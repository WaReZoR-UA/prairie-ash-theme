/*
Accessibility statement scenes, loaded as their own chunk and built only under prefers-reduced-motion: no-preference.
A reading page on the pattern of the privacy policy: calm motion, every piece finished before the eye reaches it, and
anything already on the screen when the motion arrives keeps its finished look. The stylesheet holds the finished page;
every start state is created here. The flag on <html> (data-accessibility) is set in the head of the page, so the first
paint already shows the faint title.

data-motion="accessibility-sign"      the title on the wall through the shared signScene(): the line above written, the
                                      name lettered stroke by stroke over its ghost in cream paint, the line under it
                                      written; as the page scrolls on, the sign lifts as the nearest plane and weathers
                                      back into a ghost
data-motion="accessibility-contents"  the contents never fade or move while on the screen, so their links scroll the
                                      page at every moment; a link below the window slides in from the column edge
data-motion="accessibility-part"      each part of the statement: its heading lettered by the section brush (tipped up
                                      on a narrow screen), its running text risen into its lines, a list as a lettered
                                      board through the shared board(), and the dated note that closes the statement
                                      written by the pen. Links in a row turn with it and work at every moment
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

function signIn(region, scene) {
	const intro = region.querySelector(".legal-title__intro");
	signScene(region, scene, {
		flag: "accessibility",
		title: region.querySelector("h1"),
		eyebrow: region.querySelector(".eyebrow"),
		plane: region,
		held: [region],
		// the heading is shrunk to its text: the pen and the weathered copies take its fractional width
		exact: true,
		letters: { perChar: [0.058, 0.064] },
		then: [["write", intro, 1, 1.2]],
		story: { written: [intro].filter(Boolean) },
	});
}

function contentsIn(nav, { cleanups }) {
	nav.querySelectorAll("a[href]").forEach((link) => {
		if (inView(link)) return;
		riseIn(link, { opacity: 0, x: -24 });
		cleanups.push(() => gsap.set(link, { clearProps: "opacity,transform" }));
	});
}

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

function boardIn(lv, { cleanups }) {
	board(lv, cleanups, {
		guide: "accessibility-rule",
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

// the closing part is the statement's dated note, written by the pen; elsewhere a line after a list closes it
function partIn(part, scene, last) {
	measured(scene, () => {
		headingIn(part.querySelector(".legal__heading"), scene);
		part.querySelectorAll(":scope > .legal__text").forEach((p) => {
			const prev = p.previousElementSibling;
			if (last || (prev && prev.matches(".lv"))) writtenIn(p, scene);
			else textIn(p, scene);
		});
		part.querySelectorAll(":scope > .lv").forEach((lv) => boardIn(lv, scene));
	});
}

export function accessibilityMotion({ ctx, cleanups, alive, fontsReady, wide }) {
	const scene = { ctx, cleanups, alive, fontsReady, wide };
	const sign = document.querySelector('[data-motion="accessibility-sign"]');
	if (sign) signIn(sign, scene);
	const contents = document.querySelector('[data-motion="accessibility-contents"]');
	if (contents) measured(scene, () => contentsIn(contents, scene));
	const parts = [...document.querySelectorAll('[data-motion="accessibility-part"]')];
	parts.forEach((part, i) => partIn(part, scene, i === parts.length - 1));
}
