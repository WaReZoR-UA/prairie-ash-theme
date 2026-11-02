/*
Shared motion helpers: one implementation of each small tool the page modules use, so a fix lands once.

clamp01(v), smooth(a, b, v)     a value held to 0..1, and a smoothstep from a to b
pageTop(el), pageLeft(el)       page position from the layout boxes, untouched by any transform the motion applies
inView(el)                      some of the element is inside the window
relative(el, cleanups)          a static element becomes the containing block of the layers laid over it
layer(parent, className, cleanups, { tag, html, style })   an aria-hidden layer appended to parent, removed on cleanup
redrawOnRefresh(draw, cleanups) draw again after ScrollTrigger has measured
onScroll(trigger, animation, { start, end, scrub })        scrub an animation while its element crosses the eye band
swing(tl, el, at, { axis, from, steps, hinge, start, first }) a board on a hinge or a pin swings and comes to rest
uncover(slot, img, { shut, from, to, end, also, tl, at })  a photo opened from the edge facing its text, with the scroll or on tl
sheen(slot, cleanups, { tl, at, glint, travel, duration, ease, stay })   light crossing the glass of a photo once
inkCopy(el, cleanups)          screen readers keep the real string while the motion runs on an aria-hidden copy
platesIn(plates, cleanups)     a date plate swings down from its hinge and the days are torn off to the date
freshSign(flag, region), signLettered(flag)   the head flag of a sign lettered on load
tipUp(tl, el, at, cleanups, { rise, duration, ease, stagger, revert })   a heading tips up off the wall line by line
riseIn(el, from)               running text rises into its line with the scroll, a control slides in from its edge
*/

import { gsap, ScrollTrigger } from "./gsap.js";
import { SplitText } from "gsap/SplitText";

export const clamp01 = (v) => (v < 0 ? 0 : v > 1 ? 1 : v);
export const smooth = (a, b, v) => {
	const t = clamp01((v - a) / (b - a));
	return t * t * (3 - 2 * t);
};

export function pageTop(el) {
	let y = 0;
	let n = el;
	// an svg has no offsetTop: measure from the box that holds it, plus its own place in that box
	if (!(n instanceof HTMLElement)) {
		const host = n.parentElement;
		y = n.getBoundingClientRect().top - host.getBoundingClientRect().top;
		n = host;
	}
	for (; n; n = n.offsetParent) y += n.offsetTop;
	return y;
}
export function pageLeft(el) {
	let x = 0;
	for (let n = el; n; n = n.offsetParent) x += n.offsetLeft;
	return x;
}
export function inView(el) {
	const r = el.getBoundingClientRect();
	return r.bottom > 0 && r.top < window.innerHeight;
}

export function relative(el, cleanups) {
	if (getComputedStyle(el).position !== "static") return;
	el.style.position = "relative";
	cleanups.push(() => (el.style.position = ""));
}
export function layer(parent, className, cleanups, { tag = "span", html = "", style = null } = {}) {
	const n = document.createElement(tag);
	if (className) n.className = className;
	n.setAttribute("aria-hidden", "true");
	if (html) n.innerHTML = html;
	if (style) Object.assign(n.style, style);
	parent.append(n);
	cleanups.push(() => n.remove());
	return n;
}

// ScrollTrigger renders an animation at its end and back while it measures, with callbacks off: anything
// drawn from a callback is drawn again once the measuring is done
export function redrawOnRefresh(draw, cleanups) {
	ScrollTrigger.addEventListener("refresh", draw);
	cleanups.push(() => ScrollTrigger.removeEventListener("refresh", draw));
}

// The scroll pages drive their motion by the scroll position: each piece runs while its element crosses the band
// of the viewport where the eye rests, and plays back when the page is scrolled up
const EYE = { start: "top 85%", end: "top 40%" };
// A band that would run past the foot of the page is moved up, so everything at the bottom is finished a little
// before the page stops, never on its very last pixel. Positions come from the layout box, not the painted one:
// a band's own entrance may be holding the element 90 px lower when the triggers are measured.
function eyeBand(el, start, end) {
	const f = (spec) => parseFloat(spec.split(" ")[1]) / 100;
	return () => {
		const vh = window.innerHeight;
		const max = ScrollTrigger.maxScroll(window);
		const top = pageTop(el);
		const e = Math.min(top - f(end) * vh, max - vh * 0.08);
		const s = Math.min(top - f(start) * vh, e - vh * 0.25);
		return [Math.max(0, s), Math.max(1, e)];
	};
}
export function onScroll(trigger, animation, { start = EYE.start, end = EYE.end, scrub = 0.8 } = {}) {
	// render the first frame now, so the start state shows before the band is ever reached
	animation.pause().progress(0.001, true).progress(0, true);
	const band = eyeBand(trigger, start, end);
	return ScrollTrigger.create({ trigger, start: () => band()[0], end: () => band()[1], scrub, animation });
}

