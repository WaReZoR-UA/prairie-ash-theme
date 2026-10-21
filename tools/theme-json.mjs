// Writes theme.json from the design's own settings block (variables.scss and typography.scss).
import fs from 'fs';
const fluid = (d, m) => {
	if (d === m) return `${+(d / 16).toFixed(4)}rem`;
	const slope = (d - m) / (1440 - 390);
	const y = -390 * slope + m;
	return `clamp(${+(m / 16).toFixed(4)}rem, ${+(y / 16).toFixed(4)}rem + ${+(slope * 100).toFixed(4)}vw, ${+(d / 16).toFixed(4)}rem)`;
};
const palette = [
	['brick-500', '#8c3a26', 'Brick'], ['brick-700', '#5e2417', 'Deep brick'], ['brick-800', '#4c1307', 'Darkest brick'],
	['cream-100', '#f1e4c8', 'Cream'], ['cream-200', '#e0d4b8', 'Cream shade'], ['cream-300', '#d0c3a8', 'Cream dark'],
	['soot-900', '#1e1a17', 'Soot'], ['soot-700', '#3c3734', 'Soot light'],
	['mustard-400', '#d9a441', 'Mustard'], ['mustard-500', '#c9942e', 'Mustard deep'], ['mustard-600', '#b88515', 'Mustard dark'],
].map(([slug, color, name]) => ({ slug, color, name }));
// size desktop, size mobile, line desktop, line mobile, from typography.scss
const type = [
	['sign-xl', 'Sign extra large', 156, 64, 181], ['sign-l', 'Sign large', 125, 56, 145], ['sign-m', 'Sign medium', 100, 48, 116],
	['heading-1', 'Heading 1', 80, 44, 93], ['heading-2', 'Heading 2', 64, 38, 74], ['heading-3', 'Heading 3', 51, 32, 59],
	['heading-4', 'Heading 4', 41, 28, 48], ['label-l', 'Label large', 33, 26, 38], ['lead', 'Lead text', 26, 21, 34],
	['body', 'Body text', 21, 18, 30], ['small', 'Small text', 17, 16, 24],
];
const fontSizes = type.map(([slug, name, d, m]) => ({ slug, name, size: fluid(d, m), fluid: false }));
const lineHeights = Object.fromEntries(type.map(([slug, , d, , l]) => [slug, +(l / d).toFixed(3)]));
// space/3 to space/9, seven steps so the editor draws a slider
const space = [[3, 12, 8], [4, 16, 12], [5, 24, 16], [6, 32, 24], [7, 48, 32], [8, 64, 40], [9, 96, 48]];
const spacingSizes = space.map(([n, d, m]) => ({ slug: String(n * 10), name: `Space ${n}`, size: fluid(d, m) }));
const json = {
	$schema: 'https://schemas.wp.org/wp/6.6/theme.json',
	version: 3,
	settings: {
		appearanceTools: false,
		useRootPaddingAwareAlignments: false,
		layout: { contentSize: '82rem', wideSize: '90rem' },
		background: { backgroundImage: true, backgroundSize: true },
		dimensions: { minHeight: true, aspectRatio: false },
		color: {
			custom: false, customDuotone: false, customGradient: false,
			defaultPalette: false, defaultGradients: false, defaultDuotone: false,
			text: true, background: true, link: false, heading: false, button: false,
			gradients: [], duotone: [], palette,
		},
		typography: {
			customFontSize: false, defaultFontSizes: false, fluid: false, dropCap: false,
			fontStyle: false, fontWeight: false, letterSpacing: false, lineHeight: false,
			textAlign: true, textDecoration: false, textTransform: false, writingMode: false, textColumns: false,
			fontFamilies: [
				{ slug: 'serif', name: 'Instrument Serif', fontFamily: '"InstrumentSerif", Georgia, serif' },
				{ slug: 'sign', name: 'League Gothic', fontFamily: '"LeagueGothic", "Arial Narrow", sans-serif' },
			],
			fontSizes,
		},
		spacing: {
			customSpacingSize: false, defaultSpacingSizes: false, margin: true, padding: true, blockGap: false,
			units: ['rem'], spacingSizes,
		},
		shadow: { defaultPresets: false, presets: [] },
		border: { color: false, radius: false, style: false, width: false },
		position: { sticky: false },
		custom: { lineHeight: lineHeights },
	},
	styles: {},
};
fs.writeFileSync(process.argv[2], JSON.stringify(json, null, '\t') + '\n');
console.log('written', fontSizes.length, 'sizes', spacingSizes.length, 'spaces', palette.length, 'colours');
