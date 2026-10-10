<?php
/**
 * Plugin Name:  PL Tiện Ích - TungLeAds
 * Description:  Tiện ích dùng chung cho nhiều website WordPress: 4 khối tracking (GTM · GA4 · Google Ads · Meta Pixel) · chèn mã tracking 3 vị trí (head/body/footer) · chế độ bảo trì · mục lục nội dung · ĐỔI ĐƯỜNG DẪN ĐĂNG NHẬP (ẩn wp-admin) · XÁC MINH 2 LỚP (app xác thực TOTP) · thông số sản phẩm (WooCommerce) · đặt hàng nhanh (WooCommerce). Cấu hình ở Settings → PL Tiện Ích.
 * Version:      1.2.0
 * Requires PHP: 8.2
 * Author:       Tùng Lê Ads
 * Author URI:   https://tungleads.com/
 *
 * @package TL\Utilities
 */

defined( 'ABSPATH' ) || exit;

/** Đường dẫn/URL plugin + version lấy từ header (dùng cho cache-busting asset). */
define( 'TLPI_FILE', __FILE__ );
define( 'TLPI_DIR', plugin_dir_path( __FILE__ ) );
define( 'TLPI_URL', plugin_dir_url( __FILE__ ) );
define( 'TLPI_VERSION', '1.2.0' );

/** CP8 — Mục lục nội dung (nút dọc + drawer): cấu hình ở Settings → PL Tiện Ích. */
require_once __DIR__ . '/includes/toc.php';

/** v1.1.0 — Đổi đường dẫn đăng nhập (bảo mật): cấu hình ở Settings → PL Tiện Ích. */
require_once __DIR__ . '/includes/login-path.php';

/** v1.2.0 — Xác minh 2 lớp bằng app xác thực (TOTP): quản lý ở Hồ sơ + Settings → PL Tiện Ích. */
require_once __DIR__ . '/includes/two-factor.php';
/*
 * ---------------------------------------------------------------------------
 * TƯƠNG THÍCH NGƯỢC khi ĐỔI TIỀN TỐ (v1.0.0: `tlcp_` → `tlpi_`, tên plugin/thư mục mới).
 *
 * Site đã dùng bản cũ (option `tlcp_*` đã lưu) vẫn đọc được cấu hình cũ nhờ hàm dưới đây ⇒ nâng cấp
 * KHÔNG mất dữ liệu, không cần chạy script chuyển trước. Ghi/xoá luôn dùng TÊN MỚI ⇒ sau lần lưu
 * đầu tiên là dọn dần; muốn xoá hẳn option cũ thì chạy `docs/prod-migrate-plugin-prefix.php`.
 * GỠ hàm này khi mọi site đã chuyển hẳn.
 * ---------------------------------------------------------------------------
 */

/**
 * Đọc option theo TÊN MỚI, tự lùi về TÊN CŨ (`tlcp_*`) nếu site chưa chuyển.
 *
 * @param string $name     Tên option mới (`tlpi_*`).
 * @param string $legacy   Tên option cũ (`tlcp_*`).
 * @param mixed  $default  Giá trị mặc định khi CẢ HAI đều chưa có.
 * @return mixed
 */
function tlpi_option( string $name, string $legacy, $default = false ) {
	$value = get_option( $name, null );
	if ( null !== $value ) {
		return $value;
	}

	return get_option( $legacy, $default );
}


/*
 * ---------------------------------------------------------------------------
 * TRACKING (4 khối mặc định) — v0.6.0: 4 ID nay SỬA ĐƯỢC ở Settings → PL Tiện Ích.
 *
 * 4 hằng số dưới đây chỉ là GIÁ TRỊ MẶC ĐỊNH (ID đang chạy ở dự án hiện tại, chuyển từ Flatsome →
 * Advanced → Global HTML). Giá trị ĐANG DÙNG đọc từ option `tlpi_tracking_ids` (Settings → PL Tiện Ích → “ID
 * tracking”): CHƯA lưu bao giờ ⇒ dùng 4 hằng số này (giữ nguyên hành vi cũ); đã lưu rồi thì option
 * là chuẩn — **ô để trống = KHÔNG in khối đó**, bỏ tick “Bật” = không in khối nào.
 *
 * Lưu ý: MANG PLUGIN SANG WEBSITE KHÁC THÌ PHẢI ĐỔI 4 ID NÀY (nhập ở Settings, không cần sửa file) —
 *    nếu để ID của dự án cũ thì dữ liệu site mới sẽ chảy vào tài khoản GA/Ads/Pixel của dự án đó.
 * Lưu ý: Dán mã TRÙNG ở mục “Chèn mã tracking” bên dưới ⇒ BỊ ĐẾM ĐÔI (gỡ một trong hai chỗ).
 * Lưu ý: **Khi deploy lên site đang dùng:** 4 khối này sao y bản cũ nằm ở **Flatsome → Advanced → Global
 *    HTML** (hoặc chỗ khác) ⇒
 *    khi deploy phải GỠ 4 khối đó khỏi Flatsome CÙNG LÚC, không để cả hai chạy (đếm đôi).
 * Ghi chú: GTM thường đã chứa GA + Google Ads + Meta Pixel; nếu vậy thì để trống 3 ô kia, chỉ giữ GTM.
 * ID marketing là định danh công khai (đã lộ trong HTML trang), không phải secret.
 * ---------------------------------------------------------------------------
 */
const TLPI_GTM_ID   = 'GTM-KCVHR8P';
const TLPI_GA4_ID    = 'G-L37N4Q06LP';
const TLPI_GADS_ID   = 'AW-10871632223';
const TLPI_PIXEL_ID  = '5267684856622253';

