/*
Wholesale page scenes, loaded as their own chunk and built only under prefers-reduced-motion: no-preference.
The stylesheet holds the finished page; every start state is created here and every end state is the markup.
The flag on <html> (data-wholesale) is set in the head of the three Wholesale pages, so the first paint already
shows the faint trade sign, and on the sent page the empty place where the ticket is stamped.

data-motion="wholesale-sign"    the trade sign painted on the wall beside the photo: the rule is ruled, the line above
                                written and the name lettered stroke by stroke over its ghost, the rule under it ruled
                                and the line beside it written, and light crosses the photo. As the page scrolls on,
                                the sign lifts as the nearest plane and weathers back into a ghost while the camera
                                moves in on the grinder's hopper
data-motion="wholesale-board"   what an account includes, as a lettered board: under each figure a guide is ruled, the
                                figure stands up off it and its number is struck by a numbering stamp, the digits turning
                                up to the figure as it falls, one after another along the row as down a delivery ticket;
                                the line of what the cafe gets is written in by the pen
data-motion="wholesale-ticket"  the inquiry form as a ticket filled in by hand, ink only: the heading and every label are
                                written in by the pen, the line under each field is ruled in red and lifted, each
                                "(required)" is stamped; fields, choices and buttons never move or fade, so the form works
                                at every moment. Its error summary is a correction note: written in red with a rule under it
data-motion="wholesale-sent"    the sent ticket: "Inquiry sent" is stamped onto it, the line under it written in, light
                                crosses the photo, and the camera moves in on the crate as the page scrolls
*/

import { gsap, ScrollTrigger } from "./gsap.js";
import { pen, board } from "./paint.js";
import { signScene } from "./lettering.js";
import { inView, onScroll } from "./motion-utils.js";
import { formInk, sentStamp } from "./form-ink.js";

// the hopper of the grinder the beans pour into, in title.jpg: the camera moves in on it
const HOPPER = "46% 44%";

// Account board -------------------------------------------------------------------------------------

function boardIn(terms, { ctx, cleanups, alive, fontsReady }) {
	const body = terms.querySelector(".wholesale-inquiry__body");
	// the stamp and the pen measure the type, so the board waits for the faces
	fontsReady.then(() => {
		if (!alive()) return;
		ctx.add(() => {
			board(terms, cleanups, {
				rows: ":scope > .figures > .figures__item",
				guide: "wholesale-rule",
				edge: "bottom",
				ruled: { duration: 0.32, opacity: 0.5 },
				turn: { at: 0.18, from: 76, steps: [[-6, 0.42, "power2.in"], [2, 0.14, "sine.inOut"], [0, 0.14, "sine.out"]], perspective: 900 },
				ink: 0.22,
				fade: [0.74, 0.22],
				struck: (row) => row.querySelector(".figures__number"),
				strike: { at: 0.86, size: 1.7, tilt: -5, count: true },
				// figures side by side are stamped one after another along the row, as down a ticket
				band: (row) => {
					let k = 0;
					for (let n = row.previousElementSibling; n && n.offsetTop === row.offsetTop; n = n.previousElementSibling) k++;
					return { start: `top ${90 - 7 * k}%`, end: `top ${58 - 7 * k}%` };
				},
				// a figure already on the screen when the motion arrives keeps its finished look
				onScreen: () => {},
			});
			if (body && !inView(body)) {
				const tl = gsap.timeline();
				pen(tl, body, 0, cleanups, { ink: "own", exact: true, scrub: true, duration: 1 });
				cleanups.push(() => gsap.set(body, { clearProps: "opacity" }));
				onScroll(body, tl, { start: "top 90%", end: "top 60%" });
			}
			ScrollTrigger.refresh();
		});
	});
}

// Sent ticket ---------------------------------------------------------------------------------------

// the crate of bags on the hand truck at the back door, in success.jpg: the camera moves in on it
const CRATE = "56% 66%";

export function wholesaleMotion({ ctx, cleanups, alive, fontsReady, wide }) {
	const scene = { ctx, cleanups, alive, fontsReady, wide };
	const sign = document.querySelector('[data-motion="wholesale-sign"]');
	if (sign) {
		const plate = sign.querySelector(".wholesale-title__sign");
		const text = plate && plate.querySelector(".wholesale-title__text");
		const aside = plate && plate.querySelector(".wholesale-title__aside");
		const media = sign.querySelector(".wholesale-title__media");
		const intro = aside && aside.querySelector(".wholesale-title__intro");
		signScene(sign, scene, {
			flag: "wholesale",
			title: text && text.querySelector("h1"),
			eyebrow: text && text.querySelector(".eyebrow"),
			media,
			img: media && media.querySelector("img"),
			plane: plate,
			liner: plate,
			stripes: plate ? [...plate.querySelectorAll(":scope > .pinstripe")] : [],
			held: [plate, text, aside, media],
			focus: HOPPER,
			// beside the sign at once, under it on a narrow screen
			seen: true,
			exact: true,
			// cream paint on the brick: the wet coat reads lighter than the dry paint
			letters: { perChar: [0.058, 0.064] },
			// the rule under the name is ruled as the brush lifts, then the line beside it written, the light crosses the photo
			then: [["rule", 1, 1.2, 0.9], ["write", intro, 1.3, 0.6], ["sheen", 1.2, 0.9]],
		});
	}
	document.querySelectorAll('[data-motion="wholesale-board"]').forEach((terms) => boardIn(terms, scene));
	document.querySelectorAll('[data-motion="wholesale-ticket"]').forEach((panel) =>
		formInk(panel, scene, {
			field: {
				// the line under each field is ruled in the ticket's red and lifted once the field is drawn
				ruling: { under: ".input", className: "wholesale-ruling", at: 0, duration: 0.5, ease: "power2.inOut", lift: [0.7, 0.25] },
				label: [0.1, 0.55],
				mark: { at: 0.72, size: 1.8, tilt: -7 },
				after: [".field__hint", 0.6, 0.3],
				band: { start: "top 92%", end: "top 62%" },
			},
			lines: [".choice__legend"],
			rise: [".form-panel__note"],
			correction: { rule: { className: "wholesale-ruling", at: 0.35, duration: 0.5, ease: "power2.inOut", lift: [1.05, 0.25] }, settle: true },
		}),
	);
	const sent = document.querySelector('[data-motion="wholesale-sent"]');
	// the ticket is stamped, the line under it written in, and the camera moves in on the crate
	if (sent) sentStamp(sent, scene, { flag: "wholesale", focus: CRATE, struck: { size: 1.5, tilt: -6, press: 0.3, ease: "power4.in", inkAt: 0.14, inkFor: 0.16 }, write: 0.75, light: 0.9 });
}
