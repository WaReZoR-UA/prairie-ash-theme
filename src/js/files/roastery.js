/*
Roastery page scenes, loaded as their own chunk and built only under prefers-reduced-motion: no-preference.
The stylesheet holds the finished page; every start state is created here and every end state is the markup.

data-motion="roast-sign"   the painted wall advert: ruled, written and lettered on load over its own ghost; as the
                           page scrolls on it lifts and weathers, and the camera pushes through the window
data-motion="roast-scene"  the drum roaster: the roast drawn by the scroll from charge to drop, pinned on a wide
                           screen; the photograph's heat, steam and bean colour follow the roast's own data
data-motion="roast-curve"  the curve inside the scene, owned by it
data-motion="roast-photo"  a photo uncovered from the edge facing its text, the picture drifting into its drawn crop
data-motion="roast-plate"  date plate: a calendar leaf swings down from its hinge, then the days are torn off to the date
*/

import { gsap, ScrollTrigger } from "./gsap.js";
import { rule, pen } from "./paint.js";
import { letterSign, weathering, endOnResize } from "./lettering.js";
import { clamp01, smooth, pageTop, relative, layer, redrawOnRefresh, onScroll, uncover, sheen, platesIn, freshSign, signLettered } from "./motion-utils.js";

const NS = "http://www.w3.org/2000/svg";
// the warm of the sunlit brick, sampled from both photographs (title.jpg and roaster.jpg agree)
const SUN = "187, 130, 79";
// the photo's own vapour over the trier, sampled from roaster.jpg
const VAPOUR = "167, 151, 125";

// Title ---------------------------------------------------------------------------------------------

function titleScene(sign, { ctx, cleanups, alive, fontsReady }) {
	const later = (fn) => () => alive() && ctx.add(fn);
	const root = document.documentElement;
	const section = sign.closest("section") || sign;
	const board = sign.querySelector(".roastery-title__sign");
	const title = board && board.querySelector("h1");
	const eyebrow = board && board.querySelector("p");
	const text = sign.querySelector(".roastery-title__text");
	const intro = sign.querySelector(".roastery-title__intro");
	const stripes = [...sign.querySelectorAll(":scope > .pinstripe")];
	const media = sign.querySelector(".roastery-title__media");
	const img = media && media.querySelector("img");
	if (!title || !img || !text) {
		delete root.dataset.roast;
		return;
	}
	const phone = window.matchMedia("(max-width: 47.99875em)").matches;
	const fresh = freshSign("roast", section);
	[sign, board, text].forEach((el) => relative(el, cleanups));

	// the lettering weathering back into the brick
	const weather = weathering(title, cleanups, { rules: stripes, text: [eyebrow, intro].filter(Boolean) });
	const signSpan = [40, 400];
	const placeEchoes = () => {
		weather.place();
		signSpan[1] = Math.max(signSpan[0] + 120, (pageTop(board) + board.offsetHeight) * 0.9);
	};
	placeEchoes();
	// the room behind the glass warms as the camera goes in, in the colour of its own sunlit brick
	const lamp = layer(media, "roast-lamp", cleanups);
	lamp.style.background = `radial-gradient(ellipse 34% 46% at 22% 64%, rgba(${SUN}, 0.95), rgba(${SUN}, 0.4) 50%, rgba(${SUN}, 0) 100%)`;

	const state = { p: 0, settle: 1 };
	const advert = [text, ...stripes];
	let st = null;
	const apply = () => {
		const p = state.p;
		// scroll travelled since the top of the page, the trigger runs from 0 to the section leaving
		const scrolled = st ? p * (st.end - st.start) : 0;
		const H = media.offsetHeight;
		// the push is done while the window is still on screen; the room sinks against the scroll, inside its frame
		const e = smooth(0, 0.8, p);
		lamp.style.opacity = (0.8 * smooth(0.08, 0.75, p)).toFixed(3);
		// at the top of the page nothing is left on a layer of its own, so the type renders exactly as drawn
		if (scrolled < 0.5 && state.settle === 1) gsap.set([img, ...advert], { clearProps: "transform" });
		else {
			gsap.set(img, { scale: state.settle * (1 + 0.14 * e), y: 0.05 * e * H, transformOrigin: "22% 62%", force3D: true });
			// the advert is the nearest plane: it lifts faster than the page
			gsap.set(advert, { y: -scrolled * 0.12, force3D: true });
		}
		if (root.dataset.roast !== "live") return;
		weather.apply(smooth(signSpan[0], signSpan[1], scrolled));
	};
	redrawOnRefresh(apply, cleanups);
	cleanups.push(() => {
		gsap.set([img, ...advert], { clearProps: "transform,transformOrigin" });
		[title, eyebrow, intro, ...stripes].forEach((t) => t && (t.style.opacity = ""));
	});
	st = gsap.to(state, {
		p: 1,
		ease: "none",
		onUpdate: apply,
		scrollTrigger: { trigger: section, start: 0, end: "bottom top", scrub: phone ? 0.6 : true, invalidateOnRefresh: true, onRefresh: placeEchoes },
	}).scrollTrigger;

	if (!fresh) {
		signLettered("roast");
		apply();
		return;
	}

	// Act 1, on load: the stripe is ruled, the eyebrow written, the sign lettered over its ghost, the intro
	// written, the second stripe ruled, while the window settles into its frame and daylight crosses the glass
	const faint = [title, eyebrow, intro, ...stripes].filter(Boolean);
	gsap.set(faint, { opacity: 0.3 });
	root.dataset.roast = "letter";
	state.settle = 1.06;
	apply();
	cleanups.push(() => {
		root.dataset.roast = "live";
		gsap.set(faint, { clearProps: "opacity" });
	});

	fontsReady.then(later(() => {
		const act = gsap.timeline();
		const parts = [];
		if (stripes[0]) parts.push(rule(act, stripes[0], 0, { liner: sign, cleanups, duration: phone ? 0.7 : 0.85 }));
		// the pen writes in the line's own faint look, over the faint line
		pen(act, eyebrow, 0.2, cleanups, { pace: 1.2, ink: "line" });
		const letters = letterSign(title, cleanups, { perChar: phone ? 0.064 : 0.058, dip: 0.09 });
		act.add(letters.tl, 0.45);
		parts.push(letters);
		const lettersEnd = 0.45 + letters.tl.duration();
		pen(act, intro, Math.max(1, lettersEnd - 1.6), cleanups, { pace: 2, ink: "line" });
		if (stripes[1]) parts.push(rule(act, stripes[1], Math.max(1.5, lettersEnd - 0.9), { liner: sign, cleanups, duration: phone ? 0.6 : 0.75 }));
		act.to(state, { settle: 1, duration: 1.8, ease: "expo.out", onUpdate: apply }, 0.1);
		sheen(media, cleanups, { tl: act, at: 0.9 });
		act.eventCallback("onComplete", () => {
			signLettered("roast");
			gsap.set([title, ...stripes], { clearProps: "opacity" });
			parts.forEach((part) => part.remove());
			apply();
		});
		endOnResize(act, cleanups);
		cleanups.push(() => act.kill());
	}));
}

