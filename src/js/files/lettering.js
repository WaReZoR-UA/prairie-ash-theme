/*
Sign lettering, shared by every painted title: the Home hero, Roastery, Shop and Product letter stroke by stroke,
the other pages' title signs line by line.

The brush letters the REAL heading: it stays in the DOM, in its own place and its own raster, and is uncovered
stroke by stroke through a clip path cut from its own measured glyph boxes. A clip path keeps the type on the
same text raster as the finished page, so when the clip is let go nothing on the screen changes: nothing is
swapped and no glyph moves. Around it, all aria-hidden and gone at the end: a faint ghost of the sign under the
paint, the wet sheen on fresh strokes, the bead of paint at the brush tip.

weathering(title, cleanups, { rules, text, exact }) returns { echoes, place, apply }, the sign weathering back into the
wall as the page scrolls on: the same lettering laid over the heading twice, worn and then a ghost, under a flaking mask.
	exact     the copies are never narrower than the heading's own fractional width (false)
	place()   lays the echoes over the heading again, after a reflow
	apply(w)  w from 0, freshly painted, to 1, a ghost: the heading, its rules and its text fade as the echoes come through

endOnResize(act, cleanups): the lettering is measured on the layout it starts on, so a new width ends the act at once
rather than letting it drift.

letterSign(title, cleanups, options) returns { tl, remove }:
	tl        a timeline for the caller to place; it ends with the heading untouched
	remove()  takes every layer off and hands the heading back (call it once the page has left its lettering state)
options:
	mode      "strokes" | "lines": letter by letter, or one pull of the brush along each line ("strokes")
strokes:
	perChar   seconds per letter (0.06)          dip     pause while the brush is reloaded between words (0.09)
	wet       "lighter" | "darker" | a colour: the wet coat and the bead, against the heading's own ink ("lighter")
	ghost     opacity of the old sign under the paint, 0 for none (0.3)
	order     "reading" | "reverse": the order the words are lettered in ("reading")
	stroke    "down" | "up": the way each stroke is pulled ("down")
	flat      the sign is measured and its layers placed square, while a plate it hangs on is still turned (false)
lines:
	pass      seconds the brush takes along a line (0.714)    gap     seconds between the starts of two lines (0.24)
	ease      ease of the pull ("brush")                      rise    px a line rises into place (10)
	settle    seconds a line takes to rise (0.6)              fade    the line comes up from nothing as it rises (true)
	paint     the brush loaded with the heading's own colour (false)
	split     the lines are split in place under their own masks and stay split, so the scroll can play them back
	          (the section headings and the wall advert of the scroll pages, approved on this way of lettering)

splitLines(target, cleanups) splits a block into masked lines, reverted on cleanup.

signScene(region, scene, options): a page's title sign, lettered on load and carried by the scroll. On load the first
rule is ruled, the line above written and the name lettered stroke by stroke over its ghost, then what follows the name;
as the page scrolls on, the sign lifts as the nearest plane, weathers back into a ghost and the camera moves in on the
photo. scene: { ctx, cleanups, alive, fontsReady, wide } of the page module. options:
	flag      the head flag on <html>, data-<flag>: "pre" from the head, "letter" while lettered, "live" after
	title, eyebrow, media, img   the name, the line above it, the photo's slot and its picture; a sign with no photo
	                             leaves media and img out and has no camera
	plane     the block that lifts as the nearest plane         liner   where the fresh rules are drawn (region)
	stripes   the sign's rules, the first ruled before the name ([])
	held      blocks made the containing block of the layers laid over them ([])
	focus     where in the photo the camera moves in             seen    the camera moves in only once the photo has been
	          seen whole, rather than from the top of the page (false)
	weather   the sign weathers back into a ghost (true); a sign on paper keeps its ink
	ghost     () => the scroll at which the sign is a ghost (the middle of the name at the top of the window)
	letters   letterSign options for the name, perChar as [wide, narrow] ({ perChar: [0.058, 0.064], dip: 0.09 })
	exact     the pen writes on the lines' own fractional width, the weathered copies on the heading's (false)
	then      what follows the name, in order, each at max(min, end of the name - lead): ["rule", i, min, lead],
	          ["write", el, min, lead], ["sheen", min, lead]
	story     { written, slid }: lines that are told by the scroll instead when they are below the window at the start,
	          written by the pen, or a control slid in from its edge (it never shows faint)
*/

import { gsap, ScrollTrigger } from "./gsap.js";
import { SplitText } from "gsap/SplitText";
import { rule, pen } from "./paint.js";
import { clamp01, smooth, pageTop, inView, relative, redrawOnRefresh, onScroll, sheen, freshSign, signLettered, riseIn } from "./motion-utils.js";

