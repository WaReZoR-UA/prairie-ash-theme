/*
Product page scenes, loaded as their own chunk and built only under prefers-reduced-motion: no-preference.
The stylesheet holds the finished page; every start state is created here and every end state is the markup.
The flag on <html> (data-product) is set in the head of each product page, so the first paint already shows
the faint sign the brush and the pen go over.

data-motion="product-crumb"   the breadcrumb, written in by the pen with the sign
data-motion="product-sign"    origin and notes written, the name lettered over its ghost, the price stamped;
                              as the page scrolls on, the sign lifts as the nearest plane
data-motion="product-shelf"   the bag photo stands up off the shelf edge into its frame with the light running up
                              it; as the page scrolls on, the camera moves in on the bag. The two detail photos
                              hang beside it like labels on a pin: crooked at the first paint, they swing true
data-motion="product-board"   the facts and the recipe as a lettered board: a rule is pulled along each row and
                              the row flips over on it and lies flat, the summary's from above, the recipe's
                              up off the rule below
data-motion="product-form"    the purchase controls, ink only: below the first screen each control's frame is
                              ruled round it and its ink comes up inside, the control itself never moves; a
                              choice is filled from the left and wiped off to the right (product-motion.scss).
                              Add to cart is stamped onto the price line, or across the button when the price
                              is off the screen. Sold out: the notice is struck onto the board after its row
                              lies flat, and onto the disabled button in its own faded ink
data-motion="product-origin"  the farm photo, opened from the side that faces its text while the hillside pans
                              into its crop and the morning mist lifts off the valley
*/

import { gsap, ScrollTrigger } from "./gsap.js";
import { pen, stamp, board } from "./paint.js";
import { letterSign, endOnResize } from "./lettering.js";
import { smooth, inView, relative, layer, redrawOnRefresh, onScroll, swing, uncover, freshSign, signLettered } from "./motion-utils.js";

// the valley mist in origin.jpg: its body (60th percentile of the cloud bank) and its bright core (75th)
const MIST = ["217, 219, 221", "238, 238, 239"];
// Shelf ---------------------------------------------------------------------------------------------

// The bag photo leans back against the wall at the first paint and is stood up on the shelf edge, the light
// running up it from the edge; as the page scrolls on, the camera moves in on the bag
function shelfScene(gallery, { cleanups, wide, fresh, hold }) {
	const section = gallery.closest("section") || gallery;
	const media = gallery.querySelector(".product-summary__media");
	const img = media && media.querySelector("img");
	if (!img) return;
	relative(media, cleanups);
	const state = { p: 0, settle: 1 };
	let st = null;
	const apply = () => {
		const scrolled = st ? state.p * (st.end - st.start) : 0;
		if (scrolled < 0.5 && state.settle === 1) gsap.set(img, { clearProps: "transform" });
		else gsap.set(img, { scale: state.settle * (1 + 0.07 * smooth(0, 0.8, state.p)), transformOrigin: "50% 62%", force3D: true });
	};
	redrawOnRefresh(apply, cleanups);
	cleanups.push(() => gsap.set([img, media], { clearProps: "transform,transformOrigin" }));
	st = gsap.to(state, {
		p: 1,
		ease: "none",
		onUpdate: apply,
		scrollTrigger: { trigger: section, start: 0, end: "bottom top", scrub: wide ? true : 0.6, invalidateOnRefresh: true },
	}).scrollTrigger;
	if (!fresh) return;
	// the lean the first paint drew, now held inline
	hold(media, { rotationX: 14, transformPerspective: 1100, transformOrigin: "50% 100%" });
	state.settle = 1.08;
	apply();
	const edge = layer(media, "product-edge", cleanups);
	const tl = swing(gsap.timeline({ delay: 0.05 }), media, 0, { steps: [[-1.6, 0.8, "power2.inOut"], [0, 0.3, "sine.out"]] })
		.to(state, { settle: 1, duration: 1.3, ease: "power2.out", onUpdate: apply }, 0)
		.fromTo(edge, { yPercent: 170, opacity: 0.9 }, { yPercent: -100, duration: 1.1, ease: "power2.inOut" }, 0.25)
		.to(edge, { opacity: 0, duration: 0.3 }, 1.05)
		.call(() => {
			gsap.set(media, { clearProps: "transform,transformOrigin" });
			edge.remove();
		});
	cleanups.push(() => tl.kill());
}

