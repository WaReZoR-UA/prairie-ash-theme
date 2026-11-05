// Removes the clamp() fallbacks that adaptiveValue writes for browsers without clamp().
// Every browser the theme supports has clamp() (Safari 13.1, Chrome 79, Firefox 75), so each
// "@supports not (...clamp...)" block is dead weight and each "@supports (...clamp...)" wrapper
// is unwrapped. The SCSS stays the start template's own; only the compiled output changes.
// Brace-aware, so nested @media blocks survive intact.

function blockEnd(css, open) {
	let depth = 0;
	let quote = null;
	for (let i = open; i < css.length; i++) {
		const c = css[i];
		if (quote) {
			if (c === '\\') i++;
			else if (c === quote) quote = null;
			continue;
		}
		if (c === '"' || c === "'") quote = c;
		else if (c === '{') depth++;
		else if (c === '}') {
			depth--;
			if (depth === 0) return i;
		}
	}
	throw new Error('css-slim: unbalanced braces');
}

export function slimCss(css) {
	let out = '';
	let i = 0;
	while (i < css.length) {
		const at = css.indexOf('@supports', i);
		if (at === -1) {
			out += css.slice(i);
			break;
		}
		const open = css.indexOf('{', at);
		const prelude = css.slice(at, open);
		if (!/clamp\(/.test(prelude)) {
			out += css.slice(i, open + 1);
			i = open + 1;
			continue;
		}
		out += css.slice(i, at);
		const end = blockEnd(css, open);
		// the clamp branch is kept without its wrapper, the fallback branch is dropped
		if (!/^@supports\s*not/.test(prelude)) out += slimCss(css.slice(open + 1, end));
		i = end + 1;
	}
	return out;
}

