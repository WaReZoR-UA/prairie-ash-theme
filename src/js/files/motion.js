/*
Motion layer, hand-off section "Motion". Hooks are data attributes, so copy edits keep their motion.

data-motion-scope        page region whose headings, text, rows, controls, rules and photos reveal on scroll
data-motion="sign"       title sign: lettered by a brush, line by line, side block after
  data-motion-plate      the sign hangs from a hinge and swings into place first (door plate, trade sign)
  data-motion-ghost      the sign fades to a ghost on the wall as the page scrolls on (desktop)
data-motion="stripe"     a pinstripe, ruled from the left
data-motion="card"       a coffee sign, swings down from its hook (batched)
data-motion="price"      price lettering, stamped
data-motion="count"      a figure counts up to its value (cart and order totals count too)
data-motion="plate"      date plate: drops like a calendar leaf, the day flips to its date
data-motion="wall"       painted wall advert, lines painted in while scrolling
data-motion="scene"      pinned scene on desktop: camera pushes in, heat and steam rise behind the glass
data-motion="curve"      roast curve: drawn with the scroll, the drum turns, the bean browns
data-motion="map"        map stands up, streets drawn, then the walk from the bus stop is traced
data-motion="wordmark"   footer wordmark: letters drawn and filled, then lit by a torch
data-motion="reveal"     any other block that should fade up with its neighbours
data-motion-parallax     photo slot whose image drifts while in view
data-motion-skip         opt out, the subtree keeps its static look

Sections of a page take turns with distinct scrubbed entrances (tilt, swing, depth, counter-motion), and
on a wide screen a few bands stay pinned while the next one slides over them.

The stylesheet always holds the finished page. Every start state is set here, inside
gsap.matchMedia() under prefers-reduced-motion: no-preference, so no JS or reduced motion
gives the settled page.
*/

import { gsap, ScrollTrigger } from "./gsap.js";
import { SplitText } from "gsap/SplitText";
import { CustomEase } from "gsap/CustomEase";
import { ScrollSmoother } from "gsap/ScrollSmoother";
import { Flip } from "gsap/Flip";
import { MotionPathPlugin } from "gsap/MotionPathPlugin";

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
// reveals start once the element is about a quarter into the viewport, so they play where the eye is
const START = "clamp(top 75%)";
const EARLY = START;
const SVG_NS = "http://www.w3.org/2000/svg";

const REDUCED = "(prefers-reduced-motion: reduce)";
const fontsReady = Promise.race([document.fonts ? document.fonts.ready : Promise.resolve(), new Promise((resolve) => setTimeout(resolve, 1500))]);

const TOTALS = ".lv__row--due .lv__value, .lv__row--paid .lv__value, .checkout-title__total-value";
const STAMPS = '[data-motion="price"], .roastery__fact';
const LEAF = 'h1, h2, h3, h4, p, li, tr, dl > div, dl > dt, dl > dd, figcaption, address, blockquote, legend, .field, .checkbox, .options__item, .text-link, .button, [data-motion="reveal"]';
const BLOCKER = 'img, picture, svg, table, dl, ul, ol, form, fieldset, h1, h2, h3, .field, .checkbox, [data-motion]:not([data-motion="price"]):not([data-motion="count"]):not([data-motion="stripe"]), [data-motion-skip]';
const OWNED = `[data-motion="sign"], [data-motion="card"], [data-motion="plate"], [data-motion="map"], [data-motion="wall"], [data-motion="wordmark"], [data-motion="curve"], ${STAMPS}, [data-motion="count"], [data-motion-skip], [hidden], .visually-hidden, [data-error-summary-box], header`;
const isHeading = (el) => /^H[1-4]$/.test(el.tagName);
// This module arrives after the page has painted. Whatever is on screen at that moment is already
// read, so it keeps its painted state: entrances are only staged for what is still below the fold.
let painted = () => false;
const offscreen = (el) => !painted(el);
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
	"inset(-12% -12% 42% -12%)", // opens downward
	"inset(-12% 42% -12% -12%)", // opens to the right
	"inset(20% 20% 20% 20%)", // opens from the centre out
	"inset(-12% -12% -12% 42%)", // opens to the left
];
const WIPE_OPEN = "inset(-12% -12% -12% -12%)";
function wipeShut(el, kind = 0) {
	gsap.set(el, { clipPath: WIPES[kind % WIPES.length] });
}
function wipeOpen(el, vars = {}) {
	return gsap.to(el, { clipPath: WIPE_OPEN, duration: D.brush, ease: "brush", clearProps: "clipPath", ...vars });
}

// Every reveal answers the scroll both ways: it plays when its element reaches the reading line and
// runs back to its waiting state, last in first out, when the line is crossed upward. Waiting states
// are always readable, so nothing is ever hidden whatever the scroll does.
const BACK = { duration: 0.45, ease: "power2.inOut" };
const CLEAR = "transform,transformOrigin";
// Plain transform reveals. fixed holds what must not be tweened (origin, perspective).
function toggleReveal(els, { fixed = {}, from, to, start = EARLY, each = 0, play }) {
	if (!els.length) return;
	// per element values, so a function value keeps its element's own index on the way back
	const resolve = (vars, i, el) => Object.fromEntries(Object.entries(vars).map(([k, v]) => [k, typeof v === "function" ? v(i, el, els) : v]));
	const own = new Map(els.map((el, i) => [el, { fixed: resolve(fixed, i, el), from: resolve(from, i, el) }]));
	els.forEach((el) => gsap.set(el, { ...own.get(el).fixed, ...own.get(el).from }));
	const fwd = (el, i) => (play ? play(el, i) : gsap.to(el, { ...to, delay: i * each, overwrite: true, onComplete: () => gsap.set(el, { clearProps: CLEAR }) }));
	const back = (batch) => batch.forEach((el, i) => {
		gsap.set(el, own.get(el).fixed);
		gsap.to(el, { ...own.get(el).from, ...BACK, delay: (batch.length - 1 - i) * Math.min(each, 0.05), overwrite: true });
	});
	ScrollTrigger.batch(els, {
		start,
		onEnter: (batch) => batch.forEach(fwd),
		onLeaveBack: back,
	});
}
// A reveal whose timeline rebuilds the markup it animates (split lines, ink copies). build() returns
// { tl, undo }; undo puts the markup back once the timeline has played through, and the timeline is
// built again, set to its end and run backward when the reading line is crossed upward.
function staged(trigger, build, ctx, cleanups, { start = START, back = 1.6 } = {}) {
	let cur = null;
	const make = () => {
		ctx.add(() => {
			cur = build();
		});
		cur.tl.pause();
		cur.tl.eventCallback("onComplete", done);
		return cur;
	};
	function done() {
		const c = cur;
		cur = null;
		if (c && c.undo) c.undo();
	}
	// the waiting state is staged half a window before it can be seen, so the start-up stays light
	let prepared = false;
	ScrollTrigger.create({
		trigger,
		start: "top bottom+=50%",
		once: true,
		onEnter: () => {
			if (!prepared && !cur) make();
			prepared = true;
		},
	});
	cleanups.push(() => {
		if (cur && cur.undo) cur.undo();
		cur = null;
	});
	return ScrollTrigger.create({
		trigger,
		start,
		onEnter: () => {
			prepared = true;
			(cur || make()).tl.timeScale(1).play();
		},
		onLeaveBack: () => {
			if (!cur) make().tl.progress(1, true);
			cur.tl.timeScale(back).reverse();
		},
	});
}

