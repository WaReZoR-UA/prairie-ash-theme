/*
Signup confirmation scene, loaded as its own chunk and built only under prefers-reduced-motion: no-preference.
The stylesheet holds the finished page; every start state is created here and every end state is the markup.
The flag on <html> (data-signup) is set in the head of signup-confirmation.html, so the first paint already shows the
faint sign on the wall and the empty place where the roast notes line is stamped.

data-motion="signup-sign"  the page after the roast notes signup as one sign on the wall: the roast notes line is struck
                           like the postmark of a posted roast note, the name "Check your email to confirm" lettered stroke
                           by stroke over its ghost, the line about the email written in under it and light crosses the
                           window in the photo; the next steps, what comes from whom and the way back are written in by
                           the pen as the page scrolls (those already on the first screen at once, after the name). As the
                           page scrolls on, the sign lifts as the nearest plane and weathers back into a ghost while the
                           camera moves in on the cup on the windowsill
*/

import { gsap, ScrollTrigger } from "./gsap.js";
import { stamp } from "./paint.js";
import { signScene, endOnResize } from "./lettering.js";

// the cup steaming on the windowsill, in confirmation.jpg: the camera moves in on it
const CUP = "50% 72%";

// The roast notes line is struck like a postmark as the act opens, square before the brush reaches the name
function postmark(line, board, { ctx, cleanups, alive, fontsReady }) {
	const later = (fn) => () => alive() && ctx.add(fn);
	// the empty place of the first paint, now held inline
	gsap.set(line, { opacity: 0 });
	cleanups.push(() => gsap.set(line, { clearProps: "opacity,transform,transformOrigin" }));
	fontsReady.then(later(() => {
		const act = gsap.timeline();
		stamp(act, line, 0.05, board, { size: 1.6, tilt: -8, press: 0.28, ease: "power4.in", inkAt: 0.13, inkFor: 0.15 });
		act.eventCallback("onComplete", () => gsap.set(line, { clearProps: "opacity,transform,transformOrigin" }));
		endOnResize(act, cleanups);
		cleanups.push(() => act.kill());
		ScrollTrigger.refresh();
	}));
}

export function signupMotion({ ctx, cleanups, alive, fontsReady, wide }) {
	const scene = { ctx, cleanups, alive, fontsReady, wide };
	const sign = document.querySelector('[data-motion="signup-sign"]');
	if (!sign) return;
	const message = sign.querySelector(".signup-done__message");
	const heading = sign.querySelector(".signup-done__heading");
	const help = sign.querySelector(".signup-done__help");
	const media = sign.querySelector(".signup-done__media");
	if (!message || !heading) return;
	const line = heading.querySelector(".signup-done__eyebrow");
	const lead = heading.querySelector(".signup-done__lead");
	const rows = help ? [...help.querySelectorAll(".lv__row")] : [];
	// the next steps in reading order: the heading over them, then each step's name and what to do
	const steps = [help && help.querySelector(".signup-done__help-title"), ...rows.flatMap((row) => [...row.querySelectorAll(".lv__label, .lv__value")])].filter(Boolean);
	signScene(sign, scene, {
		flag: "signup",
		title: heading.querySelector("h1"),
		media,
		img: media && media.querySelector("img"),
		plane: heading,
		held: [heading, help, ...rows, message, media],
		focus: CUP,
		exact: true,
		// cream paint on the wall: the wet coat reads lighter than the dry paint
		letters: { perChar: [0.058, 0.064] },
		// the line about the email is written as the brush lifts, the light crosses the window, then the steps on the
		// first screen are written one after another
		then: [["write", lead, 1, 1.1], ["sheen", 1.2, 0.8], ...steps.map((el, i) => ["write", el, 1.4 + 0.12 * i, 0.5 - 0.16 * i])],
		story: { written: [...steps, message.querySelector(".signup-done__sender")], slid: [...message.querySelectorAll(".signup-done__links .text-link")] },
	});
	// the act runs only on a page view fresh enough for the sign: the flag then reads "letter"
	if (line && document.documentElement.dataset.signup === "letter") postmark(line, heading, scene);
}
