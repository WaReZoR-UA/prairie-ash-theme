/*
About page scenes, loaded as their own chunk and built only under prefers-reduced-motion: no-preference.
The stylesheet holds the finished page; every start state is created here and every end state is the markup.
The flag on <html> (data-about) is set in the head of about.html, so the first paint already shows the faint
sign on the dark wall.

data-motion="about-sign"    the sign on the dark wall: the rule is ruled, the line above it written and the name
                            lettered stroke by stroke over its ghost, the story under it written in, and light
                            crosses the photo of the empty cafe. As the page scrolls on, the sign lifts as the
                            nearest plane and weathers back into a ghost while the camera moves in on the roaster
                            behind its glass wall
data-motion="about-person"  one of the people: the portrait is uncovered from the side that faces the text, the name
                            lettered on the wall like a small sign over its ghost, the line about them rising after it
*/

import { gsap, ScrollTrigger } from "./gsap.js";
import { letterSign, signScene } from "./lettering.js";
import { relative, onScroll, uncover, facing, riseIn } from "./motion-utils.js";

// the roaster behind its glass wall, in story.jpg: the camera moves in on it
const ROASTER = "70% 50%";

// People --------------------------------------------------------------------------------------------

function personIn(person, late, { ctx, cleanups, alive, fontsReady }) {
	const media = person.querySelector(".person__media");
	const img = media && media.querySelector("img");
	const text = person.querySelector(".person__text");
	const name = person.querySelector(".person__name");
	const bio = person.querySelector(".person__bio");
	if (!img || !text) return;
	const at = (spec, by) => `top ${parseFloat(spec.split(" ")[1]) - by * 100}%`;
	relative(media, cleanups);
	uncover(media, img, { shut: facing(media, text), from: { scale: 1.14, transformOrigin: "50% 40%" }, to: { scale: 1 }, end: at("top 45%", late) });
	cleanups.push(() => gsap.set([media, img], { clearProps: "clipPath,transform,transformOrigin" }));
	if (bio) {
		riseIn(bio);
		cleanups.push(() => gsap.set(bio, { clearProps: "opacity,transform" }));
	}
	if (!name) return;
	// the name waits for its face: the brush measures the letters
	gsap.set(name, { opacity: 0 });
	cleanups.push(() => gsap.set(name, { clearProps: "opacity" }));
	fontsReady.then(() => {
		if (!alive()) return;
		ctx.add(() => {
			relative(text, cleanups);
			// cream paint on the brick: lettered over the ghost of the old sign, measured square while the band turns in
			const letters = letterSign(name, cleanups, { perChar: 0.07, dip: 0.12, flat: true });
			// the name is up before the line under it has risen
			onScroll(name, letters.tl, { start: at("top 92%", late), end: at("top 66%", late) });
			ScrollTrigger.refresh();
		});
	});
}

export function aboutMotion({ ctx, cleanups, alive, fontsReady, wide }) {
	const scene = { ctx, cleanups, alive, fontsReady, wide };
	const sign = document.querySelector('[data-motion="about-sign"]');
	if (sign) {
		const heading = sign.querySelector(".about-story__heading");
		const media = sign.querySelector(".about-story__media");
		const body = sign.querySelector(".about-story__body");
		signScene(sign, scene, {
			flag: "about",
			title: heading && heading.querySelector("h1"),
			eyebrow: heading && heading.querySelector(".about-story__eyebrow"),
			media,
			img: media && media.querySelector("img"),
			plane: heading,
			stripes: [sign.querySelector(":scope > .pinstripe")].filter(Boolean),
			held: [sign, heading, media, body && body.parentElement],
			focus: ROASTER,
			// a ghost once the foot of the sign is near the top of the window
			ghost: () => (heading.offsetTop + heading.offsetHeight) * 0.9,
			// cream paint on the dark wall: the wet coat reads lighter than the dry paint
			letters: { perChar: [0.058, 0.064] },
			// the light crosses the glass of the empty cafe once the name is up, then the story is written
			then: [["sheen", 1.2, 1], ["write", body, 1, 1.2]],
			story: { written: [body], slid: [sign.querySelector(".about-story__text .text-link")] },
		});
	}
	const people = [...document.querySelectorAll('[data-motion="about-person"]')];
	// side by side, the second person steps out of the wall a beat after the first
	people.forEach((person, i) => personIn(person, i && person.offsetTop === people[i - 1].offsetTop ? 0.06 : 0, scene));
}
