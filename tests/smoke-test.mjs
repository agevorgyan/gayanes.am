/**
 * Automated Smoke Test for Gayane's Kitchen AJAX Cart & Quantity Stepper (cart.js).
 * Runs in Node without external dependencies.
 */
import assert from 'node:assert/strict';
import fs from 'node:fs';
import vm from 'node:vm';

class FakeClassList {
	constructor(el) {
		this.el = el;
		this.set = new Set();
	}
	add(...names) { names.forEach((n) => this.set.add(n)); }
	remove(...names) { names.forEach((n) => this.set.delete(n)); }
	contains(n) { return this.set.has(n); }
}

class FakeElement {
	constructor(tag, attrs = {}) {
		this.tagName = tag.toUpperCase();
		this.attrs = { ...attrs };
		this.children = [];
		this.parentNode = null;
		this.classList = new FakeClassList(this);
		if (attrs.class) {
			attrs.class.split(/\s+/).filter(Boolean).forEach((c) => this.classList.add(c));
		}
		this.hidden = Boolean(attrs.hidden);
		this.textContent = '';
		this._innerHTML = '';
		this.style = {};
	}
	set innerHTML(val) { this._innerHTML = val; }
	get innerHTML() { return this._innerHTML; }
	getAttribute(k) { return k in this.attrs ? this.attrs[k] : null; }
	setAttribute(k, v) { this.attrs[k] = String(v); }
	appendChild(child) { child.parentNode = this; this.children.push(child); return child; }
	focus() { FakeDocument.activeElement = this; }
	closest(selector) {
		let cur = this;
		while (cur) {
			if (matches(cur, selector)) return cur;
			cur = cur.parentNode;
		}
		return null;
	}
	querySelector(selector) {
		return this.querySelectorAll(selector)[0] || null;
	}
	querySelectorAll(selector) {
		const out = [];
		const walk = (node) => {
			for (const c of node.children) {
				if (matches(c, selector)) out.push(c);
				walk(c);
			}
		};
		walk(this);
		return out;
	}
}

function matches(el, selector) {
	if (selector.startsWith('.')) return el.classList.contains(selector.slice(1));
	if (selector.startsWith('[')) {
		const attr = selector.slice(1, -1);
		return attr in el.attrs;
	}
	return el.tagName.toLowerCase() === selector.toLowerCase();
}

const FakeDocument = {
	activeElement: null,
	body: new FakeElement('body'),
	listeners: {},
	addEventListener(ev, fn) {
		this.listeners[ev] = this.listeners[ev] || [];
		this.listeners[ev].push(fn);
	},
	dispatch(ev, payload) {
		for (const fn of this.listeners[ev] || []) {
			fn(payload);
		}
	},
	getElementById(id) {
		const walk = (n) => {
			if (n.attrs.id === id) return n;
			for (const c of n.children) {
				const found = walk(c);
				if (found) return found;
			}
			return null;
		};
		return walk(this.body);
	},
	querySelectorAll(sel) {
		return this.body.querySelectorAll(sel);
	},
};

// Build DOM fixture
const badge = new FakeElement('span', { class: 'gk-cart-count', 'data-count': '0' });
badge.textContent = '0';
const control = new FakeElement('div', {
	class: 'gk-qty-control',
	'data-gk-cart-control': '',
	'data-product-id': '10862',
	'data-variation-id': '0',
	'data-cart-item-key': '',
	'data-qty': '0',
});
const addBtn = new FakeElement('button', { class: 'gk-add-btn', 'data-gk-action': 'add' });
const stepper = new FakeElement('div', { class: 'gk-stepper is-hidden' });
const decBtn = new FakeElement('button', { 'data-gk-action': 'dec' });
const valEl = new FakeElement('span', { class: 'gk-stepper__val' });
valEl.textContent = '0';
const incBtn = new FakeElement('button', { 'data-gk-action': 'inc' });
stepper.appendChild(decBtn);
stepper.appendChild(valEl);
stepper.appendChild(incBtn);
control.appendChild(addBtn);
control.appendChild(stepper);

