<template>
  <Head title="Tin nhắn Telegram" />

  <AppLayout>
    <div class="space-y-6">
      <header class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Tin nhắn Telegram</h1>
          <p class="mt-1 text-sm text-gray-600">Theo dõi tin nhắn, nhắc lịch và phản hồi hai chiều từ phụ huynh.</p>
        </div>
        <Link href="/telegram/settings" class="inline-flex items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
          Cấu hình bot
        </Link>
      </header>

      <section class="rounded-lg bg-white p-4 ring-1 ring-gray-200">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
          <div>
            <h2 class="text-lg font-semibold text-gray-900">Kiểm tra lịch tự động</h2>
            <p class="mt-1 text-sm text-gray-600">Dùng khu vực này để test nhắc lịch Telegram ngay, không cần chờ đúng giờ thật.</p>
          </div>
          <div class="rounded-md bg-amber-50 px-3 py-2 text-sm text-amber-800">
            Cron production cần: <span class="font-mono">{{ schedulerDiagnostics.cron_hint }}</span>
          </div>
        </div>

        <div class="mt-4 grid grid-cols-1 gap-4 xl:grid-cols-3">
          <div class="rounded-md border border-gray-200 p-4">
            <h3 class="text-sm font-semibold text-gray-900">Trạng thái hệ thống</h3>
            <dl class="mt-3 space-y-2 text-sm">
              <div class="flex justify-between gap-3">
                <dt class="text-gray-500">APP_URL</dt>
                <dd class="truncate font-medium text-gray-900">{{ schedulerDiagnostics.app_url }}</dd>
              </div>
              <div class="flex justify-between gap-3">
                <dt class="text-gray-500">Giờ server</dt>
                <dd class="font-medium text-gray-900">{{ formatDateTime(schedulerDiagnostics.server_time) }}</dd>
              </div>
              <div class="flex justify-between gap-3">
                <dt class="text-gray-500">Timezone app</dt>
                <dd class="font-medium text-gray-900">{{ schedulerDiagnostics.app_timezone }}</dd>
              </div>
              <div class="flex justify-between gap-3">
                <dt class="text-gray-500">Giờ Việt Nam</dt>
                <dd class="font-medium text-gray-900">{{ formatDateTime(schedulerDiagnostics.vietnam_time) }}</dd>
              </div>
              <div class="flex justify-between gap-3">
                <dt class="text-gray-500">Bot Telegram</dt>
                <dd class="font-medium" :class="schedulerDiagnostics.bot_configured ? 'text-emerald-700' : 'text-red-700'">
                  {{ schedulerDiagnostics.bot_configured ? 'Đã cấu hình' : 'Chưa cấu hình' }}
                </dd>
              </div>
              <div>
                <dt class="text-gray-500">Webhook URL</dt>
                <dd class="mt-1 break-all font-medium text-gray-900">{{ schedulerDiagnostics.webhook_url || 'Chưa có webhook URL' }}</dd>
              </div>
              <div class="flex justify-between gap-3">
                <dt class="text-gray-500">Queue</dt>
                <dd class="font-medium text-gray-900">{{ schedulerDiagnostics.queue_connection }}</dd>
              </div>
            </dl>
          </div>

          <div class="rounded-md border border-gray-200 p-4">
            <h3 class="text-sm font-semibold text-gray-900">Test gửi ngay</h3>
            <select v-model="schedulerForm.child_id" class="mt-3 w-full rounded-md border-gray-300 text-sm shadow-sm">
              <option value="">Chọn bé để test</option>
              <option v-for="child in mealSuggestionTest.children" :key="child.id" :value="child.id">{{ child.full_name }}</option>
            </select>
            <div class="mt-3 grid grid-cols-1 gap-2">
              <button type="button" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-medium text-white hover:bg-indigo-700" @click="postSchedulerWithChild('telegram.test.sendDinnerNow')">
                Gửi gợi ý bữa tối ngay
              </button>
              <button type="button" class="rounded-md border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50" @click="post('telegram.test.sendTrainingReminderNow')">
                Gửi nhắc lịch tập ngay
              </button>
              <button type="button" class="rounded-md border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50" @click="postSchedulerWithChild('telegram.test.sendMealReminderNow')">
                Gửi gợi ý bữa tối ngay
              </button>
              <button type="button" class="rounded-md border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50" @click="post('telegram.test.sendSupplementReminderNow')">
                Gửi nhắc lịch bổ sung ngay
              </button>
              <button type="button" class="rounded-md border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50" @click="post('telegram.demo.createTodayData')">
                Tạo dữ liệu demo cho hôm nay
              </button>
            </div>
          </div>

          <div class="rounded-md border border-gray-200 p-4">
            <h3 class="text-sm font-semibold text-gray-900">Test lệnh và schedule engine</h3>
            <div class="mt-3 grid grid-cols-1 gap-2">
              <button type="button" class="rounded-md border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50" @click="postSchedulerWithChild('telegram.test.simulateAnCommand')">
                Giả lập /an
              </button>
              <button type="button" class="rounded-md border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50" @click="postSchedulerWithChild('telegram.test.simulateDoimonCommand')">
                Giả lập /doimon
              </button>
              <button type="button" class="rounded-md bg-slate-800 px-3 py-2 text-sm font-medium text-white hover:bg-slate-700" @click="post('telegram.test.runDinnerCommand')">
                Chạy thử telegram:send-dinner-suggestions
              </button>
              <button type="button" class="rounded-md bg-slate-800 px-3 py-2 text-sm font-medium text-white hover:bg-slate-700" @click="post('telegram.test.runReminderCommand')">
                Chạy thử telegram:send-due-reminders
              </button>
            </div>
          </div>
        </div>

        <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-2">
          <div class="rounded-md bg-gray-50 p-4">
            <h3 class="text-sm font-semibold text-gray-900">Kết quả scheduler gần đây</h3>
            <div class="mt-3 space-y-2">
              <div v-for="run in schedulerDiagnostics.recent_runs" :key="run.id" class="rounded-md bg-white p-3 text-sm ring-1 ring-gray-200">
                <div class="flex items-center justify-between gap-3">
                  <span class="font-medium text-gray-900">{{ run.command }}</span>
                  <span class="rounded-full px-2 py-0.5 text-xs font-medium" :class="run.status === 'success' ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700'">
                    {{ run.status === 'success' ? 'Thành công' : 'Thất bại' }}
                  </span>
                </div>
                <p class="mt-1 text-xs text-gray-500">{{ formatDateTime(run.ran_at) }}</p>
                <p v-if="run.summary" class="mt-1 line-clamp-2 text-xs text-gray-600">{{ run.summary }}</p>
                <p v-if="run.error" class="mt-1 text-xs text-red-600">{{ run.error }}</p>
              </div>
              <p v-if="schedulerDiagnostics.recent_runs.length === 0" class="text-sm text-gray-500">Chưa có log scheduler. Nếu cron đã chạy, bảng này sẽ có dữ liệu.</p>
            </div>
          </div>

          <div class="rounded-md bg-gray-50 p-4">
            <h3 class="text-sm font-semibold text-gray-900">Checklist nguyên nhân thường gặp</h3>
            <ul class="mt-3 space-y-2 text-sm text-gray-700">
              <li>Cron Laravel scheduler đã cấu hình chưa?</li>
              <li>Bot token có chưa?</li>
              <li>Chat ID đã liên kết chưa?</li>
              <li>Trẻ còn đang can thiệp không?</li>
              <li>Lịch hôm nay có dữ liệu không?</li>
              <li>Đã gửi rồi nên bị chặn trùng không?</li>
              <li>Server timezone có đúng với giờ mong muốn không?</li>
              <li>Queue worker có chạy không nếu không dùng queue sync?</li>
            </ul>
          </div>
        </div>
      </section>

      <section class="grid grid-cols-1 gap-4 xl:grid-cols-5">
        <div class="rounded-lg bg-white p-4 ring-1 ring-gray-200 xl:col-span-2">
          <h2 class="text-base font-semibold text-gray-900">Kiểm tra cấu hình</h2>
          <div class="mt-4 grid grid-cols-1 gap-2 sm:grid-cols-2">
            <button type="button" class="rounded-md border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50" @click="post('telegram.test.bot')">
              Kiểm tra bot
            </button>
            <button type="button" class="rounded-md border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50" @click="post('telegram.test.webhookInfo')">
              Xem trạng thái webhook
            </button>
          </div>
          <TelegramTestPanel :default-chat-id="settings.default_chat_id || selectedChatId" class="mt-4" />
        </div>

        <div class="rounded-lg bg-white p-4 ring-1 ring-gray-200 xl:col-span-3">
          <h2 class="text-base font-semibold text-gray-900">Test lịch tập</h2>
          <TelegramTwoWayTestPanel :training-test="trainingTest" :messages="messages" />
        </div>
      </section>

      <section class="grid grid-cols-1 gap-4 lg:grid-cols-3">
        <div class="rounded-lg bg-white p-4 ring-1 ring-gray-200">
          <h2 class="text-base font-semibold text-gray-900">Test lịch ăn uống</h2>
          <p class="mt-1 text-sm text-gray-500">Kiểm tra gợi ý bữa tối lúc 14:00, lệnh /an, /doimon và nút phản hồi.</p>
          <select v-model="mealSuggestionForm.child_id" class="mt-4 w-full rounded-md border-gray-300 text-sm shadow-sm">
            <option value="">Chọn bé</option>
            <option v-for="child in mealSuggestionTest.children" :key="child.id" :value="child.id">{{ child.full_name }}</option>
          </select>
          <div class="mt-3 grid grid-cols-1 gap-2">
            <button type="button" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-medium text-white hover:bg-indigo-700" @click="postMealSuggestion('telegram.test.mealSuggestion.dinner')">
              Gửi gợi ý bữa tối lúc 14:00
            </button>
            <button type="button" class="rounded-md border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50" @click="postMealSuggestion('telegram.test.mealSuggestion.an')">
              Giả lập lệnh /an
            </button>
            <button type="button" class="rounded-md border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50" @click="postMealSuggestion('telegram.test.mealSuggestion.doimon')">
              Giả lập lệnh /doimon
            </button>
          </div>
          <div class="mt-3 grid grid-cols-1 gap-2">
            <button v-for="action in mealSuggestionActions" :key="action.value" type="button" class="rounded-md border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50" @click="postMealCallback(action.value)">
              {{ action.label }}
            </button>
          </div>
          <div class="mt-4 rounded-md bg-gray-50 p-3 text-sm text-gray-600">
            <p class="font-medium text-gray-900">Tin nhắn xem trước</p>
            <p class="mt-1 line-clamp-6 whitespace-pre-wrap">{{ mealSuggestionTest.preview.preview_message }}</p>
          </div>
        </div>

        <div class="rounded-lg bg-white p-4 ring-1 ring-gray-200">
          <h2 class="text-base font-semibold text-gray-900">Test nhắc lịch</h2>
          <p class="mt-1 text-sm text-gray-500">Gửi thử tin nhắc lịch tập/bổ sung và gợi ý chuẩn bị bữa tối.</p>
          <div class="mt-4 space-y-2">
            <button type="button" class="w-full rounded-md bg-indigo-600 px-3 py-2 text-sm font-medium text-white hover:bg-indigo-700" @click="post('telegram.test.reminder.training')">
              Test nhắc lịch tập trước 30 phút
            </button>
            <button type="button" class="w-full rounded-md bg-emerald-600 px-3 py-2 text-sm font-medium text-white hover:bg-emerald-700" @click="postMealSuggestion('telegram.test.reminder.meal')">
              Test gợi ý bữa tối lúc 14:00
            </button>
            <button type="button" class="w-full rounded-md bg-amber-600 px-3 py-2 text-sm font-medium text-white hover:bg-amber-700" @click="post('telegram.test.reminder.supplement')">
              Test nhắc lịch uống/bổ sung trước 30 phút
            </button>
          </div>

          <div class="mt-5 rounded-md bg-gray-50 p-3 text-sm text-gray-600">
            <p class="font-medium text-gray-900">Nội dung xem trước</p>
            <p class="mt-1">Tin nhắc sẽ hiển thị giờ, tên bé, nội dung lịch và nút thao tác phù hợp.</p>
          </div>
        </div>

        <div class="rounded-lg bg-white p-4 ring-1 ring-gray-200">
          <h2 class="text-base font-semibold text-gray-900">Test phản hồi 2 chiều</h2>
          <div class="mt-4 space-y-3 text-sm">
            <div>
              <p class="font-medium text-gray-700">Callback data gần nhất</p>
              <p class="mt-1 break-all rounded bg-gray-50 p-2 text-gray-600">{{ latestCallback?.callback_data || 'Chưa có callback' }}</p>
            </div>
            <div>
              <p class="font-medium text-gray-700">Tin nhắn inbound</p>
              <p class="mt-1 rounded bg-gray-50 p-2 text-gray-600">{{ latestCallback?.message_text || 'Chưa có phản hồi' }}</p>
            </div>
            <div>
              <p class="font-medium text-gray-700">Trạng thái sau</p>
              <p class="mt-1 rounded bg-gray-50 p-2 text-gray-600">{{ latestCallback?.action_status || 'Chưa có dữ liệu' }}</p>
            </div>
          </div>
        </div>

        <div class="rounded-lg bg-white p-4 ring-1 ring-gray-200">
          <h2 class="text-base font-semibold text-gray-900">Nhật ký nhắc lịch gần đây</h2>
          <div class="mt-4 space-y-2">
            <div v-for="log in reminderTest.logs" :key="log.id" class="rounded-md border border-gray-200 p-3 text-sm">
              <div class="flex items-start justify-between gap-3">
                <p class="font-medium text-gray-900">{{ reminderTypeLabel(log.reminder_type) }}</p>
                <span class="rounded-full px-2 py-0.5 text-xs font-medium" :class="statusClass(log.status)">
                  {{ statusLabel(log.status) }}
                </span>
              </div>
              <p class="mt-1 text-xs text-gray-500">{{ log.child_name || 'Chưa gắn bé' }}</p>
              <p v-if="log.error_message" class="mt-1 text-xs text-red-600">{{ log.error_message }}</p>
            </div>
            <p v-if="reminderTest.logs.length === 0" class="text-sm text-gray-500">Chưa có nhật ký nhắc lịch.</p>
          </div>
        </div>
      </section>

      <div class="grid gap-6 lg:grid-cols-[16rem_minmax(0,1fr)_18rem]">
        <TelegramContactSidebar :contacts="contacts" :selected-chat-id="selectedChatId" />
        <TelegramMessageTimeline :messages="messages" :selected-chat-id="selectedChatId" />

        <div class="space-y-4">
          <TelegramSystemStatusCard :status="systemStatus" />
          <TelegramSettingsCard :settings="settings" />
          <section class="rounded-lg bg-white p-4 ring-1 ring-gray-200">
            <h2 class="text-base font-semibold text-gray-900">Nhật ký gần đây</h2>
            <dl class="mt-4 space-y-3 text-sm">
              <div class="flex justify-between gap-4">
                <dt class="text-gray-500">Tin nhắn hôm nay</dt>
                <dd class="font-semibold text-gray-900">{{ stats.messages_today }}</dd>
              </div>
              <div>
                <dt class="text-gray-500">Tin nhắn đến gần nhất</dt>
                <dd class="mt-1 line-clamp-3 font-medium text-gray-900">{{ stats.last_inbound_text || 'Chưa có tin nhắn' }}</dd>
              </div>
            </dl>
          </section>
          <TelegramQuickSend :chat-id="selectedChatId || settings.default_chat_id" />
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Components/layout/AppLayout.vue';
import TelegramContactSidebar from '@/Components/telegram/TelegramContactSidebar.vue';
import TelegramMessageTimeline from '@/Components/telegram/TelegramMessageTimeline.vue';
import TelegramQuickSend from '@/Components/telegram/TelegramQuickSend.vue';
import TelegramSettingsCard from '@/Components/telegram/TelegramSettingsCard.vue';
import TelegramSystemStatusCard from '@/Components/telegram/TelegramSystemStatusCard.vue';
import TelegramTestPanel from '@/Components/telegram/TelegramTestPanel.vue';
import TelegramTwoWayTestPanel from '@/Components/telegram/TelegramTwoWayTestPanel.vue';

