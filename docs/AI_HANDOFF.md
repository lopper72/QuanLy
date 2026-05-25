# AI Handoff Document

## Exercise Media and Steps Support
- **Date:** 2026-05-22
- **Status:** Complete
- **Feature:** Added support for media (thumbnail, video, video URL) and structured steps with images to Exercises.
- **Business rule:** Exercises now support a thumbnail image, a video file, and a YouTube/external video URL. They also support multiple steps, each with a title, instruction, and an optional image. Steps are ordered by an `order` field.
- **UI:** 
  - **Index:** Exercise cards show thumbnails.
  - **Show:** Displays video (embedded YouTube or HTML5 video), thumbnail, and a structured "Quy trình thực hiện" (Steps) section.
  - **Create/Edit:** Multi-step form with file uploads for thumbnail, video, and step images. Dynamic step management (add/remove/reorder).
- **Verification:** `php artisan test tests/Feature/ExerciseControllerTest.php` passed 14 tests / 143 assertions.
- **Files changed:**
  | File | Change |
  |------|--------|
  | `database/migrations/2026_05_22_065448_add_media_to_exercises_table.php` | Added `thumbnail_path`, `video_path`, `video_url` |
  | `database/migrations/2026_05_22_065457_create_exercise_steps_table.php` | New table for exercise steps |
  | `app/Models/Exercise.php` | Added media fields to fillable, defined `steps()` relationship |
  | `app/Models/ExerciseStep.php` | New model for steps |
  | `app/Services/ExerciseService.php` | Updated to handle file uploads and step synchronization |
  | `app/Http/Controllers/ExerciseController.php` | Updated `store`/`update` to use new service logic |
  | `app/Http/Requests/Exercise/StoreExerciseRequest.php` | Added validation for media and steps |
  | `app/Http/Requests/Exercise/UpdateExerciseRequest.php` | Added validation for media and steps |
  | `resources/js/Components/exercises/ExerciseForm.vue` | Added media upload fields and dynamic steps management |
  | `resources/js/Components/exercises/ExerciseCard.vue` | Show thumbnail in library |
  | `resources/js/Pages/Exercises/Show.vue` | New layout for media and steps display |
  | `tests/Feature/ExerciseControllerTest.php` | Added tests for media and steps persistence |

## Unknown Child Training Sessions Support
- **Date:** 2026-05-22
- **Status:** Complete
- **Feature:** Handle training sessions with missing or non-existent child information (orphaned sessions).
- **Business rule:** Sessions with `child_id = null` or a `child_id` that doesn't exist in the `children` table are grouped under "Trẻ không xác định" (Unknown Child) in the timeline. Users can delete these orphaned groups in bulk.
- **UI:** Timeline shows a special card for "Trẻ không xác định" with a red "Xóa nhóm này" button. Individual sessions within this group are displayed normally but without child-specific links.
- **Verification:** `php artisan test tests/Feature/TrainingControllerTest.php` passed 25 tests / 255 assertions.
- **Files changed:**
  | File | Change |
  |------|--------|
  | `database/migrations/2026_05_22_064511_make_child_id_nullable_in_training_sessions_table.php` | New migration: made `child_id` nullable |
  | `app/Services/TrainingService.php` | Updated `groupSessionsByChild()` to handle `null`/orphaned `child_id`; added `deleteUnknownGroup()` |
  | `app/Http/Controllers/TrainingController.php` | Added `deleteUnknownGroup()` endpoint |
  | `routes/web.php` | Added `DELETE /training/unknown-groups/{id}` route |
  | `resources/js/Components/training/TrainingTimeline.vue` | UI support for "Trẻ không xác định" group and bulk deletion |
  | `tests/Feature/TrainingControllerTest.php` | Added tests for unknown group listing and deletion |

