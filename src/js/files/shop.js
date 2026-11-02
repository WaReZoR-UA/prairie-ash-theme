/*
Shop page scenes, loaded as their own chunk and built only under prefers-reduced-motion: no-preference.
The stylesheet holds the finished page; every start state is created here and every end state is the markup.

data-motion="shop-sign"   the bag label beside the shelf photo: ruled, written and lettered on load over its own
                          ghost; as the page scrolls on it lifts and the camera moves down the shelf into the daylight
data-motion="shop-tools"  search and filter: rise in on load at the fold, or with the scroll further down
data-motion="shop-list"   the coffee list as a shelf, renamed "shop-shelf" once this module has the filter: each bag stands up on the shelf edge, the edge is ruled and
                          the signs swing down from it with their prices stamped on,
                          and the sold-out notice struck after its price; the next-roast note answers it.
                          The brew filter turns leaving bags on their hooks and carries the rest to their places,
                          and the count line is wiped and lettered again by the brush
*/

import { gsap, ScrollTrigger } from "./gsap.js";
import { Flip } from "gsap/Flip";
import { rule, pen, stamp } from "./paint.js";
import { letterSign, endOnResize } from "./lettering.js";
import { smooth, pageTop, pageLeft, relative, layer, redrawOnRefresh, onScroll, swing, uncover, sheen, freshSign, signLettered, tipUp, riseIn } from "./motion-utils.js";

// the daylight on the kraft bags, sampled from title.jpg (lit paper of the bags, 70th percentile)
const KRAFT = "204, 181, 154";

// Title ---------------------------------------------------------------------------------------------

function titleScene(sign, { ctx, cleanups, alive, fontsReady }) {
	const later = (fn) => () => alive() && ctx.add(fn);
	const root = document.documentElement;
	const section = sign.closest("section") || sign;
	const label = sign.querySelector(".shop-title__label");
	const title = label && label.querySelector("h1");
	const eyebrow = label && label.querySelector(".shop-title__eyebrow");
	const intro = label && label.querySelector(".shop-title__intro");
	const stripes = label ? [...label.querySelectorAll(":scope > .pinstripe")] : [];
	const media = sign.querySelector(".shop-title__media");
	const img = media && media.querySelector("img");
	if (!title || !img) {
		delete root.dataset.shop;
		return;
	}
	const phone = window.matchMedia("(max-width: 47.99875em)").matches;
	const fresh = freshSign("shop", section);
	relative(label, cleanups);

	// daylight from the shop window settles on the bags as the camera moves down the shelf
	const light = layer(media, "shop-light", cleanups);
	light.style.background = `radial-gradient(ellipse 46% 52% at 60% 46%, rgba(${KRAFT}, 0.95), rgba(${KRAFT}, 0.35) 55%, rgba(${KRAFT}, 0) 100%)`;

	const state = { p: 0, settle: 1 };
	let st = null;
	const apply = () => {
		const p = state.p;
		// scroll travelled since the top of the page, the trigger runs from 0 to the section leaving
		const scrolled = st ? p * (st.end - st.start) : 0;
		const e = smooth(0, 0.8, p);
		light.style.opacity = (0.75 * smooth(0.06, 0.7, p)).toFixed(3);
		// at the top of the page nothing is left on a layer of its own, so the type renders exactly as drawn
		if (scrolled < 0.5 && state.settle === 1) gsap.set([img, label], { clearProps: "transform" });
		else {
			// the camera travels down the shelf towards the bag in the glove
			gsap.set(img, { scale: state.settle * (1 + 0.12 * e), xPercent: 2.5 * e, transformOrigin: "66% 44%", force3D: true });
			// the label is the nearest plane: it lifts faster than the page
			gsap.set(label, { y: -scrolled * (phone ? 0.05 : 0.12), force3D: true });
		}
	};
	redrawOnRefresh(apply, cleanups);
	cleanups.push(() => {
		gsap.set([img, label], { clearProps: "transform,transformOrigin" });
		[title, eyebrow, intro, ...stripes].forEach((t) => t && (t.style.opacity = ""));
	});
	st = gsap.to(state, {
		p: 1,
		ease: "none",
		onUpdate: apply,
		scrollTrigger: { trigger: section, start: 0, end: "bottom top", scrub: phone ? 0.6 : true, invalidateOnRefresh: true },
	}).scrollTrigger;

	if (!fresh) {
		signLettered("shop");
		apply();
		return;
	}

	// On load: the first rule is ruled, the eyebrow written, the name lettered over its ghost, the intro written,
	// the second rule ruled, while the shelf photo settles into its frame and daylight crosses the bags
	const faint = [title, eyebrow, intro, ...stripes].filter(Boolean);
	gsap.set(faint, { opacity: 0.3 });
	root.dataset.shop = "letter";
	state.settle = 1.06;
	apply();
	cleanups.push(() => {
		root.dataset.shop = "live";
		gsap.set(faint, { clearProps: "opacity" });
	});

	fontsReady.then(later(() => {
		const act = gsap.timeline();
		const parts = [];
		// the pen writes in full ink over the faint line, which takes over once it is written
		const write = (el, at, pace) => {
			const p = pen(act, el, at, cleanups, { pace });
			if (p) parts.push(p);
		};
		if (stripes[0]) parts.push(rule(act, stripes[0], 0, { liner: label, cleanups, duration: phone ? 0.6 : 0.75 }));
		write(eyebrow, 0.2, 1.3);
		// dark ink on paper: the wet coat reads deeper than the dry paint, as the light paint on Home's brick
		// reads brighter, rather than a pale grey that darkens as it dries
		const letters = letterSign(title, cleanups, { perChar: phone ? 0.07 : 0.064, dip: 0.09, wet: "darker" });
		act.add(letters.tl, 0.4);
		parts.push(letters);
		const lettersEnd = 0.4 + letters.tl.duration();
		write(intro, Math.max(0.9, lettersEnd - 1.4), 2.2);
		if (stripes[1]) parts.push(rule(act, stripes[1], Math.max(1.3, lettersEnd - 0.9), { liner: label, cleanups, duration: phone ? 0.55 : 0.7 }));
		act.to(state, { settle: 1, duration: 1.8, ease: "expo.out", onUpdate: apply }, 0.1);
		sheen(media, cleanups, { tl: act, at: 0.8 });
		act.eventCallback("onComplete", () => {
			signLettered("shop");
			// every faint line is finished here, whether or not its pen reported back on its own
			gsap.set(faint, { clearProps: "opacity" });
			parts.forEach((part) => part.remove());
			apply();
		});
		endOnResize(act, cleanups);
		cleanups.push(() => act.kill());
	}));
}

