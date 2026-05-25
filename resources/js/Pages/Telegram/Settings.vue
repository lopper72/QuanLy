<template>
  <Head title="Cấu hình Telegram" />

  <AppLayout>
    <div class="mx-auto max-w-3xl space-y-6">
      <header>
        <h1 class="text-2xl font-bold text-gray-900">Cấu hình bot Telegram</h1>
        <p class="mt-1 text-sm text-gray-600">Lưu token, tên bot, chat mặc định và trạng thái tích hợp.</p>
      </header>

      <form class="rounded-lg bg-white p-6 ring-1 ring-gray-200" @submit.prevent="submit">
        <div class="grid gap-5">
          <div>
            <label class="block text-sm font-medium text-gray-700" for="bot-token">Bot token</label>
            <input
              id="bot-token"
              v-model="form.bot_token"
              type="password"
              autocomplete="new-password"
              class="mt-1 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
              :placeholder="settings.bot_token_masked || 'Nhập token mới'"
            />
            <p class="mt-1 text-xs text-gray-500">Để trống nếu không đổi token hiện tại.</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700" for="bot-username">Bot username</label>
            <input
              id="bot-username"
              v-model="form.bot_username"
              type="text"
              class="mt-1 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700" for="webhook-secret">Mã bí mật webhook</label>
            <input
              id="webhook-secret"
              v-model="form.webhook_secret"
              type="password"
              autocomplete="new-password"
              class="mt-1 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
              placeholder="Nhập mã bí mật mới hoặc để trống"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700" for="default-chat-id">Mã hội thoại mặc định</label>
            <input
              id="default-chat-id"
              v-model="form.default_chat_id"
              type="text"
              class="mt-1 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            />
          </div>

          <label class="flex items-center gap-3 rounded-md bg-gray-50 px-3 py-3 text-sm font-medium text-gray-800">
            <input
              v-model="form.enabled"
              type="checkbox"
              class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
            />
            Bật tích hợp Telegram
          </label>
        </div>

        <div class="mt-6 flex items-center justify-between gap-3">
          <Link href="/telegram" class="text-sm font-medium text-gray-600 hover:text-gray-900">Quay lại trung tâm tin nhắn</Link>
          <button
            type="submit"
            class="inline-flex rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-60"
            :disabled="form.processing"
          >
            Lưu cấu hình
          </button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Components/layout/AppLayout.vue';

const props = defineProps({
  settings: {
    type: Object,
    required: true,
  },
});

const form = useForm({
  bot_token: '',
  bot_username: props.settings.bot_username || '',
  webhook_secret: '',
  default_chat_id: props.settings.default_chat_id || '',
  enabled: Boolean(props.settings.enabled),
});

const submit = () => {
  form.patch('/telegram/settings', {
    preserveScroll: true,
    onSuccess: () => {
      form.reset('bot_token', 'webhook_secret');
    },
  });
};
</script>
