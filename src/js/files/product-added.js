/*
The confirmation on a coffee's page once WooCommerce has really added it, outside the motion layer, so it is there
under reduced motion too. wp/shop.js reads the words out and dispatches "pa:added" on the form; the motion layer
(product.js) takes the event and strikes the stamp; when nothing takes it, the stamp is laid on still for a moment.

addedWords(count, name)          the status read out and the stamp's text, from the Shop wording ([count] the number of
                                 bags, [coffee] the coffee), the design's wording where a field is empty
stampMark(form, text)            the stamp "Added, N bags" placed on the price line, or across the button when the
                                 price is off the screen; returns { mark, host, restore }
*/

const words = window.prairieAsh || {};
const fill = (text, values) => Object.entries(values).reduce((out, [key, value]) => out.split(`[${key}]`).join(value), text);

export function addedWords(count, name) {
	const one = count === 1;
	const status = one ? words.addedStatusOne || "Added 1 bag of [coffee] to the cart" : words.addedStatusMany || "Added [count] bags of [coffee] to the cart";
	const stamp = one ? words.addedStampOne || "Added, 1 bag" : words.addedStampMany || "Added, [count] bags";
	const values = { count: String(count), coffee: name || "" };
	return { status: fill(status, values), stamp: fill(stamp, values) };
}

export function stampMark(form, text) {
	const sign = document.querySelector('[data-motion="product-sign"]');
	const button = form.querySelector(".product-summary__buy .button");
	const price = sign && sign.querySelector('[data-motion="price"]');
	const r = price && price.getBoundingClientRect();
	const onPrice = !!r && r.top >= 0 && r.bottom <= window.innerHeight;
	const host = onPrice ? sign : button && button.parentElement;
	if (!host) return null;
	const placed = getComputedStyle(host).position !== "static";
	if (!placed) host.style.position = "relative";
	const mark = document.createElement("span");
	mark.className = "product-stamp";
	mark.setAttribute("aria-hidden", "true");
	mark.textContent = text;
	host.append(mark);
	let left;
	let top;
	if (onPrice) {
		const range = document.createRange();
		range.selectNodeContents(price);
		const ink = range.getBoundingClientRect();
		const k = price.offsetWidth / (r.width || 1);
		left = price.offsetLeft + (ink.right - r.left) * k + 20;
		top = price.offsetTop + (price.offsetHeight - mark.offsetHeight) / 2;
		left = Math.min(left, host.clientWidth - mark.offsetWidth - 4);
	} else {
		left = button.offsetLeft + (button.offsetWidth - mark.offsetWidth) / 2;
		top = button.offsetTop + (button.offsetHeight - mark.offsetHeight) / 2;
	}
	Object.assign(mark.style, { left: `${left.toFixed(1)}px`, top: `${top.toFixed(1)}px` });
	const restore = () => {
		mark.remove();
		if (!placed) host.style.position = "";
	};
	return { mark, host, restore };
}

// The stamp at rest, as the struck one lands: square to the line but for the hand's tilt, then lifted off
let still = null;
export function stillStamp(form, text) {
	if (still) still();
	const placed = stampMark(form, text);
	if (!placed) return;
	placed.mark.style.transform = "rotate(-4deg)";
	const timer = window.setTimeout(() => still && still(), 3400);
	still = () => {
		window.clearTimeout(timer);
		placed.restore();
		still = null;
	};
}