/** Scripts trong <head> — mỗi khối chỉ in khi ID của nó KHÔNG trống (v0.6.0: ID nhập ở Settings). */
add_action(
	'wp_head',
	static function (): void {
		$tlpi_ids = tlpi_tracking_ids();
		if ( empty( $tlpi_ids['on'] ) ) {
			return;
		}

		if ( '' !== $tlpi_ids['gtm'] ) {
			?>
<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','<?php echo esc_js( $tlpi_ids['gtm'] ); ?>');</script>
			<?php
		}

		if ( '' !== $tlpi_ids['ga4'] || '' !== $tlpi_ids['gads'] ) {
			?>
<!-- gtag (GA4 + Google Ads) -->
			<?php if ( '' !== $tlpi_ids['ga4'] ) : ?>
<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo esc_attr( $tlpi_ids['ga4'] ); ?>"></script>
			<?php endif; ?>
<script>
window.dataLayer = window.dataLayer || [];
function gtag(){dataLayer.push(arguments);}
gtag('js', new Date());
			<?php if ( '' !== $tlpi_ids['ga4'] ) : ?>
gtag('config', '<?php echo esc_js( $tlpi_ids['ga4'] ); ?>');
			<?php endif; ?>
			<?php if ( '' !== $tlpi_ids['gads'] ) : ?>
gtag('config', '<?php echo esc_js( $tlpi_ids['gads'] ); ?>');
			<?php endif; ?>
</script>
			<?php
		}

		if ( '' !== $tlpi_ids['pixel'] ) {
			?>
<!-- Meta Pixel -->
<script>
!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');
fbq('init', '<?php echo esc_js( $tlpi_ids['pixel'] ); ?>');
fbq('track', 'PageView');
</script>
			<?php
		}
	},
	1
);

/** Fallback <noscript> ngay sau <body> — cũng theo ID đang nhập ở Settings. */
add_action(
	'wp_body_open',
	static function (): void {
		$tlpi_ids = tlpi_tracking_ids();
		if ( empty( $tlpi_ids['on'] ) ) {
			return;
		}

		if ( '' !== $tlpi_ids['gtm'] ) {
			echo "\n" . '<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=' . esc_attr( $tlpi_ids['gtm'] ) . '" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>';
		}
		if ( '' !== $tlpi_ids['pixel'] ) {
			echo "\n" . '<noscript><img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id=' . esc_attr( $tlpi_ids['pixel'] ) . '&ev=PageView&noscript=1" alt=""/></noscript>';
		}
		if ( '' !== $tlpi_ids['gtm'] || '' !== $tlpi_ids['pixel'] ) {
			echo "\n";
		}
	},
	1
);

/* ============================ 4 ID TRACKING (v0.6.0) ============================ */

const TLPI_TRACKING_IDS_OPTION = 'tlpi_tracking_ids';

/**
 * 4 ID tracking ĐANG DÙNG — nhập ở **Settings → PL Tiện Ích → “ID tracking”**.
 *
 * `get_option(..., null)`: CHƯA lưu bao giờ (null) ⇒ dùng 4 hằng số mặc định (đúng bằng hành vi cũ,
 * nên nâng cấp plugin không đổi gì trên site đang chạy). ĐÃ lưu rồi thì option là CHUẨN: ô để trống
 * nghĩa là **KHÔNG in khối đó** (không tự quay về mặc định — nếu không thì không tắt được khối nào).
 *
 * Dùng khi mang plugin sang website khác: đổi 4 ID ở Settings là xong, không cần sửa file.
 *
 * @return array{on:string,gtm:string,ga4:string,gads:string,pixel:string}
 */
function tlpi_tracking_ids(): array {
	$saved = tlpi_option( TLPI_TRACKING_IDS_OPTION, 'tlcp_tracking_ids', null );

	if ( is_array( $saved ) ) {
		$out = array(
			'on'    => empty( $saved['on'] ) ? '' : '1',
			'gtm'   => (string) ( $saved['gtm'] ?? '' ),
			'ga4'   => (string) ( $saved['ga4'] ?? '' ),
			'gads'  => (string) ( $saved['gads'] ?? '' ),
			'pixel' => (string) ( $saved['pixel'] ?? '' ),
		);
	} else {
		$out = array(
			'on'    => '1',
			'gtm'   => TLPI_GTM_ID,
			'ga4'   => TLPI_GA4_ID,
			'gads'  => TLPI_GADS_ID,
			'pixel' => TLPI_PIXEL_ID,
		);
	}

	/**
	 * Lọc 4 ID tracking (đổi bằng code mà không cần vào Settings).
	 *
	 * @param array<string,string> $out 4 ID đang dùng.
	 */
	return (array) apply_filters( 'tlpi_tracking_ids', $out );
}

/**
 * Sanitize 4 ID: chỉ giữ chữ HOA + số + `-` + `_` (đúng dạng `GTM-KCVHR8P`, `G-L37N4Q06LP`,
 * `AW-10871632223`, số pixel) — dán kèm dấu cách/ngoặc cũng tự sạch.
 *
 * @param mixed $value Giá trị từ form.
 * @return array{on:string,gtm:string,ga4:string,gads:string,pixel:string}
 */
function tlpi_sanitize_tracking_ids( $value ): array {
	$value = is_array( $value ) ? $value : array();
	$out   = array(
		'on'    => empty( $value['on'] ) ? '' : '1',
		'gtm'   => '',
		'ga4'   => '',
		'gads'  => '',
		'pixel' => '',
	);

	foreach ( array( 'gtm', 'ga4', 'gads', 'pixel' ) as $tlpi_key ) {
		$tlpi_raw = isset( $value[ $tlpi_key ] ) ? strtoupper( sanitize_text_field( (string) $value[ $tlpi_key ] ) ) : '';
		$out[ $tlpi_key ] = (string) preg_replace( '/[^A-Z0-9_-]/', '', $tlpi_raw );
	}

	return $out;
}


/*
 * ---------------------------------------------------------------------------
 * CHÈN MÃ TRACKING TÙY Ý — 3 VỊ TRÍ (Tùng yêu cầu 2026-09-16).
 * Sửa ở Settings → PL Tiện Ích, dán NGUYÊN mã nhà cung cấp cấp (kèm cả thẻ <script> nếu có):
 *   head   → ngay sau thẻ <head>      (hook `wp_head` prio 1 — sớm nhất có thể)
 *   body   → ngay sau thẻ mở <body>   (hook `wp_body_open` prio 1 — theme con + theme cha đều gọi)
 *   footer → cuối trang, trước </body> (hook `wp_footer` prio 99 — sau mọi script khác)
 * Lưu ý: Dán mã TRÙNG với 4 khối tracking sẵn có ở trên (GTM/GA4/Google Ads/Meta Pixel) thì số liệu
 *    sẽ BỊ ĐẾM ĐÔI ⇒ gỡ một trong hai chỗ (đúng cảnh báo ở đầu file).
 * ---------------------------------------------------------------------------
 */
