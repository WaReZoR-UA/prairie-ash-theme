/*
Contact page scenes, loaded as their own chunk and built only under prefers-reduced-motion: no-preference.
The stylesheet holds the finished page; every start state is created here and every end state is the markup.
The flag on <html> (data-contact) is set in the head of the three Contact pages, so the first paint already
shows the faint title, and on the sent page the empty place where the note is stamped.

data-motion="contact-sign"   the title on paper: the line above written, the name lettered stroke by stroke over its
                             ghost in dark ink, the line under it written, and light crosses the photo of the long
                             table. As the page scrolls on, the lettering lifts as the nearest plane and the camera
                             moves in on the cups on the table
data-motion="contact-board"  phone, email and address as a lettered board: under each row a guide is ruled and the row
                             stands up off it, the links in it working at every moment
data-motion="contact-photo"  the cup handed across the bar is uncovered from the edge facing its text while the picture
                             settles into its crop
data-motion="contact-note"   the message form as a note written by hand, ink only: the heading, each label and the privacy
                             line are written in by the pen and each "(required)" is stamped once its label is written;
                             fields, the link and the button never move or fade. Its error summary is a correction note:
                             written in red with a red rule struck under it
data-motion="contact-sent"   the sent note: "Message sent" is struck on like a postmark and a rule pulled under it, the
                             line under it written in, light crosses the photo, and the camera moves in on the cloth
*/

import { gsap, ScrollTrigger } from "./gsap.js";
import { board } from "./paint.js";
import { signScene } from "./lettering.js";
import { inView, relative, uncover, facing } from "./motion-utils.js";
import { formInk, sentStamp } from "./form-ink.js";

// the cups on the long table, in title.jpg: the camera moves in on them
const TABLE = "40% 62%";
// the cup handed across the bar, in form.jpg: the picture settles onto it
const CUP = "50% 52%";
// the cloth under the barista's hand, in sent.jpg: the camera moves in on it
const CLOTH = "48% 64%";

// Phone, email and address ------------------------------------------------------------------------------

function boardIn(lv, { ctx, cleanups, alive, fontsReady }) {
	// the guides are placed on the type, so the board waits for the faces; the rows hold no layer, so a link is clicked
	// where it stands at every moment
	fontsReady.then(() => {
		if (!alive()) return;
		ctx.add(() => {
			board(lv, cleanups, {
				rows: ":scope > .contact-details__prose, :scope > .contact-details__links > li",
				guide: "contact-rule",
				edge: "bottom",
				ruled: { duration: 0.32, opacity: 0.5 },
				turn: { at: 0.18, from: 70, steps: [[-5, 0.4, "power2.in"], [1.6, 0.14, "sine.inOut"], [0, 0.14, "sine.out"]], perspective: 900 },
				ink: 0.22,
				fade: [0.74, 0.22],
				// a row already on the screen when the motion arrives keeps its finished look
				onScreen: () => {},
			});
			ScrollTrigger.refresh();
		});
	});
}

// Photo beside the form -----------------------------------------------------------------------------------

function photoIn(slot, { cleanups }) {
	const img = slot.querySelector("img");
	const text = slot.parentElement.querySelector(".contact-message__body");
	// a photo already on the screen when the motion arrives is left as it is
	if (!img || inView(slot)) return;
	relative(slot, cleanups);
	uncover(slot, img, { shut: facing(slot, text), from: { scale: 1.12, transformOrigin: CUP }, to: { scale: 1 } });
	cleanups.push(() => gsap.set([slot, img], { clearProps: "clipPath,transform,transformOrigin" }));
}

export function contactMotion({ ctx, cleanups, alive, fontsReady, wide }) {
	const scene = { ctx, cleanups, alive, fontsReady, wide };
	const sign = document.querySelector('[data-motion="contact-sign"]');
	if (sign) {
		const text = sign.querySelector(".contact-title__text");
		const media = sign.querySelector(".contact-title__media");
		const intro = text && text.querySelector(".contact-title__intro");
		signScene(sign, scene, {
			flag: "contact",
			title: text && text.querySelector("h1"),
			eyebrow: text && text.querySelector(".contact-title__eyebrow"),
			media,
			img: media && media.querySelector("img"),
			plane: text,
			held: [text, media],
			focus: TABLE,
			// ink on paper keeps its paint as the page scrolls on
			weather: false,
			exact: true,
			// dark ink on paper: the wet coat reads deeper than the dry paint
			letters: { perChar: [0.064, 0.07], wet: "darker" },
			// the line under the name is written as the brush lifts, then the light crosses the window
			then: [["write", intro, 1, 1.2], ["sheen", 1.2, 0.9]],
		});
	}
	document.querySelectorAll('[data-motion="contact-board"]').forEach((lv) => boardIn(lv, scene));
	document.querySelectorAll('[data-motion="contact-photo"]').forEach((slot) => photoIn(slot, scene));
	document.querySelectorAll('[data-motion="contact-note"]').forEach((panel) =>
		formInk(panel, scene, {
			ease: "none",
			field: {
				label: [0, 0.6],
				mark: { at: 0.66, size: 1.7, tilt: 6, press: 0.24 },
				// on the error state the reason is written in red under the label
				after: [".form__error", 0.62, 0.3],
				band: { start: "top 92%", end: "top 64%" },
			},
			lines: [".form-panel__note"],
			// the red rule struck under the correction note is lifted once the line stands corrected
			correction: { rule: { className: "contact-strike", at: 0.4, duration: 0.45, ease: "power2.in", lift: [0.95, 0.25] }, settle: true },
		}),
	);
	const sent = document.querySelector('[data-motion="contact-sent"]');
	// the note is postmarked: struck crooked and hard, square at contact, a rule pulled under it like the cancel line
	if (sent)
		sentStamp(sent, scene, {
			flag: "contact",
			focus: CLOTH,
			struck: { size: 1.6, tilt: -9, press: 0.28, ease: "power4.in", inkAt: 0.13, inkFor: 0.15 },
			cancel: { className: "contact-cancel", at: 0.62, duration: 0.45, ease: "power2.inOut", lift: [1.3, 0.35] },
			write: 0.8,
			light: 0.95,
			faces: true,
		});
}
