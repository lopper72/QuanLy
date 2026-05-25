<template>
  <form class="rounded-lg bg-white p-4 ring-1 ring-gray-200" @submit.prevent="submit">
    <h2 class="text-base font-semibold text-gray-900">Tin nhắn thử nghiệm</h2>
    <p class="mt-1 text-sm text-gray-600">Gửi thử để kiểm tra bot và ghi log gửi đi.</p>

    <label class="mt-4 block text-sm font-medium text-gray-700" for="test-chat-id">Mã hội thoại</label>
    <input
      id="test-chat-id"
      v-model="form.chat_id"
      class="mt-1 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
      type="text"
      required
    />

    <label class="mt-4 block text-sm font-medium text-gray-700" for="test-message">Nội dung thử nghiệm</label>
    <textarea
      id="test-message"
      v-model="form.message_text"
      rows="3"
      class="mt-1 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
      required
    />

    <button
      type="submit"
      class="mt-4 inline-flex w-full justify-center rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-60"
      :disabled="form.processing"
    >
      Gửi thử
    </button>
  </form>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
  defaultChatId: {
    type: String,
    default: '',
  },
});

const form = useForm({
  chat_id: props.defaultChatId,
  message_text: 'Tin nhắn thử nghiệm từ hệ thống can thiệp.',
});

const submit = () => {
  form.post('/telegram/test-send', {
    preserveScroll: true,
    onSuccess: () => form.reset('message_text'),
  });
};
</script>
