/*
A form written in by hand and its sent stamp, shared by the Wholesale ticket and the Contact note, built from the
painter's tools in paint.js. Ink only: fields, choices, links and buttons never move or fade, so the form works at every
moment. Both run under prefers-reduced-motion: no-preference only; the stylesheet holds the finished page.

formInk(panel, scene, options): the form panel as it is filled in, each part written with the scroll while it crosses the
lower part of the window: the panel's title, every field's label by the pen with its "(required)" stamped once the label
is written and the line under it written after, the lines named in `lines`, the note risen in. Its error summary is a
correction note: its line written in and a rule struck under it, with the scroll on the error state and at once when a
send finds problems, its links working throughout. options:
	ease        the ease of a line's own timeline (GSAP's default)
	field       { label: [at, seconds], mark: { at, size, tilt, press }, after: [selector, at, seconds], band,
	              ruling: { under, className, at, duration, ease, lift: [at, seconds] } }: a field with a ruling is written
	              only when it holds `under`, and the line under it is ruled on a layer of that class and lifted
	lines       selectors of lines written in after the fields ([])
	rise        selectors of lines risen in after them ([])
	correction  { rule: { className, at, duration, ease, lift }, settle }: settle, a send that finds problems first writes
	            whatever the scroll was still to write, since the note moves everything under it
sentStamp(region, scene, options): the sent page: the notice's title (.form-notice .form-panel__title) is struck like a
stamp, square at contact, the line under it written in, light crosses the photo (.sent__media), or a photo under the fold
is uncovered by the scroll; the camera moves in on the photo as the page scrolls. The flag on <html> shows the empty place
on the first paint. options:
	flag        the head flag, data-<flag>         focus    where in the photo the camera moves in
	struck      { at, ...stamp options }           cancel   { className, at, duration, ease, lift }: a rule pulled under it
	write       when the line is written (0.75)    light    when the light crosses the photo (0.9)
	faces       the photo opens from the edge facing the notice (false: from the edge facing the page's middle)
*/

import { gsap, ScrollTrigger } from "./gsap.js";
import { rule, pen, stamp } from "./paint.js";
import { endOnResize } from "./lettering.js";
import { inView, relative, layer, redrawOnRefresh, onScroll, uncover, sheen, facing, freshSign, signLettered, riseIn } from "./motion-utils.js";

const LINE = { start: "top 92%", end: "top 66%" };

// A line ruled on a layer of host under `under`, from one end at `at`, lifted at lift[0] for lift[1] seconds
function ruleUnder(host, under, tl, cleanups, { className, at = 0, duration = 0.5, ease = "power2.inOut", lift }) {
	const line = layer(host, className, cleanups);
	const place = () => Object.assign(line.style, { left: `${under.offsetLeft}px`, top: `${under.offsetTop + under.offsetHeight}px`, width: `${under.offsetWidth}px` });
	place();
	redrawOnRefresh(place, cleanups);
	rule(tl, line, at, { duration, ease, start: { opacity: 1 } });
	tl.to(line, { opacity: 0, duration: lift[1] }, lift[0]);
	return line;
}

// A line below the window written in by the pen, on tl at `at`, or on its own timeline driven by the scroll; returns
// { p, st } (the pen and the scroll trigger of a line on its own), or null for a line already on the screen
function written(el, cleanups, { tl = null, at = 0, duration = 1, ease } = {}) {
	if (!el || inView(el)) return null;
	relative(el.parentElement, cleanups);
	const own = tl || gsap.timeline(ease ? { defaults: { ease } } : {});
	const p = pen(own, el, at, cleanups, { ink: "own", exact: true, scrub: true, duration });
	cleanups.push(() => gsap.set(el, { clearProps: "opacity" }));
	return { p, st: tl ? null : onScroll(el, own, LINE) };
}

function fieldIn(field, cleanups, told, { label: [labelAt, labelFor], mark: { at: markAt, ...struck }, after = null, band, ruling = null }) {
	const label = field.querySelector(".field__label");
	const under = ruling && field.querySelector(ruling.under);
	if (!label || (ruling && !under) || inView(field)) return;
	relative(field, cleanups);
	const tl = gsap.timeline({ defaults: { ease: "none" } });
	if (under) ruleUnder(field, under, tl, cleanups, ruling);
	const w = written(label, cleanups, { tl, at: labelAt, duration: labelFor });
	// the pen writes the name of the field, the mark beside it is struck once it is written
	const mark = label.querySelector(".field__mark");
	if (w && mark) {
		w.p.ink.querySelectorAll(".field__mark").forEach((m) => (m.style.visibility = "hidden"));
		stamp(tl, mark, markAt, field, struck);
		cleanups.push(() => gsap.set(mark, { clearProps: "opacity,transform,transformOrigin" }));
	}
	if (after) written(field.querySelector(after[0]), cleanups, { tl, at: after[1], duration: after[2] });
	told.push(onScroll(field, tl, band));
}

