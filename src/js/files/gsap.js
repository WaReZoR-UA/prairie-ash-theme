/*
GSAP module: scroll reveals driven by data attributes, plus the registered plugins for custom code
Template usage: uncomment import "./files/gsap.js"; in js/app.js
Plugin docs: https://gsap.com/docs/v3/
License: GSAP Standard License, no charge, commercial and client work allowed: https://gsap.com/standard-license
Snippet (HTML): <div data-gsap="fade-up"></div>

Attributes:
data-gsap - reveal preset: fade, fade-up, fade-down, fade-left, fade-right, zoom-in, zoom-out
data-gsap-children - animate the direct children instead of the element itself, value is the stagger in seconds (default 0.1)
data-gsap-duration - duration in seconds (default 0.8)
data-gsap-delay - delay in seconds (default 0)
data-gsap-distance - offset in px for the fade-up, fade-down, fade-left and fade-right presets (default 40)
data-gsap-start - ScrollTrigger start (default "top 85%")
data-gsap-end - ScrollTrigger end, used together with data-gsap-scrub (default "top 40%")
data-gsap-scrub - tie the progress to the scroll position, empty value or a smoothing time in seconds
data-gsap-once - play once and keep the final state (by default the reveal reverses when scrolled back above the start)

Rules:
- Only transform and opacity are animated.
- The element keeps its finished look in CSS. Never hide [data-gsap] in a stylesheet, the module creates the start state itself.
- With prefers-reduced-motion: reduce nothing is animated, the element stays as the stylesheet draws it.
- Every tween and ScrollTrigger is created inside gsap.matchMedia(), which reverts them when the condition stops matching.
  ScrollTrigger recalculates its positions on resize by itself.
- After markup is added or replaced on the fly call flsModules.gsap.update()
*/

// Template functionality
import { FLS } from "./functions.js";
// List of active modules
import { flsModules } from "./modules.js";

// GSAP from node_modules
import { gsap } from "gsap";
import { ScrollTrigger } from "gsap/ScrollTrigger";
import { ScrollToPlugin } from "gsap/ScrollToPlugin";
import { CustomEase } from "gsap/CustomEase";
import { SplitText } from "gsap/SplitText";
import { DrawSVGPlugin } from "gsap/DrawSVGPlugin";
/*
Other plugins, import and add to gsap.registerPlugin() when needed:
import { Flip } from "gsap/Flip";
import { Observer } from "gsap/Observer";
import { MotionPathPlugin } from "gsap/MotionPathPlugin";
import { MorphSVGPlugin } from "gsap/MorphSVGPlugin";
import { ScrambleTextPlugin } from "gsap/ScrambleTextPlugin";
import { TextPlugin } from "gsap/TextPlugin";
import { Draggable } from "gsap/Draggable";
import { InertiaPlugin } from "gsap/InertiaPlugin";
import { ScrollSmoother } from "gsap/ScrollSmoother";
import { CustomBounce } from "gsap/CustomBounce";
import { CustomWiggle } from "gsap/CustomWiggle";
*/

gsap.registerPlugin(ScrollTrigger, ScrollToPlugin, CustomEase, SplitText, DrawSVGPlugin);

// Start state of every preset, offsets are multiplied by data-gsap-distance
const gsapPresets = {
	'fade': {},
	'fade-up': { y: 1 },
	'fade-down': { y: -1 },
	'fade-left': { x: 1 },
	'fade-right': { x: -1 },
	'zoom-in': { scale: 0.85 },
	'zoom-out': { scale: 1.15 },
}

// Builds the reveal of one [data-gsap] element
function gsapRevealItem(item) {
	const preset = gsapPresets[item.dataset.gsap] ? gsapPresets[item.dataset.gsap] : gsapPresets['fade-up'];
	const distance = item.dataset.gsapDistance ? parseFloat(item.dataset.gsapDistance) : 40;
	const isChildren = item.hasAttribute('data-gsap-children');
	const isScrub = item.hasAttribute('data-gsap-scrub');
	const isOnce = item.hasAttribute('data-gsap-once');

	const fromVars = {
		opacity: 0,
		duration: item.dataset.gsapDuration ? parseFloat(item.dataset.gsapDuration) : 0.8,
		delay: item.dataset.gsapDelay ? parseFloat(item.dataset.gsapDelay) : 0,
		ease: isScrub ? 'none' : 'power2.out',
	}
	if (preset.x) fromVars.x = preset.x * distance;
	if (preset.y) fromVars.y = preset.y * distance;
	if (preset.scale) fromVars.scale = preset.scale;
	if (isChildren) fromVars.stagger = item.dataset.gsapChildren ? parseFloat(item.dataset.gsapChildren) : 0.1;

	const scrollTrigger = {
		trigger: item,
		start: item.dataset.gsapStart ? item.dataset.gsapStart : 'top 85%',
	}
	if (isScrub) {
		scrollTrigger.end = item.dataset.gsapEnd ? item.dataset.gsapEnd : 'top 40%';
		scrollTrigger.scrub = item.dataset.gsapScrub ? parseFloat(item.dataset.gsapScrub) : true;
	} else if (isOnce) {
		scrollTrigger.once = true;
	} else {
		scrollTrigger.toggleActions = 'play none none reverse';
	}
	fromVars.scrollTrigger = scrollTrigger;

	gsap.from(isChildren ? item.children : item, fromVars);
}

// Scroll reveals for every [data-gsap] element on the page
export function gsapReveal() {
	const items = document.querySelectorAll('[data-gsap]');
	if (!items.length) return null;
	const mm = gsap.matchMedia();
	// Nothing is created for prefers-reduced-motion: reduce.
	// When the condition stops matching, matchMedia reverts every tween and ScrollTrigger made here.
	mm.add("(prefers-reduced-motion: no-preference)", () => {
		items.forEach(item => gsapRevealItem(item));
	});
	FLS(`[GSAP]: reveals created (${items.length})`);
	return mm;
}

// Start and add to the modules object
let gsapMedia = gsapReveal();
flsModules.gsap = {
	gsap,
	ScrollTrigger,
	// Rebuild the reveals after markup changed on the fly
	update() {
		if (gsapMedia) gsapMedia.revert();
		gsapMedia = gsapReveal();
		ScrollTrigger.refresh();
	},
}

export { gsap, ScrollTrigger };