const TLPI_TRACKING_OPTION = 'tlpi_tracking_code';

/**
 * Mã tracking đang lưu, luôn đủ 3 khoá `head` / `body` / `footer` (chuỗi, đã trim).
 *
 * @return array{head:string,body:string,footer:string}
 */
function tlpi_tracking_code(): array {
	$saved = tlpi_option( TLPI_TRACKING_OPTION, 'tlcp_tracking_code', array() );
	$out   = array(
		'head'   => '',
		'body'   => '',
		'footer' => '',
	);

	if ( is_array( $saved ) ) {
		foreach ( $out as $key => $unused ) {
			if ( isset( $saved[ $key ] ) && is_string( $saved[ $key ] ) ) {
				$out[ $key ] = trim( $saved[ $key ] );
			}
		}
	}

	return (array) apply_filters( 'tlpi_tracking_code', $out );
}

/**
 * Sanitize mã tracking khi lưu.
 *
 * Mã tracking LÀ code nên KHÔNG được lọc theo kiểu văn bản (lọc là hỏng mã), nhưng chỉ giữ nguyên
 * văn khi người lưu có quyền `unfiltered_html`. Thiếu quyền đó (VD quản trị viên trên multisite)
 * thì cho qua `wp_kses_post` ⇒ thẻ `<script>` bị bỏ, an toàn hơn là cho chèn JS tuỳ ý.
 *
 * @param mixed $value Giá trị từ form.
 * @return array{head:string,body:string,footer:string}
 */
function tlpi_sanitize_tracking_code( $value ): array {
	$out     = array(
		'head'   => '',
		'body'   => '',
		'footer' => '',
	);
	$can_raw = current_user_can( 'unfiltered_html' );

	if ( ! is_array( $value ) ) {
		return $out;
	}

	foreach ( $out as $key => $unused ) {
		$raw = isset( $value[ $key ] ) && is_string( $value[ $key ] ) ? trim( $value[ $key ] ) : '';
		if ( '' === $raw ) {
			continue;
		}
		$out[ $key ] = $can_raw ? $raw : wp_kses_post( $raw );
	}

	return $out;
}

/**
 * In mã của 1 vị trí (không in gì khi ô trống).
 *
 * @param string $position `head` | `body` | `footer`.
 */
function tlpi_print_tracking_code( string $position ): void {
	if ( is_admin() ) {
		return; // Không in trong wp-admin.
	}
	if ( defined( 'TLPI_IS_LOGIN' ) && TLPI_IS_LOGIN ) {
		return; // v1.1.0 — không đưa trang ĐĂNG NHẬP vào số liệu GA/Pixel.
	}

	$code = tlpi_tracking_code();
	if ( ! isset( $code[ $position ] ) || '' === $code[ $position ] ) {
		return;
	}

	echo "\n" . $code[ $position ] . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- mã do QUẢN TRỊ dán, in nguyên văn mới chạy được.
}

add_action( 'wp_head', static fn() => tlpi_print_tracking_code( 'head' ), 1 );
add_action( 'wp_body_open', static fn() => tlpi_print_tracking_code( 'body' ), 1 );
add_action( 'wp_footer', static fn() => tlpi_print_tracking_code( 'footer' ), 99 );

/*
 * ---------------------------------------------------------------------------
 * CHẾ ĐỘ BẢO TRÌ (v0.3.0) — bật/tắt ở Settings → PL Tiện Ích.
 *   • Khách CHƯA đăng nhập → thấy trang thông báo (nội dung tự nhập).
 *   • Người có quyền `manage_options` (lọc được qua `tlpi_maintenance_capability`)
 *     vẫn xem web BÌNH THƯỜNG ⇒ bật bảo trì rồi vẫn sửa nội dung được.
 *   • Trả **HTTP 503 + `Retry-After`** = đúng chuẩn cho bảo trì TẠM THỜI
 *     (Google giữ trang trong index, không đánh rớt) + `noindex` cho an toàn.
 *   • Chặn cache (`DONOTCACHEPAGE` — LiteSpeed đọc biến này) để không cache trang bảo trì.
 *   • BỎ QUA: `wp-admin`, AJAX, cron, WP-CLI, REST/JSON — nếu không sẽ làm hỏng trình
 *     soạn thảo (Gutenberg gọi `/wp-json/`) và các tác vụ nền.
 *   • Trang bảo trì là HTML + CSS nội tuyến, KHÔNG dùng CSS/JS của theme ⇒ vẫn hiện
 *     đúng kể cả khi theme đang lỗi hoặc đang nâng cấp.
 * ---------------------------------------------------------------------------
 */
const TLPI_MAINTENANCE_OPTION = 'tlpi_maintenance';

/**
 * Trạng thái bảo trì hiện tại.
 *
 * @return array{on:bool,message:string}
 */
function tlpi_maintenance(): array {
	$saved = tlpi_option( TLPI_MAINTENANCE_OPTION, 'tlcp_maintenance', array() );
	$out   = array(
		'on'      => false,
		'message' => '',
	);

	if ( is_array( $saved ) ) {
		$out['on']      = ! empty( $saved['on'] );
		$out['message'] = isset( $saved['message'] ) && is_string( $saved['message'] ) ? trim( $saved['message'] ) : '';
	}

	return (array) apply_filters( 'tlpi_maintenance', $out );
}

/**
 * Sanitize trạng thái bảo trì: `on` = cờ bật/tắt, `message` = HTML cơ bản (không cho script).
 *
 * @param mixed $value Giá trị từ form.
 * @return array{on:bool,message:string}
 */
function tlpi_sanitize_maintenance( $value ): array {
	$out = array(
		'on'      => false,
		'message' => '',
	);

	if ( ! is_array( $value ) ) {
		return $out;
	}

	$out['on']      = ! empty( $value['on'] );
	$out['message'] = isset( $value['message'] ) && is_string( $value['message'] ) ? wp_kses_post( trim( $value['message'] ) ) : '';

	return $out;
}

/**
 * Người đang xem có được BỎ QUA trang bảo trì không? (mặc định: quản trị viên)
 */
function tlpi_maintenance_bypass(): bool {
	$cap = (string) apply_filters( 'tlpi_maintenance_capability', 'manage_options' );

	return is_user_logged_in() && current_user_can( $cap );
}