// The detail photos hang like labels pinned beside the bag: crooked at the first paint, each swings on its
// pin and settles square. Further down the page each is pinned up as it comes up the window.
const PIN = [4, -3.5];
function pinTl(thumb, i, duration) {
	const s = Math.sign(PIN[i % 2]);
	return swing(gsap.timeline(), thumb, 0, { axis: "rotation", steps: [[-1.7 * s, 0.42, "power2.inOut"], [0.7 * s, 0.3, "sine.inOut"], [0, 0.26, "sine.out"]] }).duration(duration);
}
function labelsIn(gallery, { ctx, cleanups, alive, fontsReady, fresh, hold }) {
	const thumbs = [...gallery.querySelectorAll(".product-summary__detail")];
	if (!thumbs.length) return;
	cleanups.push(() => gsap.set(thumbs, { clearProps: "transform,transformOrigin,opacity" }));
	if (fresh) thumbs.forEach((thumb, i) => hold(thumb, { rotation: PIN[i % 2], transformOrigin: "50% 6%" }));
	fontsReady.then(() => {
		if (!alive()) return;
		ctx.add(() => thumbs.forEach((thumb, i) => {
			if (inView(thumb)) {
				if (!fresh) return;
				const tl = pinTl(thumb, i, 1.1).delay(0.45 + i * 0.14);
				tl.eventCallback("onComplete", () => gsap.set(thumb, { clearProps: "transform,transformOrigin" }));
				return;
			}
			// pinned up: it comes down onto its pin from above, hanging crooked, and swings true
			const r = PIN[i % 2];
			const tl = gsap.timeline()
				.fromTo(thumb, { opacity: 0, y: -18, rotation: r * 1.6, transformOrigin: "50% 6%" }, { opacity: 1, y: 0, rotation: r, duration: 0.3, ease: "power2.out" }, 0)
				.add(pinTl(thumb, i, 0.7), 0.3);
			onScroll(thumb, tl, { start: "top 92%", end: "top 55%" });
		}));
	});
}

// Board -------------------------------------------------------------------------------------------

function boardIn(lv, { ctx, cleanups, alive, fontsReady, fresh, faint }) {
	const rows = [...lv.querySelectorAll(":scope > .lv__row")];
	if (!rows.length) return;
	const summary = !!lv.closest(".product-summary");
	const dir = summary ? 1 : -1;
	// the summary's rows are drawn faint at the first paint
	if (summary && fresh) faint(rows);
	fontsReady.then(() => {
		if (!alive()) return;
		ctx.add(() => {
			let n = 0;
			// the summary's rows flip over from above on the rule along their top, the recipe's up off the rule below;
			// sold out: the notice is struck once its row lies flat, heavier and set crooked by the hand
			board(lv, cleanups, {
				guide: "product-rule",
				edge: summary ? "top" : "bottom",
				ruled: { duration: 0.34, opacity: 0.45 },
				turn: { at: 0.2, from: -84 * dir, steps: [[7 * dir, 0.4, "power2.in"], [-2.4 * dir, 0.14, "sine.inOut"], [0, 0.14, "sine.out"]], perspective: 900, hold: true },
				ink: 0.24,
				fade: [0.74, 0.24],
				struck: (row) => row.querySelector("[data-product-struck]"),
				strike: { at: 0.92, size: 1.7, tilt: 5 },
				band: { start: "top 90%", end: "top 62%" },
				onScreen: (row, make) => {
					if (!summary || !fresh) return gsap.set(row, { clearProps: "opacity" });
					// standing on the first screen, the board is lettered after the sign is begun
					make(0.3).delay(0.9 + n++ * 0.12).timeScale(1.2);
				},
			});
		});
	});
}

// Form --------------------------------------------------------------------------------------------

