// Compiles the design's SCSS and JS from src/ into assets/.
//
// Usage: node tools/build.mjs            (production, minified)
//        node tools/build.mjs --watch    (rebuild on change)
//
// The packages come from ./node_modules after "npm install", or from the folder named in
// PRAIRIE_ASH_NODE_MODULES, so a machine that already holds the markup project's packages can
// build without a second install.
import { createRequire } from 'module';
import { createHash } from 'crypto';
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';
import { slimCss, collapseFluid } from './css-slim.mjs';
import { splitCss } from './css-split.mjs';

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const modules = process.env.PRAIRIE_ASH_NODE_MODULES || path.join(root, 'node_modules');
const require = createRequire(path.join(modules, 'noop.js'));
const sass = require('sass');
const postcss = require('postcss');
const webpack = require('webpack');
const watch = process.argv.includes('--watch');

// Stylesheets: the design's entry point, and the theme's own additions for WordPress.
// The design's entry point stays as the markup project wrote it; the WordPress additions are
// compiled after it into the same file, so they win the cascade without editing the design's SCSS.
const sheets = [
	[['src/scss/style.scss', 'src/scss/wp/frontend.scss'], 'assets/css/main.css'],
	[['src/scss/wp/editor.scss'], 'assets/css/editor.css'],
];

function buildCss() {
	for (const [sources, out] of sheets) {
		const parts = sources.map((src) => sass.compile(path.join(root, src), { style: 'compressed', loadPaths: [path.join(root, 'src/scss')] }).css);
		// The markup project resolves the @img alias at build time. Here the images sit in assets/img.
		// minus the clamp() fallbacks and the pinned blocks adaptiveValue writes around each clamp(), see css-slim.mjs
		let css = collapseFluid(slimCss(parts.join('\n'))).replaceAll('@img/', '../img/');
		fs.mkdirSync(path.dirname(path.join(root, out)), { recursive: true });
		if (out === 'assets/css/main.css') css = writeSplit(css);
		fs.writeFileSync(path.join(root, out), css);
		console.log('css', out, css.length, 'bytes');
	}
}

// The design stylesheet is split so that each block's rules load only where the block renders and
// each WooCommerce template's rules only on that template, see css-split.mjs. The split sheets and
// the manifest inc/enqueue.php reads sit in assets/css/split, one folder below assets/css, so their
// relative image and font paths climb one more level.
function writeSplit(css) {
	const hash = (key) => createHash('sha1').update(key).digest('hex').slice(0, 8);
	const { global, sheets } = splitCss(css, root, postcss, hash);
	const dir = path.join(root, 'assets/css/split');
	fs.rmSync(dir, { recursive: true, force: true });
	fs.mkdirSync(dir, { recursive: true });
	const manifest = {};
	for (const { name, units, css: text } of sheets) {
		fs.writeFileSync(path.join(dir, name + '.css'), text.replaceAll('../img/', '../../img/').replaceAll('../fonts/', '../../fonts/'));
		manifest[name] = {
			blocks: units.filter((u) => u.startsWith('block:')).map((u) => 'prairie-ash/' + u.slice(6)),
			templates: units.filter((u) => u.startsWith('template:')).map((u) => u.slice(9)),
		};
	}
	fs.writeFileSync(path.join(dir, 'manifest.json'), JSON.stringify(manifest, null, '\t') + '\n');
	console.log('css', 'assets/css/split', sheets.length, 'sheets,', sheets.reduce((n, s) => n + s.css.length, 0), 'bytes');
	return global;
}

const config = {
	mode: 'production',
	context: root,
	entry: { main: './src/js/theme.js' },
	// lazy chunks carry a content hash, the entry is versioned on its mtime by enqueue.php
	output: { path: path.join(root, 'assets/js'), filename: '[name].js', chunkFilename: '[name].[contenthash:8].js' },
	resolve: { modules: [path.join(root, 'src/js'), modules] },
	resolveLoader: { modules: [modules, path.join(root, 'tools')] },
	module: {
		rules: [
			// The markup entry imports its stylesheet for the dev server. Sass compiles it above.
			{ test: /\.s?css$/, use: [path.join(root, 'tools/empty-loader.cjs')] },
		],
	},
	performance: { hints: false },
	devtool: false,
};

buildCss();
const compiler = webpack(config);
const report = (err, stats) => {
	if (err || stats.hasErrors()) {
		console.error(err || stats.toString('errors-only'));
		process.exitCode = 1;
		return;
	}
	console.log('js', stats.toString({ assets: true, modules: false, colors: false }).split('\n').slice(0, 4).join('\n'));
};
if (watch) {
	compiler.watch({}, report);
	fs.watch(path.join(root, 'src/scss'), { recursive: true }, () => {
		try {
			buildCss();
		} catch (e) {
			console.error(e.message);
		}
	});
} else {
	compiler.run((err, stats) => {
		report(err, stats);
		compiler.close(() => {});
	});
}
