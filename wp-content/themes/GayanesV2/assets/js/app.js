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

		const threshold = typeof cfg.freeDeliveryThreshold === 'number' ? cfg.freeDeliveryThreshold : 15000;
		const pct = threshold > 0 ? Math.min(100, Math.round((subtotalNum / threshold) * 100)) : 100;
		fillEl.style.width = pct + '%';

		if (threshold <= 0 || subtotalNum >= threshold) {
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

	function escapeHtml(str) {
		return String(str || '').replace(/[&<>"']/g, (c) => ({
			'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
		}[c]));
	}

	function parseSubtotalNumber(str) {
		const cleaned = String(str || '').replace(/[^\d.]/g, '');
		return parseFloat(cleaned) || 0;
	}

	function renderMiniCartItems(items) {
		const wrap = document.getElementById('gv2-mini-cart-items');
		if (!wrap) return;
		if (!Array.isArray(items) || items.length === 0) {
			wrap.innerHTML = `<div class="gv2-mini-cart__empty"><p>${escapeHtml(cfg.i18n.emptyCart || 'Your cart is empty')}</p></div>`;
			return;
		}
		const html = items.map((item) => `
			<li class="gv2-mini-item" data-cart-item-key="${escapeHtml(item.key)}" data-product-id="${escapeHtml(item.product_id)}">
				<div class="gv2-mini-item__thumb">
					<img src="${escapeHtml(item.thumbnail)}" alt="${escapeHtml(item.name)}" loading="lazy" />
				</div>
				<div class="gv2-mini-item__info">
					<a href="${escapeHtml(item.permalink)}" class="gv2-mini-item__name">${escapeHtml(item.name)}</a>
					<div class="gv2-mini-item__price">${escapeHtml(item.line_total)}</div>
					<div class="gv2-qty gv2-qty--sm" data-gv2-qty-control data-product-id="${escapeHtml(item.product_id)}" data-cart-item-key="${escapeHtml(item.key)}">
						<button type="button" class="gv2-qty__btn" data-gv2-qty-step="-1" aria-label="${escapeHtml(cfg.i18n.decreaseQty || 'Decrease')}">&minus;</button>
						<span class="gv2-qty__val" data-gv2-qty-val>${escapeHtml(item.quantity)}</span>
						<button type="button" class="gv2-qty__btn" data-gv2-qty-step="1" aria-label="${escapeHtml(cfg.i18n.increaseQty || 'Increase')}">+</button>
					</div>
				</div>
				<button type="button" class="gv2-mini-item__remove" data-gv2-remove-item="${escapeHtml(item.key)}" aria-label="${escapeHtml(cfg.i18n.removeItem || 'Remove')}">&times;</button>
			</li>
		`).join('');
		wrap.innerHTML = `<ul class="gv2-mini-cart__list">${html}</ul>`;
	}

	function bumpCartPill() {
		document.querySelectorAll('.gv2-cart-pill').forEach((pill) => {
			pill.classList.remove('is-bumping');
			void pill.offsetWidth;
			pill.classList.add('is-bumping');
		});
	}

	function applyCartPayload(data) {
		if (!data) return;

		if (typeof data.count !== 'undefined') {
			document.querySelectorAll('.gv2-cart-count').forEach((el) => {
				el.textContent = String(data.count);
				el.setAttribute('data-count', String(data.count));
			});
			bumpCartPill();
		}

		const subtotalText = data.subtotal_html || data.subtotal || '';
		if (subtotalText) {
			const subEl = document.getElementById('gv2-mini-cart-subtotal');
			if (subEl) subEl.innerHTML = subtotalText;
			updateDeliveryProgress(parseSubtotalNumber(subtotalText));
		}

		if (Array.isArray(data.items)) {
			renderMiniCartItems(data.items);
		}

		if (data.quantities && typeof data.quantities === 'object') {
			document.querySelectorAll('.gv2-card[data-product-id]').forEach((card) => {
				const pid = card.getAttribute('data-product-id');
				const entry = data.quantities[pid];
				syncProductCards(pid, entry ? entry.qty : 0, entry ? entry.cart_item_key : '');
			});
		}

		syncCartPage(data);
	}

	function syncCartPage(data) {
		const cartList = document.querySelector('.gv2-cart-list');
		if (!cartList) return;

		if (typeof data.count !== 'undefined' && Number(data.count) === 0) {
			window.location.reload();
			return;
		}

		const itemMap = new Map();
		if (Array.isArray(data.items)) {
			data.items.forEach((it) => itemMap.set(String(it.key), it));
		}

		cartList.querySelectorAll('.gv2-cart-row[data-cart-item-key]').forEach((row) => {
			const key = row.getAttribute('data-cart-item-key');
			const item = itemMap.get(String(key));
			if (!item) {
				row.remove();
				return;
			}
			const qtyVal = row.querySelector('[data-gv2-qty-val]');
			const qtyInput = row.querySelector('[data-gv2-qty-input]');
			const subEl = row.querySelector('[data-gv2-cart-row-subtotal]');
			if (qtyVal) qtyVal.textContent = String(item.quantity);
			if (qtyInput) qtyInput.value = String(item.quantity);
			if (subEl) subEl.textContent = item.line_total || '';
		});

		const cartSubEl = document.querySelector('.cart_totals .cart-subtotal td');
		const cartTotEl = document.querySelector('.cart_totals .order-total td');
		if (cartSubEl && data.subtotal) cartSubEl.textContent = data.subtotal;
		if (cartTotEl && data.total) {
			const strong = cartTotEl.querySelector('strong');
			if (strong) {
				strong.textContent = data.total;
			} else {
				cartTotEl.textContent = data.total;
			}
		}

		if (data.subtotal) {
			const subtotalNum = parseSubtotalNumber(data.subtotal);
			const threshold = typeof cfg.freeDeliveryThreshold === 'number' ? cfg.freeDeliveryThreshold : 15000;
			const pct = threshold > 0 ? Math.min(100, Math.round((subtotalNum / threshold) * 100)) : 100;
			const flowFill = document.querySelector('.gv2-flow-delivery__fill');
			const flowPct = document.querySelector('.gv2-flow-delivery__pct');
			const flowText = document.querySelector('.gv2-flow-delivery__text');
			if (flowFill) flowFill.style.width = pct + '%';
			if (flowPct) flowPct.textContent = pct + '%';
			if (flowText) {
				if (threshold <= 0 || subtotalNum >= threshold) {
					flowText.textContent = cfg.i18n.freeUnlocked || 'Free delivery unlocked!';
				} else {
					const diff = Math.max(0, threshold - subtotalNum).toLocaleString();
					const tpl = cfg.i18n.addMoreForFree || 'Add %s more for free delivery';
					flowText.textContent = tpl.replace('%s', diff + ' ' + (cfg.currencySymbol || '֏'));
				}
			}
		}

		if (document.querySelector('.cart_totals .cart-discount') && window.jQuery) {
			window.jQuery(document.body).trigger('wc_update_cart');
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

	// Checkout Country-Flag Phone Input & Strict Validation (Default: Armenia +374)
	const phoneInput = document.getElementById('billing_phone');
	if (phoneInput && !phoneInput.dataset.gv2Enhanced) {
		phoneInput.dataset.gv2Enhanced = '1';

		const countries = [
			{ code: '+374', flag: '🇦🇲', label: '🇦🇲 +374', min: 8, max: 8, placeholder: '99 12 34 56' },
			{ code: '+7', flag: '🇷🇺', label: '🇷🇺 +7', min: 10, max: 10, placeholder: '900 123 45 67' },
			{ code: '+995', flag: '🇬🇪', label: '🇬🇪 +995', min: 9, max: 9, placeholder: '555 12 34 56' },
			{ code: '+1', flag: '🇺🇸', label: '🇺🇸 +1', min: 10, max: 10, placeholder: '555 123 4567' },
			{ code: '+33', flag: '🇫🇷', label: '🇫🇷 +33', min: 9, max: 9, placeholder: '6 12 34 56 78' },
			{ code: '+49', flag: '🇩🇪', label: '🇩🇪 +49', min: 10, max: 11, placeholder: '151 23456789' }
		];

		const wrap = document.createElement('div');
		wrap.className = 'gv2-phone-wrap';

		const select = document.createElement('select');
		select.className = 'gv2-phone-country';
		select.setAttribute('aria-label', 'Country code');
		countries.forEach((c) => {
			const opt = document.createElement('option');
			opt.value = c.code;
			opt.textContent = c.label;
			select.appendChild(opt);
		});

		const national = document.createElement('input');
		national.type = 'tel';
		national.inputMode = 'numeric';
		national.autocomplete = 'tel-national';
		national.className = 'gv2-phone-national';

		const errEl = document.createElement('span');
		errEl.className = 'gv2-phone-error';
		errEl.hidden = true;

		phoneInput.type = 'hidden';
		phoneInput.parentNode.insertBefore(wrap, phoneInput);
		wrap.appendChild(select);
		wrap.appendChild(national);
		wrap.parentNode.insertBefore(errEl, wrap.nextSibling);

		const getCountry = () => countries.find((c) => c.code === select.value) || countries[0];

		const formatArmenian = (digits) => {
			const p = [];
			if (digits.length > 0) p.push(digits.slice(0, 2));
			if (digits.length > 2) p.push(digits.slice(2, 4));
			if (digits.length > 4) p.push(digits.slice(4, 6));
			if (digits.length > 6) p.push(digits.slice(6, 8));
			return p.join(' ');
		};

		const syncPhone = (showError) => {
			const c = getCountry();
			national.placeholder = c.placeholder;
			let digits = national.value.replace(/\D/g, '').slice(0, c.max);
			national.value = c.code === '+374' ? formatArmenian(digits) : digits;
			phoneInput.value = digits.length > 0 ? c.code + digits : '';

			const isValid = digits.length >= c.min && digits.length <= c.max;
			wrap.classList.toggle('is-valid', isValid);
			wrap.classList.toggle('is-invalid', Boolean(showError && !isValid));

			if (showError && !isValid) {
				errEl.textContent = cfg.i18n.phoneInvalid || 'Please enter a valid phone number.';
				errEl.hidden = false;
			} else if (isValid) {
				errEl.hidden = true;
			}
			return isValid;
		};

		// Hydrate from existing value if present
		const initVal = (phoneInput.value || '').replace(/[^\d+]/g, '');
		const matched = countries.find((c) => initVal.startsWith(c.code));
		if (matched) {
			select.value = matched.code;
			national.value = initVal.slice(matched.code.length);
		} else {
			select.value = '+374';
			if (initVal.startsWith('374')) {
				national.value = initVal.slice(3);
			}
		}
		syncPhone(false);

		national.addEventListener('input', () => syncPhone(false));
		national.addEventListener('blur', () => {
			if (national.value.trim() !== '') syncPhone(true);
		});
		select.addEventListener('change', () => syncPhone(false));

		const checkoutForm = phoneInput.closest('form.checkout');
		if (checkoutForm) {
			checkoutForm.addEventListener('submit', (e) => {
				if (!syncPhone(true)) {
					e.preventDefault();
					e.stopImmediatePropagation();
					national.focus();
				}
			});
		}
	}

	// Daily Deal Countdown Timer (counts down to midnight local time)
	const dealTimer = document.getElementById('gv2-deal-timer');
	if (dealTimer) {
		const hEl = dealTimer.querySelector('[data-gv2-cd="h"]');
		const mEl = dealTimer.querySelector('[data-gv2-cd="m"]');
		const sEl = dealTimer.querySelector('[data-gv2-cd="s"]');
		const pad = (n) => String(n).padStart(2, '0');

		const tick = () => {
			const now = new Date();
			const end = new Date(now);
			end.setHours(23, 59, 59, 999);
			const diff = Math.max(0, Math.floor((end - now) / 1000));
			if (hEl) hEl.textContent = pad(Math.floor(diff / 3600));
			if (mEl) mEl.textContent = pad(Math.floor((diff % 3600) / 60));
			if (sEl) sEl.textContent = pad(diff % 60);
		};
		tick();
		setInterval(tick, 1000);
	}

	// FAQ Page: Live Search & Category Filter Pills
	const faqList = document.getElementById('gv2-faq-list');
	if (faqList) {
		const faqSearch = document.getElementById('gv2-faq-search-input');
		const faqEmpty = document.getElementById('gv2-faq-empty');
		const catButtons = document.querySelectorAll('[data-faq-cat]');
		const faqItems = faqList.querySelectorAll('.gv2-faq-item');
		let activeCat = 'all';

		const applyFaqFilters = () => {
			const query = (faqSearch ? faqSearch.value : '').trim().toLowerCase();
			let visibleCount = 0;

			faqItems.forEach((item) => {
				const itemCat = item.getAttribute('data-category') || '';
				const blob = item.getAttribute('data-search') || '';
				const matchCat = activeCat === 'all' || itemCat === activeCat;
				const matchQuery = !query || blob.includes(query);
				const show = matchCat && matchQuery;

				item.hidden = !show;
				if (show) {
					visibleCount++;
				}
			});

			if (faqEmpty) {
				faqEmpty.hidden = visibleCount > 0;
			}
		};

		catButtons.forEach((btn) => {
			btn.addEventListener('click', () => {
				activeCat = btn.getAttribute('data-faq-cat') || 'all';
				catButtons.forEach((b) => {
					const isActive = b === btn;
					b.classList.toggle('is-active', isActive);
					b.setAttribute('aria-selected', isActive ? 'true' : 'false');
				});
				applyFaqFilters();
			});
		});

		if (faqSearch) {
			faqSearch.addEventListener('input', applyFaqFilters);
		}
	}

	// Single Article: Reading Progress Bar & Copy Link
	const readBar = document.getElementById('gv2-read-progress');
	if (readBar) {
		const updateReadProgress = () => {
			const doc = document.documentElement;
			const scrollTop = window.scrollY || doc.scrollTop || 0;
			const maxScroll = Math.max(1, doc.scrollHeight - window.innerHeight);
			const pct = Math.min(100, Math.max(0, (scrollTop / maxScroll) * 100));
			readBar.style.width = pct + '%';
		};
		window.addEventListener('scroll', updateReadProgress, { passive: true });
		updateReadProgress();
	}

	const copyBtn = document.getElementById('gv2-copy-article-link');
	if (copyBtn) {
		copyBtn.addEventListener('click', async () => {
			const url = copyBtn.getAttribute('data-url') || window.location.href;
			try {
				await navigator.clipboard.writeText(url);
				showToast('Հղումը պատճենվեց');
			} catch {
				showToast(url);
			}
		});
	}

	// Contact Page: AJAX Feedback Form
	const contactForm = document.getElementById('gv2-contact-form');
	if (contactForm) {
		const feedbackEl = document.getElementById('gv2-contact-feedback');
		const submitBtn = contactForm.querySelector('.gv2-contact-submit');

		contactForm.addEventListener('submit', async (e) => {
			e.preventDefault();
			if (submitBtn) submitBtn.disabled = true;
			if (feedbackEl) {
				feedbackEl.hidden = true;
				feedbackEl.classList.remove('is-error', 'is-success');
			}

			try {
				const formData = new FormData(contactForm);
				formData.append('action', 'gv2_submit_contact');
				const res = await fetch(cfg.ajaxUrl, {
					method: 'POST',
					credentials: 'same-origin',
					body: formData
				});
				const json = await res.json();
				const msg = (json && json.data && json.data.message) ? json.data.message : '';

				if (feedbackEl && msg) {
					feedbackEl.textContent = msg;
					feedbackEl.classList.add(json.success ? 'is-success' : 'is-error');
					feedbackEl.hidden = false;
				}
				if (json && json.success) {
					contactForm.reset();
				}
			} catch {
				if (feedbackEl) {
					feedbackEl.textContent = 'Խնդրում ենք կրկին փորձել կամ զանգահարել +374 (55) 600655։';
					feedbackEl.classList.add('is-error');
					feedbackEl.hidden = false;
				}
			} finally {
				if (submitBtn) submitBtn.disabled = false;
			}
		});
	}

	// Mobile Bottom Tab Bar: Hide on scroll down, show on scroll up
	const tabbar = document.querySelector('.gv2-tabbar');
	if (tabbar) {
		let lastY = Math.max(0, window.scrollY || window.pageYOffset || 0);
		let ticking = false;

		const updateTabbarVisibility = () => {
			const currentY = Math.max(0, window.scrollY || window.pageYOffset || 0);
			const delta = currentY - lastY;
			const maxScroll = document.documentElement.scrollHeight - window.innerHeight;
			const atBottom = maxScroll > 0 && currentY >= maxScroll - 4;

			if (currentY <= 20) {
				tabbar.classList.remove('is-hidden');
				lastY = currentY;
			} else if (Math.abs(delta) >= 8) {
				if (delta > 0 && currentY > 60) {
					tabbar.classList.add('is-hidden');
				} else if (delta < 0 && !atBottom) {
					tabbar.classList.remove('is-hidden');
				}
				lastY = currentY;
			}
			ticking = false;
		};

		window.addEventListener('scroll', () => {
			if (!ticking) {
				ticking = true;
				window.requestAnimationFrame(updateTabbarVisibility);
			}
		}, { passive: true });
	}
})();