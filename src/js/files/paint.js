/*
Sign painter's tools, shared by every painted sign: a pinstripe ruled with a liner, a caption written in by
a soft-edged pen, a price or a notice struck as a stamp. The sign itself is lettered by lettering.js.
rule places its line on the caller's timeline and returns { tl, remove }; pen writes a line in and returns the pen.
stamp places its strike on the caller's timeline; stampPivot is where it is struck from.
board letters a board row by row with all three: a guide ruled, the row turned down onto it, a mark stamped.
*/

import { gsap } from "./gsap.js";
import { SplitText } from "gsap/SplitText";
import { pageLeft, inView, relative, layer, redrawOnRefresh, onScroll, swing, inkCopy } from "./motion-utils.js";

// A pinstripe ruled along its straightedge, placed on tl at `at`; returns { tl, remove }.
// The stripe itself is pulled out from one end: origin, and start for anything more its start state carries (a guide
// fading in); held: the stripe was laid at scaleX 0 when the page was built and is ruled from where it stands; clear:
// the props let go when it is done; ease null takes the timeline's own. liner: the host on which a fresh line is drawn
// over the stripe by a liner, a wet bead at its head, for a sign lettered on load; remove() takes both off again.
export function rule(tl, stripe, at, { duration = 0.8, ease = "power1.inOut", origin = "0% 50%", start = {}, held = false, clear = "", liner = null, cleanups = null } = {}) {
	if (liner) {
		const fresh = stripe.cloneNode(false);
		fresh.className = `${stripe.className} hero__rule`;
		fresh.removeAttribute("data-motion");
		fresh.style.opacity = "";
		Object.assign(fresh.style, { left: `${stripe.offsetLeft}px`, top: `${stripe.offsetTop}px`, width: `${stripe.offsetWidth}px` });
		const bead = document.createElement("span");
		bead.className = "hero__rule-bead";
		bead.setAttribute("aria-hidden", "true");
		Object.assign(bead.style, { left: `${stripe.offsetLeft}px`, top: `${stripe.offsetTop}px` });
		liner.append(fresh, bead);
		const remove = () => {
			fresh.remove();
			bead.remove();
		};
		cleanups.push(remove);
		const pull = gsap.timeline();
		pull.fromTo(fresh, { clipPath: "inset(-4px 100% -4px 0)" }, { clipPath: "inset(-4px 0% -4px 0)", duration, ease }, 0);
		pull.fromTo(bead, { x: 0, opacity: 0 }, { x: stripe.offsetWidth, duration, ease }, 0);
		pull.to(bead, { opacity: 0.9, duration: 0.08 }, 0);
		pull.to(bead, { opacity: 0, duration: 0.25 }, duration - 0.1);
		tl.add(pull, at);
		return { tl, remove };
	}
	const to = { scaleX: 1, duration };
	if (ease) to.ease = ease;
	if (clear) to.clearProps = clear;
	if (held) tl.to(stripe, to, at);
	else tl.fromTo(stripe, { scaleX: 0, transformOrigin: origin, ...start }, to, at);
	return { tl, remove: () => {} };
}

// The copy a pen writes on: laid over the caption and written in line by line under a soft-edged mask; exact: the copy
// takes the caption's own fractional width, which a caption shrunk to its text needs or its last word wraps
function writeCaption(caption, cleanups, exact = false) {
	const host = caption.parentElement;
	const color = getComputedStyle(host).color;
	const ink = caption.cloneNode(true);
	ink.className = `${caption.className} hero__caption-ink`;
	ink.setAttribute("aria-hidden", "true");
	// nor a second stop in the tab order: a link copied with its line is not focusable or clickable
	ink.inert = true;
	// a copy is never a second label of a field, nor a second element of the same id
	[ink, ...ink.querySelectorAll("[id], [for]")].forEach((n) => {
		n.removeAttribute("id");
		n.removeAttribute("for");
	});
	// the computed width reads to three decimals, the layout snaps to 64ths: round up to the next 64th, never under
	const width = exact ? `${Math.ceil(parseFloat(getComputedStyle(caption).width) * 64 + 0.5) / 64}px` : `${caption.offsetWidth}px`;
	Object.assign(ink.style, { left: `${caption.offsetLeft}px`, top: `${caption.offsetTop}px`, width, color });
	if (exact) {
		// a line on one line stays on one: split into words, its text runs a hair wider than the line it was measured on
		const range = document.createRange();
		range.selectNodeContents(caption);
		// one line when every box shares its middle: a part set in another face sits higher or lower by a few pixels,
		// a second line by a whole line
		const boxes = [...range.getClientRects()].filter((r) => r.width > 0 && r.height > 0);
		const mids = boxes.map((r) => r.top + r.height / 2);
		if (!boxes.length || Math.max(...mids) - Math.min(...mids) < Math.min(...boxes.map((r) => r.height)) / 2) ink.style.whiteSpace = "nowrap";
	}
	host.append(ink);
	const split = SplitText.create(ink, { type: "lines", aria: "none" });
	const tl = gsap.timeline();
	let at = 0;
	split.lines.forEach((line) => {
		const dur = Math.max(0.35, line.textContent.length * 0.024);
		tl.fromTo(line, { "--pen": "-18%" }, { "--pen": "100%", duration: dur, ease: "none" }, at);
		at += dur * 0.9;
	});
	const remove = () => {
		split.revert();
		ink.remove();
	};
	cleanups.push(remove);
	return { tl, remove, ink };
}