## Scheduled Time Support for Training Sessions
- **Date:** 2026-05-22
- **Status:** Complete
- **Feature:** Added exact scheduled time (`scheduled_time`) on training sessions. Timeline now shows `20:00` prominently. Child headers show next upcoming session. Create/edit forms include Giờ bắt đầu and Thời lượng fields.
- **Business rule:** Training sessions now support `session_date` + `scheduled_time` (time) + `duration_minutes` (integer). Ordering: `session_date DESC`, `scheduled_time DESC`, `created_at DESC`. Timeline auto-computes next upcoming session per child. `scheduled_time` format: HH:MM.
- **UI:** Timeline items show bold indigo time (e.g. `20:00`), short date label (Hôm nay/Hôm qua/date), status, and duration. Child headers show "Buổi tiếp theo: 20:00 hôm nay". Create/edit form has Ngày tập, Giờ bắt đầu (time input), Thời lượng (number input). Dashboard widgets and Child/Training detail pages now display the scheduled time.
- **Verification:** `php artisan test` passed 168 tests / 1300+ assertions; `npm run build` passed.
- **Files changed:**
  | File | Change |
  |------|--------|
  | `database/migrations/2026_05_22_060621_add_scheduled_time_to_training_sessions_table.php` | New migration: added `scheduled_time` (time, nullable) |
  | `app/Models/TrainingSession.php` | Added `scheduled_time` to fillable/casts |
  | `app/Services/TrainingService.php` | `createSession()`/`updateSession()` support `scheduled_time`/`duration_minutes`; `groupSessionsByChild()` computes `next_session`; ordering uses `session_date DESC, scheduled_time DESC, created_at DESC` |
  | `app/Services/DashboardService.php` | Updated `getTodayTraining()` and `getRecentSessions()` to include `scheduled_time` and sort by it |
  | `app/Services/ChildService.php` | Updated `getChildTrainingHistory()` to sort by `scheduled_time` |
  | `app/Http/Requests/Training/StoreSessionRequest.php` | Added `scheduled_time` and `duration_minutes` validation |
  | `app/Http/Requests/Training/UpdateSessionRequest.php` | Added `scheduled_time` and `duration_minutes` validation |
  | `database/factories/TrainingSessionFactory.php` | Added `scheduled_time` to factory definition |
  | `resources/js/Components/training/TrainingSessionForm.vue` | Added Giờ bắt đầu (time input) + Thời lượng (number input) fields; form sends `scheduled_time`/`duration_minutes` |
  | `resources/js/Components/training/TrainingTimeline.vue` | Timeline shows bold time, short date, "Buổi tiếp theo" in child header, `formatTime()`/`formatDateShort()` helpers |
  | `resources/js/Components/dashboard/TodayTrainingCard.vue` | Display `scheduled_time` for today's sessions |
  | `resources/js/Components/dashboard/RecentSessionsList.vue` | Display `scheduled_time` for recent sessions |
  | `resources/js/Components/training/TodaySessionCard.vue` | Display `scheduled_time` in today's checklist |
  | `resources/js/Components/training/TrainingSessionCard.vue` | Display `scheduled_time` in general session cards |
  | `resources/js/Pages/Training/Show.vue` | Display `scheduled_time` in session detail header |
  | `resources/js/Pages/Children/Show.vue` | Display `scheduled_time` in child's training history tab |
  | `tests/Feature/TrainingControllerTest.php` | Updated tests to verify `scheduled_time` persistence |

## Voided/Stopped Child Delete Feature
- **Date:** 2026-05-22
- **Status:** Complete
- **Feature:** Children with `voided` or `stopped` status can be soft-deleted through `DELETE /children/{child}`. A "Xóa hồ sơ" button appears on their card.
- **Business rule:** Only `voided` or `stopped` children can be deleted. `active` and `paused` children are rejected with error message. Uses `SoftDeletes` — historical records (training sessions, assessments, etc.) are preserved.
- **UI:** "Xóa hồ sơ" red button on ChildCard for voided/stopped children with confirmation dialog: "Bạn có chắc muốn xóa hồ sơ trẻ này? Hồ sơ sẽ được ẩn khỏi danh sách, nhưng dữ liệu lịch sử vẫn được giữ lại."
- **Verification:** `php artisan test` passed 155 tests / 1162 assertions; `npm run build` passed.
- **Files changed:**
  | File | Change |
  |------|--------|
  | `app/Services/ChildService.php` | `deleteChild()` now validates status (voided/stopped only), performs soft delete |
  | `app/Http/Controllers/ChildController.php` | `destroy()` returns error for non-deletable statuses, success message for deletes |
  | `resources/js/Components/children/ChildCard.vue` | Added "Xóa hồ sơ" button for voided/stopped children with confirmation |
  | `tests/Feature/ChildControllerTest.php` | Added 5 new tests: active cannot delete, paused cannot delete, voided can delete, stopped can delete, deleted child disappears, soft delete keeps records |