const NS = "http://www.w3.org/2000/svg";
const ROUND = "ocebdpqgsaCGOQSD";
const WIDE = "mwMW";
const NARROW = "iljtfrI1.,:;'!";
const inOut2 = gsap.parseEase("power2.inOut");
const inOut1 = gsap.parseEase("power1.inOut");
const in1 = gsap.parseEase("power1.in");
const f = (v) => v.toFixed(2);

// Words of the heading with every character's box, read off the heading's own text so kerning is the page's own
function measure(title) {
	const base = title.getBoundingClientRect();
	const words = [];
	const range = document.createRange();
	const walker = document.createTreeWalker(title, NodeFilter.SHOW_TEXT);
	for (let node = walker.nextNode(); node; node = walker.nextNode()) {
		let word = null;
		for (let i = 0; i < node.data.length; i++) {
			const ch = node.data[i];
			if (/\s/.test(ch)) {
				word = null;
				continue;
			}
			range.setStart(node, i);
			range.setEnd(node, i + 1);
			const r = range.getClientRects()[0];
			if (!r) continue;
			const box = { ch, x: r.left - base.left, y: r.top - base.top, w: r.width, h: r.height, el: node.parentElement };
			if (!word || Math.abs(box.y - word.chars[0].y) > 2) {
				word = { chars: [] };
				words.push(word);
			}
			word.chars.push(box);
		}
	}
	return words;
}

// The strokes a sign painter pulls for a letter: a centre x at the top, a control x for a bowl, a width
function strokesFor(ch, b) {
	const x = b.x - b.w * 0.06;
	const w = b.w * 1.12;
	const line = (cx, bw) => ({ ax: x + w * cx, qx: x + w * cx, bw });
	if (NARROW.includes(ch)) return [line(0.5, w * 1.05)];
	if (WIDE.includes(ch)) return [line(0.18, w * 0.43), line(0.5, w * 0.43), line(0.82, w * 0.43)];
	// two bowls pulled down, the way a round letter is painted
	if (ROUND.includes(ch)) return [{ ax: x + w * 0.42, qx: x + w * -0.05, bw: w * 0.72 }, { ax: x + w * 0.58, qx: x + w * 1.05, bw: w * 0.72 }];
	return [line(0.27, w * 0.62), line(0.73, w * 0.62)];
}

// The coat is thinner than the finished paint and streaked by the bristles. A clip has no half measures, so the
// coat is cut into vertical slits finer than a pixel: their share of each streak is the paint the streak lacks,
// and as the paint levels out they close. One slit per device pixel column, on the pixel grid, so every column
// gets the same share and nothing beats against the grid; a coarser pitch shows as a regular hatch. Streaks as
// the old pattern drew them, 1.2 to 3 px wide, thinner as the brush runs dry.
function bristles(k, load) {
	const pitch = 1 / (window.devicePixelRatio || 1);
	const xs = [0, 0.5, 1].map((t) => pointAt(k, t, 1)[0]);
	const lo = Math.min(...xs) - k.bw;
	const hi = Math.max(...xs) + k.bw;
	const cols = [];
	let coat = 1;
	let streakEnd = -Infinity;
	for (let x = Math.ceil(lo / pitch) * pitch; x < hi; x += pitch) {
		if (x >= streakEnd) {
			streakEnd = x + gsap.utils.random(1.2, 3);
			coat = (1 - 0.58 * (1 - gsap.utils.random(0.15, 0.58))) * (0.45 + 0.55 * load);
		}
		cols.push({ x, w: pitch * (1 - coat) });
	}
	return cols;
}

