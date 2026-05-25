<template>
  <aside class="rounded-lg bg-white ring-1 ring-gray-200">
    <div class="border-b border-gray-200 px-4 py-4">
      <h2 class="text-base font-semibold text-gray-900">Hội thoại</h2>
      <p class="mt-1 text-sm text-gray-600">Danh sách liên hệ Telegram</p>
    </div>

    <div class="max-h-[38rem] overflow-y-auto p-2">
      <Link
        v-for="contact in contacts"
        :key="contact.telegram_chat_id"
        :href="`/telegram?chat_id=${encodeURIComponent(contact.telegram_chat_id)}`"
        :class="contactClass(contact.telegram_chat_id)"
      >
        <span class="block truncate text-sm font-semibold text-gray-900">
          {{ contact.display_name || contact.telegram_username || `Hội thoại ${contact.telegram_chat_id}` }}
        </span>
        <span class="mt-1 block truncate text-xs text-gray-500">
          {{ contact.telegram_username ? `@${contact.telegram_username}` : contact.telegram_chat_id }}
        </span>
        <span class="mt-2 block text-xs text-gray-500">
          {{ contact.messages_count }} tin nhắn
        </span>
      </Link>

      <div v-if="contacts.length === 0" class="px-3 py-10 text-center text-sm text-gray-500">
        Chưa có hội thoại
      </div>
    </div>
  </aside>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';

const props = defineProps({
  contacts: {
    type: Array,
    default: () => [],
  },
  selectedChatId: {
    type: String,
    default: '',
  },
});

const contactClass = (chatId) => [
  'block rounded-md px-3 py-3 transition',
  props.selectedChatId === chatId
    ? 'bg-indigo-50 ring-1 ring-indigo-200'
    : 'hover:bg-gray-50',
];
</script>