/** Trang bảo trì độc lập (HTML + CSS nội tuyến). */
add_action(
	'template_redirect',
	static function (): void {
		$mt = tlpi_maintenance();
		if ( empty( $mt['on'] ) ) {
			return;
		}
		if ( is_admin() || wp_doing_ajax() || wp_doing_cron() || ( defined( 'WP_CLI' ) && WP_CLI ) || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
			return;
		}
		if ( function_exists( 'wp_is_json_request' ) && wp_is_json_request() ) {
			return;
		}
		if ( tlpi_maintenance_bypass() ) {
			return;
		}

		if ( ! defined( 'DONOTCACHEPAGE' ) ) {
			define( 'DONOTCACHEPAGE', true );
		}
		nocache_headers();
		status_header( 503 );
		header( 'Retry-After: 3600' );
		header( 'Content-Type: text/html; charset=' . get_bloginfo( 'charset' ) );

		$site = (string) get_bloginfo( 'name' );
		$msg  = '' !== $mt['message']
			? $mt['message']
			: '<p>' . esc_html__( 'Website đang được bảo trì để nâng cấp. Vui lòng quay lại sau ít phút.', 'pl-tien-ich-tungleads' ) . '</p>';
		?>
<!DOCTYPE html>
<html lang="<?php echo esc_attr( get_bloginfo( 'language' ) ); ?>">
<head>
<meta charset="<?php echo esc_attr( get_bloginfo( 'charset' ) ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?php echo esc_html( sprintf( /* translators: %s: tên site. */ __( 'Bảo trì — %s', 'pl-tien-ich-tungleads' ), $site ) ); ?></title>
<style>
*{box-sizing:border-box}
body{margin:0;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px;
	background:#f4f5f7;color:#26221e;
	font:16px/1.65 -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif}
.box{width:100%;max-width:620px;background:#fff;border-radius:16px;padding:40px 36px;text-align:center;
	box-shadow:0 10px 40px rgba(0,0,0,.08)}
.ic{width:64px;height:64px;margin:0 auto 18px;border-radius:50%;display:grid;place-items:center;
	background:#fdf1e3;color:#c8471f}
.ic svg{width:32px;height:32px;fill:none;stroke:currentColor;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}
h1{margin:0 0 6px;font-size:22px;line-height:1.3}
.msg{margin:14px 0 0;font-size:16.5px}
.msg p{margin:0 0 10px}
.msg p:last-child{margin-bottom:0}
.tam{margin:18px 0 0;font-size:14px;color:#6f6a63}
.adm{margin:22px 0 0;font-size:13px}
.adm a{color:#c8471f}
@media(max-width:480px){.box{padding:28px 20px}h1{font-size:19px}}
</style>
</head>
<body>
	<div class="box">
		<div class="ic" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M14.7 6.3a4 4 0 0 0 5 5l-9 9a2.8 2.8 0 0 1-4-4z"/><path d="M14.7 6.3 17.6 3.4a4 4 0 0 1 3 4.9"/></svg></div>
		<h1><?php echo esc_html( $site ); ?></h1>
		<div class="msg"><?php echo $msg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- đã lọc bằng wp_kses_post khi lưu. ?></div>
		<p class="tam"><?php esc_html_e( 'Chúng tôi sẽ quay lại sớm nhất có thể — cảm ơn anh/chị đã chờ.', 'pl-tien-ich-tungleads' ); ?></p>
		<p class="adm"><a href="<?php echo esc_url( wp_login_url() ); ?>"><?php esc_html_e( 'Quản trị viên đăng nhập', 'pl-tien-ich-tungleads' ); ?></a></p>
	</div>
</body>
</html>
		<?php
		exit;
	},
	1
);



/*
 * ---------------------------------------------------------------------------
 * CPT / taxonomy / form site-specific — thêm vào đây khi cần, KHÔNG cho vào theme.
 * Nhớ flush rewrite rules khi kích hoạt plugin nếu có đăng ký rewrite.
 *
 * Ví dụ:
 * add_action( 'init', static function (): void {
 *     register_post_type( 'du_an', array( ... ) );
 * } );
 * ---------------------------------------------------------------------------
 */

/*
 * ---------------------------------------------------------------------------
 * Thông số kỹ thuật sản phẩm — tab riêng trong hộp "Dữ liệu sản phẩm" (admin).
 * Lưu vào post meta `_tlpi_spec_*`. Hiển thị ngoài frontend: CHƯA làm.
 * ---------------------------------------------------------------------------
 */

/** Trường thông số: key => [nhãn, kiểu]. */
function tlpi_spec_fields(): array {
	return array(
		'size'      => array( __( 'Kích thước', 'pl-tien-ich-tungleads' ), 'text' ),
		'door_type' => array( __( 'Loại cửa', 'pl-tien-ich-tungleads' ), 'text' ),
		'leaf'      => array( __( 'Cánh', 'pl-tien-ich-tungleads' ), 'text' ),
		'frame'     => array( __( 'Khung', 'pl-tien-ich-tungleads' ), 'text' ),
		'features'  => array( __( 'Tính năng', 'pl-tien-ich-tungleads' ), 'text' ),
		'origin'    => array( __( 'Xuất xứ', 'pl-tien-ich-tungleads' ), 'text' ),
		'warranty'  => array( __( 'Thời gian bảo hành', 'pl-tien-ich-tungleads' ), 'text' ),
		'note'      => array( __( 'Ghi chú', 'pl-tien-ich-tungleads' ), 'textarea' ),
	);
}

/** Thêm tab "Thông số kỹ thuật" vào Product Data. */
add_filter(
	'woocommerce_product_data_tabs',
	static function ( array $tabs ): array {
		$tabs['tlpi_spec'] = array(
			'label'    => __( 'Thông số kỹ thuật', 'pl-tien-ich-tungleads' ),
			'target'   => 'tlpi_spec_data',
			'class'    => array( 'show_if_simple', 'show_if_variable' ),
			'priority' => 65,
		);
		return $tabs;
	}
);

/** Nội dung tab. */
add_action(
	'woocommerce_product_data_panels',
	static function (): void {
		echo '<div id="tlpi_spec_data" class="panel woocommerce_options_panel">';
		foreach ( tlpi_spec_fields() as $key => $def ) {
			list( $label, $type ) = $def;
			$args = array(
				'id'    => "_tlpi_spec_{$key}",
				'label' => $label,
			);
			if ( 'textarea' === $type ) {
				woocommerce_wp_textarea_input( $args );
			} else {
				woocommerce_wp_text_input( $args );
			}
		}
		echo '</div>';
	}
);

/** Lưu (WooCommerce đã verify nonce của nó trước hook này). */
add_action(
	'woocommerce_admin_process_product_object',
	static function ( WC_Product $product ): void {
		foreach ( array_keys( tlpi_spec_fields() ) as $key ) {
			$field = "_tlpi_spec_{$key}";
			$val   = isset( $_POST[ $field ] ) // phpcs:ignore WordPress.Security.NonceVerification.Missing
				? sanitize_textarea_field( wp_unslash( $_POST[ $field ] ) ) // phpcs:ignore WordPress.Security.NonceVerification.Missing
				: '';
			$product->update_meta_data( $field, $val );
		}
	}
);

/*
 * ---------------------------------------------------------------------------
 * CP1.9 (2026-09-17, Tùng chốt) — MỤC “HOTLINE CHI NHÁNH” ĐÃ CHUYỂN VỀ THEME.
 *
 * Trước đây phần này nằm ở đây: option `tlpi_support_branches` + textarea trong Settings của plugin.
 * Lý do chuyển: đây là NỘI DUNG HIỂN THỊ, không phải business logic — mà hotline CHÍNH của site
 * (`cp_hotline_tel`) vốn đã ở Customizer của theme ⇒ gộp về 1 chỗ sửa mọi số điện thoại.
 *
 * Nay sửa ở **Customizer của theme đang dùng** (theme_mod `cp_branches`,
 * kéo thả từng dòng), theme đọc bằng `cp_support_branches()` trong child theme (`functions.php`).
 * Đọc option cũ ở đây KHÔNG còn hiệu lực. Lưu ý: ĐỪNG thêm lại mục này vào plugin.
 *
 * Dữ liệu cũ (nếu host đã nhập): chạy `docs/prod-migrate-branches.php` (repo gốc) để chuyển sang
 * theme_mod rồi xoá option — sau đó bỏ được CẦU NỐI tạm trong `cp_support_branches()` của theme.
 * ---------------------------------------------------------------------------
 */


/** Menu admin: Settings → PL Tiện Ích. */
add_action(
	'admin_menu',
	static function (): void {
		add_options_page(
			__( 'PL Tiện Ích - TungLeAds', 'pl-tien-ich-tungleads' ),
			__( 'PL Tiện Ích', 'pl-tien-ich-tungleads' ),
			'manage_options',
			'tlpi-support',
			'tlpi_support_page'
		);
	}
);

/** Đăng ký option (nonce + quyền do options.php lo). */
add_action(
	'admin_init',
	static function (): void {
		// Xác minh 2 lớp (v1.2.0) — bật/tắt + vai trò bắt buộc + số ngày nhớ thiết bị.
		register_setting(
			'tlpi_support',
			TLPI_2FA_OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => 'tlpi_2fa_sanitize',
				'default'           => array(),
			)
		);

		// Đường dẫn đăng nhập (v1.1.0) — bật/tắt + slug + cách xử lý đường dẫn cũ.
		register_setting(
			'tlpi_support',
			TLPI_LOGIN_OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => 'tlpi_login_sanitize',
				'default'           => array(),
			)
		);

		// 4 ID tracking (v0.6.0) — nay SỬA ĐƯỢC ở Settings nên mang plugin sang site khác không phải sửa file.
		register_setting(
			'tlpi_support',
			TLPI_TRACKING_IDS_OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => 'tlpi_sanitize_tracking_ids',
				'default'           => array(),
			)
		);

		// Chèn mã tracking 3 vị trí (mục "Chèn mã tracking" ở trên) — mảng 3 khoá, sanitize riêng.
		register_setting(
			'tlpi_support',
			TLPI_TRACKING_OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => 'tlpi_sanitize_tracking_code',
				'default'           => array(
					'head'   => '',
					'body'   => '',
					'footer' => '',
				),
			)
		);

		// Chế độ bảo trì (mục "Chế độ bảo trì" ở trên) — cờ bật/tắt + nội dung thông báo.
		register_setting(
			'tlpi_support',
			TLPI_MAINTENANCE_OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => 'tlpi_sanitize_maintenance',
				'default'           => array(
					'on'      => false,
					'message' => '',
				),
			)
		);
	}
);

