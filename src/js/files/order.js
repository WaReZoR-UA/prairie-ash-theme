/*
Order confirmation scenes, loaded as their own chunk and built only under prefers-reduced-motion: no-preference.
The stylesheet holds the finished page; every start state is created here and every end state is the markup.
The flag on <html> (data-order) is set in the head of order-confirmation.html, so the first paint already shows the
faint sign, its order number not yet struck and the receipt not yet printed.

data-motion="order-sign"     the confirmation as a sign on the wall through the shared signScene(): the line above
                             written, the name lettered stroke by stroke over its ghost, the ship date and the next steps
                             written by the pen (those under the fold with the scroll), the ways on slid in; the order
                             number, painted faint with the name, is struck by a numbering stamp the moment the brush hands
                             the name back, its wheels turning up to the number. As the page scrolls on the sign lifts and
                             weathers into a ghost
data-motion="order-receipt"  the receipt on paper: its title written, each bag's photo uncovered from the edge facing its
                             text, the bag's name and details written and its price stamped, the payment line struck like a
                             paid stamp; on the first screen on the clock of the sign, below it with the scroll.
                             The totals inside are the Cart's till receipt (data-motion="cart-receipt"), run by the Cart module
*/

import { gsap, ScrollTrigger } from "./gsap.js";
import { pen, stamp } from "./paint.js";
import { signScene, endOnResize } from "./lettering.js";
import { inView, relative, onScroll, uncover, facing, freshSign } from "./motion-utils.js";

const PRICE = { size: 1.7, tilt: 4, press: 0.26, ease: "power4.in" };
const PAID = { size: 1.6, tilt: -8, press: 0.28, ease: "power4.in", inkAt: 0.13, inkFor: 0.15 };

// One bag on the receipt, placed on tl from `at`: the photo uncovered from the edge facing its text, the name and the
// details written, the price stamped. scrub: the scroll drives tl, so the pen hands each line back by a swap
function printLine(line, tl, at, cleanups, parts, { scrub }) {
	const thumb = line.querySelector(".receipt-line__thumb");
	const img = thumb && thumb.querySelector("img");
	const text = line.querySelector(".receipt-line__text");
	const name = line.querySelector(".receipt-line__name");
	const details = line.querySelector(".receipt-line__details");
	const total = line.querySelector(".receipt-line__total");
	relative(text, cleanups);
	if (img) {
		uncover(thumb, img, { shut: facing(thumb, text), from: { scale: 1.12 }, to: { scale: 1 }, tl, at });
		gsap.set(thumb, { clearProps: "opacity" });
		cleanups.push(() => gsap.set(thumb, { clearProps: "clipPath" }));
		cleanups.push(() => gsap.set(img, { clearProps: "transform,transformOrigin" }));
	}
	const write = (el, when, duration) => {
		if (scrub) pen(tl, el, when, cleanups, { ink: "own", exact: true, scrub: true, duration });
		else {
			const p = pen(tl, el, when, cleanups, { pace: 2.2, ink: "own", exact: true });
			if (p) parts.push(p);
		}
	};
	write(name, at + 0.25, 0.45);
	write(details, at + 0.5, 0.4);
	if (total) stamp(tl, total, at + 0.85, line, PRICE);
	cleanups.push(() => gsap.set([name, details, total].filter(Boolean), { clearProps: "opacity,transform,transformOrigin" }));
}

