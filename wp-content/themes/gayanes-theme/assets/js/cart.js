/**
 * Gayane's Kitchen — Vanilla JS AJAX Cart, Quantity Stepper & Accessible Drawers.
 * Zero jQuery dependency. Prevents duplicate/conflicting AJAX requests.
 */
(function () {
	'use strict';

	const cfg = window.gayanesCartConfig || {
		ajaxUrl: '/wp-admin/admin-ajax.php',
		nonce: '',
		initialCart: { count: 0, items: [], quantities: {}, subtotal: '', total: '' },
		i18n: {
			addToCart: 'Add to Cart',
			added: 'Added to cart',
			emptyCart: 'Your cart is empty',
			decreaseQty: 'Decrease quantity',
			increaseQty: 'Increase quantity',
			remove: 'Remove',
			errorGeneric: 'Something went wrong. Please try again.',
		},
	};

	const pendingKeys = new Set();
	let cartState = cfg.initialCart || { count: 0, items: [], quantities: {} };
	let lastFocusedEl = null;

	const backdrop = document.getElementById('gk-drawer-backdrop');
	const sideCart = document.getElementById('gk-side-cart');
	const mobileNav = document.getElementById('gk-mobile-nav');
	const toastEl = document.getElementById('gk-toast');
	let toastTimer = null;

	function showToast(message) {
		if (!toastEl || !message) return;
		toastEl.textContent = message;
		toastEl.hidden = false;
		clearTimeout(toastTimer);
		toastTimer = setTimeout(() => {
			toastEl.hidden = true;
		}, 2600);
	}

	function escapeHtml(str) {
		return String(str)
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;');
	}

	async function postAjax(action, params) {
		const body = new URLSearchParams({
			action,
			nonce: cfg.nonce,
			...params,
		});

		const res = await fetch(cfg.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
			body: body.toString(),
		});

		const json = await res.json();
		if (!res.ok || !json.success) {
			const msg = (json && json.data && json.data.message) || cfg.i18n.errorGeneric;
			const err = new Error(msg);
			err.data = json && json.data;
			throw err;
		}
		return json.data;
	}

	function syncBadges(count) {
		document.querySelectorAll('.gk-cart-count').forEach((el) => {
			el.textContent = String(count);
			el.setAttribute('data-count', String(count));
		});
	}

	function syncProductCards() {
		const quantities = cartState.quantities || {};
		document.querySelectorAll('[data-gk-cart-control]').forEach((control) => {
			const productId = control.getAttribute('data-product-id');
			const variationId = control.getAttribute('data-variation-id') || '0';
			const lookupKey = variationId !== '0' ? `${productId}:${variationId}` : productId;
			const entry = quantities[lookupKey];
			const qty = entry ? Number(entry.qty) : 0;
			const cartItemKey = entry ? entry.cart_item_key : '';

			control.setAttribute('data-qty', String(qty));
			control.setAttribute('data-cart-item-key', cartItemKey);

			const addBtn = control.querySelector('.gk-add-btn');
			const stepper = control.querySelector('.gk-stepper');
			const valEl = control.querySelector('.gk-stepper__val');

			if (addBtn && stepper && valEl) {
				valEl.textContent = String(qty);
				if (qty > 0) {
					addBtn.classList.add('is-hidden');
					stepper.classList.remove('is-hidden');
				} else {
					addBtn.classList.remove('is-hidden');
					stepper.classList.add('is-hidden');
				}
			}
		});
	}

	function renderMiniCart() {
		const itemsWrap = document.getElementById('gk-mini-cart-items');
		const footerWrap = document.getElementById('gk-mini-cart-footer');
		const subtotalEl = document.getElementById('gk-mini-cart-subtotal');
		const totalEl = document.getElementById('gk-mini-cart-total');
		if (!itemsWrap) return;

		const items = cartState.items || [];
		if (items.length === 0) {
			itemsWrap.innerHTML = `<p class="gk-mini-cart__empty">${escapeHtml(cfg.i18n.emptyCart)}</p>`;
			if (footerWrap) footerWrap.hidden = true;
			return;
		}

		if (footerWrap) footerWrap.hidden = false;
		if (subtotalEl) subtotalEl.textContent = cartState.subtotal || '';
		if (totalEl) totalEl.textContent = cartState.total || '';

		itemsWrap.innerHTML = items
			.map((item) => {
				return `
				<div class="gk-mini-item" data-cart-item-key="${escapeHtml(item.key)}" data-product-id="${item.product_id}" data-variation-id="${item.variation_id}">
					<img class="gk-mini-item__img" src="${escapeHtml(item.thumbnail)}" alt="${escapeHtml(item.name)}" width="64" height="64" />
					<div>
						<div class="gk-mini-item__top">
							<a class="gk-mini-item__name" href="${escapeHtml(item.permalink)}">${escapeHtml(item.name)}</a>
							<button type="button" class="gk-mini-item__remove" data-gk-mini-action="remove" aria-label="${escapeHtml(cfg.i18n.remove)}">&times;</button>
						</div>
						<div class="gk-mini-item__meta">${escapeHtml(item.unit_price)}</div>
						<div class="gk-mini-item__bottom">
							<div class="gk-stepper gk-mini-item__stepper" role="group" aria-label="${escapeHtml(item.name)}">
								<button type="button" class="gk-stepper__btn" data-gk-mini-action="dec" aria-label="${escapeHtml(cfg.i18n.decreaseQty)}">&minus;</button>
								<span class="gk-stepper__val">${item.quantity}</span>
								<button type="button" class="gk-stepper__btn" data-gk-mini-action="inc" aria-label="${escapeHtml(cfg.i18n.increaseQty)}">+</button>
							</div>
							<strong>${escapeHtml(item.line_total)}</strong>
						</div>
					</div>
				</div>`;
			})
			.join('');
	}

	function applyCartState(newState) {
		cartState = newState || cartState;
		syncBadges(cartState.count || 0);
		syncProductCards();
		renderMiniCart();
	}

	async function mutateCardControl(control, action) {
		const productId = control.getAttribute('data-product-id');
		const variationId = control.getAttribute('data-variation-id') || '0';
		const cartItemKey = control.getAttribute('data-cart-item-key') || '';
		const currentQty = Number(control.getAttribute('data-qty') || '0');
		const lockKey = `${productId}:${variationId}`;

		if (pendingKeys.has(lockKey)) return;
		pendingKeys.add(lockKey);
		control.classList.add('is-loading');

		try {
			let data;
			if (action === 'add') {
				data = await postAjax('gayanes_add_to_cart', {
					product_id: productId,
					variation_id: variationId,
					quantity: 1,
				});
				showToast(data.message || cfg.i18n.added);
			} else {
				const nextQty = action === 'inc' ? currentQty + 1 : currentQty - 1;
				data = await postAjax('gayanes_update_cart_qty', {
					cart_item_key: cartItemKey,
					product_id: productId,
					variation_id: variationId,
					quantity: Math.max(0, nextQty),
				});
				if (data.message) showToast(data.message);
			}
			applyCartState(data);
		} catch (err) {
			if (err.data && err.data.redirectUrl) {
				window.location.href = err.data.redirectUrl;
				return;
			}
			if (err.data && err.data.cart) applyCartState(err.data.cart);
			showToast(err.message || cfg.i18n.errorGeneric);
		} finally {
			pendingKeys.delete(lockKey);
			control.classList.remove('is-loading');
		}
	}

	async function mutateMiniCartItem(row, action) {
		const key = row.getAttribute('data-cart-item-key');
		const productId = row.getAttribute('data-product-id');
		const variationId = row.getAttribute('data-variation-id') || '0';
		const valEl = row.querySelector('.gk-stepper__val');
		const currentQty = valEl ? Number(valEl.textContent) : 1;

		if (pendingKeys.has(key)) return;
		pendingKeys.add(key);
		row.style.opacity = '0.6';

		try {
			let data;
			if (action === 'remove') {
				data = await postAjax('gayanes_remove_cart_item', { cart_item_key: key });
			} else {
				const nextQty = action === 'inc' ? currentQty + 1 : currentQty - 1;
				data = await postAjax('gayanes_update_cart_qty', {
					cart_item_key: key,
					product_id: productId,
					variation_id: variationId,
					quantity: Math.max(0, nextQty),
				});
			}
			applyCartState(data);
		} catch (err) {
			if (err.data && err.data.cart) applyCartState(err.data.cart);
			showToast(err.message || cfg.i18n.errorGeneric);
		} finally {
			pendingKeys.delete(key);
			row.style.opacity = '';
		}
	}

	function openDrawer(drawer) {
		if (!drawer || !backdrop) return;
		lastFocusedEl = document.activeElement;
		backdrop.hidden = false;
		drawer.hidden = false;
		document.body.style.overflow = 'hidden';
		const closeBtn = drawer.querySelector('[data-gk-drawer-close]');
		if (closeBtn) closeBtn.focus();
	}

	function closeAllDrawers() {
		if (backdrop) backdrop.hidden = true;
		if (sideCart) sideCart.hidden = true;
		if (mobileNav) mobileNav.hidden = true;
		document.body.style.overflow = '';
		if (lastFocusedEl && typeof lastFocusedEl.focus === 'function') {
			lastFocusedEl.focus();
			lastFocusedEl = null;
		}
	}

	document.addEventListener('click', (e) => {
		const cardActionBtn = e.target.closest('[data-gk-action]');
		if (cardActionBtn) {
			const control = cardActionBtn.closest('[data-gk-cart-control]');
			if (control) {
				e.preventDefault();
				mutateCardControl(control, cardActionBtn.getAttribute('data-gk-action'));
			}
			return;
		}

		const miniActionBtn = e.target.closest('[data-gk-mini-action]');
		if (miniActionBtn) {
			const row = miniActionBtn.closest('.gk-mini-item');
			if (row) {
				e.preventDefault();
				mutateMiniCartItem(row, miniActionBtn.getAttribute('data-gk-mini-action'));
			}
			return;
		}

		const drawerOpenBtn = e.target.closest('[data-gk-drawer-open]');
		if (drawerOpenBtn) {
			e.preventDefault();
			const target = drawerOpenBtn.getAttribute('data-gk-drawer-open');
			openDrawer(target === 'cart' ? sideCart : mobileNav);
			return;
		}

		if (e.target.closest('[data-gk-drawer-close]') || e.target === backdrop) {
			closeAllDrawers();
			return;
		}

		const searchToggle = e.target.closest('[data-gk-toggle-search]');
		if (searchToggle) {
			const searchBar = document.getElementById('gk-header-search');
			if (searchBar) {
				const isHidden = searchBar.hidden;
				searchBar.hidden = !isHidden;
				searchToggle.setAttribute('aria-expanded', String(isHidden));
				if (isHidden) {
					const input = searchBar.querySelector('input[type="search"]');
					if (input) input.focus();
				}
			}
		}
	});

	document.addEventListener('keydown', (e) => {
		if (e.key === 'Escape') {
			closeAllDrawers();
		}
	});

	// Initial render + fresh cart fetch (ensures LiteSpeed cached pages stay accurate)
	applyCartState(cartState);
	if (cfg.ajaxUrl) {
		fetch(`${cfg.ajaxUrl}?action=gayanes_get_cart_state`, { credentials: 'same-origin' })
			.then((r) => (r.ok ? r.json() : null))
			.then((json) => {
				if (json && json.success && json.data) {
					applyCartState(json.data);
				}
			})
			.catch(() => {});
	}
})();