// Search and filter: standing at the fold on load they rise in once the label is begun, so the fold never
// shows an empty strip; further down the page they rise with the scroll like any text
// The fold is read once the faces are in: the fallback face sets the label taller and pushes the fields down.
function toolsIn(scope, { ctx, cleanups, alive, fontsReady }) {
	const parts = [...scope.querySelectorAll(".shop-tools__search > *, .shop-tools__legend, .options__item")];
	if (!parts.length) return;
	const t0 = performance.now();
	gsap.set(parts, { opacity: 0, y: 16 });
	cleanups.push(() => gsap.set(parts, { clearProps: "opacity,transform" }));
	fontsReady.then(() => {
		if (!alive()) return;
		ctx.add(() => {
			if (window.scrollY < 1 && scope.getBoundingClientRect().top < window.innerHeight) {
				const delay = Math.max(0, 0.6 - (performance.now() - t0) / 1000);
				gsap.to(parts, { opacity: 1, y: 0, duration: 0.7, ease: "power2.out", stagger: 0.06, delay, clearProps: "opacity,transform" });
				return;
			}
			parts.forEach((el) => riseIn(el));
		});
	});
}

// The shelf ------------------------------------------------------------------------------------------

// A bag stands up on the shelf edge while its photo crosses the eye band: it leans back against the wall,
// is uncovered from the edge up as on Home, and comes upright as the picture settles into its drawn crop
function bagIn(card, cleanups) {
	const slot = card.querySelector(".product-card__media, .shop-list__next-media");
	const img = slot && slot.querySelector("img");
	if (!slot || !img) return;
	uncover(slot, img, {
		shut: "inset(100% 0% 0% 0%)",
		from: { scale: 1.12, transformOrigin: "50% 100%" },
		also: (tl) => swing(tl, slot, 0, { from: 28, steps: [[-1.6, 0.78, "power2.inOut"], [0, 0.22, "sine.out"]], hinge: { transformPerspective: 1100, transformOrigin: "50% 100%" } }),
	});
	cleanups.push(() => gsap.set([slot, img], { clearProps: "transform,transformOrigin,clipPath" }));
}

