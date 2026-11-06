// Підключення функціоналу "Чертоги Фрілансера"
import { isMobile } from "./functions.js";
// Підключення списку активних модулів
import { flsModules } from "./modules.js";

// Motion layer (hand-off "Motion")
import { gsap } from "./motion.js";

// "All pages" on a phone: the page list opens in the flow under the header and pushes the page down.
// A disclosure, not a menu, so the template burger (which locks the scroll) is not used.
// Storyboard "page list open" (794:5029): height to auto in 360 ms, items rise 40 ms apart, close in 240 ms.
// Wording the theme prints from Site settings (inc/enqueue.php, prairie_ash_script_data()), markup wording as the fallback
const words = window.prairieAsh || {};
const pagesToggle = document.querySelector("[data-pages-toggle]");
const pagesPanel = document.querySelector("[data-pages-panel]");
if (pagesToggle && pagesPanel) {
	const reduced = window.matchMedia("(prefers-reduced-motion: reduce)");
	const items = pagesPanel.querySelectorAll("li, [data-motion='stripe'], .button");
	const firstLink = pagesPanel.querySelector("a");
	let run = null;
	const settle = () => {
		gsap.set(pagesPanel, { clearProps: "height,overflow" });
		gsap.set(items, { clearProps: "opacity,transform" });
	};
	const setPages = (open, instant = false) => {
		pagesToggle.setAttribute("aria-expanded", String(open));
		pagesToggle.textContent = open ? words.pagesOpenLabel || "Close page list" : words.pagesClosedLabel || "All pages";
		if (run) run.kill();
		run = null;
		const still = instant || reduced.matches;
		if (open) {
			pagesPanel.hidden = false;
			if (still) {
				settle();
				firstLink.focus();
				return;
			}
			run = gsap.timeline({ onComplete: () => { settle(); firstLink.focus(); } })
				.fromTo(pagesPanel, { height: 0, overflow: "clip" }, { height: "auto", duration: 0.36, ease: "expo.out" })
				.fromTo(items, { opacity: 0, y: 8 }, { opacity: 1, y: 0, duration: 0.28, ease: "expo.out", stagger: 0.04 }, 0.06);
		} else {
			if (still) {
				pagesPanel.hidden = true;
				settle();
				return;
			}
			run = gsap.timeline({ onComplete: () => { pagesPanel.hidden = true; settle(); } })
				.set(pagesPanel, { overflow: "clip" })
				.to(items, { opacity: 0, duration: 0.08, ease: "power3.inOut", stagger: { each: 0.012, from: "end" } })
				.to(pagesPanel, { height: 0, duration: 0.18, ease: "power3.inOut" }, 0.06);
		}
	};
	pagesToggle.addEventListener("click", () => {
		const open = pagesToggle.getAttribute("aria-expanded") !== "true";
		setPages(open);
		if (!open) pagesToggle.focus();
	});
	document.addEventListener("keydown", (e) => {
		if (e.key === "Escape" && pagesToggle.getAttribute("aria-expanded") === "true") {
			setPages(false);
			pagesToggle.focus();
		}
	});
	window.matchMedia("(min-width: 48em)").addEventListener("change", (e) => {
		if (e.matches) setPages(false, true);
	});
}

// "Open today" line, computed from the opening hours table in the cafe's own time zone
const todayLines = document.querySelectorAll("[data-today]");
const hoursRows = document.querySelectorAll("[data-hours] [data-days]");
if (todayLines.length && hoursRows.length) {
	const names = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
	const week = new Array(7).fill(null);
	hoursRows.forEach((row) => {
		row.dataset.days.split(",").forEach((d) => {
			week[Number(d)] = `${row.dataset.open} ${words.hoursJoiner || "to"} ${row.dataset.close}`;
		});
	});
	// the time zone set in WordPress (inc/enqueue.php), the cafe's own as the markup's fallback
	const zone = words.timeZone || "America/Winnipeg";
	let today;
	try {
		today = names.indexOf(new Intl.DateTimeFormat("en-US", { timeZone: zone, weekday: "long" }).format(new Date()));
	} catch (err) {
		// a fixed offset such as +05:30, which not every browser takes as a time zone
		const offset = /^([+-])(\d{2}):(\d{2})$/.exec(zone);
		const minutes = offset ? (offset[1] === "-" ? -1 : 1) * (Number(offset[2]) * 60 + Number(offset[3])) : 0;
		today = new Date(Date.now() + minutes * 60000).getUTCDay();
	}
	const openToday = words.todayOpen || "Open today";
	const closedToday = words.todayClosed || "Closed today, open";
	let text = "";
	if (week[today]) {
		text = `${openToday} ${week[today]}`;
	} else {
		for (let step = 1; step < 7; step++) {
			const next = (today + step) % 7;
			if (week[next]) {
				text = `${closedToday} ${step === 1 ? words.todayTomorrow || "tomorrow" : names[next]} ${week[next]}`;
				break;
			}
		}
	}
	if (text) {
		todayLines.forEach((line) => {
			const term = line.querySelector("[data-today-term]");
			const value = line.querySelector("[data-today-value]");
			if (!term || !value) {
				line.textContent = text;
				return;
			}
			// a split line keeps its label and puts the hours in the value
			const lead = [openToday, closedToday.replace(/\s*\S+$/, "")].find((l) => text.startsWith(l + " "));
			const [label, rest] = lead ? [lead, text.slice(lead.length + 1)] : ["", text];
			term.textContent = label.replace(/,$/, "");
			value.textContent = rest;
		});
	}
}

