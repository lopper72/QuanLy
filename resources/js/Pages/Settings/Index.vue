<template>
  <AppLayout>
    <div class="space-y-6">
      <PageHeader
        title="Cài đặt hệ thống"
        description="Cấu hình thông tin hệ thống và kênh nhắc lịch cho phụ huynh."
      />

      <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
          <section class="rounded-lg border border-gray-100 bg-white p-6 shadow">
            <h3 class="mb-6 text-lg font-bold text-gray-900">Cấu hình chung</h3>

            <div class="space-y-6">
              <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div>
                  <label class="mb-1 block text-sm font-semibold text-gray-700">Tên hệ thống</label>
                  <input
                    type="text"
                    :value="settings.system_name"
                    readonly
                    class="block w-full rounded-md border-gray-300 bg-gray-50 text-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                  />
                </div>

                <div>
                  <label class="mb-1 block text-sm font-semibold text-gray-700">Phiên bản hệ thống</label>
                  <input
                    type="text"
                    :value="settings.version"
                    readonly
                    class="block w-full rounded-md border-gray-300 bg-gray-50 text-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                  />
                </div>
              </div>

              <div>
                <label class="mb-1 block text-sm font-semibold text-gray-700">Múi giờ</label>
                <input
                  type="text"
                  :value="settings.timezone"
                  readonly
                  class="block w-full rounded-md border-gray-300 bg-gray-50 text-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                />
              </div>
            </div>
          </section>

          <section class="rounded-lg border border-sky-100 bg-white p-6 shadow">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
              <div>
                <h3 class="text-lg font-bold text-gray-900">Thông báo Telegram</h3>
                <p class="mt-1 text-sm text-gray-600">
                  Kết nối Telegram để nhận nhắc lịch checklist, báo trước giờ tập và tổng kết cuối ngày.
                </p>
              </div>
              <span
                class="inline-flex w-fit items-center rounded-full px-3 py-1 text-xs font-semibold"
                :class="telegram.connected ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-600'"
              >
                {{ telegram.connected ? 'Đã kết nối' : 'Chưa kết nối' }}
              </span>
            </div>

            <div class="mt-5 space-y-4">
              <button
                type="button"
                class="inline-flex min-h-11 items-center justify-center rounded-md bg-sky-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-sky-700 disabled:cursor-not-allowed disabled:opacity-60"
                :disabled="linkForm.processing"
                @click="createTelegramLink"
              >
                Kết nối Telegram
              </button>

              <div v-if="telegram.link_url" class="rounded-md border border-sky-100 bg-sky-50 p-4">
                <label class="mb-2 block text-sm font-semibold text-sky-900">Liên kết kết nối</label>
                <div class="flex flex-col gap-3 sm:flex-row">
                  <input
                    type="text"
                    :value="telegram.link_url"
                    readonly
                    class="min-w-0 flex-1 rounded-md border-sky-200 bg-white text-sm text-gray-700"
                  />
                  <a
                    :href="telegram.link_url"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex min-h-11 items-center justify-center rounded-md border border-sky-200 bg-white px-4 py-2 text-sm font-semibold text-sky-700 hover:bg-sky-100"
                  >
                    Mở Telegram
                  </a>
                </div>
                <p class="mt-2 text-sm text-sky-800">
                  Mở liên kết rồi gửi lệnh bắt đầu trong Telegram để hoàn tất kết nối.
                </p>
              </div>
            </div>
          </section>
        </div>

        <section class="flex flex-col justify-center rounded-lg border border-dashed border-gray-300 bg-gray-50 p-6 text-center">
          <svg class="mx-auto mb-4 h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2a2 2 0 01-.6 1.4L4 17h5m6 0a3 3 0 11-6 0m6 0H9" />
          </svg>
          <h3 class="mb-2 text-lg font-bold text-gray-900">Nhắc lịch cho phụ huynh</h3>
          <p class="text-sm leading-relaxed text-gray-600">
            Hệ thống sẽ gửi tin nhắn nhắc bài tập hằng ngày, nút thao tác nhanh và liên kết mở checklist.
          </p>
        </section>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import AppLayout from '../../Components/layout/AppLayout.vue';
import PageHeader from '../../Components/ui/PageHeader.vue';

defineProps({
  settings: Object,
  telegram: {
    type: Object,
    default: () => ({
      connected: false,
      notifications_enabled: false,
      link_url: null,
    }),
  },
});

const linkForm = useForm({});

const createTelegramLink = () => {
  linkForm.post(route('settings.telegram.link'), {
    preserveScroll: true,
  });
};
</script>
