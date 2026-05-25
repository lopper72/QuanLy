<template>
  <Head title="Tin nhắn Telegram" />

  <AppLayout>
    <div class="space-y-6">
      <header class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Tin nhắn Telegram</h1>
          <p class="mt-1 text-sm text-gray-600">Theo dõi tin nhắn đến, tin nhắn gửi đi và trạng thái giao hàng.</p>
        </div>
        <Link href="/telegram/settings" class="inline-flex items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
          Cấu hình bot
        </Link>
      </header>

      <div class="grid gap-6 lg:grid-cols-[16rem_minmax(0,1fr)_18rem]">
        <TelegramContactSidebar :contacts="contacts" :selected-chat-id="selectedChatId" />
        <TelegramMessageTimeline :messages="messages" :selected-chat-id="selectedChatId" />

        <div class="space-y-4">
          <TelegramSystemStatusCard :status="systemStatus" />
          <TelegramSettingsCard :settings="settings" />
          <section class="rounded-lg bg-white p-4 ring-1 ring-gray-200">
            <h2 class="text-base font-semibold text-gray-900">Theo dõi</h2>
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
          <TelegramTwoWayTestPanel :training-test="trainingTest" :messages="messages" />
          <TelegramTestPanel :default-chat-id="settings.default_chat_id || selectedChatId" />
          <TelegramQuickSend :chat-id="selectedChatId || settings.default_chat_id" />
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Components/layout/AppLayout.vue';
import TelegramContactSidebar from '@/Components/telegram/TelegramContactSidebar.vue';
import TelegramMessageTimeline from '@/Components/telegram/TelegramMessageTimeline.vue';
import TelegramQuickSend from '@/Components/telegram/TelegramQuickSend.vue';
import TelegramSettingsCard from '@/Components/telegram/TelegramSettingsCard.vue';
import TelegramSystemStatusCard from '@/Components/telegram/TelegramSystemStatusCard.vue';
import TelegramTestPanel from '@/Components/telegram/TelegramTestPanel.vue';
import TelegramTwoWayTestPanel from '@/Components/telegram/TelegramTwoWayTestPanel.vue';

defineProps({
  contacts: {
    type: Array,
    default: () => [],
  },
  messages: {
    type: Array,
    default: () => [],
  },
  selectedChatId: {
    type: String,
    default: '',
  },
  settings: {
    type: Object,
    required: true,
  },
  stats: {
    type: Object,
    required: true,
  },
  trainingTest: {
    type: Object,
    required: true,
  },
  systemStatus: {
    type: Object,
    required: true,
  },
});
</script>
