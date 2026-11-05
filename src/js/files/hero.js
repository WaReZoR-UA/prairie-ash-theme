/*
Home hero scene, built only under prefers-reduced-motion: no-preference (the stylesheet holds the still frame).

Act 1, on load: the faded sign on the brick is lettered again by a brush, stroke by stroke, the rules are
ruled, the light comes on behind the glass, heat, steam and the pour wake up, the caption is written in.
Act 2, while the hero is on screen: the roastery keeps living, from the photograph's own pixels and from
particles coloured by it, driven by noise so nothing repeats. Paused off screen, in a hidden tab, or by
the visitor.
Act 3, on scroll: the camera pushes into the window, the brick wall and the glass on a nearer plane than
the roastery, the sign lifts on its own plane and weathers back into a ghost, the light turns inward.

Geometry is in source pixels of each art-directed export, keyed by its natural width: the glass panes
are the mask that separates the wall plane from the room behind it.
*/

import { gsap, ScrollTrigger } from "./gsap.js";
import { rule, pen } from "./paint.js";
import { letterSign, weathering, endOnResize } from "./lettering.js";
import { clamp01, smooth, relative, layer as overlay, freshSign, signLettered } from "./motion-utils.js";

const SCENES = {
	1440: {
		panes: [[539, 483, 772, 759], [539, 775, 772, 1043], [814, 478, 1349, 1043], [1395, 483, 1440, 1043]],
		anchor: [1060, 760],
		push: { wall: 0.5, room: 0.42, lift: 46 },
		air: [539, 470, 1440, 1043],
		heat: [830, 596, 1342, 906],
		flow: { quad: [[986, 712], [1144, 712], [1124, 886], [998, 886]], reach: 30 },
		chute: { y: 704, x: [990, 1140], land: [880, 898], g: 1700 },
		tray: { cx: 1065, cy: 940, rx: 355, ry: 100, x: [826, 1340] },
		steam: [[836, 1000, 872, 3], [1020, 1120, 888, 2]],
		lamp: [1070, 830, 440],
		dust: [[545, 490, 768, 752], [820, 484, 990, 700]],
		scale: 1,
		bean: 1,
		pour: 3600,
		beans: 1200,
	},
	768: {
		panes: [[108, 546, 233, 667], [240, 546, 275, 667], [286, 546, 365, 667], [108, 674, 233, 798], [240, 674, 275, 798], [286, 674, 365, 798], [108, 805, 233, 919], [240, 805, 275, 919], [286, 805, 365, 919], [375, 546, 675, 919]],
		anchor: [520, 760],
		push: { wall: 0.36, room: 0.3, lift: 7 },
		air: [108, 540, 676, 920],
		heat: [300, 600, 668, 830],
		flow: { quad: [[478, 704], [586, 704], [562, 814], [472, 814]], reach: 20 },
		chute: { y: 700, x: [482, 580], land: [806, 822], g: 1150 },
		tray: { cx: 438, cy: 849, rx: 226, ry: 71, x: [378, 660] },
		steam: [[330, 470, 796, 3], [480, 560, 812, 2]],
		lamp: [450, 790, 300],
		dust: [[380, 552, 480, 720]],
		scale: 0.66,
		bean: 1.05,
		pour: 2600,
		beans: 800,
	},
	390: {
		panes: [[51, 453, 110, 523], [114, 453, 134, 523], [140, 453, 185, 523], [51, 527, 110, 595], [114, 527, 134, 595], [140, 527, 185, 595], [51, 602, 110, 667], [114, 602, 134, 667], [140, 602, 185, 667], [191, 453, 362, 667]],
		anchor: [250, 560],
		push: { wall: 0.3, room: 0.25, lift: 3 },
		air: [51, 450, 363, 668],
		heat: [150, 490, 356, 626],
		flow: { quad: [[248, 546], [311, 546], [292, 602], [244, 602]], reach: 9 },
		chute: { y: 544, x: [250, 308], land: [598, 610], g: 620 },
		tray: { cx: 226, cy: 626, rx: 127, ry: 41, x: [194, 350] },
		steam: [[168, 236, 606, 3], [244, 290, 612, 2]],
		lamp: [232, 592, 165],
		dust: [[194, 458, 252, 560]],
		scale: 0.36,
		bean: 1.6,
		pour: 1500,
		beans: 450,
	},
};

const STEAM_RGB = [162, 172, 184];
const DUST_RGB = [255, 236, 204];
const CHAFF_RGB = [206, 182, 142];
const NS = "http://www.w3.org/2000/svg";

