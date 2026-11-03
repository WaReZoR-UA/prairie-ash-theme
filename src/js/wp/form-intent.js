// jQuery and Formidable's script arrive as inert tags (inc/performance.php) on a page whose forms post
// without them. The first pointer, key or focus inside a form turns them into scripts, in their order.
const held = document.querySelectorAll("script[data-intent-src]");
const forms = document.querySelectorAll(".frm-show-form");

if (held.length && forms.length) {
	const events = ["pointerdown", "touchstart", "keydown", "focusin"];
	const load = () => {
		forms.forEach((form) => events.forEach((type) => form.removeEventListener(type, load, true)));
		held.forEach((tag) => {
			const script = document.createElement("script");
			script.id = tag.id;
			script.async = false;
			script.src = tag.dataset.intentSrc;
			tag.replaceWith(script);
		});
	};
	forms.forEach((form) => events.forEach((type) => form.addEventListener(type, load, { capture: true, passive: true })));
}