// The roast ----------------------------------------------------------------------------------------

const BEAN = ["#7d8b4f", "#a7a35a", "#c9a14a", "#a8662f", "#7a4122", "#4e2a17"];
// the minute each stage of the roast begins; the names from Site settings (inc/enqueue.php), one line per stage, the
// design's own while the field does not hold all eight
const STAGE_AT = [0, 0.6, 2, 5, 7, 9.17, 10, 11.6];
const STAGE_NAMES = ["Charge", "Turning point", "Drying", "Yellowing", "Browning", "First crack", "Development", "Drop"];
const stageNames = String((window.prairieAsh || {}).roastStages || "").split(/\r?\n/).map((name) => name.trim()).filter(Boolean);
const STAGES = STAGE_AT.map((at, i) => [at, stageNames.length === STAGE_AT.length ? stageNames[i] : STAGE_NAMES[i]]);
const CRACK = 9.17;
let clipSeq = 0;

// The chart and its readout, driven by one roast value: the minute, the temperature, the stage, the bean's
// colour and swell, the crease opening at first crack, the drum turning, the marks lit as the roast passes them
function roastCurve(fig, cleanups) {
	const svg = fig.querySelector("svg");
	const live = fig.querySelector(".roast-curve__live");
	if (!svg || !live) return null;
	const q = (s) => fig.querySelector(s);
	const dot = q(".roast-curve__dot");
	const drum = q(".roast-curve__drum");
	const bean = q(".roast-curve__bean");
	const crease = q(".roast-curve__crease");
	const time = q(".roast-curve__time");
	const temp = q(".roast-curve__temp");
	const stage = q(".roast-curve__stage");
	const ghostLine = q(".roast-curve__ghost");
	const marks = [...fig.querySelectorAll("[data-curve-at]")];
	const saved = [time, temp, stage].map((t) => t && t.textContent);
	const savedDot = dot && [dot.getAttribute("cx"), dot.getAttribute("cy")];
	const savedBean = bean && [bean.getAttribute("fill"), bean.getAttribute("transform")];
	const length = live.getTotalLength();
	const end = live.getPointAtLength(length);
	const color = gsap.utils.interpolate(BEAN);
	const pad = (n) => String(n).padStart(2, "0");
	const drumBox = drum && drum.getBBox();
	const creaseLen = crease ? crease.getTotalLength() : 0;

	// the line's own heat: a soft copy of it under the gold, lit by the temperature
	const id = `roast${++clipSeq}`;
	const defs = document.createElementNS(NS, "defs");
	defs.innerHTML = `<filter id="${id}b" x="-10%" y="-30%" width="120%" height="160%"><feGaussianBlur stdDeviation="5"/></filter><clipPath id="${id}c"><rect x="0" y="0" width="0" height="290"/></clipPath>`;
	svg.prepend(defs);
	const glow = live.cloneNode();
	glow.setAttribute("class", "roast-curve__glow");
	glow.setAttribute("filter", `url(#${id}b)`);
	live.before(glow);
	const wipe = defs.querySelector("rect");
	if (ghostLine) ghostLine.setAttribute("clip-path", `url(#${id}c)`);
	cleanups.push(() => {
		defs.remove();
		glow.remove();
		if (ghostLine) ghostLine.removeAttribute("clip-path");
	});

	const data = { m: 0, c: 200 };
	const render = (p) => {
		const pt = live.getPointAtLength(p * length);
		const minutes = Math.max(0, ((pt.x - 48) / (end.x - 48)) * 11.67);
		const celsius = Math.round(80 + (250 - pt.y) / 1.4667);
		data.m = minutes;
		data.c = celsius;
		if (dot) {
			dot.setAttribute("cx", pt.x.toFixed(1));
			dot.setAttribute("cy", pt.y.toFixed(1));
		}
		if (time) time.textContent = `${Math.floor(minutes)}:${pad(Math.min(59, Math.round((minutes % 1) * 60)))}`;
		if (temp) temp.textContent = `${celsius}°C`;
		if (stage) stage.textContent = STAGES.filter(([at]) => minutes >= at - 0.001).pop()[1];
		if (bean) {
			bean.setAttribute("fill", color(p));
			// a bean swells by about a sixth while it roasts, and lands on its drawn size at the drop
			const s = (0.84 + 0.16 * p).toFixed(3);
			bean.setAttribute("transform", `translate(404 44) rotate(24) scale(${s}) translate(-404 -44)`);
		}
		if (crease) {
			const k = smooth(CRACK - 0.2, CRACK + 0.9, minutes);
			crease.style.strokeDasharray = `${creaseLen} ${creaseLen}`;
			crease.style.strokeDashoffset = (creaseLen * (1 - k)).toFixed(2);
		}
		// the drum turns with the clock, three whole turns over the batch, so it ends as drawn
		if (drum && drumBox) {
			if (p >= 1) drum.removeAttribute("transform");
			else drum.setAttribute("transform", `rotate(${(p * 1080).toFixed(1)} ${drumBox.x + drumBox.width / 2} ${drumBox.y + drumBox.height / 2})`);
		}
		marks.forEach((m) => m.classList.toggle("is-ahead", parseFloat(m.dataset.curveAt) > p + 0.001));
		gsap.set([live, glow], { drawSVG: `0% ${(p * 100).toFixed(2)}%` });
		return data;
	};
	cleanups.push(() => {
		[time, temp, stage].forEach((t, i) => t && (t.textContent = saved[i]));
		if (dot) {
			dot.setAttribute("cx", savedDot[0]);
			dot.setAttribute("cy", savedDot[1]);
		}
		if (bean) {
			bean.setAttribute("fill", savedBean[0]);
			bean.setAttribute("transform", savedBean[1]);
		}
		if (crease) crease.style.strokeDasharray = crease.style.strokeDashoffset = "";
		if (drum) drum.removeAttribute("transform");
		marks.forEach((m) => m.classList.remove("is-ahead"));
		gsap.set(live, { clearProps: "strokeDasharray,strokeDashoffset" });
	});

	// before the charge: the grid and the axis are ruled, the scale is lettered, last Tuesday's roast is laid down
	const prep = (tl, at, span) => {
		const grid = q(".roast-curve__grid path");
		const axis = q(".roast-curve__axis");
		const ticks = [...fig.querySelectorAll(".roast-curve__ticks text")];
		if (axis) tl.fromTo(axis, { drawSVG: "0% 0%" }, { drawSVG: "0% 100%", duration: span * 0.45, ease: "power2.inOut" }, at);
		if (grid) tl.fromTo(grid, { drawSVG: "0% 0%" }, { drawSVG: "0% 100%", duration: span * 0.6, ease: "power1.inOut" }, at + span * 0.1);
		tl.fromTo(ticks, { opacity: 0 }, { opacity: 1, duration: span * 0.3, stagger: span * 0.04, ease: "power1.out" }, at + span * 0.2);
		tl.fromTo(wipe, { attr: { width: 48 } }, { attr: { width: 640 }, duration: span * 0.55, ease: "power1.inOut" }, at + span * 0.45);
		cleanups.push(() => gsap.set([axis, grid, ...ticks].filter(Boolean), { clearProps: "opacity,strokeDasharray,strokeDashoffset" }));
	};
	return { render, prep, data, glow };
}