// Where a stroke stands at travel t: a quadratic from its top to its bottom, bowed by qx for a round letter
function pointAt(k, t, dir) {
	const u = dir > 0 ? t : 1 - t;
	return [(1 - u) * (1 - u) * k.ax + 2 * (1 - u) * u * k.qx + u * u * k.ax, k.top + (k.bot - k.top) * u];
}
// The centre line of a stroke pulled to progress p, sampled the same way for the stroke and for its slits
function spine(k, p, dir) {
	const n = k.ax === k.qx ? 1 : 16;
	const pts = [];
	for (let i = 0; i < n && i / n < p; i++) pts.push(pointAt(k, i / n, dir));
	pts.push(pointAt(k, p, dir));
	return pts;
}
// A closed outline wound one way (+1) or the other (-1): strokes add up, a slit cancels its own stroke only
function outline(pts, wind) {
	let area = 0;
	for (let i = 0; i < pts.length; i++) {
		const [x0, y0] = pts[i];
		const [x1, y1] = pts[(i + 1) % pts.length];
		area += x0 * y1 - x1 * y0;
	}
	if (Math.sign(area) !== wind) pts.reverse();
	return `M${pts.map(([x, y]) => `${f(x)} ${f(y)}`).join("L")}Z`;
}
// A stroke along its spine, half width hw, with round ends of radius cap
function band(mid, hw, cap, dir) {
	const arc = ([cx, cy], a0, a1) => {
		const out = [];
		for (let i = 1; i < 6; i++) {
			const a = a0 + ((a1 - a0) * i) / 6;
			out.push([cx + cap * Math.cos(a), cy + cap * Math.sin(a)]);
		}
		return out;
	};
	// the front end faces the way the brush travels, the back end the way it came from
	const n = mid.length - 1;
	const front = dir > 0 ? arc(mid[n], Math.PI, 0) : arc(mid[n], Math.PI, 2 * Math.PI);
	const back = dir > 0 ? arc(mid[0], 0, -Math.PI) : arc(mid[0], 0, Math.PI);
	const left = mid.map(([x, y]) => [x - hw, y]);
	const right = mid.map(([x, y]) => [x + hw, y]).reverse();
	return outline([...left, ...front, ...right, ...back], 1);
}
// The slits of a stroke, each kept inside the stroke's own edges at every sample, so it never cuts or paints
// anything outside it
function slits(mid, hw, cols, open) {
	let out = "";
	cols.forEach(({ x, w }) => {
		const x1 = x + w * open;
		let run = [];
		const flush = () => {
			if (run.length > 2 || (run.length === 2 && run[0][2] !== run[1][2])) {
				// a straight run needs only its two ends
				const pts = run.filter((q, i) => i === 0 || i === run.length - 1 || q[0] !== x || q[1] !== x1 || run[i - 1][0] !== x || run[i - 1][1] !== x1 || run[i + 1][0] !== x || run[i + 1][1] !== x1);
				out += outline([...pts.map(([l, , y]) => [l, y]), ...pts.map(([, r, y]) => [r, y]).reverse()], -1);
			}
			run = [];
		};
		// a run starts and ends where the slit meets the stroke's edge, interpolated between two samples
		const span = ([cx, y]) => [Math.max(x, cx - hw), Math.min(x1, cx + hw), y];
		const lerp = (a, b, t) => a.map((v, i) => v + (b[i] - v) * t);
		mid.forEach((q, i) => {
			const cur = span(q);
			const inside = cur[1] - cur[0] > 0;
			if (i) {
				const prev = span(mid[i - 1]);
				const was = prev[1] - prev[0] > 0;
				if (inside !== was) {
					const t = (prev[1] - prev[0]) / (prev[1] - prev[0] - (cur[1] - cur[0]));
					const edge = lerp(prev, cur, t);
					edge[1] = edge[0];
					run.push(edge);
					if (!inside) flush();
				}
			}
			if (inside) run.push(cur);
		});
		flush();
	});
	return out;
}
const rect = (x0, y0, x1, y1) => `M${f(x0)} ${f(y0)}H${f(x1)}V${f(y1)}H${f(x0)}Z`;

// A copy of the heading for a layer around it: same classes, text and wrapping, so it lays out glyph for glyph.
// Never a heading itself, so the page keeps one h1 while it is lettered.
function copyOf(title, className) {
	const n = document.createElement("div");
	n.className = `${title.className} hero__echo ${className}`;
	n.innerHTML = title.innerHTML;
	n.querySelectorAll("[id]").forEach((c) => c.removeAttribute("id"));
	n.setAttribute("aria-hidden", "true");
	n.style.textWrap = getComputedStyle(title).textWrap;
	return n;
}
function place(title, layer) {
	const op = title.offsetParent || document.body;
	const a = title.getBoundingClientRect();
	const b = op.getBoundingClientRect();
	Object.assign(layer.style, { left: `${a.left - b.left - op.clientLeft}px`, top: `${a.top - b.top - op.clientTop}px`, width: `${a.width}px` });
}