const props = defineProps({
  contacts: { type: Array, default: () => [] },
  messages: { type: Array, default: () => [] },
  selectedChatId: { type: String, default: '' },
  settings: { type: Object, required: true },
  stats: { type: Object, required: true },
  trainingTest: { type: Object, required: true },
  systemStatus: { type: Object, required: true },
  schedulerDiagnostics: { type: Object, required: true },
  reminderTest: { type: Object, required: true },
  mealSuggestionTest: { type: Object, required: true },
});

const latestCallback = computed(() => props.messages.find(message => message.callback_data));
const mealSuggestionForm = useForm({
  child_id: props.mealSuggestionTest.children?.[0]?.id || '',
  action: 'change',
});
const schedulerForm = useForm({
  child_id: props.mealSuggestionTest.children?.[0]?.id || '',
});
const mealSuggestionActions = [
  { value: 'change', label: 'Giả lập bấm Đổi món khác' },
  { value: 'view', label: 'Giả lập bấm Xem lịch hôm nay' },
  { value: 'prepared', label: 'Giả lập bấm Đã chuẩn bị' },
];

function post(routeName) {
  router.post(route(routeName), {}, { preserveScroll: true });
}

function postSchedulerWithChild(routeName) {
  schedulerForm.post(route(routeName), { preserveScroll: true });
}

