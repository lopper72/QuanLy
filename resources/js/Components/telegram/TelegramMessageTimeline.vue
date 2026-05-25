<template>
  <section class="flex min-h-[34rem] flex-col rounded-lg bg-gray-50 ring-1 ring-gray-200">
    <div class="border-b border-gray-200 bg-white px-5 py-4">
      <h2 class="text-base font-semibold text-gray-900">Dòng thời gian hội thoại</h2>
      <p class="mt-1 text-sm text-gray-600">{{ subtitle }}</p>
    </div>

    <div class="flex-1 space-y-4 overflow-y-auto px-5 py-5">
      <TelegramMessageBubble
        v-for="message in messages"
        :key="message.id"
        :message="message"
      />

      <div v-if="messages.length === 0" class="flex h-full min-h-80 items-center justify-center text-sm text-gray-500">
        Không có tin nhắn
      </div>
    </div>
  </section>
</template>

<script setup>
import { computed } from 'vue';
import TelegramMessageBubble from './TelegramMessageBubble.vue';

const props = defineProps({
  messages: {
    type: Array,
    default: () => [],
  },
  selectedChatId: {
    type: String,
    default: '',
  },
});

const subtitle = computed(() => (
  props.selectedChatId ? `Hội thoại ${props.selectedChatId}` : 'Chưa có hội thoại'
));
</script>
