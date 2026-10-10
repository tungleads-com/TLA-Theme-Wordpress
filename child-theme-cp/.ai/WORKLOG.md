# WORKLOG — bàn giao công việc giữa các model

**Repo:** `tungleads-theme-cp` (child theme caophat.vn) · nhánh `main` · **không có remote** (Tùng chốt 2026-09-14: dự án không dùng remote — đừng đề xuất push)
**Parent:** `tungleads-theme` (pin version bằng git tag, xem dòng `Base:` trong `README.md`)

> Chỉ dùng **2 model: `claude` (Claude Code) và `deepseek` (DeepSeek)** — **chạy tuần tự, không đồng thời**.
> **Không cần script, không cần cài gì** — chỉ đọc và sửa markdown.
> Luật chi tiết + bảng số CP ở **`CLAUDE.md`** (nguồn duy nhất); `AGENTS.md` chỉ là bản tóm tắt bắt buộc (cấu trúc Tùng chốt 2026-09-15).
> P-index của repo này dùng tiền tố **`CP`** (`CP1`–`CP5`) — bảng nghĩa ở `CLAUDE.md`.
>
> 1. **Đầu phiên:** đọc `§1 ĐANG LÀM` + 10 dòng cuối của `§2`.
> 2. **Bắt đầu việc:** cập nhật `§1` (model, việc, file sẽ chạm, trạng thái).
> 3. **Hết việc/phiên:** cập nhật lại `§1` (xong / dang dở + việc tiếp theo) rồi ghi 1 dòng vào `§2`.

> 4. `§1` **ghi đè** (chỉ giữ khối mới nhất) · `§2` **chỉ ghi thêm**, không sửa dòng cũ.

---

## §1 ĐANG LÀM (bàn giao — ghi đè mỗi phiên, chỉ giữ 1 khối)

- **Model:** `claude` (Claude Code).
- **CP3.3 — XONG + ĐÃ DEPLOY PROD (2026-10-10):** SĐT (popup đặt hàng nhanh + `/checkout/`) chỉ nhận **10 số + đầu số di động VN** theo danh sách Tùng chốt (34 đầu số; 087, 095, số bàn 02x bị chặn); chấp nhận `.`/cách/`-` và `+84`/`84`.
- **Code:** `tlpi_is_vn_mobile()` ở plugin `pl-tien-ich-tungleads.php` (dùng cho handler AJAX + hook `woocommerce_after_checkout_validation`); `assets/quick-order.js` có `isVnMobile()` (danh sách đầu số lặp lại — **đổi danh sách phải sửa CẢ HAI**); chuỗi lỗi `cfg.i18n.phone` ở `inc/woocommerce.php`. Commit `db7227f`.
- **⚠️ Bẫy deploy (đã dính):** `deploy-caophat.sh --go` có `--delete` ⇒ xoá `screenshot.png`/`screenshot.jpg` của theme cha + `screenshot.png` của child trên prod (local không có file đó) — KHÔNG khôi phục được (không backup). Dry-run đã hiện `deleting screenshot.*` nhưng không hỏi Tùng trước. Đề xuất thêm `--exclude='screenshot.*'` vào script (chờ Tùng duyệt).
- **Chưa làm:** purge LiteSpeed (Tùng tự làm) · test tay popup/checkout trên prod. **Tiếp theo:** (1) duyệt exclude screenshot; (2) form liên hệ nếu có plugin form khác thì áp cùng quy tắc.
- **Cập nhật lúc:** 2026-10-10

## §2 NHẬT KÝ (chỉ ghi thêm, mới nhất ở dưới)

| Thời gian | Model | Việc đã làm | File chính | Trạng thái |
|---|---|---|---|---|

> 📦 **Rotate 2026-09-18:** 144 dòng cũ (trước 2026-09-13 19:45) đã chuyển **nguyên văn** sang `.ai/WORKLOG-archive-2026-Q3.md`. §2 chỉ giữ **≤20 dòng gần nhất** (luật ở `AGENTS.md`). Tra việc cũ: `grep -n "<khoá>" .ai/WORKLOG-archive-2026-Q3.md` **Rút gọn 2026-09-18:** 20 dòng đang giữ đã viết lại ≤ 300 ký tự/dòng; bản ĐẦY ĐỦ ở `.ai/FEATURE_MAP.md` §“§2 GỐC”.