// Light on the photograph, from the roast's own numbers: the drum's mouth glows with the temperature, the
// beans in the trier go from green to the photo's own brown, the vapour rises once the beans give off water
const WISPS = '<svg viewBox="0 0 100 200" preserveAspectRatio="none" focusable="false"><path d="M30 200C14 160 46 140 28 104S40 50 26 6"/><path d="M52 200C66 166 36 136 54 100S40 48 58 4"/><path d="M72 200C60 168 86 140 70 104S82 56 68 12"/><path d="M44 200C54 172 30 150 44 120S34 70 46 30"/></svg>';
function roomLight(slot, cleanups) {
	const heat = layer(slot, "roast-room roast-room--heat", cleanups);
	heat.style.background = `radial-gradient(closest-side, rgba(${SUN}, 0.55), rgba(${SUN}, 0.2) 55%, rgba(${SUN}, 0) 100%)`;
	const tint = layer(slot, "roast-room roast-room--tint", cleanups);
	tint.style.background = `radial-gradient(closest-side, ${BEAN[0]}, rgba(125, 139, 79, 0.6) 60%, rgba(125, 139, 79, 0) 100%)`;
	const steam = layer(slot, "roast-room roast-room--steam", cleanups, { html: WISPS });
	steam.style.setProperty("--vapour", `rgba(${VAPOUR}, 0.7)`);
	const wisps = [...steam.querySelectorAll("path")];
	return (data, cool) => {
		const keep = 1 - cool;
		const hot = clamp01((data.c - 90) / 125);
		heat.style.opacity = (keep * (0.25 + 0.75 * hot)).toFixed(3);
		tint.style.opacity = (keep * 0.6 * (1 - smooth(0, 9, data.m))).toFixed(3);
		const vapour = keep * smooth(2, 9.5, data.m);
		steam.style.opacity = vapour.toFixed(3);
		// each wisp is a stretch of its path that rises with the clock, so the scroll lifts the vapour
		wisps.forEach((w, i) => {
			const ph = (data.m * 0.55 + i * 0.27) % 1;
			const head = ph * 130;
			gsap.set(w, { drawSVG: `${Math.max(0, head - 45).toFixed(1)}% ${Math.min(100, head).toFixed(1)}%` });
		});
	};
}