## Child Resume Intervention Update
- **Date:** 2026-05-22
- **Status:** Complete
- **Feature:** Children with `paused` or legacy `stopped` status can resume intervention through `POST /children/{child}/resume`.
- **Business rule:** Resume changes the child back to `active`, clears pause/status notes, records `resumed_at` only if that column exists, and rejects `active`, `voided`, and soft-deleted records.
- **UI:** `/children` and child detail show the Vietnamese action `Tiếp tục can thiệp` for paused/stopped children with confirmation.
- **Workflow visibility:** Resumed children appear again in dashboard active child logic and active intervention dropdowns; voided/deleted children remain excluded.
- **Verification:** `php artisan test` passed 145 tests / 1031 assertions; `npm run build` passed; `npm run check:ui-lang` passed; Playwright QA passed and saved `storage/test-screenshots/children-resume.png`.

## Current Status
- **Date:** 2026-05-22
- **All tests passing:** 160/160 (1246 assertions)
- **Vite dev server:** Running on port 5174 (127.0.0.1)
- **Laravel dev server:** Running on port 8000

## Root Cause of Blank Login Page

The `/login` page was rendering blank due to **two issues**:

### 1. Overly inclusive `@vite` directive in `app.blade.php`
The original line was:
```
@vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
```
This passed Inertia's resolved page component (e.g., `Auth/Login.vue`) as a **separate Vite entry point** in the blade template. Vite could not resolve `resources/js/Pages/Auth/Login.vue` from the blade directive context properly when the page-specific path contained variables (the `$page['component']` interpolation). This caused the JavaScript bundle to fail silently.

**Fix:** Changed to standard Inertia setup:
```
@vite(['resources/js/app.js'])
```
The `app.js` file already uses `createInertiaApp` with `import.meta.glob` to dynamically resolve all pages.

### 2. IPv6 host address in Vite dev server
The `public/hot` file was pointing to `http://[::1]:5173`. The Vite dev server was listening on IPv6, which caused browser connectivity issues on Windows.

**Fix:** Added `server.host: '127.0.0.1'` in `vite.config.js` to force IPv4. The `hot` file now correctly points to `http://127.0.0.1:5174`.

## Files Changed
| File | Change |
|------|--------|
| `resources/views/app.blade.php` | Simplified `@vite` to only `resources/js/app.js` |
| `vite.config.js` | Added `server: { host: '127.0.0.1' }` |

## Running Commands
```bash
# Start Laravel dev server
php artisan serve --port=8089

# Start Vite dev server (in separate terminal)
npm run dev

# Run all tests
php artisan test

# Build for production
npm run build
```

## Verification Checklist
- [x] All 121 backend tests pass
- [x] `npm run build` succeeds without errors
- [x] `public/hot` file exists and points to `127.0.0.1:5174`
- [x] Vite dev server is running

## Next Steps (Recommended)
1. Open `http://localhost:8000/login` in the browser to verify the login page renders
2. Check browser console for any JS errors
3. If still blank, use browser DevTools > Network tab to inspect failed asset loads