const rand = (a, b) => a + (b - a) * Math.random();
// Smooth value noise, a fresh lattice per call, so every visit and every layer drifts differently
function noise() {
	const v = new Float32Array(256);
	for (let i = 0; i < 256; i++) v[i] = Math.random() * 2 - 1;
	const at = (x) => {
		const i = Math.floor(x);
		const f = x - i;
		const u = f * f * (3 - 2 * f);
		return v[i & 255] + (v[(i + 1) & 255] - v[i & 255]) * u;
	};
	return (x) => at(x) * 0.66 + at(x * 2.31 + 17.3) * 0.34;
}
function canvas(w, h) {
	const c = document.createElement("canvas");
	c.width = Math.max(1, Math.round(w));
	c.height = Math.max(1, Math.round(h));
	return c;
}
// A soft-edged shape: only the blurred shadow of the polygon lands in the canvas, which works in every engine
function feather(w, h, pts, blur) {
	const c = canvas(w, h);
	const g = c.getContext("2d");
	const off = w + 100;
	g.shadowColor = "#fff";
	g.shadowBlur = blur;
	g.shadowOffsetX = off;
	g.beginPath();
	pts.forEach(([x, y], i) => (i ? g.lineTo(x - off, y) : g.moveTo(x - off, y)));
	g.closePath();
	g.fill();
	return c;
}
function sprite(size, rgb, stops) {
	const c = canvas(size, size);
	const g = c.getContext("2d");
	const r = size / 2;
	const grad = g.createRadialGradient(r, r, 0, r, r, r);
	stops.forEach(([at, a]) => grad.addColorStop(at, `rgba(${rgb[0]},${rgb[1]},${rgb[2]},${a})`));
	g.fillStyle = grad;
	g.fillRect(0, 0, size, size);
	return c;
}
// Vapour is lumpy, never a clean disc: a few offset soft blobs inside one falloff
function puff(size, rgb) {
	const c = canvas(size, size);
	const g = c.getContext("2d");
	for (let i = 0; i < 7; i++) {
		const r = size * rand(0.16, 0.3);
		const a = rand(0, Math.PI * 2);
		const d = rand(0, size * 0.18);
		const x = size / 2 + Math.cos(a) * d;
		const y = size / 2 + Math.sin(a) * d;
		const grad = g.createRadialGradient(x, y, 0, x, y, r);
		grad.addColorStop(0, `rgba(${rgb[0]},${rgb[1]},${rgb[2]},0.42)`);
		grad.addColorStop(1, `rgba(${rgb[0]},${rgb[1]},${rgb[2]},0)`);
		g.fillStyle = grad;
		g.fillRect(0, 0, size, size);
	}
	return c;
}

// Layout: every layer sits on the photo's own cover box, so a source pixel lands where the photo draws it

function cover(media, img) {
	const W = media.clientWidth;
	const H = media.clientHeight;
	const nw = img.naturalWidth;
	const nh = img.naturalHeight;
	const k = Math.max(W / nw, H / nh);
	return { W, H, nw, nh, k, ox: (W - nw * k) / 2, oy: (H - nh * k) / 2 };
}
function place(node, L, [x0, y0, x1, y1], anchor) {
	const left = L.ox + x0 * L.k;
	const top = L.oy + y0 * L.k;
	Object.assign(node.style, { left: `${left}px`, top: `${top}px`, width: `${(x1 - x0) * L.k}px`, height: `${(y1 - y0) * L.k}px` });
	node.style.transformOrigin = `${L.ox + anchor[0] * L.k - left}px ${L.oy + anchor[1] * L.k - top}px`;
}
function layer(media, tag, className, cleanups) {
	return overlay(media, `hero__layer ${className}`, cleanups, { tag });
}
function panesMask(S, L) {
	const rects = S.panes.map(([x0, y0, x1, y1]) => `<rect x="${x0}" y="${y0}" width="${x1 - x0}" height="${y1 - y0}"/>`).join("");
	return `url("data:image/svg+xml,${encodeURIComponent(`<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ${L.nw} ${L.nh}" preserveAspectRatio="none"><g fill="#fff">${rects}</g></svg>`)}")`;
}

// Act 2: the room behind the glass -----------------------------------------------------------------