// A board hung on a hinge or a pin swings down from `from`, or from where it hangs now when `from` is left out, past
// its rest and back, and comes to rest. steps: [angle, seconds, ease] per leg, the first placed at `at` and each later
// one run on from the end of the timeline; hinge: the perspective and origin it turns with; start and first: more
// vars for the start state and the first leg (an opacity, an immediateRender)
export function swing(tl, el, at, { axis = "rotationX", from, steps, hinge = {}, start = {}, first = {} }) {
	const [[angle, duration, ease], ...rest] = steps;
	const to = { [axis]: angle, duration, ease, ...first };
	if (from === undefined) tl.to(el, to, at);
	else tl.fromTo(el, { [axis]: from, ...hinge, ...start }, to, at);
	rest.forEach(([a, d, e]) => tl.to(el, { [axis]: a, duration: d, ease: e }));
	return tl;
}

// The edge of a photo that faces its text: a full-width photo opens from the top, one on the right half of the window
// from its left edge, one on the left half from its right edge; text: the text it illustrates, whose side decides
export function facing(slot, text = null) {
	const r = slot.getBoundingClientRect();
	if (text) {
		const t = text.getBoundingClientRect();
		if (t.left >= r.right - 1) return "inset(0% 0% 0% 100%)";
		if (t.right <= r.left + 1) return "inset(0% 100% 0% 0%)";
		return t.top >= r.bottom - 1 ? "inset(100% 0% 0% 0%)" : "inset(0% 0% 100% 0%)";
	}
	const vw = document.documentElement.clientWidth;
	const mid = r.left + r.width / 2;
	return r.width > vw * 0.7 ? "inset(0% 0% 100% 0%)" : mid > vw / 2 ? "inset(0% 100% 0% 0%)" : "inset(0% 0% 0% 100%)";
}
// A photo uncovered from an edge while the picture settles into its drawn crop, scrubbed while the photo crosses the
// eye band. shut: the clip it opens from; from, to: the picture's own travel; also(tl): anything that rides on the
// same reveal, placed before the scroll takes the timeline; tl, at: a photo already on the screen is opened on the
// caller's timeline at `at` instead of by the scroll
export function uncover(slot, img, { shut = facing(slot), from = { scale: 1.12 }, to = { scale: 1 }, end = "top 45%", also, tl: host = null, at = 0 } = {}) {
	const tl = gsap.timeline()
		.fromTo(slot, { clipPath: shut }, { clipPath: "inset(0% 0% 0% 0%)", duration: 1, ease: "power2.inOut" }, 0)
		.fromTo(img, from, { ...to, duration: 1, ease: "power2.out" }, 0);
	if (also) also(tl);
	if (host) host.add(tl, at);
	else onScroll(slot, tl, { start: "top 92%", end });
	return tl;
}

// Light crossing the glass of a photograph once: on its own `at` seconds from now, or placed on the timeline tl at
// `at`. A pass the scroll drives brings its own layer (glint), travel, pace and ease, and stays on the photo.
export function sheen(slot, cleanups, { tl = null, at = 0, glint = null, travel = [-130, 380], duration = 1.4, ease = "power2.inOut", stay = false } = {}) {
	const light = glint || layer(slot, "motion-sheen", cleanups);
	const to = { xPercent: travel[1], duration };
	if (ease) to.ease = ease;
	if (!tl) to.delay = at;
	if (!stay) to.onComplete = () => light.remove();
	if (tl) tl.fromTo(light, { xPercent: travel[0] }, to, at);
	else gsap.fromTo(light, { xPercent: travel[0] }, to);
	return light;
}

