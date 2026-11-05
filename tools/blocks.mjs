// Writes blocks/<slug>/block.json for every section block from one shared definition.
//
// Usage: node tools/blocks.mjs
//
// Every section block opens the same control surface, so the supports live here once and a block
// only names what is its own: title, description, icon, keywords, variants and a sample for the
// inserter. CONTROL-SURFACE.md is written beside the manifests from the same list, so the record of
// what each block leaves out cannot drift from what the manifests declare.
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const blocks = JSON.parse(fs.readFileSync(path.join(root, 'tools/blocks.json'), 'utf8'));
const DECIDED = '2026-10-19';

const supports = {
	anchor: true,
	align: false,
	html: false,
	customCSS: false,
	customClassName: false,
	color: {
		background: true,
		text: true,
		gradients: false,
		link: false,
		enableContrastChecker: false,
		__experimentalSkipSerialization: true,
		__experimentalDefaultControls: { background: true, text: true },
	},
	spacing: {
		padding: ['top', 'bottom', 'left', 'right'],
		margin: ['top', 'bottom'],
		__experimentalSkipSerialization: true,
		__experimentalDefaultControls: { padding: true, margin: true },
	},
	dimensions: {
		minHeight: true,
		__experimentalSkipSerialization: true,
		__experimentalDefaultControls: { minHeight: true },
	},
	background: {
		backgroundImage: true,
		backgroundSize: true,
		__experimentalSkipSerialization: true,
		__experimentalDefaultControls: { backgroundImage: true },
	},
};

// What every section leaves out, and why. One row per capability per block in CONTROL-SURFACE.md.
const leftOut = [
	['color.gradients', 'The design contains no gradient anywhere, and the client asked for colours from the theme presets only, so there is no gradient preset to offer.'],
	['typography.fontSize', 'Every text element of the section sets its own size in the design, so a section-wide size would reach no text. The per-element panels offer the design type scale per heading, label and body instead.'],
	['typography.lineHeight', 'Line height follows the chosen text size in the per-element panels, at the ratio the design draws for that size, so a separate section-wide value would fight it.'],
	['typography.textAlign', 'Alignment is set per element in the per-element panels, so a heading can be centred while the body stays left. A section-wide control would fight those panels.'],
	['typography.__experimentalFontFamily', 'The design pairs League Gothic for signs and labels with Instrument Serif for running text, and every element sets its own family, so a section-wide family would reach no text.'],
	['typography.__experimentalFontStyle', 'The design uses italic only where it draws it, and every element sets its own style, so a section-wide style would reach no text.'],
	['typography.__experimentalFontWeight', 'Both typefaces ship one weight only, so any other weight would be drawn by the browser as a faked bold.'],
	['typography.__experimentalLetterSpacing', 'Tracking belongs to the label styles of the type scale and every element sets its own, so a section-wide value would reach no text.'],
	['typography.__experimentalTextDecoration', 'Underlines are reserved for links in this design, where they carry the link state, so decoration on other text would read as a link.'],
	['typography.__experimentalTextTransform', 'Upper case belongs to the label styles of the type scale and every element sets its own, so a section-wide value would reach no text.'],
	['align', 'Every section is full bleed by design with its own centred container, and a classic page has no narrower content column for a wide or full alignment to break out of, so the control would do nothing.'],
];

let table = '';
for (const [slug, def] of Object.entries(blocks)) {
	const dir = path.join(root, 'blocks', slug);
	fs.mkdirSync(dir, { recursive: true });
	const manifest = {
		$schema: 'https://schemas.wp.org/trunk/block.json',
		apiVersion: 3,
		name: `prairie-ash/${slug}`,
		title: def.title,
		description: def.description,
		category: 'prairie-ash',
		icon: def.icon || 'align-wide',
		keywords: def.keywords || [],
		textdomain: 'prairie-ash',
		attributes: {
			className: { type: 'string' },
			paStyle: { type: 'object', default: {} },
		},
		supports,
	};
	if (def.styles) manifest.styles = def.styles;
	if (def.example) {
		const data = {};
		for (const [name, value] of Object.entries(def.example)) data[`field_pa_${slug.replaceAll('-', '_')}_${name}`] = value;
		manifest.example = { attributes: { mode: 'preview', data } };
	}
	manifest.acf = { blockVersion: 3, renderTemplate: 'render.php', autoInlineEditing: false };
	fs.writeFileSync(path.join(dir, 'block.json'), JSON.stringify(manifest, null, '\t') + '\n');
	for (const [capability, reason] of leftOut) table += `| ${slug} | ${capability} | ${DECIDED} | ${reason} |\n`;
}

// Read with LF only, so a checkout with CRLF endings cannot mix the two in the generated file.
const head = fs.readFileSync(path.join(root, 'tools/control-surface-head.md'), 'utf8').replace(/\r\n/g, '\n');
fs.writeFileSync(path.join(root, 'CONTROL-SURFACE.md'), head + table);
console.log('manifests', Object.keys(blocks).length);