// The liner rules a frame round a control and its ink comes up inside it; the control never moves and
// stays clickable throughout, only its paint arrives
function frameOver(host, target, cleanups, fill) {
	const f = document.createElement("span");
	f.className = "product-frame";
	f.setAttribute("aria-hidden", "true");
	const h = host.getBoundingClientRect();
	const t = target.getBoundingClientRect();
	Object.assign(f.style, { left: `${t.left - h.left}px`, top: `${t.top - h.top}px`, width: `${t.width}px`, height: `${t.height}px` });
	if (fill) Object.assign(f.style, { boxShadow: "none", backgroundColor: fill });
	host.append(f);
	cleanups.push(() => f.remove());
	return f;
}
function inkControl(tl, host, target, at, cleanups, fill = "") {
	const f = frameOver(host, target, cleanups, fill);
	const to = parseFloat(getComputedStyle(target).opacity) || 1;
	tl.fromTo(f, { clipPath: "inset(0% 100% 0% 0%)", opacity: 1 }, { clipPath: "inset(0% 0% 0% 0%)", duration: 0.4, ease: "power1.inOut" }, at)
		.fromTo(target, { opacity: 0 }, { opacity: to, duration: 0.3, ease: "power1.in" }, at + 0.28)
		.to(f, { opacity: 0, duration: 0.2 }, at + 0.5);
}
function formIn(form, { ctx, cleanups, alive, fontsReady }) {
	const groups = [...form.querySelectorAll(".product-summary__option, .product-summary__buy")];
	fontsReady.then(() => {
		if (!alive()) return;
		ctx.add(() => groups.forEach((group) => {
			// a control on the first screen is there from the first paint, ready to be used
			if (inView(group)) return;
			const tl = gsap.timeline();
			const touched = [];
			const word = group.querySelector(".product-summary__legend, .field__label");
			if (word) {
				tl.fromTo(word, { opacity: 0 }, { opacity: 1, duration: 0.3, ease: "power1.in" }, 0);
				touched.push(word);
			}
			let at = 0.1;
			group.querySelectorAll(".options__item").forEach((item) => {
				inkControl(tl, item, item.querySelector(".options__label"), at, cleanups);
				touched.push(item.querySelector(".options__label"));
				at += 0.09;
			});
			const field = group.querySelector(".field");
			const input = field && field.querySelector(".input");
			if (input) {
				relative(field, cleanups);
				inkControl(tl, field, input, at, cleanups);
				touched.push(input);
				at += 0.12;
			}
			const button = group.querySelector(".button:not(:disabled)");
			if (button) {
				relative(group, cleanups);
				// the button's paint is laid on by the brush, in its own colour
				inkControl(tl, group, button, at, cleanups, getComputedStyle(button).backgroundColor);
				touched.push(button);
			}
			// sold out: the notice on the disabled button is struck after the field, landing in its own faded ink
			const sold = group.querySelector(".button:disabled");
			if (sold) {
				stamp(tl, sold, at + 0.1, group, { size: 1.9, tilt: 5, ink: parseFloat(getComputedStyle(sold).opacity) || 1, from: 0 });
				touched.push(sold);
			}
			cleanups.push(() => gsap.set(touched, { clearProps: "opacity,transform,transformOrigin" }));
			onScroll(group, tl, { start: "top 92%", end: "top 62%" });
		}));
	});
}