function room(media, img, S, L, cleanups) {
	const dpr = Math.min(window.devicePixelRatio || 1, 2);
	const unit = S.scale;
	const nHeat = [noise(), noise()];
	const nWind = noise();
	const nRate = noise();
	const nLight = [noise(), noise(), noise()];
	const amp = { heat: 0, flow: 0, beans: 0, steam: 0, dust: 0, life: 0 };

	// the wall plane: the photograph with the glass cut out, identical to the photo at rest
	const wall = layer(media, "canvas", "hero__layer--wall", cleanups);
	wall.width = L.nw;
	wall.height = L.nh;
	const wg = wall.getContext("2d");
	wg.drawImage(img, 0, 0);
	wg.globalCompositeOperation = "destination-out";
	S.panes.forEach(([x0, y0, x1, y1]) => wg.fillRect(x0, y0, x1 - x0, y1 - y0));

	// pixels of the photograph that move: heat over the drum and tray, the flowing body of the pour
	const fx = layer(media, "canvas", "hero__layer--fx", cleanups);
	const [hx0, hy0, hx1, hy1] = S.heat;
	const res = L.k * dpr;
	fx.width = (hx1 - hx0) * res;
	fx.height = (hy1 - hy0) * res;
	const fg = fx.getContext("2d");
	const heatSrc = canvas(fx.width, fx.height);
	heatSrc.getContext("2d").drawImage(img, hx0, hy0, hx1 - hx0, hy1 - hy0, 0, 0, fx.width, fx.height);
	const heatBuf = canvas(fx.width, fx.height);
	const hb = heatBuf.getContext("2d");
	const heatMask = (() => {
		const c = canvas(fx.width, fx.height);
		const g = c.getContext("2d");
		g.setTransform(fx.width / 2, 0, 0, fx.height / 2, fx.width / 2, fx.height * 0.56);
		const grad = g.createRadialGradient(0, 0, 0, 0, 0, 1);
		grad.addColorStop(0, "#fff");
		grad.addColorStop(0.5, "rgba(255,255,255,0.8)");
		grad.addColorStop(1, "rgba(255,255,255,0)");
		g.fillStyle = grad;
		g.fillRect(-1, -1, 2, 2);
		return c;
	})();
	// The pour: what stands behind the stream is filled in from its edges into a clean plate, which
	// mostly covers the photo's frozen beans, and beans coloured by the photo at their place fall over it
	const pour = (() => {
		const q = S.flow.quad;
		const m = Math.round(10 * unit + 4);
		const x0 = Math.floor(Math.min(...q.map((v) => v[0]))) - m;
		const y0 = Math.floor(Math.min(...q.map((v) => v[1]))) - m;
		const x1 = Math.ceil(Math.max(...q.map((v) => v[0]))) + m;
		const y1 = Math.ceil(Math.max(...q.map((v) => v[1]))) + m;
		const w = x1 - x0;
		const h = y1 - y0;
		const c = canvas(w, h);
		const g = c.getContext("2d", { willReadFrequently: true });
		g.drawImage(img, x0, y0, w, h, 0, 0, w, h);
		const src = g.getImageData(0, 0, w, h);
		const d = src.data;
		const pts = q.map(([x, y]) => [x - x0, y - y0]);
		const grow = 2 + 2 * unit;
		const inside = (x, y) => {
			const t = (y - pts[0][1]) / (pts[3][1] - pts[0][1]);
			if (t < -0.02 || t > 1.04) return false;
			const l = pts[0][0] + (pts[3][0] - pts[0][0]) * t - grow;
			const r = pts[1][0] + (pts[2][0] - pts[1][0]) * t + grow;
			return x >= l && x <= r;
		};
		// diffusion fill on a coarse grid: smooth, and dark where the roaster behind is dark
		const f = 3;
		const gw = Math.ceil(w / f);
		const gh = Math.ceil(h / f);
		const grid = new Float32Array(gw * gh * 3);
		const known = new Uint8Array(gw * gh);
		for (let gy = 0; gy < gh; gy++) {
			for (let gx = 0; gx < gw; gx++) {
				let n = 0;
				const acc = [0, 0, 0];
				for (let y = gy * f; y < Math.min(h, gy * f + f); y++) {
					for (let x = gx * f; x < Math.min(w, gx * f + f); x++) {
						if (inside(x, y)) continue;
						const i = (y * w + x) * 4;
						acc[0] += d[i];
						acc[1] += d[i + 1];
						acc[2] += d[i + 2];
						n++;
					}
				}
				const gi = gy * gw + gx;
				if (n > f) {
					known[gi] = 1;
					for (let k = 0; k < 3; k++) grid[gi * 3 + k] = acc[k] / n;
				}
			}
		}
		for (let it = 0; it < 260; it++) {
			for (let gi = 0; gi < gw * gh; gi++) {
				if (known[gi]) continue;
				const gx = gi % gw;
				const gy = (gi / gw) | 0;
				for (let k = 0; k < 3; k++) {
					let sum = 0;
					let n = 0;
					if (gx > 0) {
						sum += grid[(gi - 1) * 3 + k];
						n++;
					}
					if (gx < gw - 1) {
						sum += grid[(gi + 1) * 3 + k];
						n++;
					}
					if (gy > 0) {
						sum += grid[(gi - gw) * 3 + k];
						n++;
					}
					if (gy < gh - 1) {
						sum += grid[(gi + gw) * 3 + k];
						n++;
					}
					grid[gi * 3 + k] = sum / n;
				}
			}
		}
		// the colour of the stream at every point, a local average of the photo's own beans
		const field = new Uint8ClampedArray(w * h * 3);
		const rad = Math.max(1, Math.round(3 * unit));
		for (let y = 0; y < h; y++) {
			for (let x = 0; x < w; x++) {
				let n = 0;
				const acc = [0, 0, 0];
				for (let yy = Math.max(0, y - rad); yy <= Math.min(h - 1, y + rad); yy++) {
					for (let xx = Math.max(0, x - rad); xx <= Math.min(w - 1, x + rad); xx++) {
						const i = (yy * w + xx) * 4;
						if (d[i] + d[i + 1] + d[i + 2] < 75) continue;
						acc[0] += d[i];
						acc[1] += d[i + 1];
						acc[2] += d[i + 2];
						n++;
					}
				}
				const o = (y * w + x) * 3;
				for (let k = 0; k < 3; k++) field[o + k] = n ? acc[k] / n : d[(y * w + x) * 4 + k];
			}
		}
		const at = (gx, gy, k) => grid[(Math.min(gh - 1, Math.max(0, gy)) * gw + Math.min(gw - 1, Math.max(0, gx))) * 3 + k];
		const soft = feather(w, h, pts, 5 * unit + 2).getContext("2d").getImageData(0, 0, w, h).data;
		for (let y = 0; y < h; y++) {
			for (let x = 0; x < w; x++) {
				const i = (y * w + x) * 4;
				const fx0 = x / f - 0.5;
				const fy0 = y / f - 0.5;
				const gx = Math.floor(fx0);
				const gy = Math.floor(fy0);
				const tx = fx0 - gx;
				const ty = fy0 - gy;
				const grain = (Math.random() - 0.5) * 9;
				for (let k = 0; k < 3; k++) {
					const top = at(gx, gy, k) * (1 - tx) + at(gx + 1, gy, k) * tx;
					const low = at(gx, gy + 1, k) * (1 - tx) + at(gx + 1, gy + 1, k) * tx;
					d[i + k] = top * (1 - ty) + low * ty + grain;
				}
				d[i + 3] = soft[i + 3] * 0.72;
			}
		}
		g.putImageData(src, 0, 0);
		// shaded bean sprites in the photo's own bean colours, from shadow to lit, with and without the crease
		const cols = [];
		for (let y = 0; y < h; y += 2) {
			for (let x = 0; x < w; x += 2) {
				if (!inside(x, y)) continue;
				const o = (y * w + x) * 3;
				const l = 0.3 * field[o] + 0.59 * field[o + 1] + 0.11 * field[o + 2];
				if (l > 28 && l < 190) cols.push([field[o], field[o + 1], field[o + 2], l]);
			}
		}
		cols.sort((u, v) => u[3] - v[3]);
		const tones = 12;
		const sprites = [];
		const bounds = [];
		for (let i = 0; i < tones; i++) {
			const [r, gg, bb, l] = cols.length ? cols[Math.floor(((i + 0.5) / tones) * cols.length)] : [92, 58, 38, 64];
			bounds.push(l);
			const pair = [false, true].map((crease) => {
				const sp = canvas(24, 16);
				const sg = sp.getContext("2d");
				const mix = (k, add = 0) => `rgb(${Math.min(255, r * k + add) | 0},${Math.min(255, gg * k + add * 0.8) | 0},${Math.min(255, bb * k + add * 0.6) | 0})`;
				const grad = sg.createRadialGradient(9, 5.5, 0.5, 12, 8, 12);
				grad.addColorStop(0, mix(1.25, 18));
				grad.addColorStop(0.45, mix(0.95));
				grad.addColorStop(1, mix(0.38));
				sg.fillStyle = grad;
				sg.beginPath();
				sg.ellipse(12, 8, 11, 7, 0, 0, Math.PI * 2);
				sg.fill();
				if (crease) {
					sg.strokeStyle = "rgba(20,10,4,0.55)";
					sg.lineWidth = 1.2;
					sg.beginPath();
					sg.moveTo(3.5, 8.6);
					sg.quadraticCurveTo(12, 6.4, 20.5, 8.6);
					sg.stroke();
				}
				return sp;
			});
			sprites.push(pair);
		}
		const lum = new Uint8Array(w * h);
		for (let i = 0; i < w * h; i++) lum[i] = 0.3 * field[i * 3] + 0.59 * field[i * 3 + 1] + 0.11 * field[i * 3 + 2];
		const toneAt = (l) => {
			let i = 0;
			while (i < tones - 1 && bounds[i + 1] <= l) i++;
			return i;
		};
		const toneOf = new Uint8Array(256).map((_, l) => toneAt(l));
		const bean = (x, y, shift, crease) => {
			const xi = Math.min(w - 1, Math.max(0, Math.round(x - x0)));
			const yi = Math.min(h - 1, Math.max(0, Math.round(y - y0)));
			const i = Math.min(tones - 1, Math.max(0, toneOf[lum[yi * w + xi]] + shift));
			return sprites[i][crease ? 1 : 0];
		};
		return { plate: c, x0, y0, bean };
	})();

	// the air of the room: steam, chaff, dust in the light, a glint on the tray, screened over the photo
	const air = layer(media, "canvas", "hero__layer--air", cleanups);
	const [ax0, ay0, ax1, ay1] = S.air;
	const ares = L.k * Math.min(dpr, 1.5);
	air.width = (ax1 - ax0) * ares;
	air.height = (ay1 - ay0) * ares;
	const ag = air.getContext("2d");
	const steamSprites = [0, 1, 2].map(() => puff(96, STEAM_RGB));
	const mote = sprite(24, DUST_RGB, [[0, 1], [0.35, 0.5], [1, 0]]);
	const glintSprite = sprite(64, [255, 246, 230], [[0, 1], [0.2, 0.6], [1, 0]]);

	const lamp = layer(media, "div", "hero__layer--lamp", cleanups);
	const glass = layer(media, "div", "hero__layer--glass", cleanups);
	const brick = layer(media, "div", "hero__layer--brick", cleanups);
	const shade = layer(media, "div", "hero__layer--shade", cleanups);
	glass.append(document.createElement("span"));
	const mask = panesMask(S, L);
	glass.style.maskImage = mask;
	glass.style.webkitMaskImage = mask;
	const [lx, ly, lr] = S.lamp;
	const full = [0, 0, L.nw, L.nh];
	const layout = () => {
		place(wall, L, full, S.anchor);
		place(fx, L, S.heat, S.anchor);
		place(air, L, S.air, S.anchor);
		place(lamp, L, [lx - lr, ly - lr * 0.7, lx + lr, ly + lr * 0.7], S.anchor);
		place(glass, L, full, S.anchor);
		place(brick, L, full, S.anchor);
		place(shade, L, full, S.anchor);
		shade.style.setProperty("--at", `${((S.anchor[0] / L.nw) * 100).toFixed(1)}% ${((S.anchor[1] / L.nh) * 100).toFixed(1)}%`);
		img.style.transformOrigin = `${L.ox + S.anchor[0] * L.k}px ${L.oy + S.anchor[1] * L.k}px`;
	};
	layout();

	const beans = [];
	const steam = [];
	const chaff = [];
	const dust = [];
	let glint = null;
	let nextGlint = rand(3, 7);
	let nextChaff = rand(4, 9);
	let nextSteam = 0;
	let beanDebt = 0;
	const [cx0, cx1] = S.chute.x;
	const chuteMid = (cx0 + cx1) / 2;
	const dustBoxes = S.dust;
	for (let i = 0; i < Math.round(26 * Math.max(0.4, unit)); i++) {
		const b = dustBoxes[i % dustBoxes.length];
		dust.push({ x: rand(b[0], b[2]), y: rand(b[1], b[3]), b, s: rand(0.6, 1.5), ph: rand(0, 100), n: noise() });
	}

	const [qa, qb, qc, qd] = S.flow.quad;
	// a bean leaves the chute at its own instant inside the frame, or a frame's beans would fall as a row
	function spawnBean(age) {
		const u = (Math.random() + Math.random()) / 2;
		const x = cx0 + (cx1 - cx0) * u;
		const target = qd[0] + ((qc[0] - qd[0]) * (x - qa[0])) / (qb[0] - qa[0]) + rand(-6, 6) * unit;
		const fall = 0.26;
		beans.push({
			x,
			y: S.chute.y + rand(-3, 5) * unit,
			vx: (target - x) / fall + rand(-10, 10) * unit,
			vy: rand(120, 240) * unit,
			r: rand(2.5, 3.4) * unit * S.bean,
			spin: rand(0, 6.28),
			spinV: rand(-16, 16),
			shift: Math.round(rand(-3.4, 2.4)),
			crease: Math.random() < 0.45,
			land: rand(S.chute.land[0], S.chute.land[1]),
			bounced: false,
			fade: 1,
		});
		const b = beans[beans.length - 1];
		b.vy += 5200 * unit * age;
		b.x += b.vx * age;
		b.y += b.vy * age;
	}
	function spawnSteam() {
		const pick = S.steam[Math.random() * 5 < S.steam[0][3] ? 0 : 1];
		steam.push({ x: rand(pick[0], pick[1]), y: pick[2], vy: -rand(16, 30) * unit, s0: rand(26, 44) * unit, grow: rand(2.2, 3.6), life: rand(4.5, 8), t: 0, a: rand(0.09, 0.16), ph: rand(0, 50), rot: rand(0, 6.28), rv: rand(-0.25, 0.25), img: steamSprites[(Math.random() * 3) | 0] });
	}
	function spawnChaff() {
		const n = Math.round(rand(5, 10));
		for (let i = 0; i < n; i++) chaff.push({ x: rand(cx0, cx1), y: rand(S.chute.land[0] - 4, S.chute.land[1]), vx: rand(-22, 22) * unit, vy: -rand(40, 85) * unit, s: rand(1.1, 2.4) * unit, rot: rand(0, 6.28), rv: rand(-5, 5), t: 0, life: rand(2.2, 3.8), ph: rand(0, 6.28) });
	}

	let time = 0;
	function tick(dt) {
		time += dt;
		const t = time;

		// the photo canvas: heat shimmer first, then the pour's own pixels flowing, then the beans
		fg.setTransform(1, 0, 0, 1, 0, 0);
		fg.clearRect(0, 0, fx.width, fx.height);
		if (amp.heat > 0.01) {
			hb.globalCompositeOperation = "source-over";
			hb.clearRect(0, 0, fx.width, fx.height);
			const band = Math.max(2, Math.round(2 * res));
			const a = 1.25 * unit * res * amp.heat;
			for (let y = 0; y < fx.height; y += band) {
				const sy = y / res;
				const dx = a * (nHeat[0](sy * 0.045 + t * 1.7) * 0.75 + nHeat[1](sy * 0.16 + t * 3.3) * 0.25);
				hb.drawImage(heatSrc, 0, y, fx.width, band, dx, y, fx.width, band);
			}
			hb.globalCompositeOperation = "destination-in";
			hb.drawImage(heatMask, 0, 0);
			fg.drawImage(heatBuf, 0, 0);
		}
		if (amp.flow > 0.01) {
			fg.setTransform(res, 0, 0, res, -hx0 * res, -hy0 * res);
			fg.globalAlpha = amp.flow;
			fg.drawImage(pour.plate, pour.x0, pour.y0);
			fg.globalAlpha = 1;
		}
		// beans in flight, tumbling, motion-blurred along their fall
		const rate = S.pour * amp.beans * (0.8 + 0.4 * (nRate(t * 0.45) * 0.5 + 0.5));
		beanDebt += rate * dt;
		while (beanDebt >= 1 && beans.length < S.beans) {
			spawnBean(rand(0, dt));
			beanDebt -= 1;
		}
		if (beanDebt > 1) beanDebt = 0;
		fg.setTransform(res, 0, 0, res, -hx0 * res, -hy0 * res);
		const g = 5200 * unit;
		for (let i = beans.length - 1; i >= 0; i--) {
			const b = beans[i];
			b.vy += g * dt;
			b.x += b.vx * dt;
			b.y += b.vy * dt;
			b.spin += b.spinV * dt;
			if (!b.bounced && b.y >= b.land) {
				if (Math.random() < 0.28) {
					b.bounced = true;
					b.y = b.land;
					b.vy *= -0.2;
					b.vx += rand(-40, 40) * unit;
				} else b.fade = 0;
			}
			if (b.bounced) b.fade -= dt * 6;
			if (b.fade <= 0 || b.y > S.chute.land[1] + 30 * unit) {
				beans[i] = beans[beans.length - 1];
				beans.pop();
				continue;
			}
			const v = Math.hypot(b.vx, b.vy);
			const rx = b.r + v / 520;
			const ry = b.r * 0.68 * (0.6 + 0.4 * Math.abs(Math.sin(b.spin)));
			const ang = Math.atan2(b.vy, b.vx) + Math.sin(b.spin) * 0.35;
			const cos = Math.cos(ang) * res;
			const sin = Math.sin(ang) * res;
			fg.setTransform(cos, sin, -sin, cos, (b.x - hx0) * res, (b.y - hy0) * res);
			fg.globalAlpha = Math.min(1, (b.r * 1.6) / rx) * clamp01(b.fade);
			fg.drawImage(pour.bean(b.x, b.y, b.shift, b.crease), -rx, -ry, rx * 2, ry * 2);
		}
		fg.globalAlpha = 1;

		// the air canvas
		ag.setTransform(1, 0, 0, 1, 0, 0);
		ag.clearRect(0, 0, air.width, air.height);
		ag.setTransform(ares, 0, 0, ares, -ax0 * ares, -ay0 * ares);
		nextSteam -= dt * amp.steam;
		if (nextSteam <= 0 && steam.length < 48 * Math.max(0.45, unit)) {
			spawnSteam();
			nextSteam = rand(0.12, 0.3) / Math.max(0.5, unit);
		}
		const wind = nWind(t * 0.08) * 9 * unit;
		for (let i = steam.length - 1; i >= 0; i--) {
			const s = steam[i];
			s.t += dt;
			if (s.t > s.life) {
				steam.splice(i, 1);
				continue;
			}
			const u = s.t / s.life;
			s.x += (wind + nWind(s.ph + s.y * 0.012 + t * 0.21) * 14 * unit) * dt;
			s.y += s.vy * dt * (1 - u * 0.4);
			s.rot += s.rv * dt;
			const size = s.s0 * (1 + s.grow * u);
			ag.globalAlpha = s.a * smooth(0, 0.18, u) * (1 - smooth(0.45, 1, u)) * amp.steam;
			ag.save();
			ag.translate(s.x, s.y);
			ag.rotate(s.rot);
			ag.drawImage(s.img, -size / 2, -size / 2, size, size);
			ag.restore();
		}
		nextChaff -= dt * amp.life;
		if (nextChaff <= 0) {
			spawnChaff();
			nextChaff = rand(9, 18);
		}
		ag.fillStyle = `rgb(${CHAFF_RGB.join(",")})`;
		for (let i = chaff.length - 1; i >= 0; i--) {
			const c = chaff[i];
			c.t += dt;
			if (c.t > c.life) {
				chaff.splice(i, 1);
				continue;
			}
			c.vy *= 1 - 0.9 * dt;
			c.vy -= 6 * unit * dt;
			c.x += (c.vx + Math.sin(c.ph + c.t * 4) * 14 * unit) * dt;
			c.y += c.vy * dt;
			c.rot += c.rv * dt;
			ag.globalAlpha = 0.7 * smooth(0, 0.1, c.t / c.life) * (1 - smooth(0.55, 1, c.t / c.life));
			ag.save();
			ag.translate(c.x, c.y);
			ag.rotate(c.rot);
			ag.scale(1, 0.35 + 0.65 * Math.abs(Math.sin(c.t * 5 + c.ph)));
			ag.fillRect(-c.s, -c.s * 0.6, c.s * 2, c.s * 1.2);
			ag.restore();
		}
		if (amp.dust > 0.01) {
			dust.forEach((d) => {
				d.x += d.n(t * 0.11 + d.ph) * 5 * unit * dt;
				d.y += (d.n(t * 0.09 + d.ph + 40) * 4 - 0.6) * unit * dt;
				const [bx0, by0, bx1, by1] = d.b;
				if (d.x < bx0) d.x = bx1;
				if (d.x > bx1) d.x = bx0;
				if (d.y < by0) d.y = by1;
				if (d.y > by1) d.y = by0;
				// specks show only where a slant of window light crosses them
				const beam = Math.max(0, Math.sin(((d.x - bx0) * 0.9 + (d.y - by0)) * 0.012 + t * 0.03));
				const a = amp.dust * beam * beam * (0.35 + 0.65 * (d.n(t * 0.7 + d.ph * 3) * 0.5 + 0.5));
				if (a < 0.02) return;
				const size = 7 * d.s * unit + 2;
				ag.globalAlpha = a * 0.5;
				ag.drawImage(mote, d.x - size / 2, d.y - size / 2, size, size);
			});
		}
		nextGlint -= dt * amp.life;
		if (!glint && nextGlint <= 0) {
			const a0 = rand(0.18, 0.4) * Math.PI;
			glint = { t: 0, life: rand(1.1, 1.7), a0, a1: a0 + rand(0.22, 0.42) * Math.PI };
			nextGlint = rand(6, 13);
		}
		if (glint) {
			glint.t += dt;
			const u = glint.t / glint.life;
			if (u >= 1) glint = null;
			else {
				const e = u * u * (3 - 2 * u);
				const th = glint.a0 + (glint.a1 - glint.a0) * e;
				const { cx, cy, rx, ry, x: gx } = S.tray;
				const x = cx + Math.cos(th) * rx;
				const y = cy + Math.sin(th) * ry;
				if (x > gx[0] && x < gx[1]) {
					const a = Math.sin(Math.PI * u) ** 2;
					const size = 26 * unit + 6;
					ag.globalAlpha = a * 0.85;
					ag.save();
					ag.translate(x, y);
					ag.rotate(Math.atan2(Math.cos(th) * ry, -Math.sin(th) * rx));
					ag.drawImage(glintSprite, -size * 1.6, -size * 0.18, size * 3.2, size * 0.36);
					ag.drawImage(glintSprite, -size * 0.35, -size * 0.35, size * 0.7, size * 0.7);
					ag.restore();
				}
			}
		}
		ag.globalAlpha = 1;

		lights();
	}

	// a slow breath of daylight across the brick, and a room light that never quite holds still;
	// opacity and transform only, so a frame costs no repaint
	const light = { lamp: 0, push: 1, brick: 1 };
	function lights() {
		const t = time;
		const breath = 0.55 + 0.45 * nLight[2](t * 0.05);
		brick.style.opacity = (amp.life * breath * light.brick).toFixed(3);
		brick.style.translate = `${(nLight[0](t * 0.035) * 18).toFixed(2)}% ${(nLight[1](t * 0.03) * 10).toFixed(2)}%`;
		lamp.style.opacity = Math.min(1, light.lamp * 0.42 * light.push * (1 + 0.06 * nLight[0](t * 0.8 + 30))).toFixed(3);
	}

	return { amp, light, lights, tick, layout, layers: { wall, fx, air, lamp, glass, brick, shade } };
}