| 2026-09-17 15:21 (giờ thật) | cline | **XOÁ ẢNH EMOJI LỖI TRONG PLUGIN** (Tùng: *“Xoá link ảnh lỗi trong plugin `https://cdn.jsdelivr.net/gh/jdecked/twemoji@17.0.1/assets/26a0 | plugin: `tl-site-caophat.php`, `assets/t | ✅ — ❌ đã đổi tên 2026-09-17 → plugin `pl-tien-ich-tungleads` |
| 2026-09-17 15:38 (giờ thật) | cline | **PLUGIN v1.0.0 — ĐỔI TÊN “SẠCH HẲN” ĐỂ DÙNG CHUNG** (Tùng: *“Muốn đổi luôn cho ‘sạch’ hẳn không? -> đổi luôn cho mình -> test lại kỹ đảm bảo không lỗ… *(xem FEATURE_MAP §2 GỐC)* | plugin: **đổi tên thư mục + `pl-tien-ich-tungleads.php` | ✅ |
| 2026-09-17 15:58 (giờ thật) | cline | **CP3.9 — SỬA “CÓ SP HIỆN, CÓ SP KHÔNG HIỆN TAB ĐÁNH GIÁ”** (Tùng: *“kiểm tra phần đánh giá sản phẩm làm sao để hiện đánh giá lên các sản phẩm → hiện… *(xem FEATURE_MAP §2 GỐC)* | `CLAUDE.md` (CP3.9), `.ai/FEATURE_MAP.md` (CP3.9), `.ai | ✅ |
| 2026-09-17 16:13 (giờ thật) | cline | **BÀN GIAO DEPLOY CHO PHIÊN SAU (Claude) + 2 bug bắt được khi test** (Tùng: *“Claude hiện không biết các thay đổi mới nhất của bạn để push lên product… *(xem FEATURE_MAP §2 GỐC)* | `deploy-caophat.sh`, `docs/prod-import-plugin-options.p | ✅ |
| 2026-09-17 21:59 (giờ thật) | cline | **PLUGIN v1.1.0 — TÍNH NĂNG 1/2: ĐỔI ĐƯỜNG DẪN ĐĂNG NHẬP (ẩn wp-admin)** (Tùng: *“Tiếp tục thêm cho tôi chức năng thay đổi đường dẫn mặc định của admi… *(xem FEATURE_MAP §2 GỐC)* | plugin: `includes/login-path.php` (**mới**), `pl-tien-i | ✅ |
| 2026-09-17 23:31 (giờ thật) | cline | **PLUGIN v1.2.0 — TÍNH NĂNG 2/2: XÁC MINH 2 LỚP (2FA) BẰNG APP XÁC THỰC (TOTP)** (Tùng chốt phương án **B — app xác thực**, không dùng email). **Code:… *(xem FEATURE_MAP §2 GỐC)* | plugin: `includes/two-factor.php` (**mới**), `pl-tien-i | ✅ |
| 2026-09-18 07:59 (giờ thật) | cline | **CHILD THEME CP3.12 — Ô “MIÊU TẢ” CỦA DANH MỤC SẢN PHẨM THÀNH TRÌNH SOẠN THẢO ĐẦY ĐỦ (như trang thêm bài viết)** (Tùng yêu cầu). **Code:** `inc/admin… *(xem FEATURE_MAP §2 GỐC)* | child: `inc/admin-editor.php` (**mới**), `assets/admin- | ✅ |
| 2026-09-18 08:06 (giờ thật) | cline | **CSS (làn A) — icon khối “Thông số kỹ thuật” `.cp-spec__ic` 40×40 → 30×30** (Tùng yêu cầu, chỉ đổi kích thước). `assets/caophat.css` dòng 1651. **Đo… *(xem FEATURE_MAP §2 GỐC)* | child: `assets/caophat.css` | ✅ |
| 2026-09-18 10:48 (giờ thật) | cline | **PLUGIN — XOÁ EMOJI `⚠️` KHỎI 2 CHUỖI SETTINGS (ảnh vỡ Twemoji jsdelivr)** (Tùng: *“Xoá đường dẫn icon https://cdn.jsdelivr.net/gh/jdecked/twemoji@17… *(xem FEATURE_MAP §2 GỐC)* | plugin: `includes/login-path.php`, `README.md`; child:  | ✅ |
| 2026-09-18 10:58 (giờ thật) | cline | **THEME CHA `tungleads-theme` — FIX GỐC RỄ ẢNH VỠ EMOJI TWEMOJI (P2.3)** (Tùng chốt phương án **A: sửa ở theme cha**). `src/Features/Performance.php`:… *(xem FEATURE_MAP §2 GỐC)* | parent: `src/Features/Performance.php`, `CHANGELOG.md`, | ✅ |
| 2026-09-18 11:56 (giờ thật) | cline | **CSS (làn A) — `.cp-contact-btn` thêm `padding: 10px`** (Tùng yêu cầu; rule này TRƯỚC ĐÓ không có padding, dùng `height: 48px` + flex center). `asset… *(xem FEATURE_MAP §2 GỐC)* | child: `assets/caophat.css` | ✅ |
































| 2026-09-18 12:20 (giờ thật) | cline | **Tối ưu tài liệu: `CLAUDE.md` → CHỈ MỤC CP** (113,9 KB → 6,9 KB, -94%); chi tiết dồn về FEATURE_MAP; thêm hạn mức chống phình ở AGENTS.md. Chi tiết: FEATURE_MAP `### Tối ưu tài liệu 2026-09-18`. | `CLAUDE.md`, `AGENTS.md`, FEATURE_MAP, WORKLOG | ✅ |
| 2026-09-18 12:50 (giờ thật) | cline | **Rotate §2** (144 dòng cũ → `.ai/WORKLOG-archive-2026-Q3.md`, nguyên văn; §2 còn 20 dòng) + **tách 4 hàng CP > 3 KB** trong FEATURE_MAP xuống mục `### CPx.y`. | WORKLOG · archive · FEATURE_MAP · AGENTS | ✅ |
| 2026-09-18 13:25 (giờ thật) | cline | ❌ **ĐÍNH CHÍNH luật §2**: SỰ THẬT = §1 + FEATURE_MAP (sửa tại chỗ) · LỊCH SỬ = §2 + archive (chỉ thêm + phải dán nhãn đính chính); đã dán nhãn 13 dòng cũ. | AGENTS, WORKLOG, archive, plugin README | ✅ |

| 2026-09-18 14:10 (giờ thật) | cline | **Ngoài repo:** áp 6 chỉnh sửa vào chuẩn cấu trúc dự án `~/.claude/docs/project-structure-standard.md` (bản 18b) — khối tool tự sinh = **config-first**, tag `[FE]/[BE]/[DB]`, ngân sách ≤6k token/phiên, mục nhiều repo, cấm | ngoài repo | ✅ |
| 2026-09-18 20:55 (giờ thật) | cline | ❌ **ĐÍNH CHÍNH cấu trúc**: repo này giờ là **MONOREPO** (`parent-theme/` `plugin-tien-ich/` `plugin-zalo/`) — hết 4 repo riêng; local cần 4 bind mount (thiếu ⇒ `theme_no_stylesheet`); kèm fix fatal autoloader parent + deploy script trỏ thư mục con. | `AGENTS.md`/`CLAUDE.md` gốc, `docker-compose.yml`, `deploy-caophat.sh` | ✅ |
| 2026-09-24 (giờ thật) | cline | ❌ **ĐÍNH CHÍNH CP1.3 — ghi công chân trang: bỏ gạch chân tên miền + link thêm `nofollow`.** Đo 1440/768/390: `underline`→`none`, tràn ngang 0, 0 notice. Chi tiết ở FEATURE_MAP `### CP1.3`. | `footer.php`, `assets/caophat.css`, FEATURE_MAP | ✅ |
| 2026-09-24 (giờ thật #2) | cline | **DEPLOY PROD (CP1.3):** dry-run → `./deploy-caophat.sh --go`, đẩy 2 file (`footer.php`, `caophat.css`), 0 xoá; prod khớp local md5 **2/2**; Playwright prod 1440/390: `underline`→`none`, `nofollow`, tràn ngang 0. Commit `2c67e5b`. | `deploy-caophat.sh` | ✅ |
| 2026-09-24 (giờ thật #3) | cline | **Đổi CHỮ link ghi công: tungleads.com → Tùng Lê Ads** (href giữ nguyên) + **deploy prod + purge LiteSpeed**; prod khớp md5; đo local+prod: "Thiết kế bởi: Tùng Lê Ads", rect 94,84→76,53px, không gạch chân, tràn ngang 0, commit `6cc6cea`. | `footer.php` | ✅ |
| 2026-10-10 | claude | **CP3.3 — chặn SĐT sai đầu số di động VN** (popup + checkout, server + JS) → commit `db7227f` + `deploy-caophat.sh --go` (3 file; prod khớp grep). ⚠️ rsync `--delete` xoá screenshot.* trên prod (xem §1). | `plugin-tien-ich/pl-tien-ich-tungleads.php`, `assets/quick-order.js`, `inc/woocommerce.php` | ✅ |