function postMealSuggestion(routeName) {
  mealSuggestionForm.post(route(routeName), { preserveScroll: true });
}

function postMealCallback(action) {
  mealSuggestionForm.action = action;
  mealSuggestionForm.post(route('telegram.test.mealSuggestion.callback'), { preserveScroll: true });
}

function reminderTypeLabel(type) {
  return {
    training: 'Nhắc lịch tập',
    meal: 'Gợi ý bữa tối',
    supplement: 'Nhắc lịch bổ sung',
  }[type] || 'Nhắc lịch';
}

function statusLabel(status) {
  return {
    pending: 'Đang chờ',
    sent: 'Đã gửi',
    failed: 'Thất bại',
    skipped: 'Bỏ qua',
    prepared: 'Đã chuẩn bị',
  }[status] || status;
}

function statusClass(status) {
  return {
    sent: 'bg-emerald-50 text-emerald-700',
    failed: 'bg-red-50 text-red-700',
    skipped: 'bg-gray-100 text-gray-700',
    pending: 'bg-amber-50 text-amber-700',
  }[status] || 'bg-gray-100 text-gray-700';
}

function formatDateTime(value) {
  if (!value) return 'Chưa có dữ liệu';
  return new Intl.DateTimeFormat('vi-VN', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  }).format(new Date(value));
}
</script>
