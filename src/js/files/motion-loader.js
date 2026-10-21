// The motion layer (GSAP and its plugins) is fetched once the page has loaded, has been shown (a
// prerendered page waits for activation), has painted its largest content and its page transition has
// finished, so it never restyles what the transition is painting and never starts inside the LCP window
let motion = null;
let loaded = null;
export const loadMotion = () => motion || (motion = import(/* webpackChunkName: "motion" */ "./motion.js").then((m) => (loaded = m)));
// the module once it has arrived, never a request of its own
export const motionNow = () => loaded;

const shown = () => (document.prerendering ? new Promise((r) => document.addEventListener("prerenderingchange", r, { once: true })) : Promise.resolve());
const loadedPage = () => (document.readyState === "complete" ? Promise.resolve() : new Promise((r) => window.addEventListener("load", r, { once: true })));
// the head boot script resolves window.paReveal when the view transition is over; 2.5 s is the backstop
const revealed = () => Promise.race([window.paReveal || Promise.resolve(), new Promise((r) => setTimeout(r, 2500))]);
// The largest paint is on screen: the page's LCP image (the one image printed with fetchpriority="high")
// is complete, a frame has been presented after that, and the browser has reported a largest paint.
// Without this, load and idle time can come before the first frame on a fast connection, and the motion
// chunks then compete with the LCP image for the network.
const largestPainted = () => {
	const img = document.querySelector('img[fetchpriority="high"]');
	const complete = !img || img.complete ? Promise.resolve() : new Promise((r) => ["load", "error"].forEach((k) => img.addEventListener(k, r, { once: true })));
	const frame = () => new Promise((r) => requestAnimationFrame(() => requestAnimationFrame(r)));
	const types = (window.PerformanceObserver && PerformanceObserver.supportedEntryTypes) || [];
	const reported = () =>
		types.includes("largest-contentful-paint")
			? Promise.race([
					new Promise((r) => {
						const po = new PerformanceObserver((list) => {
							if (!list.getEntries().length) return;
							po.disconnect();
							r();
						});
						po.observe({ type: "largest-contentful-paint", buffered: true });
					}),
					new Promise((r) => setTimeout(r, 2500)),
				])
			: Promise.resolve();
	return complete.then(frame).then(reported);
};

export function scheduleMotion() {
	const go = () => ("requestIdleCallback" in window ? requestIdleCallback(() => loadMotion(), { timeout: 700 }) : setTimeout(loadMotion, 150));
	shown()
		.then(() => Promise.all([loadedPage(), revealed()]))
		.then(largestPainted)
		.then(go);
}