// The next roast is not a sign but a note left on the empty place on the shelf: the date is written by the
// pen, the heading tips up off the rule line by line and the body rises into its lines
function noteOn(tl, note, at, cleanups) {
	const [date, heading, body] = [".shop-list__next-label", ".shop-list__next-heading", ".shop-list__next-body"].map((q) => note.querySelector(q));
	relative(note, cleanups);
	if (date) {
		// the pen writes on a copy laid over the date, which takes over once the last line is written
		pen(tl, date, at, cleanups, { ink: "line", scrub: true, duration: 0.22 });
	}
	if (heading) {
		tipUp(tl, heading, at + 0.12, cleanups, { rise: 16, duration: 0.24, stagger: 0.06 });
	}
	if (body) tl.fromTo(body, { opacity: 0, y: 16 }, { opacity: 1, y: 0, duration: 0.26, ease: "power2.out" }, at + 0.24);
	cleanups.push(() => gsap.set([date, body].filter(Boolean), { clearProps: "transform,opacity" }));
}

// A row of the shelf, by the scroll: as the shelf edge under the row comes up the window, the edge is ruled
// in one pass from the left and each sign swings down from it and hangs flat. The edge is the cream rule the
// design draws at the top of every sign; the sign turns about it, so it never moves.
function shelfRow(row, cleanups) {
	const stripes = row.map((card) => card.querySelector('[data-motion="stripe"]'));
	const left = pageLeft(stripes[0]);
	const span = Math.max(1, pageLeft(stripes[row.length - 1]) + stripes[row.length - 1].offsetWidth - left);
	// a full shelf takes longer to rule than a single sign on a phone
	const RULE = 0.12 + 0.06 * Math.min(3, row.length);
	const tl = gsap.timeline({ defaults: { ease: "none" } });
	// on a shelf that holds the sold-out bag, the note answers its notice: it is written once the notice lands
	let struck = 0;
	row.forEach((card, col) => {
		const stripe = stripes[col];
		const sign = card.querySelector(".coffee-sign");
		const price = sign && sign.querySelector('[data-motion="price"]');
		const lines = sign ? [...sign.querySelectorAll(".coffee-sign__body > *")].filter((el) => el !== price) : [];
		const sold = sign && sign.querySelector(".coffee-sign__button:disabled");
		// the liner runs on along the shelf, so each rule is drawn when the pass reaches it
		const from = (RULE * (pageLeft(stripe) - left)) / span;
		const ruled = from + (RULE * stripe.offsetWidth) / span;
		rule(tl, stripe, from, { duration: ruled - from, ease: null });
		if (sign) {
			// the lettered board turns down from the rule, swings once past flat and comes to rest
			const at = ruled - 0.08;
			swing(tl, sign, at, { from: -75, steps: [[9, 0.3, "power2.in"], [-3.5, 0.13, "sine.inOut"], [0, 0.13, "sine.out"]], hinge: { transformPerspective: 1100, transformOrigin: "50% 0%" } })
				.fromTo(lines, { opacity: 0 }, { opacity: 1, duration: 0.2, ease: "power1.in" }, at);
			if (price) stamp(tl, price, at + 0.34, sign);
			// sold out: the notice on the disabled button is struck onto the board after the price, heavier and
			// set crooked by the hand, landing square in the button's own faded ink
			if (sold) {
				stamp(tl, sold, at + 0.48, sign, { size: 1.9, tilt: 5, ink: parseFloat(getComputedStyle(sold).opacity) || 1 });
				struck = at + 0.48 + 0.24;
			}
		}
		const note = card.querySelector(".shop-list__next-text");
		if (note) noteOn(tl, note, Math.max(ruled - 0.04, struck + 0.04), cleanups);
		cleanups.push(() => gsap.set([stripe, sign, price, sold, ...lines].filter(Boolean), { clearProps: "transform,transformOrigin,opacity" }));
	});
	return tl;
}
// Rows are taken from the layout, so the filter, which reflows the shelf, builds them again
let shelf = null;
function shelfIn(grid, cleanups) {
	const cards = [...grid.children];
	cards.filter((card) => card.offsetParent).forEach((card) => bagIn(card, cleanups));
	const built = [];
	const build = () => {
		const rows = new Map();
		cards.forEach((card) => {
			const stripe = card.querySelector('[data-motion="stripe"]');
			if (!stripe || !card.offsetParent) return;
			// one shelf: a rule a fraction lower than its neighbours still belongs to their row
			const y = pageTop(stripe);
			const top = [...rows.keys()].find((k) => Math.abs(k - y) < 4) ?? y;
			if (!rows.has(top)) rows.set(top, []);
			rows.get(top).push(card);
		});
		rows.forEach((row) => {
			row.sort((a, b) => pageLeft(a) - pageLeft(b));
			const undo = [];
			const tl = shelfRow(row, undo);
			// the shelf edge enters at the foot of the window and the row hangs by the time it is two fifths up
			const st = onScroll(row[0].querySelector('[data-motion="stripe"]'), tl, { start: "top 98%", end: "top 40%" });
			built.push({ st, tl, undo });
		});
	};
	const teardown = () => built.splice(0).forEach(({ st, tl, undo }) => {
		st.kill();
		tl.revert();
		undo.reverse().forEach((fn) => fn());
	});
	build();
	cleanups.push(() => {
		teardown();
		shelf = null;
	});
	shelf = {
		// a row that is already hanging where the scroll stands is rebuilt hanging, not swung again
		rebuild() {
			teardown();
			build();
			ScrollTrigger.refresh();
			built.forEach(({ st, tl }) => {
				const tween = st.getTween && st.getTween();
				if (tween) tween.progress(1);
				else tl.progress(st.progress);
			});
		},
	};
}