/**
 * Dòng ghi công dùng chung cho **các plugin của theme** (Tùng yêu cầu 2026-09-16):
 * `Phiên bản <x.y.z> | Bởi <a>Tùng Lê Ads</a>` — version lấy ĐỘNG từ header plugin nên không lệch
 * khi bump version. Hiện ở cuối trang Settings của mỗi plugin.
 */
function tlpi_credit_line(): string {
	$data = get_file_data( __FILE__, array( 'Version' => 'Version' ) );
	$ver  = isset( $data['Version'] ) && '' !== $data['Version'] ? (string) $data['Version'] : '';

	return sprintf(
		/* translators: %s: số phiên bản của plugin. */
		esc_html__( 'Phiên bản %s', 'pl-tien-ich-tungleads' ),
		esc_html( $ver )
	) . ' | ' . sprintf(
		/* translators: %s: tên tác giả (có link website). */
		esc_html__( 'Bởi %s', 'pl-tien-ich-tungleads' ),
		'<a href="https://tungleads.com/" target="_blank" rel="noopener">Tùng Lê Ads</a>'
	);
}

/** Giao diện trang cài đặt. */
function tlpi_support_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	?>
	<div class="wrap">
		<h1><?php echo esc_html__( 'PL Tiện Ích - TungLeAds', 'pl-tien-ich-tungleads' ); ?></h1>
		<p class="description">
			<?php esc_html_e( 'Tiện ích Plugin đa chức năng được phát triển bởi Tùng Lê Ads', 'pl-tien-ich-tungleads' ); ?>
		</p>
		<form action="options.php" method="post">
			<?php settings_fields( 'tlpi_support' ); ?>
			<?php $tlpi_ids = tlpi_tracking_ids(); ?>
			<h2><?php esc_html_e( 'ID tracking', 'pl-tien-ich-tungleads' ); ?></h2>
			<p class="description">
				<?php esc_html_e( '4 khối plugin TỰ in trên mọi trang: Google Tag Manager · GA4 · Google Ads · Meta Pixel. Đổi ID ở đây, KHÔNG cần sửa file.', 'pl-tien-ich-tungleads' ); ?><br>
				<?php esc_html_e( 'Để TRỐNG một ô = không in khối đó. Bỏ tick “Bật” = không in khối nào (chỉ còn mã dán ở mục dưới).', 'pl-tien-ich-tungleads' ); ?>
			</p>
			<label style="display:flex;align-items:center;gap:8px;font-weight:600;margin:10px 0 0;">
				<input type="checkbox" name="<?php echo esc_attr( TLPI_TRACKING_IDS_OPTION . '[on]' ); ?>" value="1" <?php checked( ! empty( $tlpi_ids['on'] ) ); ?>>
				<?php esc_html_e( 'In 4 khối tracking ở trên', 'pl-tien-ich-tungleads' ); ?>
			</label>
			<p style="display:grid;grid-template-columns:190px minmax(240px,1fr);gap:8px 12px;align-items:center;max-width:680px;margin:12px 0 0;">
				<label for="tlpi-track-gtm"><?php esc_html_e( 'Google Tag Manager', 'pl-tien-ich-tungleads' ); ?></label>
				<input id="tlpi-track-gtm" type="text" class="code" name="<?php echo esc_attr( TLPI_TRACKING_IDS_OPTION . '[gtm]' ); ?>" value="<?php echo esc_attr( $tlpi_ids['gtm'] ); ?>" placeholder="GTM-XXXXXXX">

				<label for="tlpi-track-ga4"><?php esc_html_e( 'GA4 (Google Analytics)', 'pl-tien-ich-tungleads' ); ?></label>
				<input id="tlpi-track-ga4" type="text" class="code" name="<?php echo esc_attr( TLPI_TRACKING_IDS_OPTION . '[ga4]' ); ?>" value="<?php echo esc_attr( $tlpi_ids['ga4'] ); ?>" placeholder="G-XXXXXXXXXX">

				<label for="tlpi-track-gads"><?php esc_html_e( 'Google Ads', 'pl-tien-ich-tungleads' ); ?></label>
				<input id="tlpi-track-gads" type="text" class="code" name="<?php echo esc_attr( TLPI_TRACKING_IDS_OPTION . '[gads]' ); ?>" value="<?php echo esc_attr( $tlpi_ids['gads'] ); ?>" placeholder="AW-XXXXXXXXXX">

				<label for="tlpi-track-pixel"><?php esc_html_e( 'Meta Pixel', 'pl-tien-ich-tungleads' ); ?></label>
				<input id="tlpi-track-pixel" type="text" class="code" name="<?php echo esc_attr( TLPI_TRACKING_IDS_OPTION . '[pixel]' ); ?>" value="<?php echo esc_attr( $tlpi_ids['pixel'] ); ?>" placeholder="1234567890">
			</p>
			<p class="description">
				<?php esc_html_e( 'Để trống hết + bỏ tick = website không gửi dữ liệu đi đâu cả.', 'pl-tien-ich-tungleads' ); ?><br>
				<?php esc_html_e( 'GTM thường đã chứa GA4 + Google Ads + Meta Pixel — nếu vậy chỉ giữ ô GTM, để trống 3 ô kia để khỏi bắn 2 lần.', 'pl-tien-ich-tungleads' ); ?><br>
				<span style="color:#b32d2e;">
					<?php esc_html_e( 'Lưu ý: mang plugin sang website KHÁC thì ĐỔI 4 ID này — để nguyên ID của dự án cũ là dữ liệu site mới chảy vào tài khoản của dự án đó.', 'pl-tien-ich-tungleads' ); ?>
				</span>
			</p>

			<hr style="margin:28px 0 0;">
			<h2><?php esc_html_e( 'Chèn mã tracking', 'pl-tien-ich-tungleads' ); ?></h2>
			<p>
				<?php esc_html_e( 'Dán NGUYÊN mã nhà cung cấp cấp (kèm cả thẻ script nếu có) — Google Tag Manager, GA4, Meta Pixel, mã xác minh site, chat widget… Ô trống = không chèn gì.', 'pl-tien-ich-tungleads' ); ?>
			</p>
			<?php
			$tlpi_code = tlpi_tracking_code();
			$tlpi_pos  = array(
				'head'   => array(
					__( 'Sau thẻ <head>', 'pl-tien-ich-tungleads' ),
					__( 'Chạy sớm nhất trong <head> — dùng cho GTM, GA4, Google Ads, mã xác minh site.', 'pl-tien-ich-tungleads' ),
				),
				'body'   => array(
					__( 'Sau thẻ mở <body>', 'pl-tien-ich-tungleads' ),
					__( 'Ngay sau thẻ mở body — thường là thẻ noscript của GTM / Meta Pixel.', 'pl-tien-ich-tungleads' ),
				),
				'footer' => array(
					__( 'Cuối trang (footer)', 'pl-tien-ich-tungleads' ),
					__( 'Trước </body>, sau mọi script khác — dùng cho chat widget hoặc script tải chậm.', 'pl-tien-ich-tungleads' ),
				),
			);
			foreach ( $tlpi_pos as $tlpi_key => $tlpi_meta ) :
				?>
				<h3 style="margin:20px 0 4px;"><?php echo esc_html( $tlpi_meta[0] ); ?></h3>
				<textarea
					name="<?php echo esc_attr( TLPI_TRACKING_OPTION . '[' . $tlpi_key . ']' ); ?>"
					rows="5"
					class="large-text code"
					spellcheck="false"
					placeholder="<!-- dán mã vào đây -->"
				><?php echo esc_textarea( $tlpi_code[ $tlpi_key ] ); ?></textarea>
				<p class="description"><?php echo esc_html( $tlpi_meta[1] ); ?></p>
			<?php endforeach; ?>
			<p class="description" style="color:#b32d2e;">
				<?php esc_html_e( 'Lưu ý: plugin đang in sẵn 4 khối tracking ở mục “ID tracking” phía trên (GTM / GA4 / Google Ads / Meta Pixel). Nếu dán mã TRÙNG ở đây thì số liệu sẽ BỊ ĐẾM ĐÔI — gỡ một trong hai chỗ (hoặc để trống ô ID ở trên).', 'pl-tien-ich-tungleads' ); ?>
			</p>

			<hr style="margin:28px 0 0;">
			<h2><?php esc_html_e( 'Chế độ bảo trì', 'pl-tien-ich-tungleads' ); ?></h2>
			<?php $tlpi_mt = tlpi_maintenance(); ?>
			<label style="display:flex;align-items:center;gap:8px;font-weight:600;margin:8px 0 0;">
				<input type="checkbox" name="<?php echo esc_attr( TLPI_MAINTENANCE_OPTION . '[on]' ); ?>" value="1" <?php checked( ! empty( $tlpi_mt['on'] ) ); ?>>
				<?php esc_html_e( 'Bật chế độ bảo trì', 'pl-tien-ich-tungleads' ); ?>
			</label>
			<p class="description">
				<?php esc_html_e( 'Khách CHƯA đăng nhập sẽ thấy trang thông báo với HTTP 503 + Retry-After (Google giữ trang trong index, không đánh rớt) + noindex, và trang bảo trì không bị cache.', 'pl-tien-ich-tungleads' ); ?><br>
				<?php esc_html_e( 'Người có quyền quản trị vẫn xem web BÌNH THƯỜNG — bật bảo trì rồi vẫn sửa nội dung, cài plugin thoải mái.', 'pl-tien-ich-tungleads' ); ?>
			</p>

			<h3 style="margin:20px 0 4px;"><?php esc_html_e( 'Nội dung thông báo', 'pl-tien-ich-tungleads' ); ?></h3>
			<textarea name="<?php echo esc_attr( TLPI_MAINTENANCE_OPTION . '[message]' ); ?>" rows="4" class="large-text code" spellcheck="false"><?php echo esc_textarea( (string) $tlpi_mt['message'] ); ?></textarea>
			<p class="description">
				<?php esc_html_e( 'Để trống = dùng câu mặc định: “Website đang được bảo trì để nâng cấp. Vui lòng quay lại sau ít phút.”.', 'pl-tien-ich-tungleads' ); ?><br>
				<?php esc_html_e( 'Cho phép HTML cơ bản (p, strong, br, a, ul/li…) — KHÔNG cho thẻ script.', 'pl-tien-ich-tungleads' ); ?>
			</p>

			<?php tlpi_toc_settings_ui(); ?>

			<?php tlpi_login_settings_ui(); ?>

			<?php tlpi_2fa_settings_ui(); ?>

			<?php submit_button(); ?>

			<p class="tlpi-credit" style="margin-top:16px;color:#646970;font-style:italic;">
				<?php echo tlpi_credit_line(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- chuỗi đã escape từng phần, chỉ có 1 link cố định. ?>
			</p>
		</form>
	</div>
	<?php
}

