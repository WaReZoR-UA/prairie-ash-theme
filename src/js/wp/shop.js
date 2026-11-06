// WordPress glue for the design's markup: adding a coffee from a card, the cart quantities, the
// checkout, and forms built with Formidable. Everything here enhances a form that already works
// without JavaScript.
import { formValidate } from "../files/forms/forms.js";
import { buildErrorSummary } from "../files/script.js";
import { addedWords, stillStamp } from "../files/product-added.js";

const data = window.prairieAsh || {};
const $ = window.jQuery;

// One status region for the whole page, read out by screen readers and taking no space.
let status = null;
const announce = (text) => {
	if (!status) {
		status = document.createElement("div");
		status.className = "visually-hidden";
		status.setAttribute("role", "status");
		document.body.append(status);
	}
	status.textContent = "";
	window.setTimeout(() => (status.textContent = text), 50);
};

// Card buttons: add one bag through WooCommerce's own add-to-cart endpoint, then swap in the
// header cart links it returns. Without JavaScript the button posts the shared card form.
document.addEventListener("click", async (event) => {
	const button = event.target.closest("[data-card-add]");
	if (!button || !data.wcAjax) return;
	event.preventDefault();
	if (button.getAttribute("aria-busy") === "true") return;
	button.setAttribute("aria-busy", "true");
	announce(data.addingLabel || "");
	const body = new URLSearchParams({ product_id: button.value, quantity: "1" });
	try {
		const response = await fetch(data.wcAjax.replace("%%endpoint%%", "add_to_cart"), { method: "POST", body, credentials: "same-origin" });
		const result = await response.json();
		if (result.error && result.product_url) {
			// the page may come back from the back/forward cache, so the button is left ready
			button.removeAttribute("aria-busy");
			window.location.href = result.product_url;
			return;
		}
		Object.entries(result.fragments || {}).forEach(([selector, html]) => {
			document.querySelectorAll(selector).forEach((node) => {
				const holder = document.createElement("div");
				holder.innerHTML = html;
				if (holder.firstElementChild) node.replaceWith(holder.firstElementChild);
			});
		});
		announce(data.addedLabel || "");
		// read now rather than at load: on a page with a form, jQuery is deferred and runs after this bundle
		const jq = window.jQuery;
		if (jq) jq(document.body).trigger("added_to_cart", [result.fragments, result.cart_hash, jq(button)]);
	} catch (error) {
		button.removeAttribute("aria-busy");
		button.form ? button.form.requestSubmit(button) : null;
		return;
	}
	button.removeAttribute("aria-busy");
});

// A page restored from the back/forward cache keeps the state it was left in: no button stays busy.
window.addEventListener("pageshow", (event) => {
	if (event.persisted) document.querySelectorAll('[data-card-add][aria-busy="true"], form.product-summary__form[aria-busy="true"]').forEach((el) => el.removeAttribute("aria-busy"));
});

// Product page: Add to cart stays on the page, as the design stamps the added coffee on the price line and
// announces it. The form is posted to its own address, which runs WooCommerce's add-to-cart handler
// exactly as the plain post does, and the header cart links are taken from the page that comes back.
// An error, or a failed request, posts the form for real, so WooCommerce shows its notice. Only a clean
// answer is confirmed: the words are read out here, and the stamp is struck by the motion layer when it
// takes the "pa:added" event, or laid on still when it does not (reduced motion, or its chunk not in yet).
const productForm = document.querySelector("form.product-summary__form");
if (productForm) {
	let plain = false;
	const added = (body) => {
		const count = Math.max(1, parseInt(body.get("quantity"), 10) || 1);
		const name = document.querySelector('[data-motion="product-sign"] h1');
		const words = addedWords(count, name ? name.textContent.trim() : "");
		announce(name ? words.status : data.addedLabel || "");
		const event = new CustomEvent("pa:added", { cancelable: true, detail: { count, stamp: words.stamp } });
		if (productForm.dispatchEvent(event)) stillStamp(productForm, words.stamp);
	};
	productForm.addEventListener("submit", async (event) => {
		const button = event.submitter;
		if (plain || !button || button.name !== "add-to-cart" || !window.fetch || !window.DOMParser) return;
		event.preventDefault();
		if (productForm.getAttribute("aria-busy") === "true") return;
		productForm.setAttribute("aria-busy", "true");
		const postForReal = () => {
			plain = true;
			productForm.removeAttribute("aria-busy");
			productForm.requestSubmit(button);
		};
		const body = new FormData(productForm);
		body.set(button.name, button.value);
		try {
			const response = await fetch(productForm.action, { method: "POST", body, credentials: "same-origin" });
			if (!response.ok) return postForReal();
			const page = new DOMParser().parseFromString(await response.text(), "text/html");
			if (page.querySelector(".woocommerce-error, [data-wc-errors]")) return postForReal();
			const fresh = [...page.querySelectorAll("a[data-cart-link]")];
			document.querySelectorAll("a[data-cart-link]").forEach((link, i) => {
				if (fresh[i]) link.replaceWith(document.importNode(fresh[i], true));
			});
		} catch (error) {
			return postForReal();
		}
		productForm.removeAttribute("aria-busy");
		added(body);
	});
}

