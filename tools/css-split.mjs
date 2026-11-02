// Splits the compiled stylesheet into a global sheet and sheets that load only where they can match.
//
// Units are the blocks (blocks/<name>/) and three WooCommerce template groups (single-product,
// cart, checkout). A BEM root class belongs to the set of units whose files print it, or to the
// global sheet when any other theme file prints it as well. A rule leaves the global sheet when
// each of its selectors names at least one class with a unit set, because the selector can then
// only match on a page where one of those units renders. Classes inside :not(), :is(), :where()
// and :matches() prove nothing about the page and are ignored. Keyframes, font faces and any class
// only a script adds stay global. A moved rule keeps the whole chain of @media and @supports
// wrappers around it and its order inside its own sheet. Splitting only the outer level is not
// enough: a rule left behind inside a nested group would then sit before a base rule it used to
// follow, and lose to it. A set of units whose rules add up to less than MIN_BYTES stays global.
//
// The split sheets are printed after the global one (see inc/enqueue.php), so a moved rule lands
// later in the cascade than before. The build is accepted by comparing the computed style of every
// element on every page before and after, not by this script.
import fs from 'fs';
import path from 'path';

const SCAN_SKIP = new Set(['vendor', 'node_modules', 'assets', '.git', 'tools', 'src', 'languages', 'acf-json']);
// Files that print no markup but name blocks or pages in plain words: the package manifests and the
// motion layer's page-flag map (inc/motion.php), whose block names would read as printed classes.
const SCAN_SKIP_FILES = new Set(['package.json', 'package-lock.json', 'composer.json', 'composer.lock', 'inc/motion.php']);

