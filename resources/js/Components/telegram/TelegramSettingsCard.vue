<template>
  <section class="rounded-lg bg-white p-5 ring-1 ring-gray-200">
    <div class="flex items-start justify-between gap-4">
      <div>
        <h2 class="text-base font-semibold text-gray-900">Cấu hình bot</h2>
        <p class="mt-1 text-sm text-gray-600">Trạng thái kết nối và webhook Telegram.</p>
      </div>
      <span :class="statusClass">{{ statusLabel }}</span>
    </div>

    <dl class="mt-5 space-y-3 text-sm">
      <div class="flex justify-between gap-4">
        <dt class="text-gray-500">Tên bot</dt>
        <dd class="font-medium text-gray-900">{{ settings.bot_username || 'Chưa cấu hình' }}</dd>
      </div>
      <div class="flex justify-between gap-4">
        <dt class="text-gray-500">Mã token</dt>
        <dd class="font-medium text-gray-900">{{ settings.bot_token_masked || (settings.has_bot_token ? 'Đã cấu hình' : 'Chưa cấu hình') }}</dd>
      </div>
      <div class="flex justify-between gap-4">
        <dt class="text-gray-500">Trạng thái webhook</dt>
        <dd class="font-medium text-gray-900">{{ settings.webhook_secret_configured ? 'Đã bảo vệ' : 'Chưa có mã bí mật' }}</dd>
      </div>
      <div class="flex justify-between gap-4">
        <dt class="text-gray-500">Mã hội thoại mặc định</dt>
        <dd class="font-medium text-gray-900">{{ settings.default_chat_id || 'Chưa cấu hình' }}</dd>
      </div>
    </dl>

    <Link href="/telegram/settings" class="mt-5 inline-flex w-full justify-center rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800">
      Mở cấu hình
    </Link>
  </section>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
  settings: {
    type: Object,
    required: true,
  },
});

const online = computed(() => props.settings.enabled && props.settings.has_bot_token);
const statusLabel = computed(() => (online.value ? 'Đang bật' : 'Đang tắt'));
const statusClass = computed(() => [
  'inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset',
  online.value ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : 'bg-gray-50 text-gray-600 ring-gray-200',
]);
</script>