// Cart: a changed quantity updates the cart at once, through WooCommerce's own update handler. Steps
// clicked on the spinner are gathered for half a second; a visitor who leaves within it does not lose them:
// Go to checkout sends the update with WooCommerce's own "proceed", which goes on to the checkout, and any
// other way off the page sends it on its way first.
const cartForm = document.querySelector("[data-cart-form]");
if (cartForm) {
	let timer = null;
	const flag = (name) => {
		const input = document.createElement("input");
		input.type = "hidden";
		input.name = name;
		input.value = "1";
		cartForm.append(input);
	};
	const update = (proceed = false) => {
		window.clearTimeout(timer);
		timer = null;
		flag("update_cart");
		if (proceed) flag("proceed");
		cartForm.submit();
	};
	cartForm.addEventListener("change", (event) => {
		if (!event.target.matches("[data-cart-quantity]")) return;
		window.clearTimeout(timer);
		timer = window.setTimeout(() => update(), 500);
	});
	// a submit of the form itself (Enter, a remove button) carries the quantities with it
	cartForm.addEventListener("submit", () => {
		window.clearTimeout(timer);
		timer = null;
	});
	const checkoutLink = document.querySelector(".cart-summary__actions a.button");
	document.addEventListener("click", (event) => {
		if (!timer || event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
		const link = event.target.closest("a[href]");
		if (!link || (link.target && link.target !== "_self")) return;
		const url = new URL(link.href, location.href);
		// a link within this page leaves nothing behind
		if (url.origin === location.origin && url.pathname === location.pathname && url.search === location.search && url.hash) return;
		event.preventDefault();
		if (link === checkoutLink) update(true);
		else {
			const body = new FormData(cartForm);
			body.set("update_cart", "1");
			window.clearTimeout(timer);
			timer = null;
			fetch(cartForm.action, { method: "POST", body, credentials: "same-origin", keepalive: true }).finally(() => (window.location.href = link.href));
		}
	}, true);
	window.addEventListener("pagehide", () => {
		if (!timer) return;
		const body = new FormData(cartForm);
		body.set("update_cart", "1");
		window.clearTimeout(timer);
		timer = null;
		navigator.sendBeacon(cartForm.action, body);
	});
}

// Checkout.
const checkout = document.querySelector("form.woocommerce-checkout");
if (checkout && $) {
	const $checkout = $(checkout);
	const box = checkout.querySelector("[data-error-summary-box]");
	const list = checkout.querySelector("[data-error-summary-list]");

	// The design's own check runs first; WooCommerce sends nothing while a field is wrong.
	// WooCommerce's own submit handler returns false, which stops the event before the document,
	// where the design's script builds the error summary, so the summary is built here.
	$checkout.on("checkout_place_order", () => {
		const valid = formValidate.getErrors(checkout) === 0;
		if (!valid) buildErrorSummary(checkout);
		return valid;
	});

	// Pickup needs no address: the address fields hide, and the design's check skips hidden fields.
	const syncAddress = () => {
		const chosen = checkout.querySelector('input[name^="shipping_method"]:checked');
		const group = checkout.querySelector("[data-address-fields]");
		if (group) group.hidden = !!chosen && chosen.dataset.pickup === "1";
	};
	checkout.addEventListener("change", (event) => {
		if (event.target.matches('input[name^="shipping_method"]')) syncAddress();
		if (event.target.matches("#billing_state, #billing_postcode, #billing_city")) $(document.body).trigger("update_checkout");
	});
	$(document.body).on("updated_checkout", syncAddress);

	// WooCommerce's errors arrive as a notice above the form; they move into the design's summary. Its
	// script wraps each field's item in a second link around the design's own, which the parser splits
	// into an empty link before the real one, so an empty link is dropped. A failure that comes with no
	// list (a timeout, a server error, a refused nonce, a gateway with nothing to say) carries only
	// WooCommerce's own sentence: that sentence becomes the summary's item, so it is never lost.
	$(document.body).on("checkout_error", () => {
		const group = document.querySelector(".woocommerce-NoticeGroup-checkout");
		if (!group || !box || !list) return;
		const items = [...group.querySelectorAll(".error-summary__list > li")];
		const text = items.length ? "" : group.textContent.replace(/\s+/g, " ").trim();
		if (!items.length && !text) return;
		list.innerHTML = "";
		items.forEach((item) => {
			item.querySelectorAll("a").forEach((link) => {
				if (!link.textContent.trim()) link.remove();
			});
			list.append(item);
		});
		if (text) {
			const item = document.createElement("li");
			item.textContent = text;
			list.append(item);
		}
		group.remove();
		list.querySelectorAll("a[href^='#']").forEach((link) => {
			link.addEventListener("click", (e) => {
				e.preventDefault();
				const field = document.getElementById(link.getAttribute("href").slice(1));
				if (field) field.focus();
			});
		});
		box.hidden = list.children.length === 0;
		if (!box.hidden) box.focus();
	});
}

// Formidable: its submit handler sends the form even when the design's check cancelled the event,
// so a cancelled submit stops here, before it reaches the handler on the document. The design's
// error summary, which also listens on the document, is built here instead.
document.querySelectorAll("form.pa-form").forEach((form) => {
	form.addEventListener("submit", (event) => {
		if (!event.defaultPrevented) return;
		event.stopPropagation();
		if (form.matches("[data-error-summary]")) buildErrorSummary(form);
	});
});