// Add to cart: a stamp struck on the price line, crooked as a hand leaves it, then the ink fades. When the
// price is off the screen it is struck across the button the visitor pressed.
function cartIn(form, sign, cleanups) {
	const button = form.querySelector(".product-summary__buy .button");
	if (!button || button.disabled) return;
	const quantity = form.querySelector('input[name="quantity"]');
	const price = sign && sign.querySelector('[data-motion="price"]');
	const name = sign && sign.querySelector("h1");
	const status = document.createElement("p");
	status.className = "visually-hidden";
	status.setAttribute("role", "status");
	form.append(status);
	cleanups.push(() => status.remove());
	let run = null;
	let mark = null;
	const clear = () => {
		if (run) run.kill();
		run = null;
		if (mark) mark.remove();
		mark = null;
	};
	cleanups.push(clear);
	const press = () => {
		clear();
		const n = Math.max(1, parseInt(quantity && quantity.value, 10) || 1);
		const bags = `${n} ${n === 1 ? "bag" : "bags"}`;
		status.textContent = `Added ${bags} of ${name ? name.textContent.trim() : "this coffee"} to the cart`;
		const r = price && price.getBoundingClientRect();
		const onPrice = r && r.top >= 0 && r.bottom <= window.innerHeight;
		const host = onPrice ? sign : button.parentElement;
		relative(host, cleanups);
		mark = document.createElement("span");
		mark.className = "product-stamp";
		mark.setAttribute("aria-hidden", "true");
		mark.textContent = `Added, ${bags}`;
		host.append(mark);
		let left;
		let top;
		if (onPrice) {
			const range = document.createRange();
			range.selectNodeContents(price);
			const ink = range.getBoundingClientRect();
			const k = price.offsetWidth / (r.width || 1);
			left = price.offsetLeft + (ink.right - r.left) * k + 20;
			top = price.offsetTop + (price.offsetHeight - mark.offsetHeight) / 2;
			left = Math.min(left, host.clientWidth - mark.offsetWidth - 4);
		} else {
			left = button.offsetLeft + (button.offsetWidth - mark.offsetWidth) / 2;
			top = button.offsetTop + (button.offsetHeight - mark.offsetHeight) / 2;
		}
		Object.assign(mark.style, { left: `${left.toFixed(1)}px`, top: `${top.toFixed(1)}px` });
		run = gsap.timeline({ onComplete: clear })
			.fromTo(mark, { scale: 1.8, rotation: -11, opacity: 0 }, { scale: 1, rotation: -4, duration: 0.24, ease: "power3.in" }, 0)
			.to(mark, { opacity: 1, duration: 0.12, ease: "power1.in" }, 0.11)
			.to(mark, { opacity: 0, duration: 0.6, ease: "power1.in" }, 2.8);
	};
	button.addEventListener("click", press);
	cleanups.push(() => button.removeEventListener("click", press));
	// a press made while this module was still on its way: the pointer left the focus on the button
	if (document.activeElement === button && !button.matches(":focus-visible")) press();
}

// Origin --------------------------------------------------------------------------------------------

// The farm photo is opened from the side that faces its text, so the light comes from the copy it illustrates;
// the hillside pans after the opening edge into its crop and the morning mist lifts off the valley
function originIn(slot, { cleanups }) {
	const img = slot.querySelector("img");
	const text = slot.parentElement && slot.parentElement.querySelector(".product-story__text");
	if (!img || !text) return;
	relative(slot, cleanups);
	const s = slot.getBoundingClientRect();
	const t = text.getBoundingClientRect();
	const beside = t.right <= s.left + 1;
	const mist = layer(slot, "product-mist", cleanups);
	mist.style.background = `radial-gradient(ellipse 55% 38% at 62% 30%, rgba(${MIST[1]}, 0.9), rgba(${MIST[0]}, 0.5) 50%, rgba(${MIST[0]}, 0) 100%), radial-gradient(ellipse 70% 30% at 30% 70%, rgba(${MIST[0]}, 0.7), rgba(${MIST[0]}, 0) 100%)`;
	uncover(slot, img, {
		shut: beside ? "inset(0% 100% 0% 0%)" : "inset(0% 0% 100% 0%)",
		// the scale always covers the pan, so the photo never uncovers its slot
		from: { scale: 1.16, xPercent: beside ? -5 : 0, yPercent: beside ? 0 : -5, transformOrigin: "30% 60%" },
		to: { scale: 1, xPercent: 0, yPercent: 0 },
		end: "top 40%",
		also: (tl) => tl.fromTo(mist, { opacity: 0.85, xPercent: -8, yPercent: 6 }, { opacity: 0, xPercent: 10, yPercent: -14, duration: 0.85, ease: "power1.in" }, 0.15),
	});
	cleanups.push(() => gsap.set([slot, img], { clearProps: "clipPath,transform,transformOrigin" }));
}

// Sign ----------------------------------------------------------------------------------------------