// The brush along a line of type: it travels the length of the ink, tilting as a hand pulls it, and is lifted at the end
function brushPass(tl, parent, at, { left, top, height, ink, pass, ease, paint, style = {} }) {
	const brush = document.createElement("span");
	brush.className = paint ? "motion-brush motion-brush--paint" : "motion-brush";
	brush.setAttribute("aria-hidden", "true");
	Object.assign(brush.style, { left: `${left}px`, top: `${top}px`, height: `${height}px`, ...style });
	parent.append(brush);
	tl.fromTo(brush, { x: 0, rotation: -14, opacity: 0 }, { x: ink, rotation: 10, duration: pass, ease }, at)
		.to(brush, { opacity: 1, duration: 0.06 }, at)
		.to(brush, { opacity: 0, duration: 0.2, ease: "power1.in" }, at + pass - 0.12);
	return brush;
}
// A lettered line rises into place under the brush, coming up from nothing as the paint takes
function riseLine(tl, el, at, { rise, settle, fade, pass }) {
	if (rise) tl.fromTo(el, { y: rise, opacity: fade ? 0 : 1 }, { y: 0, duration: settle, ease: "expo.out" }, at);
	else gsap.set(el, { opacity: fade ? 0 : 1 });
	if (fade) tl.to(el, { keyframes: [{ opacity: 0.55, duration: pass * 0.35 }, { opacity: 1, duration: settle * 0.6 }], ease: "none" }, at);
}
// Reads taken flat: an ancestor that is scaled or turned right now (a hung plate, a section's own entrance) is set
// square for the read and handed straight back, so the layers are measured in the heading's own pixels
function flat(el, read) {
	const held = [];
	for (let n = el.parentElement; n && n !== document.body; n = n.parentElement) {
		const t = getComputedStyle(n).transform;
		if (t === "none" || /^matrix\(1, 0, 0, 1, /.test(t)) continue;
		held.push([n, n.style.transform]);
		n.style.transform = "none";
	}
	try {
		return read();
	} finally {
		held.forEach(([n, t]) => (n.style.transform = t));
	}
}

// Line by line, the way a section sign is lettered: one pull of the brush along each line uncovers it while the line
// rises into place. A line rises on a copy laid over it and is handed to the heading once it has settled, which is
// the moment the copy and the heading draw the same pixels, so nothing changes on the screen at any hand-over.
function letterLines(title, cleanups, { pass = 0.714, gap = 0.24, ease = "brush", rise = 10, settle = 0.6, fade = true, paint = false }) {
	const host = title.parentElement;
	const style = getComputedStyle(title);
	const easeFn = gsap.parseEase(ease) || inOut2;
	// the visual lines, each from the content edge of the block it is set in to the end of its ink
	const lines = [];
	const { base, ox, oy } = flat(title, () => {
		const base = title.getBoundingClientRect();
		const op = title.offsetParent || document.body;
		const ob = op.getBoundingClientRect();
		measure(title).forEach((word) => {
			const c0 = word.chars[0];
			let line = lines.find((l) => Math.abs(l.y - c0.y) <= 2);
			if (!line) {
				let block = c0.el;
				while (block !== title && /^inline|^contents/.test(getComputedStyle(block).display)) block = block.parentElement;
				const r = block.getBoundingClientRect();
				const bs = getComputedStyle(block);
				const lh = parseFloat(bs.lineHeight) || c0.h;
				const mid = c0.y + c0.h / 2;
				line = { y: c0.y, chars: [], left: r.left - base.left + block.clientLeft + parseFloat(bs.paddingLeft), top: mid - lh / 2, bottom: mid + lh / 2, lh };
				lines.push(line);
			}
			line.chars.push(...word.chars);
		});
		return { base, ox: base.left - ob.left - op.clientLeft, oy: base.top - ob.top - op.clientTop };
	});
	lines.sort((a, b) => a.y - b.y);
	// each line owns a band of the heading, split halfway between line boxes, so no pixel is painted twice
	const wide = base.width + 100;
	lines.forEach((line, i) => {
		line.at = i * gap;
		line.ink = Math.max(8, Math.max(...line.chars.map((c) => c.x + c.w)) - line.left + 6);
		line.bandTop = i ? (lines[i - 1].bottom + line.top) / 2 : line.top - line.lh;
		line.bandBottom = i < lines.length - 1 ? (line.bottom + lines[i + 1].top) / 2 : line.bottom + line.lh;
		line.done = line.at + pass;
	});
	// the brush is lifted off the last line before the heading is let go
	const total = lines.length ? Math.max(...lines.map((l) => l.at + Math.max(pass + 0.08, rise ? settle : 0, fade ? pass * 0.35 + settle * 0.6 : 0))) + 0.05 : 0;

	const tl = gsap.timeline();
	const layers = [];
	lines.forEach((line) => {
		const at = line.at;
		const copy = copyOf(title, "lettering__line");
		host.append(copy);
		Object.assign(copy.style, { left: `${ox}px`, top: `${oy}px`, width: `${base.width}px` });
		line.copy = copy;
		layers.push(copy);
		riseLine(tl, copy, at, { rise, settle, fade, pass });
		layers.push(brushPass(tl, host, at, { left: ox + line.left, top: oy + line.top + line.lh * 0.14, height: line.lh * 0.72, ink: line.ink, pass, ease, paint, style: { fontSize: style.fontSize, color: style.color } }));
	});
	title.dataset.lettering = "on";
	gsap.set(title, { opacity: 1 });

	let live = true;
	const render = (t) => {
		if (!live) return;
		const over = t >= total;
		layers.forEach((l) => (l.style.visibility = over ? "hidden" : ""));
		if (over) {
			title.style.clipPath = "";
			return;
		}
		let clip = "";
		lines.forEach((line) => {
			const band = (x1) => rect(-wide, line.bandTop, x1, line.bandBottom);
			if (t >= line.done) {
				clip += band(wide);
				line.copy.style.visibility = "hidden";
				return;
			}
			line.copy.style.clipPath = `path("${band(line.left + line.ink * easeFn(clamp01((t - line.at) / pass)))}")`;
		});
		title.style.clipPath = `path("${clip || "M0 0Z"}")`;
	};
	const clock = { t: 0 };
	tl.to(clock, { t: total, duration: total, ease: "none", onUpdate: () => render(clock.t) }, 0);
	render(0);

	const remove = () => {
		if (!live) return;
		live = false;
		layers.forEach((l) => l.remove());
		title.style.clipPath = "";
		delete title.dataset.lettering;
	};
	cleanups.push(remove);
	return { tl, remove };
}

export function splitLines(target, cleanups) {
	const split = SplitText.create(target, { type: "lines", mask: "lines", aria: "none", tag: "span" });
	split.masks.forEach((mask) => {
		mask.style.overflowX = "clip";
		mask.style.overflowY = "visible";
	});
	cleanups.push(() => split.revert());
	return { split, masks: split.masks, lines: split.lines };
}

// Line by line in place: each line is split out under its own mask and uncovered by its own clip as the brush passes,
// and stays split, so the scroll can play it back. Measured in the heading's own pixels: a section entrance may be
// scaling or tilting it right now.
function letterSplit(el, cleanups, { pass = 0.714, gap = 0.24, ease = "brush", rise = 10, settle = 0.6, fade = true, paint = false }) {
	const kids = [...el.children];
	const parts = kids.length && kids.every((kid) => getComputedStyle(kid).display === "block") ? kids : [el];
	const painted = parts.map((part) => splitLines(part, cleanups));
	const lines = painted.flatMap((p) => p.lines);
	const hadPosition = el.style.position;
	if (getComputedStyle(el).position === "static") el.style.position = "relative";
	const hostRect = el.getBoundingClientRect();
	const kx = hostRect.width / (el.offsetWidth || hostRect.width || 1) || 1;
	const ky = hostRect.height / (el.offsetHeight || hostRect.height || 1) || 1;
	const brushes = [];
	const tl = gsap.timeline();
	lines.forEach((line, i) => {
		const lineRect = line.getBoundingClientRect();
		const range = document.createRange();
		range.selectNodeContents(line);
		const inkRect = range.getBoundingClientRect();
		const ink = Math.max(8, (inkRect.right - lineRect.left) / kx + 6);
		const block = lineRect.width / kx;
		const at = i * gap;
		const reach = { v: 0 };
		const clip = () => (line.style.clipPath = `inset(-30% ${Math.max(0, block - reach.v).toFixed(1)}px -30% -0.2em)`);
		clip();
		tl.to(reach, { v: ink, duration: pass, ease, onUpdate: clip, onComplete: () => (line.style.clipPath = "") }, at);
		riseLine(tl, line, at, { rise, settle, fade, pass });
		const lh = lineRect.height / ky;
		brushes.push(brushPass(tl, el, at, { left: (lineRect.left - hostRect.left) / kx, top: (lineRect.top - hostRect.top) / ky + lh * 0.14, height: lh * 0.72, ink, pass, ease, paint }));
	});
	const remove = () => {
		brushes.forEach((b) => b.remove());
		el.style.position = hadPosition;
	};
	cleanups.push(remove);
	return { tl, remove };
}

export function letterSign(title, cleanups, options = {}) {
	if (options.mode !== "lines") return letterStrokes(title, cleanups, options);
	return options.split ? letterSplit(title, cleanups, options) : letterLines(title, cleanups, options);
}

function letterStrokes(title, cleanups, { perChar = 0.06, dip = 0.09, wet = "lighter", ghost = 0.3, order = "reading", stroke = "down", flat: square = false }) {
	const read = square ? (fn) => flat(title, fn) : (fn) => fn();
	// size and ink of the glyphs themselves, which may sit in a span set larger than the heading
	const first = document.createTreeWalker(title, NodeFilter.SHOW_TEXT, { acceptNode: (n) => (n.data.trim() ? 1 : 3) }).nextNode();
	const cs = getComputedStyle(first ? first.parentElement : title);
	const em = parseFloat(cs.fontSize);
	const ink = cs.color;
	const wetInk = wet === "lighter" ? `color-mix(in srgb, ${ink} 30%, #fff)` : wet === "darker" ? `color-mix(in srgb, ${ink} 70%, #000)` : wet;
	const dir = stroke === "up" ? -1 : 1;
	const host = title.parentElement;
	const words = read(() => measure(title));
	if (order === "reverse") words.reverse();
	const pad = em * 0.12;

	// the timeline of every stroke, as the brush pulls them
	const tl = gsap.timeline();
	let at = 0;
	words.forEach((word, wi) => {
		if (wi) at += dip;
		word.strokes = [];
		word.chars.forEach((c, ci) => {
			const load = 1 - 0.42 * (ci / Math.max(1, word.chars.length));
			const set = strokesFor(c.ch, c);
			const span = perChar * gsap.utils.random(0.85, 1.15);
			set.forEach((s, si) => {
				const k = {
					...s,
					top: c.y + c.h * 0.06,
					bot: c.y + c.h * 0.94,
					box: [c.x - pad, c.y - pad, c.x + c.w + pad, c.y + c.h + pad],
					start: at + ((si * span) / set.length) * 0.82,
					dur: (span / set.length) * 1.5,
					load,
					word,
				};
				k.cols = bristles(k, load);
				word.strokes.push(k);
			});
			at += span;
		});
		word.end = at;
		word.box = [Math.min(...word.chars.map((c) => c.x)) - pad, Math.min(...word.chars.map((c) => c.y)) - pad, Math.max(...word.chars.map((c) => c.x + c.w)) + pad, Math.max(...word.chars.map((c) => c.y + c.h)) + pad];
	});
	const total = words.length ? Math.max(...words.map((w) => w.end)) + 1 : 0;
	const lastLevel = words.length ? Math.max(...words.map((w) => w.end)) + 0.25 : 0;

	// the layers: the old sign under the paint, the wet coat over each word, the brush tip
	const old = ghost > 0 ? copyOf(title, "hero__echo--ghost lettering__ghost") : null;
	const sheens = words.map(() => {
		const n = copyOf(title, "lettering__sheen");
		Object.assign(n.style, { color: wetInk, filter: `blur(${f(em * 0.024)}px)`, opacity: 0 });
		return n;
	});
	const tip = document.createElementNS(NS, "svg");
	tip.setAttribute("class", "lettering__tip");
	tip.setAttribute("aria-hidden", "true");
	tip.setAttribute("focusable", "false");
	tip.setAttribute("overflow", "visible");
	const blur = document.createElementNS(NS, "filter");
	const fid = `lt${Math.random().toString(36).slice(2, 8)}`;
	Object.entries({ id: fid, x: "-100%", y: "-100%", width: "300%", height: "300%" }).forEach(([k, v]) => blur.setAttribute(k, v));
	const fe = document.createElementNS(NS, "feGaussianBlur");
	fe.setAttribute("stdDeviation", f(em * 0.014));
	blur.append(fe);
	const bead = document.createElementNS(NS, "ellipse");
	Object.entries({ fill: wetInk, opacity: 0, filter: `url(#${fid})` }).forEach(([k, v]) => bead.setAttribute(k, v));
	tip.append(blur, bead);
	// the ghost goes in before the heading and so paints under it, the wet coat and the tip after it and over it;
	// the heading itself is never lifted onto a layer, which would cost it the screen's subpixel smoothing.
	// A word's wet coat is on the page only while that word is wet, so the blurred copies are never all painted at once.
	const layers = [old, ...sheens, tip].filter(Boolean);
	if (old) title.before(old);
	host.append(tip);
	read(() => layers.forEach((l) => place(title, l)));
	if (old) old.style.opacity = ghost;
	title.dataset.lettering = "on";
	gsap.set(title, { opacity: 1 });

	let live = true;
	const render = (t) => {
		if (!live) return;
		if (t >= total) {
			title.style.clipPath = "";
			layers.forEach((l) => (l.style.visibility = "hidden"));
			sheens.forEach((n) => n.remove());
			return;
		}
		layers.forEach((l) => (l.style.visibility = ""));
		let clip = "";
		let tipAt = null;
		words.forEach((word, wi) => {
			const level = inOut1(clamp01((t - word.end - 0.25) / 0.7));
			const sheen = sheens[wi];
			let wetPath = "";
			if (level >= 1) clip += rect(...word.box);
			else
				word.strokes.forEach((k) => {
					if (t <= k.start) return;
					const p = inOut2(clamp01((t - k.start) / k.dur));
					if (!tipAt || k.start >= tipAt.k.start) tipAt = { k, p };
					// the paint levels out: the bristle marks close and the coat runs out to the letter's edges
					const g = level
						? { ax: k.ax + ((k.box[0] + k.box[2]) / 2 - k.ax) * level, qx: k.qx + ((k.box[0] + k.box[2]) / 2 - k.qx) * level, top: k.top + (k.box[1] - k.top) * level, bot: k.bot + (k.box[3] - k.bot) * level }
						: k;
					const hw = k.bw / 2 + ((k.box[2] - k.box[0]) / 2 - k.bw / 2) * level;
					const mid = spine(g, p, dir);
					const stroke = band(mid, hw, (k.bw / 2) * (1 - level), dir);
					clip += stroke;
					wetPath += stroke;
					// the slits close as the paint levels out
					if (level < 1) clip += slits(mid, hw, k.cols, 1 - level);
				});
			// the wet sheen on a fresh word dries off
			const sh = t < word.end + 0.15 ? 0.85 : 0.85 * (1 - in1(clamp01((t - word.end - 0.15) / 0.85)));
			const wet = wetPath && sh > 0;
			if (wet !== sheen.isConnected) wet ? tip.before(sheen) : sheen.remove();
			if (wet) Object.assign(sheen.style, { opacity: f(sh), clipPath: `path("${wetPath}")` });
		});
		title.style.clipPath = `path("${clip || "M0 0Z"}")`;
		// the bead of paint rides the brush tip and is wiped off at the end of a word
		if (tipAt) {
			const { k, p } = tipAt;
			const [x, y] = pointAt(k, p, dir);
			const end = k.word.end;
			const o = t < end ? 0.45 + 0.5 * k.load : (0.45 + 0.5 * k.load) * (1 - clamp01((t - end) / 0.06));
			Object.entries({ cx: f(x), cy: f(y - k.bw * 0.1 * dir), rx: f(k.bw * 0.42), ry: f(k.bw * 0.26), opacity: f(o) }).forEach(([a, v]) => bead.setAttribute(a, v));
		} else bead.setAttribute("opacity", 0);
		// the old sign is let go under the paint as the last word levels out
		if (old) old.style.opacity = f(ghost * (1 - inOut1(clamp01((t - lastLevel) / 0.7))));
	};
	const clock = { t: 0 };
	tl.to(clock, { t: total, duration: total, ease: "none", onUpdate: () => render(clock.t) }, 0);
	render(0);

	const remove = () => {
		if (!live) return;
		live = false;
		layers.forEach((l) => l.remove());
		title.style.clipPath = "";
		delete title.dataset.lettering;
	};
	cleanups.push(remove);
	return { tl, remove };
}

export function weathering(title, cleanups, { rules = [], text = [], exact = false } = {}) {
	const echoes = ["worn", "ghost"].map((kind) => {
		const echo = copyOf(title, `hero__echo--${kind}`);
		title.parentElement.append(echo);
		cleanups.push(() => echo.remove());
		return echo;
	});
	// exact: where the rounded width falls short of the heading's own, the copies take its fractional width, rounded up to
	// the next 64th as the pen does, or a heading shrunk to its text wraps its last word in the copies
	const width = () => {
		const w = parseFloat(getComputedStyle(title).width);
		return exact && title.offsetWidth < w ? `${Math.ceil(w * 64 + 0.5) / 64}px` : `${title.offsetWidth}px`;
	};
	const place = () => {
		const wide = width();
		echoes.forEach((e) => Object.assign(e.style, { left: `${title.offsetLeft}px`, top: `${title.offsetTop}px`, width: wide }));
	};
	const apply = (w) => {
		title.style.opacity = (1 - w ** 0.7).toFixed(3);
		text.forEach((t) => (t.style.opacity = (1 - 0.7 * w).toFixed(3)));
		rules.forEach((s) => (s.style.opacity = (1 - 0.72 * w).toFixed(3)));
		echoes[0].style.opacity = Math.sin(Math.PI * Math.min(1, w * 1.25)).toFixed(3);
		echoes[1].style.opacity = (0.8 * smooth(0.45, 1, w)).toFixed(3);
	};
	return { echoes, place, apply };
}

export function endOnResize(act, cleanups) {
	const width = window.innerWidth;
	const onResize = () => {
		if (window.innerWidth !== width && act.progress() < 1) act.progress(1);
	};
	window.addEventListener("resize", onResize);
	cleanups.push(() => window.removeEventListener("resize", onResize));
}

export function signScene(region, { ctx, cleanups, alive, fontsReady, wide }, options) {
	const { flag, title, eyebrow = null, media, img, plane, liner = region, stripes = [], held = [], focus, seen = false, weather: weathers = true, ghost = null, letters = {}, exact = false, then = [], story = {} } = options;
	const later = (fn) => () => alive() && ctx.add(fn);
	const root = document.documentElement;
	const section = region.closest("section") || region;
	if (!title) {
		delete root.dataset[flag];
		return;
	}
	const fresh = freshSign(flag, section);
	held.forEach((el) => el && relative(el, cleanups));

	// the sign's weathered states for the scroll: worn, then a ghost on the wall
	const weather = weathers ? weathering(title, cleanups, { rules: stripes, text: [eyebrow].filter(Boolean), exact }) : null;
	const span = [40, 400];
	const zoom = [0, 400];
	const place = () => {
		if (weather) weather.place();
		if (seen) {
			zoom[0] = Math.max(0, pageTop(media) - window.innerHeight * 0.15);
			zoom[1] = zoom[0] + Math.max(120, media.offsetHeight * 0.8);
		}
		// by default a ghost by the time the middle of the name reaches the top of the window
		span[1] = Math.max(span[0] + 120, ghost ? ghost() : pageTop(title) + title.offsetHeight * 0.5);
	};
	place();

	const state = { p: 0 };
	let st = null;
	const apply = () => {
		const scrolled = st ? state.p * (st.end - st.start) : 0;
		if (weather && root.dataset[flag] === "live") weather.apply(smooth(span[0], span[1], scrolled));
		// at the top of the page nothing is left on a layer of its own, so the type renders exactly as drawn
		if (scrolled < 0.5) {
			gsap.set([img, plane].filter(Boolean), { clearProps: "transform" });
			return;
		}
		if (img) gsap.set(img, { scale: 1 + 0.08 * (seen ? smooth(zoom[0], zoom[1], scrolled) : smooth(0, 0.8, state.p)), transformOrigin: focus, force3D: true });
		// the sign is the nearest plane: it lifts faster than the page
		gsap.set(plane, { y: -scrolled * (wide ? 0.12 : 0.06), force3D: true });
	};
	redrawOnRefresh(() => {
		place();
		apply();
	}, cleanups);
	const writes = then.filter(([kind]) => kind === "write").map(([, el]) => el);
	const faint = [title, eyebrow, ...writes, ...stripes].filter(Boolean);
	cleanups.push(() => {
		gsap.set([img, plane].filter(Boolean), { clearProps: "transform,transformOrigin" });
		faint.forEach((t) => (t.style.opacity = ""));
	});
	st = gsap.to(state, {
		p: 1,
		ease: "none",
		onUpdate: apply,
		scrollTrigger: { trigger: section, start: 0, end: "bottom top", scrub: wide ? true : 0.6, invalidateOnRefresh: true },
	}).scrollTrigger;

	// below the first screen the story is told by the scroll: written in by the pen, a control slid in from its edge
	const { written = [], slid = [] } = story;
	const told = [...written, ...slid].filter(Boolean);
	const byScroll = (el) => {
		if (slid.includes(el)) return riseIn(el, { opacity: 0, x: -24 });
		const tl = gsap.timeline();
		pen(tl, el, 0, cleanups, { ink: "own", exact, scrub: true, duration: 1 });
		cleanups.push(() => gsap.set(el, { clearProps: "opacity" }));
		return onScroll(el, tl, { start: "top 90%", end: "top 60%" });
	};

	if (!fresh) {
		signLettered(flag);
		apply();
		if (told.length)
			fontsReady.then(later(() => {
				told.filter((el) => !inView(el)).forEach(byScroll);
				ScrollTrigger.refresh();
			}));
		return;
	}

	// the first paint's faint sign, now held inline; a control keeps its full ink
	gsap.set(faint, { opacity: 0.3 });
	if (weather) weather.echoes.forEach((e) => gsap.set(e, { opacity: 0 }));
	root.dataset[flag] = "letter";
	cleanups.push(() => {
		root.dataset[flag] = "live";
		gsap.set(faint, { clearProps: "opacity" });
	});

	fontsReady.then(later(() => {
		// what is not on the screen now is left to the scroll, from its own start state
		const now = told.filter(inView);
		const left = told.filter((el) => !now.includes(el));
		left.forEach((el) => {
			gsap.set(el, { clearProps: "opacity" });
			byScroll(el);
		});
		const act = gsap.timeline();
		const parts = [];
		const write = (el, at, pace) => {
			const p = pen(act, el, at, cleanups, { pace, ink: "own", exact });
			if (p) parts.push(p);
		};
		if (stripes[0]) parts.push(rule(act, stripes[0], 0, { liner, cleanups, duration: wide ? 0.85 : 0.7 }));
		write(eyebrow, 0.2, 1.3);
		const { perChar = [0.058, 0.064], ...rest } = letters;
		const name = letterSign(title, cleanups, { perChar: wide ? perChar[0] : perChar[1], dip: 0.09, ...rest });
		act.add(name.tl, 0.4);
		parts.push(name);
		const end = 0.4 + name.tl.duration();
		then.forEach((step) => {
			const [kind] = step;
			if (kind === "rule") {
				const [, i, min, lead] = step;
				if (stripes[i]) parts.push(rule(act, stripes[i], Math.max(min, end - lead), { liner, cleanups, duration: wide ? 0.8 : 0.65 }));
			} else if (kind === "write") {
				const [, el, min, lead] = step;
				if (!left.includes(el)) write(el, Math.max(min, end - lead), 2.2);
			} else if (kind === "sheen") {
				const [, min, lead] = step;
				sheen(media, cleanups, { tl: act, at: Math.max(min, end - lead) });
			}
		});
		act.eventCallback("onComplete", () => {
			signLettered(flag);
			gsap.set(faint.filter((el) => !left.includes(el)), { clearProps: "opacity" });
			parts.forEach((part) => part.remove());
			apply();
		});
		endOnResize(act, cleanups);
		cleanups.push(() => act.kill());
		ScrollTrigger.refresh();
	}));
}