const backdrop = new FakeElement('div', { id: 'gk-drawer-backdrop', hidden: true });
const sideCart = new FakeElement('aside', { id: 'gk-side-cart', hidden: true });
const miniItems = new FakeElement('div', { id: 'gk-mini-cart-items' });
const miniFooter = new FakeElement('div', { id: 'gk-mini-cart-footer' });
const toast = new FakeElement('div', { id: 'gk-toast', hidden: true });
sideCart.appendChild(miniItems);
sideCart.appendChild(miniFooter);

[badge, control, backdrop, sideCart, toast].forEach((el) => FakeDocument.body.appendChild(el));

// Mock WooCommerce server cart
let serverQty = 0;
let ajaxCalls = 0;
const makeCartPayload = () => ({
	count: serverQty,
	subtotal: `${serverQty * 3600} AMD`,
	total: `${serverQty * 3600} AMD`,
	quantities: serverQty > 0 ? { '10862': { qty: serverQty, cart_item_key: 'key_10862' } } : {},
	items: serverQty > 0 ? [{
		key: 'key_10862',
		product_id: 10862,
		variation_id: 0,
		name: 'Домашние хинкали 10 шт.',
		permalink: '/product/khinkali/',
		thumbnail: '/img.webp',
		unit_price: '3600 AMD',
		line_total: `${serverQty * 3600} AMD`,
		quantity: serverQty,
	}] : [],
});

const sandbox = {
	window: {},
	document: FakeDocument,
	URLSearchParams,
	FormData: class {},
	setTimeout,
	clearTimeout,
	fetch: async (url, opts) => {
		ajaxCalls++;
		if (opts && opts.body) {
			const params = new URLSearchParams(opts.body);
			const action = params.get('action');
			if (action === 'gayanes_add_to_cart') serverQty += Number(params.get('quantity') || 1);
			if (action === 'gayanes_update_cart_qty') serverQty = Number(params.get('quantity') || 0);
			if (action === 'gayanes_remove_cart_item') serverQty = 0;
		}
		return {
			ok: true,
			json: async () => ({ success: true, data: makeCartPayload() }),
		};
	},
};
sandbox.window = sandbox;
sandbox.window.gayanesCartConfig = {
	ajaxUrl: '/wp-admin/admin-ajax.php',
	nonce: 'test_nonce',
	initialCart: makeCartPayload(),
	i18n: { addToCart: 'Add to Cart', added: 'Added to cart', emptyCart: 'Empty', decreaseQty: '-', increaseQty: '+', remove: 'x', errorGeneric: 'Err' },
};

const code = fs.readFileSync(new URL('../wp-content/themes/gayanes-theme/assets/js/cart.js', import.meta.url), 'utf8');
vm.runInNewContext(code, sandbox);

const click = async (target) => {
	FakeDocument.dispatch('click', { target, preventDefault() {} });
	await new Promise((r) => setTimeout(r, 20));
};

// 1. Click Add to Cart -> qty 1, stepper visible, addBtn hidden
await click(addBtn);
assert.equal(serverQty, 1);
assert.equal(badge.textContent, '1');
assert.equal(valEl.textContent, '1');
assert.equal(addBtn.classList.contains('is-hidden'), true);
assert.equal(stepper.classList.contains('is-hidden'), false);
assert.ok(miniItems.innerHTML.includes('key_10862'));

// 2. Click + -> qty 2
await click(incBtn);
assert.equal(serverQty, 2);
assert.equal(badge.textContent, '2');
assert.equal(valEl.textContent, '2');

// 3. Click - twice -> qty 1 then 0 (removed & Add to Cart restored)
await click(decBtn);
assert.equal(serverQty, 1);
await click(decBtn);
assert.equal(serverQty, 0);
assert.equal(badge.textContent, '0');
assert.equal(addBtn.classList.contains('is-hidden'), false);
assert.equal(stepper.classList.contains('is-hidden'), true);

// 4. Duplicate request lock: two rapid clicks on Add to Cart only fire once
const beforeCalls = ajaxCalls;
FakeDocument.dispatch('click', { target: addBtn, preventDefault() {} });
FakeDocument.dispatch('click', { target: addBtn, preventDefault() {} });
await new Promise((r) => setTimeout(r, 20));
assert.equal(ajaxCalls - beforeCalls, 1);
assert.equal(serverQty, 1);

console.log('ALL SMOKE TESTS PASSED (Add to Cart, +, -, 0-removal, Mini Cart sync, Duplicate-request lock).');

