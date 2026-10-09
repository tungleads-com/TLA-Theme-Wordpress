# TLA Theme Wordpress — caophat.vn

License: GPL-2.0-or-later (xem [`LICENSE`](LICENSE)) — chuẩn WordPress, ai cũng được dùng/sửa/phân phối lại, kể cả thương mại, miễn giữ cùng giấy phép GPL cho bản phái sinh.

Monorepo gộp 4 thành phần triển khai cho site **caophat.vn** (WordPress/WooCommerce). `parent-theme/` là theme nền tự viết (không phải child theme của theme trả phí nào), dùng chung được cho nhiều site — 3 phần còn lại là tuỳ biến riêng cho caophat.vn.
Child theme không tự hoạt động nếu thiếu parent theme.

```
TLA-Theme-Wordpress/
├── parent-theme/        # Parent theme tuỳ biến (tungleads-theme) — nền tảng dùng chung nhiều site
├── child-theme-cp/       # Child theme riêng cho caophat.vn (tungleads-theme-cp) — style/tính năng riêng site
├── plugin-tien-ich/       # Plugin tiện ích riêng site (pl-tien-ich-tungleads) — 2FA, term editor, v.v.
└── plugin-zalo/           # Plugin widget nút liên hệ nổi (button-call-zalo-tungleads)
```

## Yêu cầu môi trường

- WordPress ≥ 6.5, PHP ≥ 8.2, WooCommerce (cho các tính năng liên quan sản phẩm/giỏ hàng).
- Không phụ thuộc theme/plugin trả phí nào khác — `parent-theme/` tự viết từ đầu.

## Dùng lại cho dự án/site khác

Repo này public để tham khảo hoặc tái sử dụng theo GPL-2.0. Muốn dùng cho site khác:

- **`parent-theme/`** — dùng được nguyên trạng làm nền cho site mới (đổi `Text Domain`, `Theme Name` trong `style.css` cho phù hợp).
- **`child-theme-cp/`** — gắn cứng theo skin/nghiệp vụ caophat.vn (tên hàm, class CSS `cp-*`, nội dung tiếng Việt riêng site) — nên dùng làm **ví dụ tham khảo cấu trúc**, không copy nguyên để chạy site khác.
- **`plugin-tien-ich/`**, **`plugin-zalo/`** — 2 plugin tương đối độc lập, dùng lại được sau khi đổi text domain/tên hiển thị.

## Cài đặt lên WordPress

Copy/deploy đúng thư mục đích:

| Thư mục trong repo | Đích trên WordPress |
|---|---|
| `parent-theme/` | `wp-content/themes/tungleads-theme/` |
| `child-theme-cp/` | `wp-content/themes/tungleads-theme-cp/` |
| `plugin-tien-ich/` | `wp-content/plugins/pl-tien-ich-tungleads/` |
| `plugin-zalo/` | `wp-content/plugins/button-call-zalo-tungleads/` |

Kích hoạt theo thứ tự: **parent theme trước** (không kích hoạt trực tiếp, chỉ cần có mặt) → **kích hoạt child theme** (Giao diện > Themes) → **kích hoạt plugin**.

## Cách dùng repo này

### 1. Clone về máy (chỉ để xem/sửa code, không phải để chạy WordPress ngay)

```bash
git clone git@github.com:tungleads-com/TLA-Theme-Wordpress.git
```

> Lưu ý: **gốc repo không phải 1 theme WordPress** (không có `style.css` ở root) — WordPress phải trỏ vào từng thư mục con (`child-theme-cp/`, `parent-theme/`...) theo bảng "Cài đặt lên WordPress" ở trên, KHÔNG trỏ vào gốc repo.

### 2. Deploy lên site thật (chỉ áp dụng cho caophat.vn)

Repo này không có script deploy — script deploy (`deploy-caophat.sh`, rsync-based, mặc định dry-run) nằm ở repo vận hành riêng (**private**, không public vì chứa quy trình/SSH của server production). Nếu đây là site của bạn: tự viết script deploy phù hợp hạ tầng của bạn (rsync/SSH, CI/CD, hoặc plugin di chuyển site).

### 3. Lấy 1 phần (VD chỉ plugin) ra dùng cho dự án khác

```bash
# Copy thô 1 thư mục con (mất lịch sử commit riêng của phần đó)
git clone --depth 1 git@github.com:tungleads-com/TLA-Theme-Wordpress.git tmp-clone
cp -R tmp-clone/plugin-tien-ich ./plugin-moi
rm -rf tmp-clone
```

Muốn giữ nguyên lịch sử commit của riêng 1 thư mục con (VD tách `plugin-zalo/` ra repo riêng): dùng `git subtree split --prefix=plugin-zalo -b plugin-zalo-only` rồi push nhánh đó sang repo mới.

### 4. Theme/plugin phụ thuộc lẫn nhau — không dùng lẻ `child-theme-cp/`

`child-theme-cp/` bắt buộc phải có `parent-theme/` cài cùng cấp trong `wp-content/themes/`, nếu không WordPress báo lỗi thiếu theme cha. 2 plugin (`plugin-tien-ich/`, `plugin-zalo/`) độc lập, có thể dùng riêng cho site khác.

## Tài liệu chi tiết từng phần

Mỗi thư mục con là 1 dự án con độc lập, có tài liệu riêng:

- `child-theme-cp/CLAUDE.md`, `child-theme-cp/AGENTS.md`, `child-theme-cp/.ai/` — quy ước code, P-index (CPx.y), nhật ký làm việc của child theme.
- `parent-theme/` — xem README/CLAUDE.md riêng trong thư mục (nếu có).
- `plugin-tien-ich/` — xem README riêng trong thư mục.

## Lịch sử gộp repo

4 thư mục trên trước đây là 4 git repo riêng (đúng với cách chúng được version độc lập trong máy dev), được gộp vào monorepo này bằng `git subtree` (giữ nguyên lịch sử commit từng phần) ngày 2026-09-18 — `plugin-zalo/` gộp sau cùng, để dễ quản lý trên GitHub và phản ánh đúng quan hệ phụ thuộc (child theme không chạy được nếu thiếu parent theme).