// Screen readers keep the real string, the motion runs on an aria-hidden copy
function inkCopy(el, cleanups) {
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

function brushLines(target, cleanups) {
	const split = SplitText.create(target, { type: "lines", mask: "lines", aria: "none", tag: "span" });
	split.masks.forEach((mask) => {
		mask.style.overflowX = "clip";
		mask.style.overflowY = "visible";
	});
	cleanups.push(() => split.revert());
	return { split, masks: split.masks, lines: split.lines };
}

function deco(parent, className, cleanups, html = "") {
	const el = document.createElement("span");
	el.className = className;
	el.setAttribute("aria-hidden", "true");
	if (html) el.innerHTML = html;
	parent.append(el);
	cleanups.push(() => el.remove());
	return el;
}

// Sign painter's lettering: each line opens behind a brush that travels exactly along its ink,
// a thin first coat then full paint
function letter(el, { brush = true, from = 0.4 } = {}) {
	const local = [];
	const kids = [...el.children];
	const parts = kids.length && kids.every((kid) => getComputedStyle(kid).display === "block") ? kids : [el];
	const painted = parts.map((part) => brushLines(part, local));
	const lines = painted.flatMap((p) => p.lines);
	const hadPosition = el.style.position;
	if (getComputedStyle(el).position === "static") el.style.position = "relative";
	const hostRect = el.getBoundingClientRect();
	// measured in the element's own pixels: a section entrance may be scaling or tilting it right now
	const kx = hostRect.width / (el.offsetWidth || hostRect.width || 1) || 1;
	const ky = hostRect.height / (el.offsetHeight || hostRect.height || 1) || 1;
	const brushes = [];
	const stroke = D.brush * 1.7;
	const tl = gsap.timeline({ paused: true });
	const undo = () => {
		painted.forEach((p) => p.split.revert());
		brushes.forEach((b) => b.remove());
		lines.forEach((line) => (line.style.clipPath = ""));
		el.style.position = hadPosition;
	};
	lines.forEach((line, i) => {
		const lineRect = line.getBoundingClientRect();
		const range = document.createRange();
		range.selectNodeContents(line);
		const inkRect = range.getBoundingClientRect();
		const ink = Math.max(8, (inkRect.right - lineRect.left) / kx + 6);
		const block = lineRect.width / kx;
		const at = i * STAGGER.line * 1.5;
		const reach = { v: ink * from };
		const clip = () => (line.style.clipPath = `inset(-30% ${Math.max(0, block - reach.v).toFixed(1)}px -30% -0.2em)`);
		clip();
		tl.to(reach, { v: ink, duration: stroke * (1 - from * 0.6), ease: "brush", onUpdate: clip, onComplete: () => (line.style.clipPath = "") }, at);
		tl.fromTo(line, { y: 6 }, { y: 0, duration: D.slow, ease: OUT }, at);
		if (!brush) return;
		const b = document.createElement("span");
		b.className = "motion-brush";
		b.setAttribute("aria-hidden", "true");
		const lh = lineRect.height / ky;
		Object.assign(b.style, { left: `${(lineRect.left - hostRect.left) / kx}px`, top: `${(lineRect.top - hostRect.top) / ky + lh * 0.14}px`, height: `${lh * 0.72}px` });
		el.append(b);
		brushes.push(b);
		tl.fromTo(b, { x: 0, rotation: -14, opacity: 0 }, { x: ink, rotation: 10, duration: stroke, ease: "brush" }, at)
			.to(b, { opacity: 1, duration: 0.06 }, at)
			.to(b, { opacity: 0, duration: 0.2, ease: "power1.in" }, at + stroke - 0.12);
	});
	return { tl, undo };
}

// Title sign, storyboard "sign paint-in" (794:4944), lettered by a brush
function signIn(sign, ctx, cleanups, alive, wide) {
	const stripes = [...sign.querySelectorAll('[data-motion="stripe"]')];
	const title = sign.querySelector("h1, h2");
	if (!title) return;
	const side = collectLeaves(sign, OWNED, [title]);
	const plate = sign.hasAttribute("data-motion-plate");
	const ghosted = wide && sign.hasAttribute("data-motion-ghost");

	if (painted(sign)) {
		if (ghosted) ghost(sign, title);
		return;
	}
	if (ghosted) ghost(sign, title);
	const sideStagger = Math.min(STAGGER.item * 1.5, 0.6 / Math.max(side.length, 1));
	const build = () => {
		gsap.set(stripes, { scaleX: 0.3, transformOrigin: (i) => (i % 2 ? "100% 50%" : "0% 50%") });
		gsap.set(side, { y: RISE, rotationX: -20, transformPerspective: 700, transformOrigin: "50% 0%" });
		// a hung plate swings down from its hinge before it is lettered
		if (plate) gsap.set(sign, { rotationX: -40, transformPerspective: 1400, transformOrigin: "50% 0%" });
		const lettering = letter(title);
		// lettering starts while the plate is still swinging, so the sign is never a blank board
		const t0 = plate ? 0.12 : 0;
		const tl = gsap.timeline();
		if (plate) tl.to(sign, { rotationX: 0, duration: 0.9, ease: "elastic.out(1, 0.5)" }, 0);
		if (stripes[0]) tl.to(stripes[0], { scaleX: 1, duration: D.brush * 1.4, ease: "expo.inOut" }, t0);
		tl.add(lettering.tl.play(), t0 + 0.05);
		if (stripes.length > 1) tl.to(stripes.slice(1), { scaleX: 1, duration: D.brush * 1.4, ease: "expo.inOut", stagger: 0.08 }, t0 + 0.75);
		tl.to(side, { y: 0, rotationX: 0, duration: D.slow * 1.2, ease: OUT, stagger: sideStagger }, t0 + 0.2);
		return {
			tl,
			undo: () => {
				lettering.undo();
				gsap.set([...stripes, ...side, ...(plate ? [sign] : [])], { clearProps: CLEAR });
			},
		};
	};
	fontsReady.then(() => alive() && ctx.add(() => staged(sign, build, ctx, cleanups)));
}

// As the hero leaves, its lettering fades back into the brick like an old painted sign
function ghost(sign, title) {
	gsap.fromTo(title, { opacity: 1, y: 0 }, {
		opacity: 0.28,
		y: -48,
		ease: "none",
		scrollTrigger: { trigger: sign, start: "top top", end: "bottom top", scrub: true },
	});
}

// Section headings: h2 lettered by the brush on a wide screen; h3 and h4 tip up off the wall line by
// line. The waiting state is partly lettered or tilted, never blank.
function headingBuild(el, wide) {
	if (el.tagName === "H2" && wide) return () => letter(el);
	return () => {
		const split = SplitText.create(el, { type: "lines", aria: "none", tag: "span" });
		gsap.set(split.lines, { y: RISE, rotationX: -35, transformPerspective: 600, transformOrigin: "50% 100%" });
		const tl = gsap.timeline().to(split.lines, { y: 0, rotationX: 0, duration: D.slow * 1.1, ease: OUT, stagger: STAGGER.line });
		return { tl, undo: () => split.revert() };
	};
}

// Running text rises, table rows flip down like a lettered board, controls slide in from the side.
// Every waiting state is readable: offset, tilted or partly lettered, at full ink
function leavesIn(leaves, ctx, cleanups, wide) {
	if (!leaves.length) return;
	const control = (el) => el.matches(".button, .text-link");
	const heads = leaves.filter(isHeading);
	const rows = leaves.filter((el) => !isHeading(el) && isRow(el));
	const controls = leaves.filter((el) => !isHeading(el) && !isRow(el) && control(el));
	const text = leaves.filter((el) => !isHeading(el) && !isRow(el) && !control(el));
	fontsReady.then(() => heads.forEach((el) => ctx.add(() => staged(el, headingBuild(el, wide), ctx, cleanups))));
	toggleReveal(rows, {
		fixed: { transformPerspective: 900, transformOrigin: "50% 0%" },
		from: { rotationX: -35 },
		to: { rotationX: 0, duration: D.slow, ease: "back.out(1.4)" },
		each: 0.06,
	});
	toggleReveal(controls, { from: { x: -24 }, to: { x: 0, duration: D.slow, ease: OUT }, each: STAGGER.item });
	toggleReveal(text, { from: { y: RISE * 1.5 }, to: { y: 0, duration: D.slow, ease: OUT }, each: STAGGER.item });
}

function stripesIn(stripes) {
	toggleReveal(stripes, {
		fixed: { transformOrigin: (i) => (i % 2 ? "100% 50%" : "0% 50%") },
		from: { scaleX: 0.3 },
		to: { scaleX: 1, duration: D.brush * 1.4, ease: "expo.inOut" },
		each: STAGGER.card,
	});
}

// Coffee signs hang from a hook: each swings down from the wall, overshoots and settles; scrolled
// back above, it swings up onto its hook again
function cardsIn(cards) {
	if (!cards.length) return;
	const stripeOf = (card) => card.querySelector('[data-motion="stripe"]');
	const stripes = cards.map(stripeOf).filter(Boolean);
	gsap.set(stripes, { scaleX: 0.3, transformOrigin: "0% 50%" });
	toggleReveal(cards, {
		fixed: { transformPerspective: 1100, transformOrigin: "50% 0%" },
		from: { rotationX: -28, rotation: (i) => (i % 2 ? 3 : -3) },
		start: EARLY,
		each: STAGGER.card,
		play: (card, i) => {
			gsap.to(card, { rotationX: 0, rotation: 0, duration: 1, ease: "elastic.out(1, 0.6)", delay: i * STAGGER.card, overwrite: true, onComplete: () => gsap.set(card, { clearProps: CLEAR }) });
			const stripe = stripeOf(card);
			if (stripe) gsap.to(stripe, { scaleX: 1, duration: D.brush, ease: "brush", delay: 0.4 + i * STAGGER.card, overwrite: true, onComplete: () => gsap.set(stripe, { clearProps: CLEAR }) });
		},
	});
	// the stripes follow their card back
	ScrollTrigger.batch(stripes, {
		start: EARLY,
		onLeaveBack: (batch) => batch.forEach((el) => {
			gsap.set(el, { transformOrigin: "0% 50%" });
			gsap.to(el, { scaleX: 0.3, ...BACK, overwrite: true });
		}),
	});
}

function settle(base, parallax) {
	const vars = { scale: base, duration: D.showpiece, ease: OUT };
	if (!parallax) vars.clearProps = "transform";
	return vars;
}

// The pour in the hero photograph: green beans slide off the scoop's lip and fall into the hopper,
// at the size, colour and light of the beans in the picture. They come out from behind the lip and
// drop behind the hopper's front rim (the layer's clip-path), so they never leave the photograph.
// Geometry is in the photo's own percentages (1696 x 848); the slot always shows it whole at 2:1.
const POUR = {
	lip: [[59, 35.85], [64.9, 34.43], [69.6, 32.55]], // underside of the scoop's lip, left to right
	spawn: [58.9, 67.8], // where beans leave the lip, weighted to the left like the photographed stream
	rim: 46.1, // top of the hopper's front rim, where a bean may clip it and hop in
	gone: 48, // well behind the rim: the bean is recycled
	fall: 0.6, // seconds from lip to rim, a slow motion of the real 0.15 s that the eye can follow
};
const lipAt = (x) => {
	const [a, b, c] = POUR.lip;
	if (x <= a[0]) return a[1];
	const [p, q] = x <= b[0] ? [a, b] : [b, c];
	return p[1] + ((Math.min(x, q[0]) - p[0]) / (q[0] - p[0])) * (q[1] - p[1]);
};
function pourIn(ctx, cleanups) {
	const slot = document.querySelector(".hero__media");
	const hero = slot && slot.closest(".hero");
	if (!slot || !hero) return;
	const layer = deco(slot, "hero-pour", cleanups);
	const pool = [];
	for (let i = 0; i < 64; i++) {
		const bean = document.createElement("span");
		bean.className = "hero-pour__bean";
		bean.innerHTML = '<span class="hero-pour__body"><span class="hero-pour__face"></span><span class="hero-pour__face hero-pour__face--back"></span></span>';
		layer.append(bean);
		const body = bean.firstChild;
		pool.push({
			bean,
			body,
			live: false,
			set: {
				x: gsap.quickSetter(bean, "x", "px"),
				y: gsap.quickSetter(bean, "y", "px"),
				r: gsap.quickSetter(bean, "rotation", "deg"),
				sy: gsap.quickSetter(bean, "scaleY"),
				tx: gsap.quickSetter(body, "rotationX", "deg"),
			},
		});
	}
	let W = 0;
	let H = 0;
	const measure = () => {
		W = layer.offsetWidth;
		H = layer.offsetHeight;
	};
	measure();
	const ro = new ResizeObserver(measure);
	ro.observe(layer);
	cleanups.push(() => ro.disconnect());

	const state = { flow: 0, wind: 0 }; // beans a second, and a sideways lean from the scroll
	const rand = gsap.utils.random;
	let carry = 0;
	let running = false;
	let visible = true;
	const spawn = () => {
		const b = pool.find((p) => !p.live);
		if (!b || !W) return;
		const [x0, x1] = POUR.spawn;
		const xp = x0 + (x1 - x0) * Math.pow(Math.random(), 1.7);
		const yp = lipAt(xp) - 0.9;
		const g = (2 * (POUR.rim - yp) * H) / 100 / (POUR.fall * POUR.fall);
		const z = rand(-1, 1); // depth: near beans larger, far ones smaller
		Object.assign(b, {
			live: true,
			x: 0,
			y: 0,
			vx: rand(-0.012, 0.022) * W + state.wind * W * 0.03,
			vy: rand(0.01, 0.04) * H,
			g: g * rand(0.92, 1.06),
			rimY: ((POUR.rim - yp) / 100) * H + (rand(-0.3, 0.4) * H) / 100,
			goneY: ((POUR.gone - yp) / 100) * H,
			hop: Math.random() < 0.28,
			hopped: false,
			spin: rand(360, 900) * (Math.random() < 0.5 ? -1 : 1),
			angle: rand(0, 360),
		});
		gsap.set(b.bean, {
			left: `${xp}%`,
			top: `${yp}%`,
			xPercent: -50,
			yPercent: -50,
			autoAlpha: 1,
		});
		// the axis it tumbles around, fixed for its whole fall
		gsap.set(b.body, { scale: 1 + z * 0.18, rotation: rand(-60, 60), rotationY: rand(-25, 25) });
	};
	function stop() {
		running = false;
		gsap.ticker.remove(tick);
	}
	function tick(time, dt) {
		const t = Math.min(dt, 50) / 1000;
		if (visible && state.flow > 0) {
			carry += state.flow * t;
			while (carry >= 1) {
				carry -= 1;
				spawn();
			}
		}
		let any = false;
		pool.forEach((b) => {
			if (!b.live) return;
			any = true;
			b.vy += b.g * t;
			b.x += b.vx * t;
			b.y += b.vy * t;
			b.angle += b.spin * t;
			// a bean that clips the rim loses most of its speed and hops in
			if (b.hop && !b.hopped && b.y >= b.rimY && b.vy > 0) {
				b.hopped = true;
				b.vy *= -rand(0.18, 0.3);
				b.vx *= 0.55;
				b.spin *= -0.5;
			}
			const speed = Math.hypot(b.vx, b.vy);
			b.set.x(b.x);
			b.set.y(b.y);
			b.set.r((-Math.atan2(b.vx, b.vy) * 180) / Math.PI);
			// motion blur: stretched along its path in proportion to its speed
			b.set.sy(1 + Math.min(0.32, speed / (H * 3.2)));
			b.set.tx(b.angle);
			if (b.y > b.goneY) {
				b.live = false;
				gsap.set(b.bean, { autoAlpha: 0 });
			}
		});
		if (!any && state.flow <= 0) stop();
	}
	const start = () => {
		if (running) return;
		running = true;
		gsap.ticker.add(tick);
	};
	cleanups.push(stop);
	let flowTl = null;
	let until = 0;
	const pour = (flow, hold, wind = 0) => {
		// a running scoop is never cut short by a smaller one
		const end = gsap.ticker.time + 0.25 + hold;
		if (flowTl && flowTl.isActive() && end < until) return;
		until = end;
		if (flowTl) flowTl.kill();
		start();
		flowTl = gsap.timeline()
			.to(state, { flow, wind, duration: 0.25, ease: "power2.out" })
			.to(state, { flow: 0, duration: 0.6, ease: "power2.in" }, 0.25 + hold);
	};
	ScrollTrigger.create({
		trigger: hero,
		start: "top bottom",
		end: "bottom top",
		onToggle: (self) => (visible = self.isActive),
		// the scoop pours harder while the visitor scrolls past it, and the stream leans with the scroll
		onUpdate: (self) => {
			const v = Math.abs(self.getVelocity());
			if (v < 30) return;
			pour(gsap.utils.clamp(18, 40, v / 40), 0.2, self.direction * Math.min(1, v / 1500));
		},
	});
	// a full scoop is tipped when the stream first shows and again when it reaches the reading zone,
	// from either direction (the stream sits about 40% down the photo)
	const burst = () => pour(34, 2.4);
	ScrollTrigger.create({ trigger: slot, start: "40% bottom", end: "40% top", onEnter: burst, onEnterBack: burst });
	ScrollTrigger.create({ trigger: slot, start: "40% 70%", end: "40% 20%", onEnter: burst, onEnterBack: burst });
}

// The hero photograph drifts down behind its window once the page leaves the top, and stays exactly
// as painted until then. The pour layer rides along, so the beans stay on the photographed stream.
function heroDrift(slot, img) {
	const targets = [img, ...slot.querySelectorAll(".hero-pour")];
	gsap.fromTo(targets, { yPercent: 0, scale: 1 }, { yPercent: 5, scale: 1.12, ease: "none", scrollTrigger: { trigger: slot, start: 0, end: "bottom top", scrub: true } });
}

function mediaIn(imgs, wide) {
	imgs.forEach((img, n) => {
		const slot = img.closest('[class*="-ibg"]') || img.parentElement;
		const parallax = slot.hasAttribute("data-motion-parallax");
		if (painted(slot)) {
			if (parallax && window.scrollY < 2 && slot.closest(".hero")) heroDrift(slot, img);
			return;
		}
		const base = parallax ? 1.1 : 1;
		const shut = { scale: base + 0.14, rotation: n % 2 ? 1.5 : -1.5 };
		// opened from a side that changes photo by photo, so the part in view is never an empty block
		wipeShut(slot, n);
		gsap.set(img, shut);
		ScrollTrigger.create({
			trigger: slot,
			start: "clamp(top 98%)",
			onEnter: () => {
				wipeOpen(slot, { duration: 1.1, ease: "power3.out", overwrite: true });
				gsap.to(img, { ...settle(base, parallax), rotation: 0, overwrite: "auto" });
			},
			// leaving below the window, the photo closes again the way it opened
			onLeaveBack: () => {
				gsap.set(slot, { clipPath: WIPE_OPEN });
				gsap.to(slot, { clipPath: WIPES[n % WIPES.length], ...BACK, duration: 0.6, overwrite: true });
				gsap.to(img, { ...shut, ...BACK, duration: 0.6, overwrite: "auto" });
			},
		});
		if (parallax) {
			const amp = wide ? 4.5 : 3;
			gsap.fromTo(img, { yPercent: -amp }, { yPercent: amp, ease: "none", scrollTrigger: { trigger: slot, start: "top bottom", end: "bottom top", scrub: true } });
		}
	});
}

// Prices are stamped onto the board: the lettering lands from above the paper and bites in, and is
// lifted off again when scrolled back above
function stampsIn(stamps, ctx, cleanups) {
	stamps.forEach((el, i) => {
		const range = document.createRange();
		range.selectNodeContents(el);
		const ink = range.getBoundingClientRect();
		const box = el.getBoundingClientRect();
		const align = getComputedStyle(el).textAlign;
		const cx = align === "right" || align === "end" ? box.width - ink.width / 2 : ink.left - box.left + ink.width / 2;
		const origin = `${cx.toFixed(1)}px 50%`;
		staged(el, () => {
			gsap.set(el, { scale: 1.3, rotation: -6, transformOrigin: origin });
			const tl = gsap.timeline()
				.to(el, { scale: 1, rotation: i % 3 === 1 ? 1.2 : -0.8, duration: 0.28, ease: "power4.in" })
				.to(el, { rotation: 0, duration: 0.45, ease: "elastic.out(1.2, 0.4)" });
			return { tl, undo: () => gsap.set(el, { clearProps: CLEAR }) };
		}, ctx, cleanups, { start: EARLY, back: 1.4 });
	});
}

// Figures count up to their value, decimals and currency kept, so a total ticks up like a till, and
// count back down when scrolled back above
function countsIn(counts, ctx, cleanups) {
	counts.forEach((el) => {
		const text = el.textContent.replace(/\s+/g, " ").trim();
		const match = text.match(/^(\D*?)(\d[\d,]*(?:\.\d+)?)([\s\S]*)$/);
		if (!match) return;
		const [, before, digits, after] = match;
		const target = parseFloat(digits.replace(/,/g, ""));
		if (!target) return;
		const decimals = (digits.split(".")[1] || "").length;
		const commas = digits.includes(",");
		const format = (v) => {
			const s = v.toFixed(decimals);
			return commas ? Number(s).toLocaleString("en-CA", { minimumFractionDigits: decimals, maximumFractionDigits: decimals }) : s;
		};
		staged(el, () => {
			const local = [];
			const { ink, restore } = inkCopy(el, local);
			const state = { value: 0 };
			const show = () => (ink.textContent = `${before}${format(state.value)}${after}`);
			show();
			const tl = gsap.timeline().to(state, { value: target, duration: decimals ? 1.3 : 0.9, ease: "power3.out", delay: 0.15, onUpdate: show });
			return { tl, undo: restore };
		}, ctx, cleanups, { back: 2 });
	});
}

// Date plate: drops like a torn calendar leaf from its top edge, then the day flips to its date
function platesIn(plates, ctx, cleanups) {
	plates.forEach((plate) => {
		const parts = [...plate.children];
		const day = parts.find((part) => /^\d+$/.test(part.textContent.trim()));
		const rest = parts.filter((p) => p !== day);
		staged(plate, () => {
			gsap.set(plate, { rotationX: -40, transformPerspective: 700, transformOrigin: "50% 0%" });
			gsap.set(rest, { y: -8 });
			let flip = null;
			if (day) {
				const target = parseInt(day.textContent, 10);
				const { ink, restore } = inkCopy(day, []);
				ink.style.display = "inline-block";
				ink.textContent = "1";
				flip = { ink, target, restore };
			}
			const tl = gsap.timeline();
			tl.to(plate, { rotationX: 0, duration: 0.9, ease: "elastic.out(1, 0.55)" }, 0).to(rest, { y: 0, duration: D.base, ease: OUT, stagger: 0.08 }, 0.3);
			if (flip) {
				for (let n = 2; n <= flip.target; n++) {
					const at = 0.25 + (n - 2) * 0.06;
					tl.set(flip.ink, { textContent: String(n) }, at);
					tl.fromTo(flip.ink, { rotationX: -90, transformPerspective: 300, transformOrigin: "50% 0%" }, { rotationX: 0, duration: n === flip.target ? 0.5 : 0.09, ease: n === flip.target ? "back.out(2.5)" : "none" }, at);
				}
			}
			return {
				tl,
				undo: () => {
					if (flip) flip.restore();
					gsap.set([plate, ...rest], { clearProps: CLEAR });
				},
			};
		}, ctx, cleanups, { back: 2 });
	});
}

// Painted wall advert on Home: each line is painted in as it comes into view and painted out again
// when scrolled back above. Played, not scrubbed, so a visitor who stops scrolling never looks at a
// half-painted line
function wallsIn(walls, ctx, cleanups, alive) {
	walls.forEach((wall) => {
		const stripes = [...wall.querySelectorAll('[data-motion="stripe"]')];
		const rows = [...wall.querySelectorAll("li")];
		toggleReveal(stripes, {
			fixed: { transformOrigin: "0% 50%" },
			from: { scaleX: 0.3 },
			to: { scaleX: 1, duration: D.brush, ease: "brush" },
			start: START,
			each: 0.3,
		});
		fontsReady.then(() => {
			if (!alive()) return;
			ctx.add(() => {
				rows.forEach((row) => {
					// a line set to the right of the wall is painted from the right
					const dir = getComputedStyle(row).justifyContent === "flex-end" ? -1 : 1;
					staged(row, () => {
						const local = [];
						const parts = [...row.children].map((part) => brushLines(part, local));
						const masks = parts.flatMap((p) => p.masks);
						const lines = parts.flatMap((p) => p.lines);
						gsap.set(masks, { xPercent: -45 * dir });
						gsap.set(lines, { xPercent: 45 * dir });
						const tl = gsap.timeline({ defaults: { duration: D.brush * 1.6, ease: "power2.out", stagger: 0.12 } })
							.to(masks, { xPercent: 0 }, 0)
							.to(lines, { xPercent: 0 }, 0);
						return { tl, undo: () => local.forEach((fn) => fn()) };
					}, ctx, cleanups);
				});
			});
		});
	});
}

// Roast curve: a timeline from charge to drop, scrubbed by the caller. The finished drawing is the
// markup; the readout, the dot, the drum and the bean are driven from the progress
const BEAN = ["#7d8b4f", "#a7a35a", "#c9a14a", "#a8662f", "#7a4122", "#4e2a17"];
const STAGES = [[0, "Charge"], [0.6, "Turning point"], [2, "Drying"], [5, "Yellowing"], [7, "Browning"], [9.17, "First crack"], [10, "Development"], [11.6, "Drop"]];
function curveTimeline(fig, cleanups) {
	const live = fig.querySelector(".roast-curve__live");
	if (!live) return null;
	const dot = fig.querySelector(".roast-curve__dot");
	const drum = fig.querySelector(".roast-curve__drum");
	const bean = fig.querySelector(".roast-curve__bean");
	const time = fig.querySelector(".roast-curve__time");
	const temp = fig.querySelector(".roast-curve__temp");
	const stage = fig.querySelector(".roast-curve__stage");
	const marks = [...fig.querySelectorAll("[data-curve-at]")];
	const saved = [time, temp, stage].map((t) => t && t.textContent);
	const savedDot = dot && [dot.getAttribute("cx"), dot.getAttribute("cy")];
	const savedFill = bean && bean.getAttribute("fill");
	const length = live.getTotalLength();
	const end = live.getPointAtLength(length);
	const color = gsap.utils.interpolate(BEAN);
	const pad = (n) => String(n).padStart(2, "0");
	const render = (p) => {
		const pt = live.getPointAtLength(p * length);
		const minutes = Math.max(0, ((pt.x - 48) / (end.x - 48)) * 11.67);
		const celsius = Math.round(80 + (250 - pt.y) / 1.4667);
		if (dot) {
			dot.setAttribute("cx", pt.x.toFixed(1));
			dot.setAttribute("cy", pt.y.toFixed(1));
		}
		if (time) time.textContent = `${Math.floor(minutes)}:${pad(Math.min(59, Math.round((minutes % 1) * 60)))}`;
		if (temp) temp.textContent = `${celsius}°C`;
		if (stage) stage.textContent = STAGES.filter(([at]) => minutes >= at - 0.001).pop()[1];
		if (bean) bean.setAttribute("fill", color(p));
		marks.forEach((m) => m.classList.toggle("is-ahead", parseFloat(m.dataset.curveAt) > p + 0.001));
	};
	const state = { p: 0 };
	const tl = gsap.timeline({ defaults: { ease: "none" } });
	tl.fromTo(live, { drawSVG: "0% 0%" }, { drawSVG: "0% 100%", duration: 1 }, 0);
	tl.fromTo(state, { p: 0 }, { p: 1, duration: 1, onUpdate: () => render(state.p) }, 0);
	if (drum) {
		const box = drum.getBBox();
		tl.fromTo(drum, { rotation: 0 }, { rotation: 1080, svgOrigin: `${box.x + box.width / 2} ${box.y + box.height / 2}`, duration: 1 }, 0);
	}
	render(0);
	cleanups.push(() => {
		[time, temp, stage].forEach((t, i) => t && (t.textContent = saved[i]));
		if (dot) {
			dot.setAttribute("cx", savedDot[0]);
			dot.setAttribute("cy", savedDot[1]);
		}
		if (bean) bean.setAttribute("fill", savedFill);
		marks.forEach((m) => m.classList.remove("is-ahead"));
	});
	return tl;
}
function curvesIn(curves, cleanups) {
	curves.forEach((fig) => {
		const tl = curveTimeline(fig, cleanups);
		if (!tl) return;
		ScrollTrigger.create({ trigger: fig, start: "top 85%", end: "bottom 30%", scrub: 0.5, animation: tl });
	});
}

// Steam off the hot beans: wisps travel up their own paths while the scene is pinned
const STEAM = '<svg viewBox="0 0 200 300" preserveAspectRatio="none" focusable="false"><path d="M40 300C20 240 70 210 45 160S60 80 40 20"/><path d="M90 300C110 250 60 200 90 150S70 70 95 10"/><path d="M140 300C120 245 165 205 140 150S160 75 135 15"/><path d="M175 300C190 255 160 215 180 170S165 100 180 50"/></svg>';
function steam(slot, cleanups) {
	const layer = deco(slot, "motion-steam", cleanups, STEAM);
	const wisps = [...layer.querySelectorAll("path")];
	gsap.set(wisps, { drawSVG: "0% 0%" });
	return { layer, wisps };
}

// Pinned scene, desktop only. The photograph is whole and the text is set from the first pixel:
// on the way in the camera eases towards the glass and the lines close up into their places,
// then, pinned, it keeps pushing in while the drum's heat warms the pane, steam rises and a reflection crosses it
function sceneLayer(slot, cleanups, style) {
	const layer = document.createElement("span");
	layer.setAttribute("aria-hidden", "true");
	Object.assign(layer.style, { position: "absolute", pointerEvents: "none", zIndex: "1", ...style });
	slot.append(layer);
	cleanups.push(() => layer.remove());
	return layer;
}
function scenesIn(scenes, cleanups) {
	scenes.forEach((scene) => {
		const img = scene.querySelector("img");
		const slot = img ? img.closest('[class*="-ibg"]') || img.parentElement : null;
		const text = collectLeaves(scene, OWNED);
		const curve = scene.querySelector('[data-motion="curve"]');
		const pinned = gsap.timeline({
			defaults: { ease: "none" },
			scrollTrigger: {
				trigger: scene,
				start: () => (scene.offsetHeight > window.innerHeight * 0.9 ? "top top" : "center center"),
				end: curve ? "+=120%" : "+=70%",
				pin: true,
				pinSpacing: true, // a flex parent would turn it off by default
				scrub: 0.6,
				anticipatePin: 1,
				invalidateOnRefresh: true,
			},
		});
		const approach = gsap.timeline({
			defaults: { ease: "none" },
			scrollTrigger: { trigger: scene, start: "top 85%", end: () => pinned.scrollTrigger.start, scrub: 0.6, invalidateOnRefresh: true },
		});
		if (img && slot) {
			// the scale always exceeds twice the drift, so the photo never uncovers its slot
			approach.fromTo(img, { scale: 1.24, yPercent: 6 }, { scale: 1.14, yPercent: 3, duration: 1 }, 0);
			approach.fromTo(slot, { rotationY: -12, transformPerspective: 1600, transformOrigin: "0% 50%" }, { rotationY: 0, duration: 1 }, 0);
			pinned.fromTo(img, { scale: 1.14, yPercent: 3 }, { scale: 1.02, yPercent: 0, duration: 1 }, 0);
			const heat = sceneLayer(slot, cleanups, {
				inset: "0",
				mixBlendMode: "soft-light",
				background: "radial-gradient(ellipse 70% 80% at 45% 70%, rgba(217, 164, 65, 0.95) 0%, rgba(201, 148, 46, 0.45) 45%, rgba(94, 36, 23, 0) 75%)",
			});
			const sheenLayer = sceneLayer(slot, cleanups, {
				top: "-20%",
				bottom: "-20%",
				left: "0",
				width: "35%",
				background: "linear-gradient(100deg, rgba(255, 248, 235, 0) 0%, rgba(255, 248, 235, 0.16) 45%, rgba(255, 248, 235, 0.26) 50%, rgba(255, 248, 235, 0) 60%)",
			});
			const { layer, wisps } = steam(slot, cleanups);
			pinned.fromTo(heat, { opacity: 0 }, { keyframes: [{ opacity: 1, duration: 0.6 }, { opacity: 0.6, duration: 0.4 }] }, 0);
			pinned.fromTo(sheenLayer, { xPercent: -120 }, { xPercent: 320, duration: 0.6 }, 0.15);
			pinned.fromTo(layer, { yPercent: 20 }, { yPercent: -25, duration: 1 }, 0);
			wisps.forEach((wisp, i) => {
				pinned.fromTo(wisp, { drawSVG: "0% 0%" }, { drawSVG: "35% 100%", duration: 0.7, ease: "power1.inOut" }, 0.05 + i * 0.08);
				pinned.fromTo(wisp, { opacity: 0 }, { keyframes: [{ opacity: 0.85, duration: 0.25 }, { opacity: 0.6, duration: 0.25 }, { opacity: 0, duration: 0.2 }] }, 0.05 + i * 0.08);
			});
		}
		if (curve) {
			const tl = curveTimeline(curve, cleanups);
			if (tl) pinned.add(tl.duration(0.9), 0.05);
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
		if (!host.hasAttribute("data-route")) return;
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
		.fromTo(reveal, { drawSVG: "0% 0%" }, { drawSVG: "0% 100%", duration: 1.1, ease: "power1.inOut" }, 0)
		.to(walker, { motionPath: { path, start: 0, end: 1 }, duration: 1.1, ease: "power1.inOut" }, 0)
		.fromTo(ring, { scale: 0.2, opacity: 0, svgOrigin: `${cx} ${cy}` }, { scale: 1, opacity: 1, duration: 0.5, ease: "back.out(3)" }, 1.05);
}

// The map stands up off the table already drawn, the pins drop onto it, then the walk is traced;
// scrolled back above, the walk runs back to the bus stop and the map lies down again
function mapsIn(maps, ctx, cleanups) {
	maps.forEach((map) => {
		const building = map.querySelectorAll(".map__building");
		const markers = map.querySelectorAll(".map__marker");
		const route = routes.find((r) => r.host === map);
		staged(map, () => {
			gsap.set(map, { rotationX: 50, transformPerspective: 1200, transformOrigin: "50% 100%" });
			gsap.set(building, { rotationY: 35, transformPerspective: 400 });
			gsap.set(markers, { y: -14, rotation: -8 });
			if (route) routeStart(route.svg);
			const tl = gsap.timeline({ defaults: { ease: OUT } })
				.to(map, { rotationX: 0, duration: 1, ease: "power3.out" }, 0)
				.to(building, { rotationY: 0, duration: D.slow * 1.4 }, 0.2)
				.to(markers, { y: 0, rotation: 0, duration: D.slow, ease: "bounce.out", stagger: 0.1 }, 0.2);
			if (route) tl.add(routeTimeline(route.svg), 0.6);
			return { tl, undo: () => gsap.set([map, ...building, ...markers], { clearProps: CLEAR }) };
		}, ctx, cleanups, { start: EARLY, back: 2 });
	});
}
function visitRoutesIn(ctx, cleanups) {
	routes.filter((r) => r.host.classList.contains("visit-route__map") && offscreen(r.host)).forEach(({ host, svg }) => {
		staged(host, () => {
			routeStart(svg);
			gsap.set(host, { rotationX: 40, rotationZ: -2, transformPerspective: 1400, transformOrigin: "50% 100%" });
			const tl = gsap.timeline()
				.to(host, { rotationX: 0, rotationZ: 0, duration: 1.1, ease: "power3.out" })
				.add(routeTimeline(svg), 0.5);
			return { tl, undo: () => gsap.set(host, { clearProps: CLEAR }) };
		}, ctx, cleanups, { start: EARLY, back: 2 });
	});
}

// Footer wall wordmark: letters rise and are drawn from the centre, then filled, then a torch lights
// the paint. Scrolled back above, the paint lifts off again and is redrawn on the way down
function wordmarksIn(marks, ctx, cleanups, fine) {
	marks.forEach((svg) => {
		const letters = [...svg.querySelectorAll("path")];
		if (!letters.length) return;
		let lit = null;
		staged(svg, () => {
			if (lit) gsap.set(lit, { opacity: 0 });
			gsap.set(letters, { attr: { stroke: "currentColor", "stroke-width": 3 }, drawSVG: "15% 85%", y: 40, rotationX: -30, transformOrigin: "50% 100%", fillOpacity: 0.4 });
			const tl = gsap.timeline()
				.to(letters, { drawSVG: "0% 100%", y: 0, rotationX: 0, duration: D.slow * 1.6, ease: OUT, stagger: { each: 0.05, from: "center" } }, 0)
				.to(letters, { fillOpacity: 1, duration: D.slow, ease: "power1.inOut", stagger: { each: 0.04, from: "center" } }, 0.45);
			return {
				tl,
				undo: () => {
					letters.forEach((path) => {
						path.removeAttribute("stroke");
						path.removeAttribute("stroke-width");
					});
					gsap.set(letters, { clearProps: "transform,fillOpacity,strokeDasharray,strokeDashoffset" });
					if (lit) gsap.set(lit, { opacity: 1 });
					else lit = torch(svg, letters, cleanups, fine);
				},
			};
		}, ctx, cleanups, { back: 1.6 });
	});
}
let torchSeq = 0;
function torch(svg, letters, cleanups, fine) {
	const id = `torch-${++torchSeq}`;
	const defs = document.createElementNS(SVG_NS, "defs");
	defs.innerHTML = `<radialGradient id="${id}" gradientUnits="userSpaceOnUse" cx="-400" cy="171" r="300"><stop offset="0" stop-color="#f1e4c8" stop-opacity="0.55"/><stop offset="0.35" stop-color="#d9a441" stop-opacity="0.5"/><stop offset="1" stop-color="#d9a441" stop-opacity="0"/></radialGradient>`;
	const lit = document.createElementNS(SVG_NS, "g");
	lit.setAttribute("fill", `url(#${id})`);
	letters.forEach((path) => lit.append(path.cloneNode()));
	svg.append(defs, lit);
	const grad = defs.firstChild;
	const off = () => {};
	cleanups.push(() => {
		defs.remove();
		lit.remove();
		off();
	});
	// one pass of light across the old paint on arrival, then on a mouse it follows the pointer
	gsap.fromTo(grad, { attr: { cx: -400 } }, { attr: { cx: 1700 }, duration: 2.4, ease: "power2.inOut" });
	if (!fine) return lit;
	const footer = svg.closest("footer") || svg.parentElement;
	const pos = { x: 1700, y: 171, r: 300 };
	const apply = () => {
		grad.setAttribute("cx", pos.x.toFixed(1));
		grad.setAttribute("cy", pos.y.toFixed(1));
		grad.setAttribute("r", pos.r.toFixed(1));
	};
	const toX = gsap.quickTo(pos, "x", { duration: 0.6, ease: "power3", onUpdate: apply });
	const toY = gsap.quickTo(pos, "y", { duration: 0.6, ease: "power3", onUpdate: apply });
	const move = (e) => {
		const m = svg.getScreenCTM();
		if (!m) return;
		const p = new DOMPoint(e.clientX, e.clientY).matrixTransform(m.inverse());
		toX(p.x);
		toY(p.y);
	};
	const leave = () => toX(pos.x < 656 ? -500 : 1800);
	footer.addEventListener("pointermove", move, { passive: true });
	footer.addEventListener("pointerleave", leave);
	cleanups.push(() => {
		footer.removeEventListener("pointermove", move);
		footer.removeEventListener("pointerleave", leave);
	});
	return lit;
}

// A gold pinstripe along the top edge of the window, ruled as the page is read
function progressIn(cleanups) {
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
function sectionsIn(main, wide, cleanups) {
	const sections = [...main.children].filter((s) => s.tagName === "SECTION" && s.offsetHeight > 0);
	let turn = 0;
	const stackable = [];
	sections.forEach((section, i) => {
		if (i === 0 || painted(section) || section.querySelector('[data-motion="scene"], [data-motion="sign"]')) return;
		// a form is quiet on purpose: its fields only rise, the band stays still
		if (section.querySelector("form")) {
			section.dataset.motionChoreo = "quiet";
			return;
		}
		let container = section.querySelector(':scope > [class*="__container"]') || section.firstElementChild;
		// a tall block turned in depth would mislead every trigger inside it, so only its head moves
		if (container && container.offsetHeight > window.innerHeight * 1.3) {
			const head = [...container.children].find((k) => k.offsetHeight > 0 && !k.matches('[data-motion="stripe"]'));
			container = head && head.offsetHeight <= window.innerHeight && !head.matches("[data-motion]") ? head : null;
		}
		if (!container) return;
		let kind = ORDER[turn++ % ORDER.length];
		const st = { trigger: container, start: "top 85%", end: "top 30%", scrub: 0.5 };
		if (kind === "counter") {
			const kids = counterTargets(container);
			if (kids) {
				kids.forEach((kid, k) => {
					const dir = k % 2 ? 1 : -1;
					gsap.fromTo(kid, { x: dir * (wide ? 90 : 28), rotationY: dir * -8, transformPerspective: 1600, transformOrigin: dir < 0 ? "100% 50%" : "0% 50%" }, { x: 0, rotationY: 0, ease: "none", scrollTrigger: st });
				});
				section.dataset.motionChoreo = kind;
				stackable.push(section);
				return;
			}
			kind = "depth";
		}
		const [from, to] = CHOREO[kind](container, wide);
		gsap.fromTo(container, from, { ...to, ease: "none", scrollTrigger: st });
		section.dataset.motionChoreo = kind;
		stackable.push(section);
	});
	cleanups.push(() => sections.forEach((s) => delete s.dataset.motionChoreo));
	if (wide) stacksIn(sections, cleanups);
}

// Stack: a band stays pinned and sinks back while the next band slides over it, twice a page at most
function opaque(el) {
	const bg = getComputedStyle(el).backgroundColor;
	return bg && bg !== "transparent" && !/rgba\(.*,\s*0\)$/.test(bg);
}
function stacksIn(sections, cleanups) {
	let used = 0;
	for (let i = 1; i < sections.length - 1 && used < 2; i++) {
		const s = sections[i], next = sections[i + 1];
		const busy = (el) => el.querySelector('[data-motion="scene"], [data-motion="sign"], [data-motion="map"], form, [data-motion="curve"]');
		// the next band must cover the whole travel, or the band after it would pass under the pinned one
		if (busy(s) || busy(next) || !opaque(next) || s.offsetHeight > window.innerHeight * 1.4 || next.offsetHeight < Math.min(s.offsetHeight, window.innerHeight) + 2) continue;
		if (s.dataset.motionChoreo === "depth") continue;
		used++;
		i++;
		const container = s.querySelector(':scope > [class*="__container"]') || s.firstElementChild;
		gsap.set(s, { position: "relative" });
		const shade = deco(s, "motion-shade", cleanups);
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
	cleanups.push(() => {
		smoother.kill();
		outer.before(content);
		outer.remove();
		fixed.forEach(({ el, parent, next }) => parent.insertBefore(el, next));
	});
	return smoother;
}

let smoother = null;
buildRoutes();

const mm = gsap.matchMedia();
mm.add({ motion: "(prefers-reduced-motion: no-preference)", wide: "(min-width: 62em)", fine: "(pointer: fine)" }, (ctx) => {
	const { motion, wide, fine } = ctx.conditions;
	if (!motion) return undefined;
	const vh = window.innerHeight;
	painted = (el) => {
		const r = el.getBoundingClientRect();
		return r.bottom > 0 && r.top < vh && (r.width > 0 || r.height > 0);
	};
	const cleanups = [];
	let live = true;
	const alive = () => live;

	smoother = wide && fine ? smoothIn(cleanups) : null;

	const owned = wide ? `${OWNED}, [data-motion="scene"]` : OWNED;
	const scopes = [...document.querySelectorAll("[data-motion-scope]")];
	const inScopes = (selector) => scopes.flatMap((scope) => [...scope.querySelectorAll(selector)]);
	const scenes = wide ? inScopes('[data-motion="scene"]').filter(offscreen) : [];
	const insideScene = (el) => scenes.some((scene) => scene.contains(el));

	// pins first, so every trigger below is measured with their spacing in place
	scenesIn(scenes, cleanups);
	const main = document.querySelector("main.page");
	if (main) sectionsIn(main, wide, cleanups);
	inScopes('[data-motion="sign"]').forEach((sign) => signIn(sign, ctx, cleanups, alive, wide));
	wallsIn(inScopes('[data-motion="wall"]').filter(offscreen), ctx, cleanups, alive);
	cardsIn(inScopes('[data-motion="card"]').filter(offscreen));
	platesIn(inScopes('[data-motion="plate"]').filter(offscreen), ctx, cleanups);
	mapsIn(inScopes('[data-motion="map"]').filter(offscreen), ctx, cleanups);
	visitRoutesIn(ctx, cleanups);
	curvesIn(inScopes('[data-motion="curve"]').filter((el) => !insideScene(el) && offscreen(el)), cleanups);
	countsIn([...inScopes('[data-motion="count"]'), ...inScopes(TOTALS)].filter(offscreen), ctx, cleanups);
	stampsIn(inScopes(STAMPS).filter((el) => !el.closest("[data-motion-skip]") && !insideScene(el) && offscreen(el)), ctx, cleanups);
	stripesIn(inScopes('[data-motion="stripe"]').filter((el) => !el.closest(`[data-motion="sign"], [data-motion="card"], [data-motion="wall"], [data-motion-skip]`) && !insideScene(el) && offscreen(el)));
	pourIn(ctx, cleanups, alive);
	mediaIn(inScopes("img").filter((img) => !insideScene(img) && !img.closest("[data-motion-skip], .visit-route__map")), wide);
	leavesIn(scopes.flatMap((scope) => collectLeaves(scope, owned)).filter((el) => !insideScene(el) && !el.matches(TOTALS) && offscreen(el)), ctx, cleanups, wide);
	wordmarksIn(inScopes('[data-motion="wordmark"]').filter(offscreen), ctx, cleanups, fine && wide);
	progressIn(cleanups);

	// measure again with the final fonts and splits
	fontsReady.then(() => {
		if (!alive()) return;
		ScrollTrigger.refresh();
		// a trigger whose start sits exactly on the current scroll has not entered yet: enter it
		ScrollTrigger.getAll().forEach((t) => {
			if (!t.vars.scrub && t.vars.onEnter && !t.isActive && Math.abs(t.start - t.scroll()) < 1) t.vars.onEnter(t);
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
});

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
	if (url.pathname !== location.pathname || url.search !== location.search || url.hash.length < 2) return;
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

// Shop filter, called by script.js on a change: leaving cards turn away, then the rest slide into
// their new places with Flip. Under reduced motion the cards are simply hidden and shown.
let running = null;
let recount = null;
function filterShop(value) {
	const cards = [...document.querySelectorAll("[data-brew]")];
	const grid = cards.length ? cards[0].parentElement : null;
	const count = document.querySelector("[data-shop-count]");
	if (!grid) return;
	if (running) running.progress(1);
	const wanted = (card) => value === "all" || card.dataset.brew.split(" ").includes(value);
	const apply = () => cards.forEach((card) => (card.hidden = !wanted(card)));
	const still = window.matchMedia(REDUCED).matches;
	// the count line follows the filter, its text swapped while it is faded out
	const text = count && count.getAttribute(`data-count-${value}`);
	if (text && text !== count.textContent) {
		if (recount) recount.kill();
		recount = null;
		if (still) {
			count.textContent = text;
			gsap.set(count, { clearProps: "opacity" });
		} else {
			recount = gsap.timeline()
				.to(count, { opacity: 0, duration: D.fast, ease: IN_OUT, onComplete: () => (count.textContent = text) })
				.to(count, { opacity: 1, duration: D.base, ease: OUT, clearProps: "opacity" });
		}
	}
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
}

export { gsap, ScrollTrigger, D as motionDurations, filterShop };