// A line written in by a soft-edged pen; returns the pen's { tl, remove, ink }, or null when there is no line.
// Placed on tl at `at` (on its own when tl is null) and run pace times faster; the line keeps its look under the pen
// and is handed back once it is written. ink: "full" writes in full ink, "own" in full ink of the line's own colour,
// "line" in the look the line has now (faint while it is faint). scrub: the pen takes `duration` of a timeline the
// scroll drives and hands the line back by a swap at its end, which plays both ways. over: written in place under a
// mask for `duration`, for a line that cannot be copied (a breadcrumb of links), from its own faint look. exact: the copy
// takes the line's fractional width, for a line shrunk to its text (a heading in a column that does not stretch it).
export function pen(tl, el, at, cleanups, { pace = 1, ink = "full", scrub = false, over = false, duration, exact = false } = {}) {
	if (!el) return null;
	if (over) {
		const mask = "linear-gradient(90deg, #000 var(--pen), rgba(0, 0, 0, 0.3) calc(var(--pen) + 2.4em))";
		tl.call(() => {
			el.style.setProperty("--pen", "-30%");
			el.style.webkitMaskImage = mask;
			el.style.maskImage = mask;
			el.style.opacity = "1";
		}, null, at);
		tl.fromTo(el, { "--pen": "-30%" }, { "--pen": "100%", duration, ease: "none", immediateRender: false }, at);
		tl.call(() => {
			el.style.removeProperty("--pen");
			el.style.webkitMaskImage = "";
			el.style.maskImage = "";
			el.style.opacity = "";
		}, null, at + duration);
		return null;
	}
	const p = writeCaption(el, cleanups, exact);
	if (ink === "full") gsap.set(p.ink, { opacity: 1 });
	else if (ink === "own") gsap.set(p.ink, { opacity: 1, color: getComputedStyle(el).color });
	if (scrub) {
		tl.add(p.tl.duration(duration), at)
			.fromTo(el, { opacity: 0 }, { opacity: 1, duration: 0.01 }, at + duration)
			.fromTo(p.ink, { opacity: 1 }, { opacity: 0, duration: 0.01 }, at + duration);
		return p;
	}
	// the ink starts unwritten, not full for the frame before the pen reaches it
	p.tl.progress(0.001).progress(0).timeScale(pace);
	p.tl.eventCallback("onComplete", () => {
		gsap.set(el, { clearProps: "opacity" });
		p.remove();
	});
	if (tl) tl.add(p.tl, at);
	return p;
}