// The filter takes bags off the shelf and puts the others in their new places: a leaving card is turned on its
// hook like a shelf tag and goes back into the dark, the cards that stay are lifted off the shelf, carried and
// set down where the reflowed grid puts them, and a card that comes back is turned round to face the room.
// The whole move is 0.7 s and a new choice finishes the running one at once, so it never holds up shopping.
let moving = null;
function shelfMove(grid, apply) {
	if (moving) moving.progress(1);
	const items = [...grid.children];
	const shown = new Set(items.filter((el) => !el.hidden));
	const before = new Map(items.map((el) => [el, el.getBoundingClientRect()]));
	const state = Flip.getState(items);
	apply();
	if (shelf) shelf.rebuild();
	else ScrollTrigger.refresh();
	const movers = items.filter((el) => {
		if (el.hidden || !shown.has(el)) return false;
		const a = before.get(el);
		const b = el.getBoundingClientRect();
		return Math.abs(a.left - b.left) > 1 || Math.abs(a.top - b.top) > 1;
	});
	// seen from far enough and pushed back far enough that the near edge of a turning tag stays inside its row
	const tag = { transformPerspective: 1600, transformOrigin: "50% 0%" };
	moving = Flip.from(state, {
		duration: 0.62,
		ease: "power3.inOut",
		absoluteOnLeave: true,
		onLeave: (els) => gsap.fromTo(els, { opacity: 1, rotationY: 0, z: 0, ...tag }, { opacity: 0, rotationY: 70, z: -220, duration: 0.32, ease: "power2.in", stagger: 0.03 }),
		onEnter: (els) => gsap.fromTo(els, { opacity: 0, rotationY: -70, z: -220, ...tag }, { opacity: 1, rotationY: 0, z: 0, duration: 0.46, ease: "power3.out", stagger: 0.05, delay: 0.2 }),
		onComplete: () => {
			gsap.set(items, { clearProps: "transform,transformOrigin,opacity,zIndex" });
			moving = null;
			ScrollTrigger.refresh();
		},
	});
	// carried, not slid: lifted towards the room and leaning into the way it travels, then set down square
	movers.forEach((el) => {
		const dx = el.getBoundingClientRect().left - before.get(el).left;
		gsap.set(el, { zIndex: 2 });
		moving.fromTo(el, { scale: 1, rotation: 0 }, { keyframes: { scale: [1, 1.04, 1], rotation: [0, dx < 0 ? -1.4 : 1.4, 0], ease: "sine.inOut" }, duration: 0.62, ease: "none" }, 0);
	});
}
// The count line follows the filter: the old line is wiped off the wall and the new one is lettered in by a
// brush loaded with its own colour. The real line changes at once and keeps its voice in the live region; the
// motion runs on aria-hidden copies laid over it, lent its ink while they work.
let recount = null;
const undoCount = [];
function settleCount() {
	if (recount) recount.kill();
	recount = null;
	undoCount.splice(0).reverse().forEach((fn) => fn());
}
function rewriteCount(count, text) {
	if (!count || !text || text === count.textContent) return;
	settleCount();
	const old = count.textContent;
	// the used width, fractions kept: a copy a fraction narrower breaks its line where the real one does not
	const used = () => parseFloat(getComputedStyle(count).width) + 0.5;
	const oldWidth = used();
	count.textContent = text;
	// a line the scroll has not inked yet is simply changed
	if (parseFloat(getComputedStyle(count).opacity) < 0.99) return;
	relative(count.parentElement, undoCount);
	count.style.color = "transparent";
	undoCount.push(() => (count.style.color = ""));
	const copy = (line, width) => {
		const c = document.createElement("span");
		c.className = `${count.className} shop-count-copy`;
		c.setAttribute("aria-hidden", "true");
		c.textContent = line;
		Object.assign(c.style, { left: `${count.offsetLeft}px`, top: `${count.offsetTop}px`, width: `${width}px` });
		count.parentElement.append(c);
		undoCount.push(() => c.remove());
		return c;
	};
	const gone = copy(old, oldWidth);
	const fresh = copy(text, used());
	recount = gsap.timeline({ onComplete: settleCount })
		.fromTo(gone, { clipPath: "inset(-20% 0% -20% 0%)" }, { clipPath: "inset(-20% 0% -20% 100%)", duration: 0.19, ease: "power2.in" }, 0)
		.add(letterSign(fresh, undoCount, { mode: "lines", pass: 0.42, gap: 0.12, ease: "power2.inOut", rise: 0, fade: false, paint: true }).tl, 0.1);
}
// The filter on the page's own fieldset. The motion layer keeps the filter until the shelf is renamed
// "shop-shelf" here, so a click before this chunk arrives still filters; under reduced motion it is the
// motion layer's again.
function filterIn(grid, cleanups) {
	const filter = document.querySelector("[data-shop-filter]");
	if (!filter) return;
	const cards = [...grid.querySelectorAll("[data-brew]")];
	const count = document.querySelector("[data-shop-count]");
	const onChange = (e) => {
		if (e.target.type !== "radio") return;
		const value = e.target.value;
		const wanted = (card) => value === "all" || card.dataset.brew.split(" ").includes(value);
		shelfMove(grid, () => {
			cards.forEach((card) => (card.hidden = !wanted(card)));
			rewriteCount(count, count && count.getAttribute(`data-count-${value}`));
		});
	};
	filter.addEventListener("change", onChange);
	grid.dataset.motion = "shop-shelf";
	cleanups.push(() => {
		grid.dataset.motion = "shop-list";
		filter.removeEventListener("change", onChange);
		if (moving) moving.progress(1);
		settleCount();
	});
}

export function shopMotion({ ctx, cleanups, alive, fontsReady }) {
	const sign = document.querySelector('[data-motion="shop-sign"]');
	if (sign) titleScene(sign, { ctx, cleanups, alive, fontsReady });
	const tools = document.querySelector('[data-motion="shop-tools"]');
	if (tools) toolsIn(tools, { ctx, cleanups, alive, fontsReady });
	const grid = document.querySelector('[data-motion="shop-list"]');
	// the shelf measures type and stamps, so it waits for the faces
	if (grid) {
		filterIn(grid, cleanups);
		fontsReady.then(() => {
			if (!alive()) return;
			ctx.add(() => {
				shelfIn(grid, cleanups);
				ScrollTrigger.refresh();
			});
		});
	}
	ScrollTrigger.refresh();
}