function signScene(sign, { ctx, cleanups, alive, fontsReady, wide, fresh, faint }) {
	const section = sign.closest("section") || sign;
	const title = sign.querySelector("h1");
	const origin = sign.querySelector(".product-summary__origin");
	const notes = sign.querySelector(".product-summary__notes");
	const price = sign.querySelector('[data-motion="price"]');
	const crumb = document.querySelector('[data-motion="product-crumb"] ol');
	if (!title) return;
	relative(sign, cleanups);

	// the sign is the nearest plane: as the page scrolls on it lifts faster than the page
	const state = { p: 0 };
	let st = null;
	const apply = () => {
		const scrolled = st ? state.p * (st.end - st.start) : 0;
		// at the top of the page nothing is left on a layer of its own, so the type renders exactly as drawn
		if (scrolled < 0.5 || !wide) gsap.set(sign, { clearProps: "transform" });
		else gsap.set(sign, { y: -scrolled * 0.1, force3D: true });
	};
	redrawOnRefresh(apply, cleanups);
	cleanups.push(() => gsap.set(sign, { clearProps: "transform" }));
	st = gsap.to(state, {
		p: 1,
		ease: "none",
		onUpdate: apply,
		scrollTrigger: { trigger: section, start: 0, end: "bottom top", scrub: true, invalidateOnRefresh: true },
	}).scrollTrigger;

	if (!fresh) return;
	const parts = [title, origin, notes, price].filter(Boolean);
	faint(parts);
	// the breadcrumb is written at once wherever the sign stands
	if (crumb) {
		faint([crumb]);
		fontsReady.then(() => {
			if (!alive()) return;
			ctx.add(() => {
				if (!inView(crumb)) return gsap.set(crumb, { clearProps: "opacity" });
				const tl = gsap.timeline({ delay: 0.05 });
				// written in place over its own faint line: a breadcrumb of links is not copied
				pen(tl, crumb, 0, cleanups, { over: true, duration: 0.7 });
				cleanups.push(() => tl.progress(1).kill());
			});
		});
	}

	const write = () => fontsReady.then(() => {
		if (!alive()) return;
		ctx.add(() => {
			const act = gsap.timeline();
			const done = [];
			// the pen writes in the line's own ink, over the faint line, which takes over once it is written
			const write = (el, at, pace) => {
				const p = pen(act, el, at, cleanups, { pace, ink: "own" });
				if (p) done.push(p);
			};
			write(origin, 0.1, 1.3);
			// dark ink on paper: the wet coat reads deeper than the dry paint
			const letters = letterSign(title, cleanups, { perChar: wide ? 0.064 : 0.07, dip: 0.09, wet: "darker" });
			act.add(letters.tl, 0.3);
			done.push(letters);
			const lettersEnd = 0.3 + letters.tl.duration();
			write(notes, Math.max(0.8, lettersEnd - 1.3), 2.2);
			// the price is struck once the name is up, from its faint ghost
			if (price) stamp(act, price, Math.max(1.1, lettersEnd - 0.55), sign, { from: 0 });
			act.eventCallback("onComplete", () => {
				signLettered("product");
				gsap.set(parts, { clearProps: "opacity,transform,transformOrigin" });
				done.forEach((part) => part.remove());
				apply();
			});
			endOnResize(act, cleanups);
			cleanups.push(() => act.kill());
		});
	});
	// a sign below the first screen is lettered when it comes up the window, never behind the visitor's back
	if (inView(title)) write();
	else {
		const once = ScrollTrigger.create({ trigger: title, start: "top 88%", once: true, onEnter: write });
		cleanups.push(() => once.kill());
	}
}

export function productMotion({ ctx, cleanups, alive, fontsReady, wide }) {
	const root = document.documentElement;
	const fresh = freshSign("product");
	// the faint first paint is handed over to inline styles before the flag that drew it is changed
	const held = [];
	const hold = (els, vars) => {
		gsap.set(els, vars);
		held.push(...[].concat(els));
	};
	const faint = (els) => hold(els, { opacity: 0.3 });
	const scene = { ctx, cleanups, alive, fontsReady, wide, fresh, faint, hold };
	const gallery = document.querySelector('[data-motion="product-shelf"]');
	if (gallery) {
		shelfScene(gallery, scene);
		labelsIn(gallery, scene);
	}
	const sign = document.querySelector('[data-motion="product-sign"]');
	if (sign) signScene(sign, scene);
	document.querySelectorAll('[data-motion="product-board"]').forEach((lv) => boardIn(lv, scene));
	const origin = document.querySelector('[data-motion="product-origin"]');
	if (origin) originIn(origin, scene);
	const form = document.querySelector('[data-motion="product-form"]');
	if (form) {
		formIn(form, scene);
		cartIn(form, sign, cleanups);
	}
	root.dataset.product = fresh ? "letter" : "live";
	cleanups.push(() => {
		root.dataset.product = "live";
		gsap.set(held, { clearProps: "opacity,transform,transformOrigin" });
	});
	ScrollTrigger.refresh();
}
