<template>
  <form class="rounded-lg bg-white p-4 ring-1 ring-gray-200" @submit.prevent="submit">
    <h2 class="text-base font-semibold text-gray-900">Gửi tin nhắn nhanh</h2>

    <label class="mt-4 block text-sm font-medium text-gray-700" for="quick-chat-id">Mã hội thoại</label>
    <input
      id="quick-chat-id"
      v-model="form.chat_id"
      class="mt-1 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
      type="text"
      required
    />

    <label class="mt-4 block text-sm font-medium text-gray-700" for="quick-message">Nội dung</label>
    <textarea
      id="quick-message"
      v-model="form.message_text"
      rows="4"
      class="mt-1 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
      required
    />

    <button
      type="submit"
      class="mt-4 inline-flex w-full justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-60"
      :disabled="form.processing"
    >
      Gửi tin nhắn
    </button>
  </form>
</template>

<script setup>
import { watch } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
  chatId: {
    type: String,
    default: '',
  },
});

const form = useForm({
  chat_id: props.chatId,
  message_text: '',
});

watch(() => props.chatId, (value) => {
  if (!form.chat_id) {
    form.chat_id = value;
  }
});

const submit = () => {
  form.post('/telegram/send', {
    preserveScroll: true,
    onSuccess: () => form.reset('message_text'),
  });
};
</script>