// Screen readers keep the real string, the motion runs on an aria-hidden copy
export function inkCopy(el, cleanups) {
	const html = el.innerHTML;
	const label = el.textContent.replace(/\s+/g, " ").trim();
	const real = document.createElement("span");
	real.className = "visually-hidden";
	real.textContent = label;
	const ink = document.createElement("span");
	ink.setAttribute("aria-hidden", "true");
	ink.innerHTML = html;
	el.replaceChildren(real, ink);
	let restored = false;
	const restore = () => {
		if (restored) return;
		restored = true;
		el.innerHTML = html;
	};
	cleanups.push(restore);
	return { ink, restore };
}

// Date plate: the leaf swings down from its hinge and hangs flat, then the days are torn off one by one to the date
export function platesIn(plates, cleanups) {
	plates.forEach((plate) => {
		const parts = [...plate.children];
		const day = parts.find((part) => /^\d+$/.test(part.textContent.trim()));
		const rest = parts.filter((p) => p !== day);
		const tl = gsap.timeline({ defaults: { ease: "none" } });
		// the leaf swings down from its hinge and hangs flat, a little past and back like a hung sign
		swing(tl, plate, 0, { from: -100, steps: [[8, 0.42, "power2.in"], [-3, 0.14, "sine.inOut"], [0, 0.12, "sine.out"]], hinge: { transformPerspective: 700, transformOrigin: "50% 0%" } })
			.fromTo(plate, { opacity: 0 }, { opacity: 1, duration: 0.1 }, 0)
			.fromTo(rest, { opacity: 0, y: -8 }, { opacity: 1, y: 0, duration: 0.2, ease: "power2.out", stagger: 0.08 }, 0.3);
		if (day) {
			// the days are torn off one by one down to the date: each leaf falls from the top edge of the plate
			const target = parseInt(day.textContent, 10);
			const { ink } = inkCopy(day, cleanups);
			ink.style.display = "inline-block";
			const span = target - 1 + 0.6;
			const leaf = { t: 0 };
			const show = () => {
				const n = Math.min(target - 1, Math.floor(leaf.t));
				const k = clamp01((leaf.t - n) / 0.6);
				ink.textContent = String(n + 1);
				gsap.set(ink, { rotationX: -90 * (1 - k * (2 - k)), transformPerspective: 300, transformOrigin: "50% 0%" });
			};
			tl.fromTo(leaf, { t: 0 }, { t: span, duration: 0.6, onUpdate: show }, 0.36);
			show();
			redrawOnRefresh(show, cleanups);
		}
		onScroll(plate, tl, { start: "top 88%", end: "top 50%" });
		cleanups.push(() => gsap.set(plate, { clearProps: "transform,opacity" }));
	});
}

// The head flag: a page whose sign is lettered on load carries data-<flag>="pre" on <html> from its head, so the first
// paint already shows the faint sign. freshSign: the sign is to be lettered now, because this page view has not
// lettered it yet, the page has only just loaded and the region (when given) is still on the screen. signLettered:
// the act is over, or skipped, and the flag reads "live" for good.
const lettered = new Set();
export function freshSign(flag, region) {
	return !lettered.has(flag) && document.documentElement.dataset[flag] === "pre" && performance.now() < 4600 && (!region || region.getBoundingClientRect().bottom > 0);
}
export function signLettered(flag) {
	lettered.add(flag);
	document.documentElement.dataset[flag] = "live";
}

// A heading tips up off the wall line by line, each line hinged on its foot, placed on tl at `at`; revert: the lines
// are put back together once they all stand
export function tipUp(tl, el, at, cleanups, { rise = 16, duration = 1, ease = "power2.out", stagger = 0.25, revert = false } = {}) {
	const split = SplitText.create(el, { type: "lines", aria: "none", tag: "span" });
	cleanups.push(() => split.revert());
	const to = { opacity: 1, y: 0, rotationX: 0, duration, ease, stagger };
	if (revert) to.onComplete = () => split.revert();
	return tl.fromTo(split.lines, { opacity: 0, y: rise, rotationX: -70, transformPerspective: 600, transformOrigin: "50% 100%" }, to, at);
}

// Running text rises into its line while it crosses the lower part of the window; a control comes in from the edge
// it ends on (from: its start state)
export function riseIn(el, from = { opacity: 0, y: 16 }) {
	return onScroll(el, gsap.timeline().fromTo(el, from, { opacity: 1, x: 0, y: 0, duration: 1, ease: "power2.out" }), { start: "top 90%", end: "top 65%" });
}