## Exercise Thumbnail Propagation and Placeholder Generation
- **Date:** 2026-05-22
- **Status:** Complete
- **Feature:** Exercise thumbnails now appear across exercise cards, training timelines/items/checklists, dashboard training widgets, and child detail training history.
- **Root cause:** Exercise media fields existed on `exercises`, but related workflows only rendered exercise titles and did not eager-load or pass thumbnail data consistently.
- **Fallback:** Missing thumbnails use local `/images/exercise-placeholder.svg`. The UI does not hotlink external images.
- **Command:** Added `php artisan exercises:generate-placeholders`. It scans exercises missing `thumbnail_path`, downloads/caches category-matched child-friendly placeholders under `storage/app/public/exercises/placeholders`, and falls back to local generated SVGs when remote download fails. Existing uploaded thumbnails are never overwritten.
- **Verification:** `php artisan test` passed 167 tests / 1309 assertions; `npm run build` passed; `npm run check:ui-lang` passed; Playwright QA passed and saved:
  - `storage/test-screenshots/exercises-thumbnails.png`
  - `storage/test-screenshots/training-thumbnails.png`
  - `storage/test-screenshots/mobile-thumbnails.png`
- **Files changed:**
  | File | Change |
  |------|--------|
  | `resources/js/Components/exercises/ExerciseThumbnail.vue` | New reusable thumbnail component with storage URL + local fallback |
  | `public/images/exercise-placeholder.svg` | Local fallback image |
  | `resources/js/Components/exercises/ExerciseCard.vue` | Uses shared thumbnail component |
  | `resources/js/Components/training/TrainingTimeline.vue` | Shows thumbnails beside exercises in session timelines |
  | `resources/js/Components/training/TrainingItemList.vue` | Shows thumbnails in item tables |
  | `resources/js/Components/training/TodayExerciseChecklist.vue` | Shows thumbnails in today's checklist and uses Vietnamese `phút` |
  | `resources/js/Components/training/TrainingExercisePicker.vue` | Shows selected exercise preview with thumbnail |
  | `resources/js/Components/dashboard/TodayTrainingCard.vue` | Shows thumbnail stack for today's sessions |
  | `resources/js/Components/dashboard/RecentSessionsList.vue` | Shows thumbnail stack for recent sessions |
  | `resources/js/Pages/Children/Show.vue` | Shows thumbnails in child training history |
  | `app/Services/DashboardService.php` | Eager-loads training items/exercises and exposes `exercise_thumbnails` |
  | `app/Services/ChildService.php` | Eager-loads exercise data for child training sessions |
  | `routes/console.php` | Adds `exercises:generate-placeholders` command and helper functions |
  | `tests/Feature/ExerciseControllerTest.php` | Adds thumbnail list and placeholder-generation tests |
  | `tests/Feature/TrainingControllerTest.php` | Adds training thumbnail data test |
  | `tests/Feature/DashboardControllerTest.php` | Adds dashboard thumbnail data test |
  | `scripts/check-english-ui.mjs` | Avoids false positives for dynamic bindings and technical YouTube URL parsing |

## Assessment Blank Page Null Query Fix
- **Date:** 2026-05-22
- **Status:** Complete
- **Root cause:** Assessment pagination rendered Inertia `<Link>` components with `href=null` for disabled previous/next/page links. Assessment filters also built query objects with empty/undefined values. Inertia attempted to merge those values into a URL and hit `null.toString()`, blanking `/assessment`.
- **Fix:** Assessment filters now remove `null`, `undefined`, and empty string values before `router.get()`. Reset sends an empty query object. Backend index/progress actions strip empty filters before passing props/query strings. Disabled pagination controls are rendered as non-link `<span>` elements, and desktop pagination filters out links without URLs.
- **UI:** Empty assessment state now says `Chưa có dữ liệu đánh giá`. Pagination labels are rendered in Vietnamese and do not expose Laravel's raw previous/next labels.
- **Verification:** `php artisan test` passed 170 tests / 1350 assertions; `npm run build` passed; `npm run check:ui-lang` passed; Playwright QA passed with no console errors and saved `storage/test-screenshots/assessment-fixed.png`.
- **Files changed:**
  | File | Change |
  |------|--------|
  | `app/Http/Controllers/AssessmentController.php` | Added filter sanitization for index/progress |
  | `resources/js/Components/assessment/AssessmentFilters.vue` | Added `cleanQuery()` and safe reset behavior |
  | `resources/js/Components/assessment/SkillFilter.vue` | Added `cleanQuery()` for progress filters |
  | `resources/js/Components/assessment/AssessmentList.vue` | Removed null-href pagination links and updated Vietnamese empty state/pagination labels |
  | `tests/Feature/AssessmentControllerTest.php` | Added empty-filter, invalid-filter, and progress empty-filter coverage |