// Where a stamp is pressed from: the scale it falls from (s) and the x it pivots on (cx). By default the centre of its
// glyphs in its own pixels, held over its board, never wider than it and never reaching past it into the gutter or
// off the window; with no board, at its size. screen: read off the screen as the element stands now, right-aligned
// text from its box's right edge (the Home prices, measured while their signs still hang turned up on the hook).
export function stampPivot(el, board, { size = 1.6, tilt = -4, screen = false } = {}) {
	const range = document.createRange();
	range.selectNodeContents(el);
	const glyphs = range.getBoundingClientRect();
	const box = el.getBoundingClientRect();
	if (screen) {
		const align = getComputedStyle(el).textAlign;
		return { s: size, cx: align === "right" || align === "end" ? box.width - glyphs.width / 2 : glyphs.left - box.left + glyphs.width / 2 };
	}
	// measured in the element's own pixels: the board may already be turned up on its rule
	const k = el.offsetWidth / (box.width || 1);
	const centre = (glyphs.left - box.left + glyphs.width / 2) * k;
	if (!board) return { s: size, cx: centre };
	const turn = Math.abs(tilt) * (Math.PI / 180);
	const width = el.offsetWidth * Math.cos(turn) + el.offsetHeight * Math.sin(turn);
	const room = board.offsetWidth;
	const s = Math.max(1.15, Math.min(size, room / Math.max(1, width)));
	const offset = pageLeft(el) - pageLeft(board);
	const lo = (s * width + offset - room) / (s - 1);
	const hi = offset / (s - 1);
	return { s, cx: Math.min(hi, Math.max(lo, centre)) };
}

// A figure read off its own text, shown on an aria-hidden copy so a screen reader keeps the real string: returns
// { target, decimals, show(v), restore() }, or null when the text holds no figure. show(v) puts v on the copy (made on the
// first show), decimals, thousands and currency kept; read: "wheel" a digit clicks over only once its wheel has turned all
// the way, as on a numbering stamp; "till" the nearest value shows, as a total ticks up on a till
export function figure(el, cleanups, { read = "wheel" } = {}) {
	const m = el.textContent.trim().match(/^(\D*?)(\d[\d,]*(?:\.\d+)?)([\s\S]*)$/);
	if (!m) return null;
	const [, before, digits, after] = m;
	const decimals = (digits.split(".")[1] || "").length;
	const unit = 10 ** decimals;
	const target = parseFloat(digits.replace(/,/g, ""));
	const value = read === "till" ? (v) => Number(v.toFixed(decimals)) : (v) => Math.floor(v * unit + 1e-6) / unit;
	const format = (v) => (digits.includes(",") ? value(v).toLocaleString("en-CA", { minimumFractionDigits: decimals, maximumFractionDigits: decimals }) : value(v).toFixed(decimals));
	let copy = null;
	const show = (v) => {
		copy = copy || inkCopy(el, cleanups);
		copy.ink.textContent = `${before}${format(v)}${after}`;
	};
	return { target, decimals, show, restore: () => copy && copy.restore() };
}

// The wheels of a numbering stamp: the figure's digits turn up from `from` to its own value while the stamp falls, and
// read the value itself at the moment of contact
function turnWheels(tl, el, at, duration, { cleanups, from = 0 }) {
	const fig = figure(el, cleanups);
	if (!fig) return;
	fig.show(from);
	const wheels = { v: from };
	const draw = () => fig.show(wheels.v);
	tl.fromTo(wheels, { v: from }, { v: fig.target, duration, ease: "power1.in", onUpdate: draw }, at);
	draw();
	redrawOnRefresh(draw, cleanups);
}

// A price or a notice pressed onto its board: it gathers speed to the paper, the tilt is gone at the moment of contact
// and nothing moves after it. Placed on tl at `at`, returns tl.
// press, ease: the fall to the paper; inkAt, inkFor, inkEase: when and how the ink comes up, at `ink` opacity;
// from: the opacity it is struck from, laid only as the stamp falls, so until then it keeps its own look (without it
// the start state is laid at once); land, wobble: set down crooked by the hand, then shaken square in [seconds, ease];
// pivot: a stampPivot taken earlier, otherwise measured now with board, size, tilt and screen; count: { cleanups, from },
// a figure struck by a numbering stamp, its digits turning up to its value as it falls
export function stamp(tl, el, at, board, { size = 1.6, tilt = -4, ink = 1, from, press = 0.24, ease = "power3.in", inkAt = 0.11, inkFor = 0.13, inkEase = "power1.in", land = 0, wobble = null, screen = false, pivot = null, count = null } = {}) {
	const { s, cx } = pivot || stampPivot(el, board, { size, tilt, screen });
	if (count) turnWheels(tl, el, at, press, count);
	const held = from !== undefined;
	tl.fromTo(el, { scale: s, rotation: tilt, transformOrigin: `${cx.toFixed(1)}px 50%` }, { scale: 1, rotation: land, duration: press, ease, ...(held ? { immediateRender: false } : {}) }, at);
	if (held) tl.set(el, { opacity: from }, at).to(el, { opacity: ink, duration: inkFor, ease: inkEase }, at + inkAt);
	else tl.fromTo(el, { opacity: 0 }, { opacity: ink, duration: inkFor, ease: inkEase }, at + inkAt);
	if (wobble) tl.to(el, { rotation: 0, duration: wobble[0], ease: wobble[1], clearProps: "opacity,transform,transformOrigin" }, at + press);
	return tl;
}