function sceneIn(scene, wide, cleanups) {
	const fig = scene.querySelector('[data-motion="roast-curve"]');
	const img = scene.querySelector(".roastery-roaster__media img");
	const slot = img && img.closest('[class*="-ibg"]');
	const chart = fig && fig.querySelector("svg");
	const curve = fig && roastCurve(fig, cleanups);
	if (!curve || !slot) return;
	const light = roomLight(slot, cleanups);
	const roast = { p: 0, cool: 0 };
	// the chart lying on the bench and standing up; written by hand so that flat means no transform at all,
	// and the scale is lettered exactly as drawn
	const bench = { r: 0 };
	const tilt = () => {
		chart.style.transformOrigin = "50% 100%";
		chart.style.transform = bench.r > 0.01 ? `perspective(${wide ? 900 : 700}px) rotateX(${bench.r.toFixed(2)}deg)` : "";
	};
	redrawOnRefresh(tilt, cleanups);
	const draw = () => {
		const data = curve.render(roast.p);
		light(data, roast.cool);
		const lit = (1 - roast.cool) * 0.55 * clamp01((data.c - 90) / 125);
		curve.glow.style.opacity = lit.toFixed(3);
		// a cold line is not drawn at all, so the finished chart is exactly the markup
		curve.glow.style.display = lit < 0.001 ? "none" : "";
	};
	redrawOnRefresh(draw, cleanups);
	cleanups.push(() => {
		gsap.set([img, slot], { clearProps: "transform,transformOrigin,clipPath" });
		chart.style.transform = chart.style.transformOrigin = "";
	});

	if (wide) {
		const text = [...scene.querySelectorAll(".roastery-roaster__text > *:not(figure)")];
		const pinned = gsap.timeline({
			defaults: { ease: "none" },
			scrollTrigger: {
				trigger: scene,
				start: () => (scene.offsetHeight > window.innerHeight * 0.9 ? "top top" : "center center"),
				end: "+=140%",
				pin: true,
				pinSpacing: true,
				scrub: 0.6,
				anticipatePin: 1,
				invalidateOnRefresh: true,
				refreshPriority: 1,
			},
		});
		const approach = gsap.timeline({
			defaults: { ease: "none" },
			scrollTrigger: { trigger: scene, start: "top bottom", end: () => pinned.scrollTrigger.start, scrub: 0.6, invalidateOnRefresh: true, refreshPriority: 1 },
		});
		// the glass wall swings open towards the viewer while the camera eases towards the drum
		approach.fromTo(slot, { rotationY: -12, transformPerspective: 1600, transformOrigin: "0% 50%" }, { rotationY: 0, duration: 1 }, 0);
		approach.fromTo(img, { scale: 1.16, yPercent: 4, transformOrigin: "70% 56%" }, { scale: 1.08, yPercent: 2, duration: 1 }, 0);
		// lines close up from a slightly open spacing, opaque throughout
		approach.fromTo(text, { y: (i) => 14 + i * 10 }, { y: 0, duration: 0.8, ease: "power1.out" }, 0.2);
		// the chart is lying on the bench and stands up to be read, then is ruled
		approach.fromTo(bench, { r: 32 }, { r: 0, duration: 0.5, ease: "power2.out", onUpdate: tilt }, 0.35);
		curve.prep(approach, 0.45, 0.55);
		// pinned: the roast runs from charge to drop as the page is scrolled, then the batch cools on the tray
		// while the camera settles on the photograph's drawn crop
		pinned.fromTo(roast, { p: 0 }, { p: 1, duration: 0.86, onUpdate: draw }, 0);
		pinned.fromTo(roast, { cool: 0 }, { cool: 1, duration: 0.12, onUpdate: draw }, 0.88);
		pinned.fromTo(img, { scale: 1.08, yPercent: 2 }, { scale: 1, yPercent: 0, duration: 1 }, 0);
		draw();
		return;
	}
	// one column: the photo is uncovered from the top, the roast is drawn while the chart crosses the eye band
	uncover(slot, img, { shut: "inset(0% 0% 100% 0%)", from: { scale: 1.12, transformOrigin: "70% 56%" } });
	const tl = gsap.timeline({ defaults: { ease: "none" } });
	tl.fromTo(bench, { r: 28 }, { r: 0, duration: 0.14, ease: "power2.out", onUpdate: tilt }, 0);
	curve.prep(tl, 0.02, 0.18);
	tl.fromTo(roast, { p: 0 }, { p: 1, duration: 0.68, onUpdate: draw }, 0.2);
	tl.fromTo(roast, { cool: 0 }, { cool: 1, duration: 0.1, onUpdate: draw }, 0.9);
	draw();
	onScroll(chart, tl, { start: "top 85%", end: "top 25%", scrub: 0.6 });
}

// Photos -------------------------------------------------------------------------

function photosIn(pictures, cleanups) {
	pictures.forEach((picture) => {
		const img = picture.querySelector("img");
		const slot = picture.closest('[class*="-ibg"]');
		if (!img || !slot) return;
		// the picture drifts down into its frame while the frame opens, and lands on the drawn crop
		uncover(slot, img, { from: { scale: 1.12, yPercent: -5 }, to: { scale: 1, yPercent: 0 } });
		cleanups.push(() => gsap.set([slot, img], { clearProps: "clipPath,transform" }));
	});
}


export function roasteryMotion({ ctx, cleanups, alive, fontsReady, wide }) {
	const sign = document.querySelector('[data-motion="roast-sign"]');
	if (sign) titleScene(sign, { ctx, cleanups, alive, fontsReady });
	document.querySelectorAll('[data-motion="roast-scene"]').forEach((scene) => sceneIn(scene, wide, cleanups));
	photosIn([...document.querySelectorAll('[data-motion="roast-photo"]')], cleanups);
	platesIn([...document.querySelectorAll('[data-motion="roast-plate"]')], cleanups);
	// the pin arrived after the page's other triggers: put it first and measure everything again
	ScrollTrigger.sort();
	ScrollTrigger.refresh();
}