/*
 * ---------------------------------------------------------------------------
 * CP3.3 — Đặt hàng nhanh.
 * Child theme in popup + gửi AJAX tới đây; phần dữ liệu/hành vi nằm ở plugin:
 * tạo ĐƠN WOOCOMMERCE THẬT, thanh toán COD, trạng thái "Đang xử lý" — giống luồng
 * checkout COD chuẩn (tự trừ tồn kho + gửi email "Đơn hàng mới" cho admin).
 * ---------------------------------------------------------------------------
 */

const TLPI_QO_NONCE    = 'tlpi_quick_order';
const TLPI_QO_MAX      = 5;  // số đơn tối đa / IP / 10 phút
const TLPI_QO_WINDOW   = 600;
const TLPI_QO_SHIPPING = 0;

/**
 * Số di động VN hợp lệ: đúng 10 số, đầu số thuộc danh sách nhà mạng Tùng chốt (2026-10-10).
 * Chấp nhận dấu chấm/cách/gạch và tiền tố +84/84 (đổi về 0). Dùng chung popup đặt hàng nhanh + checkout.
 * Đồng bộ danh sách với `assets/quick-order.js` (kiểm tra phía trình duyệt).
 */
function tlpi_is_vn_mobile( string $phone ): bool {
	$digits = (string) preg_replace( '/\D/', '', $phone );
	if ( 0 === strpos( $digits, '84' ) && 11 === strlen( $digits ) ) {
		$digits = '0' . substr( $digits, 2 );
	}
	$prefixes = array(
		'032', '033', '034', '035', '036', '037', '038', '039',
		'052', '056', '058', '059',
		'070', '076', '077', '078', '079',
		'081', '082', '083', '084', '085', '086', '088', '089',
		'090', '091', '092', '093', '094', '096', '097', '098', '099',
	);
	return 10 === strlen( $digits ) && in_array( substr( $digits, 0, 3 ), $prefixes, true );
}