function correctionIn(box, cleanups, told, { rule: line, settle = false }) {
	const title = box.querySelector(".error-summary__title");
	if (!title) return;
	relative(box, cleanups);
	const build = () => {
		const tl = gsap.timeline({ defaults: { ease: "none" } });
		pen(tl, title, 0, cleanups, { ink: "own", exact: true, scrub: true, duration: 0.7 });
		ruleUnder(box, title, tl, cleanups, line);
		return tl;
	};
	cleanups.push(() => gsap.set(title, { clearProps: "opacity" }));
	// a note already standing on the page (the error state) is written in with the scroll
	if (!box.hidden && !inView(box)) onScroll(box, build(), { start: "top 90%", end: "top 55%" });
	// a send that finds problems shows the note, and it is written in at once while its links already work
	const seen = new MutationObserver(() => {
		if (box.hidden) return;
		if (settle)
			told.splice(0).forEach((st) => {
				const done = st.animation;
				st.kill(false);
				done.progress(1);
			});
		build().duration(1.1).progress(0.001).progress(0);
		if (settle) ScrollTrigger.refresh();
	});
	seen.observe(box, { attributes: true, attributeFilter: ["hidden"] });
	cleanups.push(() => seen.disconnect());
}

export function formInk(panel, { ctx, cleanups, alive, fontsReady }, { ease, field, lines = [], rise = [], correction }) {
	const box = panel.querySelector("[data-error-summary-box]");
	// the pen and the stamps measure the type, so the form waits for the faces; until then it is the finished form
	fontsReady.then(() => {
		if (!alive()) return;
		ctx.add(() => {
			const told = [];
			const line = (el) => {
				const w = written(el, cleanups, { ease });
				if (w) told.push(w.st);
			};
			line(panel.querySelector(".form-panel__title"));
			panel.querySelectorAll(".field").forEach((f) => fieldIn(f, cleanups, told, field));
			lines.forEach((sel) => line(panel.querySelector(sel)));
			rise.forEach((sel) => {
				const el = panel.querySelector(sel);
				if (!el || inView(el)) return;
				riseIn(el);
				cleanups.push(() => gsap.set(el, { clearProps: "opacity,transform" }));
			});
			if (box) correctionIn(box, cleanups, told, correction);
			ScrollTrigger.refresh();
		});
	});
}

export function sentStamp(region, { ctx, cleanups, alive, fontsReady, wide }, { flag, focus, struck: { at: struckAt = 0.3, ...struck } = {}, cancel = null, write = 0.75, light = 0.9, faces = false }) {
	const later = (fn) => () => alive() && ctx.add(fn);
	const root = document.documentElement;
	const section = region.closest("section") || region;
	const panel = region.querySelector(".form-notice");
	const title = panel && panel.querySelector(".form-panel__title");
	const body = panel && panel.querySelector(".form-panel__body");
	const media = region.querySelector(".sent__media");
	const img = media && media.querySelector("img");
	if (!title || !img) {
		delete root.dataset[flag];
		return;
	}
	const fresh = freshSign(flag, section);
	relative(panel, cleanups);
	relative(media, cleanups);
	// the camera moves in once the photo has been seen whole
	gsap.fromTo(img, { scale: 1 }, { scale: 1.06, transformOrigin: focus, ease: "none", scrollTrigger: { trigger: media, start: "top 15%", end: "bottom top", scrub: wide ? true : 0.6 } });
	cleanups.push(() => gsap.set([img, title], { clearProps: "transform,transformOrigin" }));

	if (!fresh) {
		signLettered(flag);
		return;
	}
	// the first paint's empty place on the notice and its faint line, now held inline
	gsap.set(title, { opacity: 0 });
	if (body) gsap.set(body, { opacity: 0.3 });
	root.dataset[flag] = "letter";
	cleanups.push(() => {
		root.dataset[flag] = "live";
		gsap.set([title, body].filter(Boolean), { clearProps: "opacity" });
	});

	fontsReady.then(later(() => {
		const act = gsap.timeline();
		const parts = [];
		// the stamp gathers speed to the paper and is square at contact
		stamp(act, title, struckAt, panel, struck);
		const line = cancel ? ruleUnder(panel, title, act, cleanups, cancel) : null;
		if (body) {
			const p = pen(act, body, write, cleanups, { pace: 2.2, ink: "own", exact: true });
			if (p) parts.push(p);
		}
		// the light crosses the photo once the notice is stamped; a photo still under the fold is opened by the scroll
		if (inView(media)) sheen(media, cleanups, { tl: act, at: light });
		else {
			uncover(media, img, { shut: faces ? facing(media, panel) : facing(media), from: { scale: 1.12, transformOrigin: focus }, to: { scale: 1 } });
			cleanups.push(() => gsap.set(media, { clearProps: "clipPath" }));
		}
		act.eventCallback("onComplete", () => {
			signLettered(flag);
			gsap.set(title, { clearProps: "opacity,transform,transformOrigin" });
			if (body) gsap.set(body, { clearProps: "opacity" });
			parts.forEach((part) => part.remove());
			if (line) line.remove();
		});
		endOnResize(act, cleanups);
		cleanups.push(() => act.kill());
		ScrollTrigger.refresh();
	}));
}