// Home: the hero photo runs up under the header, so the hero needs the header's real height
const heroWall = document.querySelector(".hero");
const pageHeader = document.querySelector("[data-header]");
if (heroWall && pageHeader && "ResizeObserver" in window) {
	new ResizeObserver(([entry]) => {
		heroWall.style.setProperty("--header-height", `${entry.borderBoxSize ? entry.borderBoxSize[0].blockSize : pageHeader.offsetHeight}px`);
	}).observe(pageHeader);
}

// Error summary above a form, built after the template validator has marked the fields.
// Exported so a form whose submit event is stopped before the document (Formidable) builds it too.
export const buildErrorSummary = (form) => {
	const box = form.querySelector("[data-error-summary-box]");
	const list = form.querySelector("[data-error-summary-list]");
	if (!box || !list) return;
	const fields = form.querySelectorAll("._form-error[data-summary]");
	list.innerHTML = "";
	fields.forEach((field) => {
		const item = document.createElement("li");
		const link = document.createElement("a");
		link.className = "text-link text-link--on-paper";
		link.href = `#${field.id}`;
		link.textContent = field.dataset.summary;
		link.addEventListener("click", (ev) => {
			ev.preventDefault();
			field.focus();
		});
		item.append(link);
		list.append(item);
	});
	box.hidden = fields.length === 0;
	if (fields.length) box.focus();
};
document.addEventListener("submit", (e) => {
	const form = e.target.closest("[data-error-summary]");
	if (form) buildErrorSummary(form);
});

// Analytics notice: shown until a choice is made, the choice kept for 12 months in localStorage
const notice = document.querySelector("[data-analytics-notice]");
if (notice) {
	const KEY = "analyticsChoice";
	const YEAR = 365 * 24 * 60 * 60 * 1000;
	const text = notice.querySelector("[data-analytics-text]");
	const defaultText = text.textContent;
	const read = () => {
		try {
			const saved = JSON.parse(localStorage.getItem(KEY));
			return saved && Date.now() - saved.at < YEAR ? saved.value : null;
		} catch (err) {
			return null;
		}
	};
	// Hand-off "Analytics notice": rises in 1200 ms after load on a first visit, leaves by 8 px
	const quiet = window.matchMedia("(prefers-reduced-motion: reduce)");
	const show = (late = false) => {
		notice.classList.remove("is-leaving");
		notice.hidden = false;
		notice.getBoundingClientRect();
		if (late && !quiet.matches) setTimeout(() => notice.classList.add("is-shown"), 1200);
		else notice.classList.add("is-shown");
	};
	const hide = () => {
		notice.classList.remove("is-shown");
		notice.classList.add("is-leaving");
		notice.addEventListener("transitionend", () => {
			if (!notice.classList.contains("is-shown")) {
				notice.hidden = true;
				notice.classList.remove("is-leaving");
			}
		}, { once: true });
	};
	notice.querySelectorAll("[data-analytics-choice]").forEach((button) => {
		button.addEventListener("click", () => {
			const value = button.dataset.analyticsChoice;
			try {
				localStorage.setItem(KEY, JSON.stringify({ value, at: Date.now() }));
			} catch (err) {}
			document.documentElement.classList.toggle("analytics-off", value === "off");
			if (value === "off") {
				text.textContent = words.analyticsOffText || "Counting is off on this browser. You can turn it back on under Analytics settings.";
			} else {
				hide();
			}
		});
	});
	document.querySelectorAll("[data-analytics-settings]").forEach((button) => {
		button.addEventListener("click", () => {
			text.textContent = read() === "off" ? words.analyticsOffText || "Counting is off on this browser. You can turn it back on under Analytics settings." : defaultText;
			show();
			notice.querySelector("[data-analytics-choice]").focus();
		});
	});
	const choice = read();
	document.documentElement.classList.toggle("analytics-off", choice === "off");
	if (!choice) show(true);
}