/** Checkout WooCommerce: chặn đặt hàng nếu SĐT sai đầu số. */
add_action(
	'woocommerce_after_checkout_validation',
	static function ( $data, $errors ) {
		$phone = isset( $data['billing_phone'] ) ? (string) $data['billing_phone'] : '';
		if ( '' !== $phone && ! tlpi_is_vn_mobile( $phone ) ) {
			$errors->add( 'billing_phone_invalid', __( 'Số điện thoại chưa hợp lệ (cần 10 số, đầu số di động Việt Nam). Ví dụ: 0834.021.021', 'pl-tien-ich-tungleads' ) );
		}
	},
	10,
	2
);

/** Nonce cho popup (child theme gọi khi in form). */
function tlpi_quick_order_nonce(): string {
	return wp_create_nonce( TLPI_QO_NONCE );
}

add_action( 'wp_ajax_nopriv_tlpi_quick_order', 'tlpi_quick_order_handle' );
add_action( 'wp_ajax_tlpi_quick_order', 'tlpi_quick_order_handle' );
/* TƯƠNG THÍCH NGƯỢC: theme/bản cũ còn gửi action `cp_quick_order` thì vẫn chạy (gỡ khi mọi site đã chuyển). */
add_action( 'wp_ajax_nopriv_cp_quick_order', 'tlpi_quick_order_handle' );
add_action( 'wp_ajax_cp_quick_order', 'tlpi_quick_order_handle' );