// The unit a theme file belongs to: a block, a WooCommerce template group, or null for global.
function unitOf(rel) {
	let m = rel.match(/^blocks\/([^/]+)\//);
	if (m) return 'block:' + m[1];
	if (/^woocommerce\/single-product(\.php|\/)/.test(rel)) return 'template:product';
	if (/^woocommerce\/cart\//.test(rel)) return 'template:cart';
	if (/^woocommerce\/checkout\//.test(rel)) return 'template:checkout';
	return null;
}

function themeFiles(root) {
	const out = [];
	const walk = (dir) => {
		for (const f of fs.readdirSync(dir, { withFileTypes: true })) {
			const p = path.join(dir, f.name);
			if (f.isDirectory()) {
				if (!SCAN_SKIP.has(f.name)) walk(p);
			} else if (/\.(php|json|html)$/.test(f.name) && !SCAN_SKIP_FILES.has(path.relative(root, p).split(path.sep).join('/'))) {
				out.push([path.relative(root, p).split(path.sep).join('/'), fs.readFileSync(p, 'utf8')]);
			}
		}
	};
	walk(root);
	return out;
}

const MIN_BYTES = 512;
const bemRoot = (cls) => cls.split(/__|--/)[0];
const escape = (s) => s.replace(/[-/\\^$*+?.()|[\]{}]/g, '\\$&');

// The units that print each BEM root, or null when a file outside every unit prints it too.
function rootOwners(root, roots) {
	const files = themeFiles(root);
	const owners = new Map();
	for (const r of roots) {
		const re = new RegExp(`(?<![\\w-])${escape(r)}(?![\\w]|-(?!-))`);
		const units = new Set();
		for (const [rel, text] of files) if (re.test(text)) units.add(unitOf(rel));
		owners.set(r, units.size && !units.has(null) ? units : null);
	}
	return owners;
}

// Classes the design's scripts add to the page at run time (src/js). A script can add one on any page
// whatever BEM root it carries (the motion layer letters every page's title with the hero's echo and
// caption-ink classes), so such a class proves nothing about which block renders: a selector whose
// only unit classes are script-added stays global. A name written with a leading dot is a query, not an
// addition, and does not count. A name built in a template string (`hero__echo--${kind}`) counts for
// every class that starts with its fixed part.
function scriptClasses(root) {
	const names = new Set();
	const prefixes = new Set();
	const walk = (dir) => {
		for (const f of fs.readdirSync(dir, { withFileTypes: true })) {
			const p = path.join(dir, f.name);
			if (f.isDirectory()) walk(p);
			else if (/\.js$/.test(f.name)) {
				const text = fs.readFileSync(p, 'utf8');
				for (const m of text.matchAll(/(^|[^.\w-])(-?[_a-zA-Z][\w-]*(?:__|--)[\w-]+)/g)) names.add(m[2]);
				for (const m of text.matchAll(/(^|[^.\w-])(-?[_a-zA-Z][\w-]*(?:__|--|-))\$\{/g)) prefixes.add(m[2]);
				for (const m of text.matchAll(/(?:className\s*=\s*|classList\.(?:add|toggle|replace)\()[`'"]([^`'"]+)/g)) {
					m[1].split(/[\s$}{]+/).forEach((c) => /^-?[_a-zA-Z][\w-]*$/.test(c) && names.add(c));
				}
			}
		}
	};
	walk(path.join(root, 'src/js'));
	return { has: (c) => names.has(c) || [...prefixes].some((x) => c.startsWith(x)) };
}

const proofClasses = (selector) => {
	const plain = selector.replace(/:(not|is|where|matches|-webkit-any)\((?:[^()]|\([^()]*\))*\)/g, '');
	return [...plain.matchAll(/\.(-?[_a-zA-Z][\w-]*)/g)].map((m) => m[1]);
};

/**
 * Split a compiled stylesheet by the blocks and templates that can use each rule.
 *
 * A selector can only match where every class it names is present, so it is enough to load it
 * wherever ONE of those classes can be printed: the smallest owner set among its classes. A rule
 * with several selectors loads with the union of their sets. Rules that share the same set of
 * units share one sheet.
 *
 * @param {string} css      The compiled stylesheet.
 * @param {string} root     Theme root, scanned for the files that print each class.
 * @param {object} postcss  The postcss module.
 * @param {Function} hash   Short stable name for a set of units.
 * @returns {{ global: string, sheets: Array<{ name: string, units: string[], css: string }> }}
 */
export function splitCss(css, root, postcss, hash) {
	const tree = postcss.parse(css);
	const roots = new Set();
	tree.walkRules((rule) => rule.selectors.forEach((s) => proofClasses(s).forEach((c) => roots.add(bemRoot(c)))));
	const owners = rootOwners(root, roots);
	const added = scriptClasses(root);

	const ownerOfRule = (rule) => {
		const union = new Set();
		for (const sel of rule.selectors) {
			const sets = proofClasses(sel).map((c) => (added.has(c) ? null : owners.get(bemRoot(c)))).filter(Boolean);
			if (!sets.length) return null;
			sets.sort((a, b) => a.size - b.size)[0].forEach((u) => union.add(u));
		}
		return [...union].sort().join(' ');
	};

	// A set whose rules add up to less than MIN_BYTES stays in the global sheet, where it costs less
	// than a stylesheet handle of its own and keeps its original place in the cascade.
	const size = new Map();
	tree.walkRules((rule) => {
		const key = ownerOfRule(rule);
		if (key) size.set(key, (size.get(key) || 0) + rule.toString().length);
	});
	const ownerOf = (rule) => {
		const key = ownerOfRule(rule);
		return key && size.get(key) >= MIN_BYTES ? key : null;
	};

	const sheets = new Map();
	const take = (key, node) => {
		if (!sheets.has(key)) sheets.set(key, postcss.root());
		sheets.get(key).append(node);
	};

	// Conditional groups are split at every depth: a moved rule keeps the whole chain of @media and
	// @supports wrappers around it, and whatever stays behind keeps its own.
	const extract = (container) => {
		const byKey = new Map();
		const push = (key, node) => {
			if (!byKey.has(key)) byKey.set(key, []);
			byKey.get(key).push(node);
		};
		for (const node of [...container.nodes]) {
			if (node.type === 'rule') {
				const key = ownerOf(node);
				if (key) push(key, node.remove());
			} else if (node.type === 'atrule' && /^(media|supports)$/.test(node.name) && node.nodes) {
				for (const [key, nodes] of extract(node)) {
					const wrap = node.clone({ nodes: [] });
					nodes.forEach((n) => wrap.append(n));
					push(key, wrap);
				}
				if (!node.nodes.length) node.remove();
			}
		}
		return byKey;
	};
	for (const [key, nodes] of extract(tree)) nodes.forEach((n) => take(key, n));

	const out = [];
	for (const [key, sheet] of sheets) {
		const units = key.split(' ');
		const name = 1 === units.length ? units[0].replace(/^[a-z]+:/, '') : 'shared-' + hash(key);
		out.push({ name, units, css: sheet.toString() });
	}
	return { global: tree.toString(), sheets: out };
}
