# Telegram Production Setup

## Domain production

Webhook production:

```text
https://hongbiennhanh.online/telegram/webhook
```

Telegram yêu cầu webhook dùng HTTPS hợp lệ. Không dùng `http://127.0.0.1`, `localhost` hoặc domain không có chứng chỉ TLS.

## Biến môi trường

Thiết lập trên server production:

```env
TELEGRAM_BOT_TOKEN=
TELEGRAM_BOT_USERNAME=
TELEGRAM_WEBHOOK_SECRET=
TELEGRAM_WEBHOOK_URL=https://hongbiennhanh.online/telegram/webhook
```

Không commit token hoặc webhook secret vào source code.

## Đăng ký webhook

Sau khi deploy:

```bash
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan storage:link
npm run build
php artisan telegram:webhook:set
php artisan telegram:webhook:info
```

Hoặc dùng trang quản trị:

```text
/telegram/settings
```

Chọn `Đăng ký webhook`, sau đó chọn `Xem trạng thái webhook`.

## Kiểm tra bằng curl

```bash
curl https://api.telegram.org/bot<TELEGRAM_BOT_TOKEN>/getWebhookInfo
```

Kết quả đúng phải có:

```json
{
  "ok": true,
  "result": {
    "url": "https://hongbiennhanh.online/telegram/webhook",
    "pending_update_count": 0
  }
}
```

## Cloudflare

- Bật SSL/TLS chế độ Full hoặc Full strict.
- Không chặn `POST /telegram/webhook`.
- Không bật rule yêu cầu JavaScript challenge cho webhook.
- Nếu dùng WAF, cho phép Telegram gọi endpoint webhook.

## Queue worker

Nếu production dùng queue khác `sync`, chạy worker:

```bash
php artisan queue:work --tries=3
```

Nên quản lý worker bằng Supervisor hoặc systemd.

## Luồng callback nút inline

Khi phụ huynh bấm `✅ Đã hoàn thành`:

1. Telegram gửi `callback_query` tới `/telegram/webhook`.
2. Laravel kiểm tra `X-Telegram-Bot-Api-Secret-Token`.
3. Laravel đọc `callback_query.data`.
4. Hệ thống cập nhật `training_sessions.status`.
5. Hệ thống gọi `answerCallbackQuery`.
6. Hệ thống gửi tin nhắn xác nhận.
7. Timeline `/telegram` hiển thị phản hồi đến.

## Debug callback

Kiểm tra log:

```bash
tail -f storage/logs/laravel.log
```

Các log an toàn có thể thấy:

- `Telegram webhook received`
- `Telegram training callback dispatch`
- `Telegram training callback processed`
- `Telegram answerCallbackQuery response`
- `Telegram sendMessage response`

Log không chứa bot token hoặc webhook secret.

## Lỗi thường gặp

- `404 Not Found`: webhook đang trỏ sai URL.
- `419`: webhook chưa được loại trừ CSRF.
- `403`: webhook secret sai hoặc thiếu trên production.
- `pending_update_count` tăng: Telegram không gọi được server.
- Nút bấm không phản hồi: kiểm tra `answerCallbackQuery`, log callback và webhook URL.

## Checklist sau deploy

1. Mở `https://hongbiennhanh.online`.
2. Đăng nhập quản trị.
3. Mở `/telegram/settings`.
4. Lưu token, webhook URL và webhook secret.
5. Chọn `Đăng ký webhook`.
6. Chạy `php artisan telegram:webhook:info`.
7. Gửi tin nhắn thử.
8. Gửi lịch tập hôm nay.
9. Bấm nút inline Telegram.
10. Kiểm tra `/telegram` có inbound callback.
11. Kiểm tra `/training/{id}` đã cập nhật trạng thái.
12. Kiểm tra không có mojibake tiếng Việt.

## Nhắc lịch tự động

Hệ thống có lệnh gửi nhắc lịch Telegram trước 30 phút cho:

- Lịch tập
- Lịch ăn uống
- Lịch bổ sung

Lệnh chạy thủ công:

```bash
php artisan telegram:send-due-reminders
```

Cron production:

```cron
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

Nếu dùng queue khác `sync`, cần chạy thêm:

```bash
php artisan queue:work --tries=3
```

## Xử lý lỗi nhắc lịch

- Không gửi nhắc lịch: kiểm tra `TELEGRAM_BOT_TOKEN`, `default_chat_id`, trạng thái bé và thời gian lịch.
- Gửi trùng: kiểm tra bảng `telegram_reminder_logs`; hệ thống có khóa chống trùng theo loại nhắc, đối tượng, giờ nhắc và chat id.
- Callback bổ sung không nhận: kiểm tra webhook đang trỏ đúng `/telegram/webhook`, có `allowed_updates` gồm `callback_query`.
- Lịch ăn không nhắc: kiểm tra `meal_plan_items.scheduled_time` đã có giờ cụ thể.
