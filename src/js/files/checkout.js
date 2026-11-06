/*
Checkout page scenes, loaded as their own chunk and built only under prefers-reduced-motion: no-preference.
The stylesheet holds the finished page; every start state is created here and every end state is the markup.
The flag on <html> (data-checkout) is set in the head of the two Checkout pages, so the first paint already shows the
faint title and the empty place where the total to pay is stamped.

data-motion="checkout-sign"    the title on paper through the shared signScene(): the line above written, the name
                               lettered stroke by stroke over its ghost in dark ink, the line under it and the total's
                               label written, the total to pay struck by a numbering stamp and the line under it written
data-motion="checkout-fields"  the delivery, pickup and payment fields as an order form filled in by hand, through the
                               shared formInk(): a guide ruled under each field, its label written by the pen, its
                               "(required)" stamped, its format written after; the choice notes and the card line written,
                               the small print risen. Inputs, choices and their code style never move or fade. The error
                               summary is a correction note: written in with a red rule struck under it, at once when a send
                               finds problems, everything the scroll was still to write settled first
data-motion="checkout-submit"  the consent and the small print beside the pay button: the print rises into its lines; the
                               checkbox, the links and the pay button never move or fade
The order review's totals are the Cart's till receipt (data-motion="cart-receipt"), run by the Cart module itself.
*/

import { gsap, ScrollTrigger } from "./gsap.js";
import { stamp } from "./paint.js";
import { signScene, endOnResize } from "./lettering.js";
import { inView, onScroll, freshSign } from "./motion-utils.js";
import { formInk } from "./form-ink.js";

// The title sign -------------------------------------------------------------------------------------------

function signIn(region, scene) {
	const { ctx, cleanups, alive, fontsReady } = scene;
	const text = region.querySelector(".checkout-title__text");
	const label = region.querySelector(".checkout-title__total-label");
	const value = region.querySelector(".checkout-title__total-value");
	const note = region.querySelector(".checkout-title__total-note");
	// read before the sign takes the flag over
	const fresh = freshSign("checkout", region.closest("section"));
	const struck = { size: 1.5, tilt: -5, press: 0.26, ease: "power4.in", inkAt: 0.13, inkFor: 0.15 };
	// below the window, the total to pay is struck by the numbering stamp as the scroll reaches it
	const byScroll = (el) =>
		fontsReady.then(() => {
			if (!alive()) return;
			ctx.add(() => {
				const tl = gsap.timeline({ defaults: { ease: "none" } });
				stamp(tl, el, 0, region, { ...struck, count: { cleanups } });
				cleanups.push(() => gsap.set(el, { clearProps: "opacity,transform,transformOrigin" }));
				onScroll(el, tl, { start: "top 88%", end: "top 62%" });
				ScrollTrigger.refresh();
			});
		});
	// WooCommerce replaces the total with the order: a new total below the window is struck by the scroll in its turn,
	// one on the screen is already finished
	const $ = window.jQuery;
	if ($) {
		const restrike = () => {
			const fresh = region.querySelector(".checkout-title__total-value");
			if (alive() && fresh && fresh !== value && !inView(fresh)) byScroll(fresh);
		};
		$(document.body).on("updated_checkout", restrike);
		cleanups.push(() => $(document.body).off("updated_checkout", restrike));
	}
	if (value) {
		if (fresh && inView(value)) {
			// the first paint's empty place, now held inline until the numbering stamp strikes it
			gsap.set(value, { opacity: 0 });
			fontsReady.then(() => {
				if (!alive()) return;
				ctx.add(() => {
					// struck once the name is lettered and the label above it written, on the same clock as the sign
					const act = gsap.timeline({ delay: 1.45 });
					stamp(act, value, 0, region, { ...struck, from: 0, count: { cleanups } });
					act.eventCallback("onComplete", () => gsap.set(value, { clearProps: "opacity,transform,transformOrigin" }));
					endOnResize(act, cleanups);
					cleanups.push(() => {
						act.kill();
						gsap.set(value, { clearProps: "opacity,transform,transformOrigin" });
					});
				});
			});
		} else if (!inView(value)) byScroll(value);
	}
	signScene(region, scene, {
		flag: "checkout",
		title: region.querySelector("h1"),
		eyebrow: region.querySelector(".checkout-title__eyebrow"),
		plane: region,
		held: [text, region.querySelector(".checkout-title__total")],
		// ink on paper keeps its paint as the page scrolls on
		weather: false,
		exact: true,
		// dark ink on paper: the wet coat reads deeper than the dry paint
		letters: { perChar: [0.064, 0.07], wet: "darker" },
		// the line under the name as the brush lifts, then the total's label, and its line once the total is struck
		then: [["write", region.querySelector(".checkout-title__note"), 1, 1.2], ["write", label, 1.15, 0.9], ["write", note, 1.75, 0.2]],
		story: { written: [label, note].filter(Boolean) },
	});
}

export function checkoutMotion({ ctx, cleanups, alive, fontsReady, wide }) {
	const scene = { ctx, cleanups, alive, fontsReady, wide };
	// the parts WooCommerce replaced when the order changed are gone from the page: their scroll triggers go with them
	const $ = window.jQuery;
	if ($) {
		const forget = () => {
			ScrollTrigger.getAll().forEach((t) => {
				if (t.trigger && !t.trigger.isConnected) t.kill();
			});
			ScrollTrigger.refresh();
		};
		$(document.body).on("updated_checkout", forget);
		cleanups.push(() => $(document.body).off("updated_checkout", forget));
	}
	const sign = document.querySelector('[data-motion="checkout-sign"]');
	if (sign) signIn(sign, scene);
	const fields = document.querySelector('[data-motion="checkout-fields"]');
	const form = fields && fields.closest("form");
	if (form)
		formInk(form, scene, {
			ease: "none",
			field: {
				// a guide is ruled under the field as on an order form, and lifted once the field is written
				ruling: { under: ".input", className: "checkout-guide", at: 0, duration: 0.45, ease: "power2.inOut", lift: [0.75, 0.25] },
				label: [0.08, 0.55],
				mark: { at: 0.68, size: 1.7, tilt: -6, press: 0.24 },
				after: [".field__hint", 0.6, 0.3],
				band: { start: "top 92%", end: "top 62%" },
			},
			// the notes under the two ways to get the coffee, the card line and, on the error state, the red reasons
			lines: [
				".checkout-choice__item:first-child .checkout-choice__note",
				".checkout-choice__item:last-child .checkout-choice__note",
				".checkout-fields__label",
				"#checkout-postal-error",
				"#checkout-card-error",
			],
			rise: [".checkout-fields__text", '[data-motion="checkout-submit"] > .checkout-review__small', '[data-motion="checkout-submit"] > .checkout-review__small + .checkout-review__small'],
			// the red rule struck under the correction note is lifted once the order stands corrected
			correction: { rule: { className: "checkout-strike", at: 0.4, duration: 0.45, ease: "power2.in", lift: [0.95, 0.25] }, settle: true },
		});
}