// A lettered board, row by row: a guide is ruled along the edge the row turns on, the row turns over past flat and
// back and lies down on it, the guide goes, and the row's mark (struck) is stamped once it lies flat. Every row is
// driven by the scroll while it crosses band; a row already on the screen is handed to onScreen(row, make) when one
// is given, make(from) building that row's timeline. Returns the rows.
// guide: the guide's class; edge: "top" | "bottom", where the guide lies and, unless turn.origin says otherwise, the
// hinge; ruled: the guide's { duration, opacity }; turn: { at, from, steps, perspective, origin, hold }, steps as in
// swing, hold: the turned start state is laid only when the row starts to turn; ink: seconds the row takes to come up
// from its opacity (from); fade: [at, seconds] of the guide; struck(row): the row's mark or null; strike: { at, size, tilt,
// set, square, count }, set(i): where the hand sets row i's mark down, { x, rotation } off its column, then square:
// [seconds, ease] it takes to be pushed square onto the column; count: the mark is a figure struck by a numbering stamp;
// band: the eye band, or band(row, i) for a band of each row's own
export function board(lv, cleanups, { rows: sel = ":scope > .lv__row", guide: guideClass, edge = "bottom", ruled = {}, turn, ink = 0.16, fade = [0.72, 0.2], struck = null, strike = {}, band = { start: "top 90%", end: "top 58%" }, onScreen = null }) {
	const rows = [...lv.querySelectorAll(sel)];
	if (!rows.length) return rows;
	relative(lv, cleanups);
	const marks = rows.map((row) => (struck ? struck(row) : null));
	cleanups.push(() => gsap.set([...rows, ...marks.filter(Boolean)], { clearProps: "transform,transformOrigin,opacity" }));
	const { duration: ruleFor = 0.3, opacity: ruleFrom = 0.6 } = ruled;
	const { at, from: angle, steps, perspective = 800, origin = edge === "top" ? "50% 0%" : "50% 100%", hold = false } = turn;
	const make = (row, mark, from = 0, i = 0) => {
		const guide = layer(lv, guideClass, cleanups);
		const place = () => Object.assign(guide.style, { left: `${row.offsetLeft}px`, top: `${row.offsetTop + (edge === "top" ? 0 : row.offsetHeight)}px`, width: `${row.offsetWidth}px` });
		place();
		redrawOnRefresh(place, cleanups);
		const { tl } = rule(gsap.timeline({ defaults: { ease: "none" } }), guide, 0, { duration: ruleFor, ease: "power2.inOut", start: { opacity: ruleFrom } });
		swing(tl, row, at, { from: angle, steps, hinge: { transformPerspective: perspective, transformOrigin: origin }, start: { opacity: from }, first: hold ? { immediateRender: false } : {} })
			.to(row, { opacity: 1, duration: ink, ease: "power1.in" }, at)
			.to(guide, { opacity: 0, duration: fade[1] }, fade[0]);
		if (mark) {
			tl.set(mark, { opacity: 0 }, at);
			const off = strike.set ? strike.set(i) : null;
			stamp(tl, mark, strike.at, lv, { size: strike.size, tilt: strike.tilt, from: 0, ...(off ? { land: off.rotation } : {}), ...(strike.count ? { count: { cleanups } } : {}) });
			if (off) {
				const [squareFor = 0.3, squareEase = "power2.out"] = strike.square || [];
				tl.fromTo(mark, { x: off.x }, { x: 0, rotation: 0, duration: squareFor, ease: squareEase }, strike.at + 0.24);
			}
		}
		return tl;
	};
	rows.forEach((row, i) => {
		if (onScreen && inView(row)) onScreen(row, (from) => make(row, marks[i], from, i));
		else onScroll(row, make(row, marks[i], 0, i), typeof band === "function" ? band(row, i) : band);
	});
	return rows;
}