// The scene ------------------------------------------------------------------------------------------

export function heroScene(hero, { ctx, cleanups, alive, fontsReady }) {
	// work started after a promise still belongs to the matchMedia context, so it reverts with it
	const later = (fn) => () => alive() && ctx.add(fn);
	const media = hero.querySelector(".hero__media");
	const img = media && media.querySelector("img");
	const sign = hero.querySelector('[data-motion="sign"]');
	const title = sign && sign.querySelector("h1");
	const caption = hero.querySelector(".hero__caption");
	const root = document.documentElement;
	if (!img || !title) {
		delete root.dataset.hero;
		return;
	}
	const stripes = [...sign.querySelectorAll(".pinstripe")];
	const phone = window.matchMedia("(max-width: 47.99875em)").matches;
	const fresh = freshSign("hero", hero);
	let scene = null;
	let userPaused = false;
	let onScreen = true;
	let running = false;
	let scrollP = 0;

	media.classList.add("hero__media--live");
	cleanups.push(() => media.classList.remove("hero__media--live"));
	relative(sign, cleanups);

	// the sign's weathered states for the scroll
	const weather = weathering(title, cleanups, { rules: stripes });
	// the sign weathers between a first nudge of the scroll and the moment it reaches the top of the screen
	const signSpan = [40, 400];
	const placeEchoes = () => {
		weather.place();
		const bottom = sign.getBoundingClientRect().bottom - (Number(gsap.getProperty(sign, "y")) || 0) - hero.getBoundingClientRect().top;
		signSpan[1] = Math.max(signSpan[0] + 120, bottom * 0.9);
	};
	placeEchoes();

	const tick = () => {
		if (scene) scene.tick(Math.min(gsap.ticker.deltaRatio(60) / 60, 0.05));
	};
	const run = () => {
		const should = !!scene && onScreen && !userPaused && !document.hidden;
		if (should === running) return;
		running = should;
		if (should) gsap.ticker.add(tick);
		else gsap.ticker.remove(tick);
	};
	cleanups.push(() => gsap.ticker.remove(tick));
	const io = new IntersectionObserver(([entry]) => {
		onScreen = entry.isIntersecting;
		run();
	});
	io.observe(hero);
	const onVisibility = () => run();
	document.addEventListener("visibilitychange", onVisibility);
	cleanups.push(() => {
		io.disconnect();
		document.removeEventListener("visibilitychange", onVisibility);
	});

	// WCAG 2.2.2: motion that runs on its own next to text can be stopped by the visitor
	const host = hero.querySelector(".hero__intro .hero__container");
	let still = null;
	let warned = false;
	if (host) {
		const button = document.createElement("button");
		still = button;
		button.type = "button";
		button.className = "hero__still";
		// the wording from Site settings (inc/enqueue.php), the design's where a field is empty
		const words = window.prairieAsh || {};
		const pause = words.stillPauseLabel || "Pause the roastery";
		const play = words.stillPlayLabel || "Play the roastery";
		button.setAttribute("aria-pressed", "false");
		button.textContent = pause;
		button.addEventListener("click", () => {
			userPaused = !userPaused;
			button.setAttribute("aria-pressed", String(userPaused));
			button.textContent = userPaused ? play : pause;
			run();
		});
		host.append(button);
		cleanups.push(() => button.remove());
	}

	// Act 3: the camera pushes into the window as the hero scrolls away
	const applyScroll = () => {
		const p = scrollP;
		if (!scene) return;
		const S = scene.S;
		const L = scene.L;
		const travel = hero.offsetHeight;
		// the push is done while the window is still on screen, not after the hero has gone
		const e = smooth(0, 0.72, p);
		const room = 1 + S.push.room * e;
		const wallS = 1 + S.push.wall * e;
		// the room is farther than the wall, so it sinks with the page less: it drifts down against the scroll
		// where the photo is only the top of a stacked hero, a deep drift would carry the window out of its frame
		const stacked = media.offsetHeight < travel * 0.9;
		const roomY = p * travel * (stacked ? 0.14 : 0.56);
		const wallY = roomY - S.push.lift * L.k * p;
		const { wall, fx, air, lamp, glass, brick, shade } = scene.layers;
		gsap.set([img, fx, air, lamp], { y: roomY, scale: room, force3D: true });
		gsap.set([wall, glass, brick, shade], { y: wallY, scale: wallS, force3D: true });
		// light turns inward: the brick falls into shadow, the room glows, the glass catches the sky once
		shade.style.opacity = (0.62 * smooth(0.12, 0.85, p)).toFixed(3);
		scene.light.push = 1 + 0.75 * smooth(0.05, 0.7, p);
		scene.light.brick = 1 - smooth(0.1, 0.5, p);
		scene.lights();
		glass.style.opacity = (0.5 * Math.sin(Math.PI * smooth(0.04, 0.8, p))).toFixed(3);
		gsap.set(glass.firstElementChild, { xPercent: -60 + 120 * p });
		// the sign is the nearest plane: it lifts faster than the page and weathers back into a ghost
		const w = smooth(signSpan[0], signSpan[1], p * travel);
		gsap.set(sign, { y: -p * travel * 0.18, force3D: true });
		if (root.dataset.hero === "live") weather.apply(w);
	};
	cleanups.push(() => {
		gsap.set([img, sign], { clearProps: "transform,transformOrigin" });
		title.style.opacity = "";
		stripes.forEach((s) => (s.style.opacity = ""));
	});
	const proxy = { p: 0 };
	gsap.to(proxy, {
		p: 1,
		ease: "none",
		onUpdate: () => {
			scrollP = proxy.p;
			applyScroll();
		},
		scrollTrigger: { trigger: hero, start: "top top", end: "bottom top", scrub: phone ? 0.6 : true, invalidateOnRefresh: true },
	});

	// the scene is rebuilt whenever the picture swaps to another art-directed export. It reads the photo's pixels, which
	// a browser refuses for a photo from another host (a CDN) unless that host allows it and the image asked: such a
	// photo, or any scene that fails to build, leaves the photo as it is, with no half-built layers and no pause
	// control for a room that does not move
	const build = () => {
		const S = SCENES[img.naturalWidth];
		if (scene) {
			scene.cleanups.reverse().forEach((fn) => fn());
			scene = null;
		}
		const source = new URL(img.currentSrc || img.src, location.href);
		if (S && img.naturalWidth && (source.origin === location.origin || img.crossOrigin)) {
			const local = [];
			try {
				const L = cover(media, img);
				const r = room(media, img, S, L, local);
				scene = { ...r, S, L, cleanups: local };
			} catch (error) {
				local.reverse().forEach((fn) => fn());
				if (!warned) console.warn("Hero: the roastery scene could not be built.", error);
				warned = true;
			}
		}
		// the control stands in the layout from the start, as the scene is expected; it leaves, and the triggers
		// are measured again, only when no scene could be built
		if (still && still.hidden === !!scene) {
			still.hidden = !scene;
			ScrollTrigger.refresh();
		}
		applyScroll();
		run();
		return scene;
	};
	const onLoad = () => {
		if (!alive()) return;
		const prev = scene && { amp: { ...scene.amp }, lamp: scene.light.lamp };
		build();
		if (scene && prev) {
			Object.assign(scene.amp, prev.amp);
			scene.light.lamp = prev.lamp;
			scene.lights();
		}
	};
	img.addEventListener("load", onLoad);
	const ro = new ResizeObserver(() => {
		if (!scene) return;
		Object.assign(scene.L, cover(media, img));
		scene.layout();
		placeEchoes();
		applyScroll();
	});
	ro.observe(media);
	cleanups.push(() => {
		img.removeEventListener("load", onLoad);
		ro.disconnect();
		if (scene) scene.cleanups.reverse().forEach((fn) => fn());
		scene = null;
	});

	const settled = { heat: 1, flow: 1, beans: 1, steam: 1, dust: 1, life: 1 };
	const wake = (from) => {
		if (!scene) return;
		const { amp, light } = scene;
		if (!from) {
			Object.assign(amp, settled);
			light.lamp = 1;
			scene.lights();
			return;
		}
		// the light comes on behind the glass, an old lamp catching on the second try
		const tl = gsap.timeline({ delay: from });
		tl.fromTo(light, { lamp: 0 }, { keyframes: [{ lamp: 0.6, duration: 0.05 }, { lamp: 0.1, duration: 0.09 }, { lamp: 0.8, duration: 0.05 }, { lamp: 0.35, duration: 0.12 }, { lamp: 1, duration: 0.6, ease: "power2.out" }], ease: "none", onUpdate: () => scene && scene.lights() }, 0);
		tl.to(amp, { heat: 1, duration: 1.6, ease: "power1.inOut" }, 0.2);
		tl.to(amp, { flow: 1, beans: 1, duration: 0.9, ease: "power2.out" }, 0.1);
		tl.to(amp, { steam: 1, duration: 1.4, ease: "power1.inOut" }, 0.35);
		tl.to(amp, { dust: 1, life: 1, duration: 1.6, ease: "power1.inOut" }, 1.4);
		cleanups.push(() => tl.kill());
	};

	const decoded = img.complete && img.naturalWidth ? Promise.resolve() : new Promise((r) => img.addEventListener("load", r, { once: true }));
	const ready = decoded.then(() => (img.decode ? img.decode().catch(() => {}) : null));

	if (!fresh) {
		signLettered("hero");
		ready.then(
			later(() => {
				build();
				wake(0);
			}),
		);
		return;
	}

	// Act 1 ------------------------------------------------------------------------------------------
	const captionVisible = caption && caption.getBoundingClientRect().top < window.innerHeight * 0.95;
	gsap.set(title, { opacity: 0.3 });
	if (caption) gsap.set(caption, { opacity: 0.3 });
	stripes.forEach((s) => gsap.set(s, { opacity: 0.3 }));
	weather.echoes.forEach((e) => gsap.set(e, { opacity: 0 }));
	root.dataset.hero = "letter";
	cleanups.push(() => {
		root.dataset.hero = "live";
		gsap.set([title, caption, ...stripes].filter(Boolean), { clearProps: "opacity" });
	});

	ready.then(
		later(() => {
			build();
			wake(0.25);
		}),
	);

	fontsReady.then(later(() => {
		const act = gsap.timeline();
		const parts = [];
		if (stripes[0]) parts.push(rule(act, stripes[0], 0, { liner: sign, cleanups, duration: phone ? 0.7 : 0.85 }));
		const letters = letterSign(title, cleanups, { perChar: phone ? 0.064 : 0.058, dip: 0.09 });
		act.add(letters.tl, 0.2);
		parts.push(letters);
		const lettersEnd = 0.2 + letters.tl.duration();
		if (stripes[1]) parts.push(rule(act, stripes[1], Math.max(1.2, lettersEnd - 1.15), { liner: sign, cleanups, duration: phone ? 0.6 : 0.75 }));
		const finish = () => {
			signLettered("hero");
			gsap.set([title, ...stripes], { clearProps: "opacity" });
			parts.forEach((part) => part.remove());
			applyScroll();
		};
		act.eventCallback("onComplete", finish);
		endOnResize(act, cleanups);

		if (caption) {
			if (captionVisible) pen(act, caption, 1.5, cleanups, { ink: "line" });
			else ScrollTrigger.create({ trigger: caption, start: "top 92%", once: true, onEnter: () => alive() && pen(null, caption, 0, cleanups, { ink: "line" }) });
		}
	}));
}