## Dashboard Daily Training Lifecycle
- **Date:** 2026-05-23
- **Status:** Complete
- **Feature:** Dashboard now shows only today's training sessions in a grouped timeline titled `Lịch tập hôm nay`.
- **Business rule:** Newly created training sessions default to `pending` / `Chưa thực hiện`. Past sessions with `pending`, legacy `planned`, or `in_progress` are auto-closed to `missed` / `Chưa hoàn thành` without deleting history.
- **Auto-close:** Added `php artisan training:close-missed`, scheduled daily at `00:05`. Dashboard loading also runs the same idempotent closure to recover when the scheduler was offline.
- **History:** Missed sessions remain visible in `/training` and can be updated later. Late updates append the audit note `Cập nhật sau ngày tập`.
- **Telegram:** `/today` and checklist reminders use only today's checklist items. End-of-day Telegram report includes `Bạn còn X buổi chưa hoàn thành hôm nay`.
- **Verification:** `php artisan migrate --force` passed; `php artisan test` passed 195 tests / 1480 assertions; `npm run build` passed; `npm run check:ui-lang` passed; Playwright QA passed and saved:
  - `storage/test-screenshots/dashboard-today-training-timeline.png`
  - `storage/test-screenshots/training-missed-history.png`
- **Files changed:**
  | File | Change |
  |------|--------|
  | `database/migrations/2026_05_23_000000_add_missed_closure_fields_to_training_sessions.php` | Added `closed_at`, `auto_closed_reason`, and status/date index |
  | `app/Models/TrainingSession.php` | Added missed closure fields and unfinished scope |
  | `app/Models/TrainingSessionItem.php` | Added normalized item status constants |
  | `app/Models/ChecklistItem.php` | Added `pending` and `missed` constants |
  | `app/Services/TrainingService.php` | Defaults new sessions/items to `pending`, closes missed sessions, supports late missed updates |
  | `app/Services/DashboardService.php` | Closes stale sessions on load and returns grouped today's timeline data |
  | `app/Services/DailyChecklistService.php` | Syncs checklist/training states with `pending`, `refused`, and `missed` |
  | `app/Services/TelegramService.php` | Adds unfinished count to end-of-day report |
  | `routes/console.php` | Adds and schedules `training:close-missed`; keeps Telegram reminders scoped to today |
  | `resources/js/Components/dashboard/TodayTrainingCard.vue` | Rebuilt as grouped timeline with thumbnails, exact time, status, and quick actions |
  | `resources/js/Lib/labels.js` | Added Vietnamese labels for `pending`, `refused`, and `missed` |
  | `resources/js/Components/training/TrainingStatusBadge.vue` | Added styles/labels for the new lifecycle statuses |
  | `resources/js/Components/training/TrainingSessionForm.vue` | Defaults new sessions to `pending` |
  | `resources/js/Components/training/TrainingExercisePicker.vue` | Defaults new items to `pending` |
  | `resources/js/Components/training/TrainingItemList.vue` | Allows editing `pending`, `refused`, and `missed` item results |
  | `resources/js/Pages/Checklist/Today.vue` | Shows checklist pending/missed states with Vietnamese labels |
  | `resources/js/Pages/Training/Show.vue` | Updates item status through the dedicated item endpoint |
  | `tests/Feature/DashboardControllerTest.php` | Added today-only and scheduled-time ordering coverage |
  | `tests/Feature/TrainingControllerTest.php` | Added default pending, close-missed, and late-update tests |
  | `tests/Feature/DailyChecklistControllerTest.php` | Updated checklist timeline expectations to `pending` |
  | `tests/Feature/TelegramIntegrationTest.php` | Added `/today` reminder scoping coverage |

