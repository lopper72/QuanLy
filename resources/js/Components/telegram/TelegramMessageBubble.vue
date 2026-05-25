<template>
  <div :class="wrapperClass">
    <div :class="bubbleClass">
      <div class="mb-1 flex items-center justify-between gap-3 text-xs">
        <span class="font-medium">{{ directionLabel }}</span>
        <TelegramStatusBadge :status="message.delivery_status" />
      </div>
      <p class="whitespace-pre-wrap text-sm leading-6">{{ message.message_text || 'Không có nội dung' }}</p>
      <div v-if="message.related_child_id || message.related_training_id || message.callback_data" class="mt-2 space-y-1 text-xs opacity-85">
        <div v-if="message.related_child_id">Trẻ: #{{ message.related_child_id }}</div>
        <div v-if="message.related_training_id">Buổi tập: #{{ message.related_training_id }}</div>
        <div v-if="message.callback_data" class="break-all">Dữ liệu phản hồi: {{ message.callback_data }}</div>
      </div>
      <div class="mt-2 flex items-center justify-between gap-3 text-xs opacity-80">
        <span>{{ senderLabel }}</span>
        <time>{{ timeLabel }}</time>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import TelegramStatusBadge from './TelegramStatusBadge.vue';

const props = defineProps({
  message: {
    type: Object,
    required: true,
  },
});

const isOutbound = computed(() => props.message.direction === 'outbound');
const directionLabel = computed(() => (isOutbound.value ? 'Tin nhắn gửi đi' : 'Tin nhắn đến'));
const senderLabel = computed(() => {
  if (props.message.telegram_username) {
    return `@${props.message.telegram_username}`;
  }

  return props.message.telegram_chat_id ? `Hội thoại ${props.message.telegram_chat_id}` : 'Hệ thống';
});

const timeLabel = computed(() => {
  const value = props.message.sent_at || props.message.received_at || props.message.created_at;

  if (!value) {
    return '';
  }

  return new Intl.DateTimeFormat('vi-VN', {
    hour: '2-digit',
    minute: '2-digit',
    day: '2-digit',
    month: '2-digit',
  }).format(new Date(value));
});

const wrapperClass = computed(() => [
  'flex',
  isOutbound.value ? 'justify-end' : 'justify-start',
]);

const bubbleClass = computed(() => [
  'max-w-[78%] rounded-lg px-4 py-3 shadow-sm ring-1',
  isOutbound.value
    ? 'bg-indigo-600 text-white ring-indigo-600'
    : 'bg-white text-gray-900 ring-gray-200',
]);
</script>