function receiptIn(panel, number, scene, fresh) {
	const { ctx, cleanups, alive, fontsReady } = scene;
	const root = document.documentElement;
	const title = panel.querySelector(".confirmation__receipt-title");
	const lines = [...panel.querySelectorAll(".receipt-line")];
	const pay = panel.querySelector(".confirmation__payment");
	relative(panel, cleanups);
	const now = fresh ? [title, ...lines, pay].filter((el) => el && inView(el)) : [];
	const struck = !!(fresh && number && inView(number));
	// the first paint's look, now held inline until the act: faint lines, empty photos, prices and payment line
	now.forEach((el) => {
		if (el === title) gsap.set(title, { opacity: 0.3 });
		else if (el === pay) gsap.set(pay, { opacity: 0 });
		else {
			gsap.set(el.querySelectorAll(".receipt-line__name, .receipt-line__details"), { opacity: 0.3 });
			gsap.set(el.querySelectorAll(".receipt-line__thumb, .receipt-line__total"), { opacity: 0 });
		}
	});
	const held = [number, ...now.flatMap((el) => (el === title || el === pay ? [el] : [...el.querySelectorAll(".receipt-line__name, .receipt-line__details, .receipt-line__thumb, .receipt-line__total")]))].filter(Boolean);
	cleanups.push(() => gsap.set(held, { clearProps: "opacity" }));

	fontsReady.then(() => {
		if (!alive()) return;
		ctx.add(() => {
			// below the first screen the receipt is printed by the scroll; what is on the screen keeps its look
			const later = (el) => el && !now.includes(el) && !inView(el);
			if (later(title)) {
				const tl = gsap.timeline({ defaults: { ease: "none" } });
				pen(tl, title, 0, cleanups, { ink: "own", exact: true, scrub: true, duration: 1 });
				cleanups.push(() => gsap.set(title, { clearProps: "opacity" }));
				onScroll(title, tl, { start: "top 90%", end: "top 60%" });
			}
			lines.filter(later).forEach((line) => {
				const tl = gsap.timeline({ defaults: { ease: "none" } });
				printLine(line, tl, 0, cleanups, [], { scrub: true });
				onScroll(line, tl, { start: "top 90%", end: "top 50%" });
			});
			if (later(pay)) {
				const tl = gsap.timeline({ defaults: { ease: "none" } });
				stamp(tl, pay, 0, panel, PAID);
				cleanups.push(() => gsap.set(pay, { clearProps: "opacity,transform,transformOrigin" }));
				onScroll(pay, tl, { start: "top 88%", end: "top 62%" });
			}
			ScrollTrigger.refresh();
		});
	});
	// on the first screen the receipt is printed once the name is under way, on the clock of the sign; the pen's copies
	// are laid only when it starts, so until then the lines keep the first paint's faint look
	if (now.length)
		fontsReady.then(() => {
			if (!alive()) return;
			const wait = gsap.delayedCall(2.2, () => alive() && ctx.add(() => {
				const tl = gsap.timeline();
				const parts = [];
				if (now.includes(title)) {
					const p = pen(tl, title, 0, cleanups, { pace: 2.2, ink: "own", exact: true });
					if (p) parts.push(p);
				}
				const printed = lines.filter((line) => now.includes(line));
				printed.forEach((line, i) => printLine(line, tl, 0.25 + i * 0.5, cleanups, parts, { scrub: false }));
				if (now.includes(pay)) stamp(tl, pay, 0.35 + printed.length * 0.5 + 0.4, panel, PAID);
				tl.eventCallback("onComplete", () => {
					gsap.set(held.filter((el) => el !== number), { clearProps: "opacity,transform,transformOrigin" });
					// a photo opened on load is handed back to the markup, so it renders exactly as the still page
					printed.forEach((line) => {
						gsap.set(line.querySelectorAll(".receipt-line__thumb"), { clearProps: "clipPath" });
						gsap.set(line.querySelectorAll(".receipt-line__thumb img"), { clearProps: "transform,transformOrigin" });
					});
					parts.forEach((part) => part.remove());
				});
				endOnResize(tl, cleanups);
				cleanups.push(() => tl.kill());
			}));
			cleanups.push(() => wait.kill());
		});
	if (!struck) return;

	// the order number, painted faint with the name, is struck into its place the moment the brush hands the name
	// back; the flag going live is the backstop when the name is not lettered
	const h1 = number.closest("h1");
	let done = false;
	let painting = false;
	const strike = () => {
		if (done || !alive()) return;
		done = true;
		watch.disconnect();
		ctx.add(() => {
			const tl = gsap.timeline();
			const clear = () => gsap.set(number, { clearProps: "opacity,transform,transformOrigin,display,minWidth,whiteSpace" });
			// a box of its own so the stamp can turn it, as wide as the number so the wheels never move the line
			gsap.set(number, { display: "inline-block", minWidth: number.getBoundingClientRect().width, whiteSpace: "nowrap" });
			stamp(tl, number, 0, h1, { size: 1.5, tilt: -6, from: 0.3, press: 0.28, ease: "power4.in", inkAt: 0.13, inkFor: 0.15, count: { cleanups, from: 1000 } });
			tl.eventCallback("onComplete", clear);
			endOnResize(tl, cleanups);
			cleanups.push(() => {
				tl.kill();
				clear();
			});
		});
	};
	const watch = new MutationObserver(() => {
		if (h1.style.clipPath) painting = true;
		if ((painting && !h1.style.clipPath) || root.dataset.order === "live") strike();
	});
	watch.observe(root, { attributes: true, attributeFilter: ["data-order"] });
	watch.observe(h1, { attributes: true, attributeFilter: ["style"] });
	cleanups.push(() => watch.disconnect());
}

export function orderMotion({ ctx, cleanups, alive, fontsReady, wide }) {
	const scene = { ctx, cleanups, alive, fontsReady, wide };
	const sign = document.querySelector('[data-motion="order-sign"]');
	const panel = document.querySelector('[data-motion="order-receipt"]');
	const heading = sign && sign.querySelector(".confirmation__heading");
	const number = heading && heading.querySelector(".confirmation__number");
	// the receipt reads the head flag before the sign takes it over
	const fresh = freshSign("order", sign ? sign.closest("section") : null);
	if (panel) receiptIn(panel, number, scene, fresh);
	if (!heading) return;
	const steps = sign.querySelector(".confirmation__steps");
	const rows = steps ? [...steps.querySelectorAll(".lv__row")] : [];
	// the next steps in reading order: the heading over them, then each step's name and what it says
	const told = [steps && steps.querySelector(".confirmation__steps-title"), ...rows.flatMap((row) => [...row.querySelectorAll(".lv__label, .lv__value")])].filter(Boolean);
	signScene(sign, scene, {
		flag: "order",
		title: heading.querySelector("h1"),
		eyebrow: heading.querySelector(".confirmation__eyebrow"),
		plane: heading,
		held: [heading, steps, ...rows, sign],
		exact: true,
		// cream paint on the wall: the wet coat reads lighter than the dry paint
		letters: { perChar: [0.058, 0.064] },
		// the ship date is written as the brush lifts, then the steps on the first screen one after another
		then: [["write", heading.querySelector(".confirmation__note"), 1, 1.1], ...told.map((el, i) => ["write", el, 1.4 + 0.12 * i, 0.5 - 0.16 * i])],
		story: { written: told, slid: [...sign.querySelectorAll(".confirmation__links .text-link")] },
	});
	// the number is painted faint with the name until it is struck; held only now, so the sign's weathered copies keep it
	if (number && fresh && panel && inView(number)) gsap.set(number, { opacity: 0.3 });
}
