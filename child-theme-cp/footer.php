<?php
/**
 * Footer Cao Phát Door: footer 3–4 CỘT (mỗi cột = tiêu đề + ô soạn thảo văn bản).
 *
 * CP1.3 footer — nền (màu + ảnh + độ đậm lớp màu) và nội dung từng cột đọc từ Customizer
 * "Footer Cao Phát": `cp_footer_columns()` / `cp_footer_get()` / `cp_footer_flag()` /
 * `cp_footer_col_get()` (`functions.php`, 1 nguồn sự thật cho default); dòng bản quyền + ghi công qua
 * `cp_footer_text()`. Ô để trống trong Customizer → dùng lại mặc định nên file này không giữ chuỗi nào.
 *
 * CP1.3 (2026-09-16, Tùng yêu cầu): **XOÁ nút hotline nổi (FAB)** — cả markup `<div class="cp-fab">`
 * lẫn CSS `.cp-fab*`. Thay thế: plugin riêng **`button-call-zalo-tungleads`** (widget Gọi/Zalo neo
 * lề phải, số nhập ở Settings → Button Call/Zalo). Xoá FAB cũng hết lỗi đã đo trước đây: FAB phủ
 * **832px²** lên link ghi công ở đáy trang (bấm giữa link lại trúng `A.call`).
 *
 * @package TL\Theme\CP
 */

defined( 'ABSPATH' ) || exit;

$cp_cols = cp_footer_columns();
$cp_logo = cp_footer_flag( 'logo' );
?>
<footer class="cp-footer">
	<div class="cp-container">
		<div class="cp-footer-grid cp-footer-grid--cols-<?php echo esc_attr( (string) count( $cp_cols ) ); ?>">
			<?php
			/* CP1.3 — mỗi cột = TIÊU ĐỀ + 1 ô soạn thảo văn bản (Customizer "Footer Cao Phát").
			   Cột chọn nguồn "Menu" thì in menu "Footer" (Appearance → Menus) thay cho ô soạn thảo.
			   Logo (Site Identity) chỉ in ở CỘT 1 và chỉ khi bật "Hiện logo ở Cột 1". */
			foreach ( $cp_cols as $cp_i => $cp_col ) :
				?>
				<div class="cp-foot-col">
					<?php if ( 0 === $cp_i && $cp_logo ) : ?>
						<?php
						/*
						 * CP1.3 — DOM: `the_custom_logo()` in ra `<a class="custom-logo-link">`; nếu bọc nó
						 * trong `<a class="cp-logo">` thì HTML lồng thẻ <a> KHÔNG hợp lệ → trình duyệt tự đóng
						 * thẻ ngoài, để lại `<a class="cp-logo">` RỖNG (đo 2026-09-16: cao 0px) và ảnh logo
						 * thành CON của `.cp-foot-col` ⇒ nay in THẲNG logo khi có logo, chỉ bọc `<a>` cho bản chữ.
						 */
						?>
						<?php if ( has_custom_logo() ) : ?>
							<?php the_custom_logo(); ?>
						<?php else : ?>
							<a class="cp-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>">
								<span class="cp-logo-mark">CP</span>
								<span class="cp-logo-text"><b><?php bloginfo( 'name' ); ?></b><span><?php esc_html_e( 'Thế giới cửa gỗ', 'tungleads-theme' ); ?></span></span>
							</a>
						<?php endif; ?>
					<?php endif; ?>

					<?php if ( '' !== $cp_col['title'] ) : ?>
						<h4><?php echo esc_html( $cp_col['title'] ); ?></h4>
					<?php endif; ?>

					<?php if ( 'menu' === $cp_col['source'] ) : ?>
						<?php
						if ( has_nav_menu( 'footer' ) ) {
							wp_nav_menu(
								array(
									'theme_location' => 'footer',
									'container'      => '',
									'items_wrap'     => '<ul>%3$s</ul>',
									'fallback_cb'    => false,
									'depth'          => 1,
								)
							);
						}
						?>
					<?php elseif ( '' !== $cp_col['content'] ) : ?>
						<div class="cp-col__body"><?php echo cp_footer_kses_content( $cp_col['content'] ); // phpcs:ignore WordPress.Security.EscapeOutput -- đã lọc bằng KSES ở `cp_footer_kses_content()`. ?></div>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>

		<div class="cp-footer-bottom">
			<?php $cp_copy = cp_footer_text( 'copyright' ); ?>
			<?php if ( '' !== $cp_copy ) : ?>
				<span><?php echo esc_html( $cp_copy ); ?></span>
			<?php endif; ?>
			<span class="cp-footer-credit">
				<?php
				/*
				 * CP1.3 — Dòng ghi công CỐ ĐỊNH trong code (yêu cầu Tùng 2026-09-16): luôn in kèm link về
				 * tungleads.com, KHÔNG còn ô nhập trong Customizer (đã bỏ setting/control `cp_footer_credit`).
				 * 2026-09-24 (Tùng yêu cầu): `<a>` thêm `nofollow` (trước đó cố ý KHÔNG đặt) ⇒
				 * `rel="noopener nofollow"`; CSS gạch chân của link này đã bỏ — xem `assets/caophat.css`.
				 * 2026-09-24 (Tùng yêu cầu, lần 2): CHỮ CỦA LINK đổi `tungleads.com` → **Tùng Lê Ads**
				 * (`href` GIỮ NGUYÊN `https://tungleads.com/`).
				 */
				printf(
					/* translators: %s: thẻ liên kết tới trang tungleads.com (chữ hiển thị: “Tùng Lê Ads”). */
					esc_html__( 'Thiết kế bởi: %s', 'tungleads-theme' ), // phpcs:ignore WordPress.Security.EscapeOutput -- markup <a> cố định bên dưới.
					'<a href="https://tungleads.com/" target="_blank" rel="noopener nofollow">Tùng Lê Ads</a>'
				);
				?>
			</span>
		</div>
	</div>
</footer>

<?php
/*
 * CP1.3 (2026-09-16, Tùng yêu cầu) — ĐÃ XOÁ nút hotline nổi `<div class="cp-fab">` (chỉ 1 nút
 * `<a class="call" href="tel:…">`) cùng toàn bộ CSS `.cp-fab*` + `@keyframes cp-pulse`.
 * Chức năng gọi/Zalo nay do plugin riêng **`button-call-zalo-tungleads`** lo (widget neo lề phải,
 * in ở `wp_footer` prio 5; số nhập ở Settings → Button Call/Zalo). Muốn quay lại: xem git trước
 * commit này (hoặc tắt plugin mới rồi khôi phục markup trong lịch sử `footer.php`).
 * Xoá FAB cũng hết lỗi đã đo: FAB phủ 832px² lên link ghi công ở đáy trang (bấm giữa link trúng `A.call`).
 */
?>

<?php wp_footer(); ?>
</body>
</html>
