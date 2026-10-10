/**
 * CP3.3 — Popup "Đặt hàng nhanh".
 * Nút mở: `.cp-buynow[data-cp-quick-order]`; gửi AJAX tới handler ở plugin
 * `pl-tien-ich-tungleads` (action `tlpi_quick_order`) để tạo đơn WooCommerce thật.
 * JS tắt → nút vẫn là link `tel:` (progressive enhancement).
 * Config từ PHP: `window.cpQuickOrder` (wp_localize_script).
 */
(function () {
	'use strict';

	var cfg = window.cpQuickOrder;
	if (!cfg) { return; }

	var lastFocus = null;
	// Đang chờ chuyển trang (đặt hàng xong) → giữ nút khoá để khách không bấm tạo đơn trùng.
	var redirecting = false;

	function modal() {
		return document.getElementById('cp-quick-order');
	}

	function open(trigger) {
		var box = modal();
		if (!box) { return; }
		lastFocus = trigger || null;
		box.hidden = false;
		document.body.classList.add('cp-qo-open');
		var first = box.querySelector('input[name="name"]');
		if (first) { first.focus(); }
	}

	function close() {
		var box = modal();
		if (!box) { return; }
		box.hidden = true;
		document.body.classList.remove('cp-qo-open');
		if (lastFocus && typeof lastFocus.focus === 'function') { lastFocus.focus(); }
	}

	function setMsg(text, isError) {
		var box = modal();
		var msg = box ? box.querySelector('.cp-quick-order__msg') : null;
		if (!msg) { return; }
		msg.textContent = text || '';
		msg.classList.toggle('is-error', !!isError);
		msg.classList.toggle('is-ok', !isError && !!text);
	}

	// Đầu số di động VN hợp lệ — đồng bộ với `tlpi_is_vn_mobile()` ở plugin (server vẫn kiểm tra lại).
	var VN_PREFIXES = ['032','033','034','035','036','037','038','039','052','056','058','059','070','076','077','078','079','081','082','083','084','085','086','088','089','090','091','092','093','094','096','097','098','099'];
	function isVnMobile(v) {
		var d = String(v).replace(/\D/g, '');
		if (d.length === 11 && d.indexOf('84') === 0) { d = '0' + d.slice(2); }
		return d.length === 10 && VN_PREFIXES.indexOf(d.slice(0, 3)) !== -1;
	}

	function submitForm(form) {
		var submit = form.querySelector('.cp-quick-order__submit');
		if (!submit || submit.disabled) { return; }

		if (!isVnMobile(form.elements.phone.value)) {
			setMsg(cfg.i18n.phone, true);
			form.elements.phone.focus();
			return;
		}

		submit.disabled = true;
		setMsg(cfg.i18n.sending, false);

		fetch(cfg.ajaxUrl, {
			method: 'POST',
			body: new FormData(form),
			credentials: 'same-origin'
		})
			.then(function (res) { return res.json(); })
			.then(function (res) {
				if (res && res.success) {
					form.reset();
					setMsg((res.data && res.data.message) || '', false);
					var url = res.data && res.data.redirect;
					if (url) {
						// CP3.3 — đặt thành công → chuyển sang trang hoàn tất đơn (CP3.6) như luồng checkout.
						// Giữ thông báo ~0,8s cho khách kịp thấy rồi mới rời trang.
						redirecting = true;
						window.setTimeout(function () { window.location.assign(url); }, 800);
					} else {
						window.setTimeout(close, 4000);
					}
				} else {
					setMsg((res && res.data && res.data.message) || cfg.i18n.error, true);
				}
			})
			.catch(function () { setMsg(cfg.i18n.error, true); })
			.then(function () { if (!redirecting) { submit.disabled = false; } });
	}

	/* Uỷ quyền sự kiện ở `document`: không phụ thuộc thứ tự in markup/script ở footer. */
	document.addEventListener('click', function (e) {
		if (!e.target || typeof e.target.closest !== 'function') { return; }

		var trigger = e.target.closest('[data-cp-quick-order]');
		if (trigger) {
			e.preventDefault();
			open(trigger);
			return;
		}
		if (e.target.closest('[data-cp-qo-close]')) {
			e.preventDefault();
			close();
		}
	});

	document.addEventListener('submit', function (e) {
		var form = e.target;
		if (!form || typeof form.matches !== 'function' || !form.matches('[data-cp-qo-form]')) {
			return;
		}
		e.preventDefault();
		submitForm(form);
	});

	document.addEventListener('keydown', function (e) {
		var box = modal();
		if ('Escape' === e.key && box && !box.hidden) { close(); }
	});
})();