## Training Status Persistence and Detail UI Fix
- **Date:** 2026-05-23
- **Status:** Complete
- **Root cause:** The training detail page updated overall session status by submitting the full edit form through `PUT /training/{id}` instead of the dedicated status endpoint. Dashboard/list quick actions also mixed item-level statuses with session-level statuses, so a visible change could be local or item-only while `training_sessions.status` stayed unchanged after reload. Some detail/list strings still contained mojibake text from earlier encoded Vietnamese literals.
- **Fix:** Session status updates now use `PATCH /training/{trainingSession}/status`; item updates use `PATCH /training/items/{trainingSessionItem}/status`. Session dropdowns only expose `planned`, `in_progress`, `completed`, `skipped`; item dropdowns only expose `not_started`, `completed`, `partially_completed`, `skipped`.
- **UI:** Rebuilt the training detail page with normal Tailwind font classes and clean Vietnamese text. Training status badges now read from `labels.js`, and the training list/dashboard "Hoàn thành" action persists session status through the backend.
- **Verification:** `php artisan test` passed 200 tests / 1507 assertions; `npm run build` passed; `npm run check:ui-lang` passed; Playwright smoke QA passed for `/training` completion persistence and `/training/{id}` detail rendering.

## Parent Daily Checklist Homepage
- **Date:** 2026-05-22
- **Status:** Complete
- **Feature:** Added `/today` as the parent-friendly logged-in homepage. Login, registration, email verification, and `/` now land on `/today`; admin analytics remains available at `/dashboard`.
- **Architecture:** Existing training sessions/items remain the source of scheduled exercises. New checklist tables store parent workflow state: status, performance result, parent note, mood, progress logs, reminders, carry-over, and streaks.
- **UX:** Mobile-first checklist with progress bar, one-tap status buttons, super quick `Đã tập xong`, quick notes, mood tracker, progress log, context mode selector, upcoming reminders, video preview link, AI-style repeated-refusal suggestions, and end-of-day timeline.
- **Business rules:** Checklist items sync back to `training_session_items.completion_status`. Carry-over creates one tomorrow training task and marks the source item as carried over to prevent duplicate spam. Streak updates only when all checklist items for a child/day are completed.
- **Verification:** `php artisan test` passed 177 tests / 1405 assertions; `npm run build` passed; `npm run check:ui-lang` passed; Playwright QA passed and saved:
  - `storage/test-screenshots/checklist-home.png`
  - `storage/test-screenshots/checklist-mobile.png`
  - `storage/test-screenshots/checklist-timeline.png`
- **Files changed:**
  | File | Change |
  |------|--------|
  | `database/migrations/2026_05_22_100000_create_daily_checklist_tables.php` | New checklist/mood/progress/reminder/streak tables |
  | `app/Models/DailyChecklist.php` | New checklist model |
  | `app/Models/ChecklistItem.php` | New checklist item model |
  | `app/Models/ChecklistProgress.php` | New checklist progress model |
  | `app/Models/ParentNote.php` | New parent note model |
  | `app/Models/DailyMood.php` | New daily mood model |
  | `app/Models/ProgressLog.php` | New quick progress log model |
  | `app/Models/Reminder.php` | New reminder model |
  | `app/Models/StreakTracking.php` | New streak model |
  | `app/Models/Child.php` | Added checklist-related relationships |
  | `app/Models/TrainingSessionItem.php` | Added checklist item relationship |
  | `app/Services/DailyChecklistService.php` | Checklist generation, status sync, notes, mood, progress, reminders, carry-over, streaks, suggestions |
  | `app/Http/Controllers/DailyChecklistController.php` | `/today` page and quick action endpoints |
  | `routes/web.php` | Added `/today`, `/checklist/today`, checklist quick action routes, root redirect |
  | `app/Http/Controllers/Auth/*` | Login/register/verify redirects now target `/today` |
  | `resources/js/Pages/Checklist/Today.vue` | New parent checklist homepage |
  | `tests/Feature/DailyChecklistControllerTest.php` | Generation, completion, streak, carry-over, mood, reminder, timeline tests |
  | `tests/Feature/Auth/*` and `tests/Feature/ExampleTest.php` | Updated redirect expectations |