/** AJAX: validate + tạo đơn, trả JSON cho popup. */
function tlpi_quick_order_handle(): void {
	/** Trả lỗi rồi dừng (wp_send_json_* tự exit). */
	$fail = static function ( string $message, int $code = 400 ): void {
		wp_send_json_error( array( 'message' => $message ), $code );
	};

	if ( 'POST' !== strtoupper( (string) ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) ) {
		$fail( __( 'Yêu cầu không hợp lệ.', 'pl-tien-ich-tungleads' ), 405 );
	}

	// 1. Nonce — chống gửi chéo site. Nhận cả nonce tạo theo TÊN CŨ (`cp_quick_order`) cho theme cũ.
	$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
	if ( '' === $nonce || ( ! wp_verify_nonce( $nonce, TLPI_QO_NONCE ) && ! wp_verify_nonce( $nonce, 'cp_quick_order' ) ) ) {
		$fail( __( 'Phiên làm việc đã hết hạn. Vui lòng tải lại trang rồi gửi lại.', 'pl-tien-ich-tungleads' ), 403 );
	}

	// 2. Honeypot — người thật không thấy field này. Nhận cả tên MỚI (`tlpi_hp`) lẫn tên CŨ (`cp_hp`).
	$honeypot = '';
	foreach ( array( 'tlpi_hp', 'cp_hp' ) as $tlpi_hp_field ) {
		if ( ! empty( $_POST[ $tlpi_hp_field ] ) ) {
			$honeypot = trim( (string) wp_unslash( $_POST[ $tlpi_hp_field ] ) );
			break;
		}
	}
	if ( '' !== $honeypot ) {
		$fail( __( 'Không gửi được yêu cầu.', 'pl-tien-ich-tungleads' ) );
	}

	// 3. Chống spam: giới hạn số đơn trên mỗi IP trong 10 phút.
	$ip    = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	$key   = 'tlpi_qo_' . md5( $ip );
	$count = (int) get_transient( $key );
	if ( $count >= TLPI_QO_MAX ) {
		$fail( __( 'Bạn vừa gửi quá nhiều yêu cầu. Vui lòng gọi hotline để được hỗ trợ ngay.', 'pl-tien-ich-tungleads' ), 429 );
	}
	set_transient( $key, $count + 1, TLPI_QO_WINDOW );

	// 4. Dữ liệu khách nhập.
	$product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
	$qty        = isset( $_POST['qty'] ) ? absint( $_POST['qty'] ) : 1;
	$name       = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
	$phone      = isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '';
	$address    = isset( $_POST['address'] ) ? sanitize_text_field( wp_unslash( $_POST['address'] ) ) : '';
	$note       = isset( $_POST['note'] ) ? sanitize_textarea_field( wp_unslash( $_POST['note'] ) ) : '';

	$qty = ( $qty < 1 ) ? 1 : min( $qty, 99 );

	if ( mb_strlen( $name ) < 2 ) {
		$fail( __( 'Vui lòng nhập họ tên người nhận.', 'pl-tien-ich-tungleads' ) );
	}
	if ( ! tlpi_is_vn_mobile( $phone ) ) {
		$fail( __( 'Số điện thoại chưa hợp lệ. Ví dụ: 0834.021.021', 'pl-tien-ich-tungleads' ) );
	}
	if ( mb_strlen( $address ) < 8 ) {
		$fail( __( 'Vui lòng nhập địa chỉ nhận hàng cụ thể.', 'pl-tien-ich-tungleads' ) );
	}

	// 5. Sản phẩm phải đang bán và đủ hàng (giá luôn lấy từ server, không tin client).
	$product = function_exists( 'wc_get_product' ) ? wc_get_product( $product_id ) : null;
	if ( ! $product instanceof WC_Product || 'publish' !== get_post_status( $product_id ) || ! $product->is_purchasable() ) {
		$fail( __( 'Sản phẩm không còn bán. Vui lòng chọn sản phẩm khác.', 'pl-tien-ich-tungleads' ) );
	}
	if ( ! $product->has_enough_stock( $qty ) ) {
		$fail( __( 'Sản phẩm hiện không đủ số lượng bạn cần. Vui lòng gọi hotline để kiểm tra kho.', 'pl-tien-ich-tungleads' ) );
	}

	// 6. Tạo đơn.
	$order = wc_create_order( array( 'created_via' => 'tlpi-quick-order' ) );
	if ( is_wp_error( $order ) ) {
		$fail( __( 'Chưa tạo được đơn hàng. Vui lòng gọi hotline để đặt trực tiếp.', 'pl-tien-ich-tungleads' ), 500 );
	}

	$order->add_product( $product, $qty );

	$addr = array(
		'first_name' => $name,
		'phone'      => $phone,
		'address_1'  => $address,
	);
	$order->set_address( $addr, 'billing' );
	$order->set_address( $addr, 'shipping' );
	$order->set_shipping_total( TLPI_QO_SHIPPING );

	// Cổng thanh toán: dùng COD nếu đang bật; tắt thì vẫn lưu đơn, chỉ ghi nhãn.
	$gateways = ( function_exists( 'WC' ) && WC()->payment_gateways() )
		? WC()->payment_gateways()->get_available_payment_gateways()
		: array();
	if ( isset( $gateways['cod'] ) ) {
		$order->set_payment_method( $gateways['cod'] );
	} else {
		$order->set_payment_method_title( __( 'Trả tiền mặt khi nhận hàng', 'pl-tien-ich-tungleads' ) );
	}

	if ( '' !== $note ) {
		$order->set_customer_note( $note );
	}
	$order->add_order_note( __( 'Đơn đặt nhanh từ popup "Mua hàng" (CP3.3) — khách tự nhập, không qua giỏ hàng.', 'pl-tien-ich-tungleads' ) );
	$order->calculate_totals();
	$order->update_status( 'processing', __( 'Đặt nhanh từ popup (CP3.3).', 'pl-tien-ich-tungleads' ), true );

	wp_send_json_success(
		array(
			'order'    => $order->get_order_number(),
			// CP3.3 — đặt thành công thì chuyển khách sang trang hoàn tất đơn (CP3.6), giống luồng checkout.
			// `get_checkout_order_received_url()` đã kèm `?key=<order_key>` nên trang đích tự xác thực được.
			'redirect' => $order->get_checkout_order_received_url(),
			'message'  => sprintf(
				/* translators: %s: mã đơn hàng */
				__( 'Đã tạo đơn #%s. Tùng Lê Ads sẽ gọi xác nhận trong ít phút.', 'pl-tien-ich-tungleads' ),
				$order->get_order_number()
			),
		)
	);
}
