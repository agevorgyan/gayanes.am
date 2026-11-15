/**
 * GayanesV2 Interactive UI:
 * - Instant Add -> [ - qty + ] Stepper morphing & AJAX cart sync
 * - iOS 27 Glass Mini-Cart & Mobile Nav sheets
 * - Infinite Scroll on Shop / Category / Search pages
 * - Search bar toggle & Toast notifications
 */
(function () {
	'use strict';

	const cfg = window.gv2Config || {
		ajaxUrl: '/wp-admin/admin-ajax.php',
		nonce: '',
		freeDeliveryThreshold: 15000,
		currencySymbol: '֏',
		i18n: {}
	};

	const backdrop = document.getElementById('gv2-backdrop');
	const toastEl = document.getElementById('gv2-toast');
	let toastTimer = null;

	function showToast(msg) {
		if (!toastEl || !msg) return;
		toastEl.textContent = msg;
		toastEl.hidden = false;
		clearTimeout(toastTimer);
		toastTimer = setTimeout(() => {
			toastEl.hidden = true;
		}, 2400);
	}

	function openSheet(id) {
		const sheet = document.getElementById(id);
		if (!sheet) return;
		closeAllSheets();
		if (backdrop) backdrop.hidden = false;
		sheet.hidden = false;
		document.body.style.overflow = 'hidden';
	}

	function closeAllSheets() {
		document.querySelectorAll('.gv2-sheet').forEach((s) => {
			s.hidden = true;
		});
		if (backdrop) backdrop.hidden = true;
		document.body.style.overflow = '';
	}

	async function postAjax(action, params) {
		const body = new URLSearchParams({ action, nonce: cfg.nonce, ...params });
		const res = await fetch(cfg.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
			body: body.toString()
		});
		return res.json();
	}

	function updateDeliveryProgress(subtotalNum) {
		const textEl = document.getElementById('gv2-delivery-text');
		const fillEl = document.getElementById('gv2-delivery-fill');
		if (!textEl || !fillEl) return;

		const threshold = Number(cfg.freeDeliveryThreshold) || 15000;
		const pct = Math.min(100, Math.round((subtotalNum / threshold) * 100));
		fillEl.style.width = pct + '%';

		if (subtotalNum >= threshold) {
			textEl.textContent = cfg.i18n.freeUnlocked || 'Free delivery unlocked!';
		} else {
			const diff = Math.max(0, threshold - subtotalNum).toLocaleString();
			const tpl = cfg.i18n.addMoreForFree || 'Add %s more for free delivery';
			textEl.textContent = tpl.replace('%s', diff + ' ' + (cfg.currencySymbol || '֏'));
		}
	}

	function syncProductCards(productId, qty, cartKey) {
		document.querySelectorAll(`.gv2-card[data-product-id="${productId}"]`).forEach((card) => {
			const addBtn = card.querySelector('[data-gv2-add]');
			const qtyBox = card.querySelector('[data-gv2-qty-control]');
			const qtyVal = card.querySelector('[data-gv2-qty-val]');
			if (!addBtn || !qtyBox || !qtyVal) return;

			if (qty > 0) {
				addBtn.classList.add('is-hidden');
				qtyBox.classList.remove('is-hidden');
				qtyVal.textContent = String(qty);
				if (cartKey) qtyBox.setAttribute('data-cart-item-key', cartKey);
			} else {
				qtyBox.classList.add('is-hidden');
				qtyBox.setAttribute('data-cart-item-key', '');
				qtyVal.textContent = '0';
				addBtn.classList.remove('is-hidden');
			}
		});
	}

	function applyCartPayload(data) {
		if (!data) return;

		if (typeof data.count !== 'undefined') {
			document.querySelectorAll('.gv2-cart-count').forEach((el) => {
				el.textContent = String(data.count);
				el.setAttribute('data-count', String(data.count));
			});
		}

		if (data.subtotal_html) {
			const subEl = document.getElementById('gv2-mini-cart-subtotal');
			if (subEl) subEl.innerHTML = data.subtotal_html;
		}

		if (typeof data.subtotal_raw !== 'undefined') {
			updateDeliveryProgress(Number(data.subtotal_raw) || 0);
		}

		if (data.mini_cart_html) {
			const listWrap = document.getElementById('gv2-mini-cart-items');
			if (listWrap) listWrap.innerHTML = data.mini_cart_html;
		}
	}

	// Delegated click handler for Sheets, Search, Add-to-Cart, and +/- Steppers.
	document.addEventListener('click', async (e) => {
		const openBtn = e.target.closest('[data-gv2-drawer-open]');
		if (openBtn) {
			const target = openBtn.getAttribute('data-gv2-drawer-open');
			openSheet(target === 'cart' ? 'gv2-side-cart' : 'gv2-mobile-nav');
			return;
		}

		if (e.target.closest('[data-gv2-drawer-close]') || e.target === backdrop) {
			closeAllSheets();
			return;
		}

		const searchToggle = e.target.closest('[data-gv2-toggle-search]');
		if (searchToggle) {
			const searchBar = document.getElementById('gv2-header-search');
			if (searchBar) {
				searchBar.hidden = !searchBar.hidden;
				if (!searchBar.hidden) {
					const input = searchBar.querySelector('input[type="search"]');
					if (input) input.focus();
				}
			}
			return;
		}

		const addBtn = e.target.closest('[data-gv2-add]');
		if (addBtn) {
			const productId = addBtn.getAttribute('data-gv2-add');
			if (!productId || addBtn.disabled) return;
			addBtn.disabled = true;

			try {
				const res = await postAjax('gayanes_add_to_cart', { product_id: productId, quantity: 1 });
				if (res && res.success) {
					const key = res.data?.cart_item_key || '';
					const qty = res.data?.quantity || 1;
					syncProductCards(productId, qty, key);
					applyCartPayload(res.data);
					showToast(cfg.i18n.addedToCart || 'Added to cart');
				} else {
					showToast(res?.data?.message || cfg.i18n.errorCart || 'Could not update cart');
				}
			} catch (_) {
				showToast(cfg.i18n.errorCart || 'Could not update cart');
			} finally {
				addBtn.disabled = false;
			}
			return;
		}

		const stepBtn = e.target.closest('[data-gv2-qty-step]');
		if (stepBtn) {
			const qtyBox = stepBtn.closest('[data-gv2-qty-control]');
			if (!qtyBox) return;

			const delta = parseInt(stepBtn.getAttribute('data-gv2-qty-step') || '0', 10);
			const valEl = qtyBox.querySelector('[data-gv2-qty-val]');
			const current = parseInt(valEl?.textContent || '0', 10);
			const nextQty = Math.max(0, current + delta);
			const productId = qtyBox.getAttribute('data-product-id') || '';
			const cartKey = qtyBox.getAttribute('data-cart-item-key') || '';

			stepBtn.disabled = true;
			try {
				const action = nextQty === 0 ? 'gayanes_remove_cart_item' : 'gayanes_update_cart_qty';
				const res = await postAjax(action, {
					cart_item_key: cartKey,
					product_id: productId,
					quantity: nextQty
				});
				if (res && res.success) {
					syncProductCards(productId, nextQty, res.data?.cart_item_key || cartKey);
					applyCartPayload(res.data);
					showToast(cfg.i18n.updatedQty || 'Updated quantity');
				}
			} catch (_) {
				showToast(cfg.i18n.errorCart || 'Could not update cart');
			} finally {
				stepBtn.disabled = false;
			}
			return;
		}

		const removeBtn = e.target.closest('[data-gv2-remove-item]');
		if (removeBtn) {
			const cartKey = removeBtn.getAttribute('data-gv2-remove-item') || '';
			const itemRow = removeBtn.closest('[data-product-id]');
			const productId = itemRow?.getAttribute('data-product-id') || '';
			try {
				const res = await postAjax('gayanes_remove_cart_item', { cart_item_key: cartKey });
				if (res && res.success) {
					if (productId) syncProductCards(productId, 0, '');
					applyCartPayload(res.data);
				}
			} catch (_) {
				showToast(cfg.i18n.errorCart || 'Could not update cart');
			}
		}
	});

	document.addEventListener('keydown', (e) => {
		if (e.key === 'Escape') closeAllSheets();
	});

	// Infinite Scroll on Shop / Category / Search pages
	const grid = document.getElementById('gv2-infinite-grid');
	const sentinel = document.getElementById('gv2-infinite-sentinel');
	if (grid && sentinel && 'IntersectionObserver' in window) {
		let isLoading = false;

		const loadMore = async () => {
			if (isLoading || sentinel.getAttribute('data-done') === '1') return;
			const currentPage = parseInt(grid.getAttribute('data-page') || '1', 10);
			const maxPages = parseInt(grid.getAttribute('data-max-pages') || '1', 10);
			if (currentPage >= maxPages) {
				sentinel.setAttribute('data-done', '1');
				sentinel.innerHTML = `<span class="gv2-muted">${cfg.i18n.allLoaded || 'All dishes loaded'}</span>`;
				return;
			}

			isLoading = true;
			const params = new URLSearchParams({
				action: 'gv2_load_products',
				paged: String(currentPage + 1),
				per_page: grid.getAttribute('data-per-page') || '12',
				category: grid.getAttribute('data-category') || '',
				orderby: grid.getAttribute('data-orderby') || 'menu_order',
				s: grid.getAttribute('data-search') || ''
			});

			try {
				const res = await fetch(`${cfg.ajaxUrl}?${params.toString()}`, { credentials: 'same-origin' });
				const json = await res.json();
				if (json && json.success && json.data?.html) {
					grid.insertAdjacentHTML('beforeend', json.data.html);
					grid.setAttribute('data-page', String(json.data.next_page - 1));
					if (!json.data.has_more) {
						sentinel.setAttribute('data-done', '1');
						sentinel.innerHTML = `<span class="gv2-muted">${cfg.i18n.allLoaded || 'All dishes loaded'}</span>`;
					}
				}
			} catch (_) {
				// Silent retry on next intersection
			} finally {
				isLoading = false;
			}
		};

		const observer = new IntersectionObserver(
			(entries) => {
				if (entries.some((entry) => entry.isIntersecting)) {
					loadMore();
				}
			},
			{ rootMargin: '260px 0px' }
		);
		observer.observe(sentinel);
	}
})();