## Telegram Checklist Reminder Integration
- **Date:** 2026-05-22
- **Status:** Complete
- **Feature:** Added Telegram linking and queued checklist reminder delivery for parent daily workflows.
- **Configuration:** `config/services.php` now reads `TELEGRAM_BOT_TOKEN`, `TELEGRAM_WEBHOOK_SECRET`, and `TELEGRAM_BOT_USERNAME` (default `YOUR_BOT` for local link generation).
- **Parent linking:** `/settings` shows `Kết nối Telegram`. Posting to `/settings/telegram/link` creates a per-user `telegram_link_token` and exposes a deep link like `https://t.me/YOUR_BOT?start=parent_{token}`. Telegram `/start parent_{token}` links the incoming `chat_id` to the user and enables notifications.
- **Webhook:** `POST /webhooks/telegram` is excluded from CSRF, verifies `X-Telegram-Bot-Api-Secret-Token` or `?secret=...`, handles `/start`, and handles inline callback buttons for completed/refused checklist items.
- **Queue rule:** Controllers do not send Telegram HTTP requests directly. Outbound messages are queued through `SendTelegramMessageJob`, which calls `TelegramService`.
- **Scheduler:** `routes/console.php` registers `php artisan telegram:send-reminders` and schedules it every minute. The command queues morning checklist summaries, pre-session reminders, and end-of-day summaries for Telegram-linked users.
- **Inline buttons:** Reminder messages include `Đã tập xong`, `Bé từ chối`, `Ghi chú`, and `Mở checklist` actions.
    - Morning reminders now send individual messages for each checklist item with inline buttons (Done, Refuse, Note).
    - Supports fallback commands `/done {id}` and `/refuse {id}`.
    - Uses `answerCallbackQuery` for better user feedback.
- **Secure Auto-login:** "Mở checklist" buttons in Telegram now use Laravel temporary signed URLs (12-hour expiry). This allows parents to access their checklist directly without manual login while maintaining security. Access is logged for audit purposes.
- **Verification:** `php artisan migrate --force` passed; `php artisan test` passed 185 tests / 1437 assertions; `npm run build` passed; `npm run check:ui-lang` passed; Playwright QA passed and saved `storage/test-screenshots/telegram-link.png`.
- **Files changed:**
  | File | Change |
  |------|--------|
  | `database/migrations/2026_05_22_110000_add_telegram_fields_to_users_table.php` | Added Telegram chat/link/enabled fields to `users` |
  | `app/Models/User.php` | Added Telegram fillable fields and boolean cast |
  | `config/services.php` | Added Telegram service config |
  | `bootstrap/app.php` | Excluded Telegram webhook from CSRF validation |
  | `app/Services/TelegramService.php` | Link generation, parent linking, message sending, callback status updates, inline keyboards |
  | `app/Jobs/SendTelegramMessageJob.php` | Queued Telegram message delivery |
  | `app/Http/Controllers/TelegramWebhookController.php` | Webhook verification and update routing |
  | `app/Http/Controllers/SettingController.php` | Settings Telegram props and link creation endpoint |
  | `routes/web.php` | Added settings link route and Telegram webhook route |
  | `routes/console.php` | Added reminder command and per-minute scheduler |
  | `resources/js/Pages/Settings/Index.vue` | Added Vietnamese Telegram connection UI |
  | `app/Http/Controllers/TelegramLoginController.php` | Handles signed auto-login and logging |
  | `tests/Feature/TelegramAutoLoginTest.php` | Tests for signed URL authentication and expiry |
  | `tests/Feature/TelegramIntegrationTest.php` | Added linking, webhook, callback, reminder, and invalid-secret coverage |
- **Known constraint:** There is no parent-child ownership schema yet, so queued Telegram reminders are sent to all Telegram-linked users for active child checklists. Add a user-child assignment table before production multi-family use.
