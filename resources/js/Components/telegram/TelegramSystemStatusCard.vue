<template>
  <section class="rounded-lg bg-white p-4 ring-1 ring-gray-200">
    <div class="flex items-start justify-between gap-3">
      <div>
        <h2 class="text-base font-semibold text-gray-900">Trạng thái hệ thống Telegram</h2>
        <p class="mt-1 text-sm text-gray-600">Theo dõi webhook, bot và tin nhắn gần nhất.</p>
      </div>
      <span :class="badgeClass">{{ registeredLabel }}</span>
    </div>

    <dl class="mt-4 space-y-3 text-sm">
      <div>
        <dt class="text-gray-500">Đường dẫn webhook</dt>
        <dd class="mt-1 break-all font-medium text-gray-900">{{ status.webhook_url || 'Chưa cấu hình' }}</dd>
      </div>
      <div class="flex justify-between gap-4">
        <dt class="text-gray-500">Cập nhật đang chờ</dt>
        <dd class="font-medium text-gray-900">{{ status.pending_updates_count ?? 'Chưa có dữ liệu' }}</dd>
      </div>
      <div>
        <dt class="text-gray-500">Lỗi webhook gần nhất</dt>
        <dd class="mt-1 font-medium text-gray-900">{{ status.last_webhook_error || 'Không có lỗi' }}</dd>
      </div>
      <div class="flex justify-between gap-4">
        <dt class="text-gray-500">Bot</dt>
        <dd class="font-medium text-gray-900">{{ status.bot_reachable ? 'Có thể kết nối' : 'Chưa xác minh' }}</dd>
      </div>
      <div class="flex justify-between gap-4">
        <dt class="text-gray-500">Tin nhắn đến gần nhất</dt>
        <dd class="font-medium text-gray-900">{{ formatDate(status.last_inbound_at) }}</dd>
      </div>
      <div class="flex justify-between gap-4">
        <dt class="text-gray-500">Tin nhắn gửi gần nhất</dt>
        <dd class="font-medium text-gray-900">{{ formatDate(status.last_outbound_at) }}</dd>
      </div>
    </dl>
  </section>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  status: {
    type: Object,
    required: true,
  },
});

const registeredLabel = computed(() => (props.status.webhook_registered ? 'Đã đăng ký' : 'Chưa đăng ký'));
const badgeClass = computed(() => [
  'inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset',
  props.status.webhook_registered
    ? 'bg-emerald-50 text-emerald-700 ring-emerald-200'
    : 'bg-amber-50 text-amber-700 ring-amber-200',
]);

function formatDate(value) {
  if (!value) {
    return 'Chưa có dữ liệu';
  }

  return new Intl.DateTimeFormat('vi-VN', {
    day: '2-digit',
    month: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
  }).format(new Date(value));
}
</script>
