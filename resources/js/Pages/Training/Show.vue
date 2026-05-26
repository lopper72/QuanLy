<template>
  <AppLayout>
    <div class="mx-auto max-w-7xl space-y-6">
      <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
          <Link
            :href="route('training.index')"
            class="inline-flex items-center text-xs font-semibold text-indigo-600 hover:underline"
          >
            &larr; Quay lại tập luyện hằng ngày
          </Link>
          <h1 class="mt-2 text-3xl font-bold leading-tight text-gray-900">Chi tiết buổi tập</h1>
        </div>

        <div class="flex flex-wrap items-center gap-3">
          <Link
            :href="route('behavior.create', { training_session_id: session.id })"
            class="inline-flex items-center rounded-md border border-indigo-300 bg-white px-4 py-2 text-sm font-medium text-indigo-700 hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
          >
            Ghi nhận hành vi trong buổi tập
          </Link>
          <button
            type="button"
            class="inline-flex items-center rounded-md border border-emerald-300 bg-white px-4 py-2 text-sm font-medium text-emerald-700 hover:bg-emerald-50 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2"
            @click="sendTelegramSchedule"
          >
            Gửi lịch qua Telegram
          </button>
          <button
            type="button"
            class="inline-flex items-center rounded-md border border-red-300 bg-white px-4 py-2 text-sm font-medium text-red-700 hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
            @click="confirmDelete"
          >
            Xóa buổi tập
          </button>
          <Link
            :href="route('training.edit', session.id)"
            class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
          >
            Chỉnh sửa buổi tập
          </Link>
        </div>
      </div>

      <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="space-y-4 rounded-lg border border-gray-100 bg-white p-6 shadow lg:col-span-1">
          <h3 class="border-b border-gray-200 pb-2 text-lg font-medium text-gray-900">Tổng quan buổi tập</h3>

          <div class="space-y-3">
            <div>
              <span class="block text-xs font-medium text-gray-500">Trẻ</span>
              <Link
                v-if="session.child_id"
                :href="route('children.show', session.child_id)"
                class="text-sm font-semibold text-indigo-600 hover:underline"
              >
                {{ session.child?.full_name || `${session.child?.first_name || ''} ${session.child?.last_name || ''}` }}
              </Link>
              <span v-else class="text-sm font-semibold text-gray-900">Không xác định</span>
            </div>

            <div>
              <span class="block text-xs font-medium text-gray-500">Ngày tập</span>
              <span class="text-sm font-medium text-gray-900">
                {{ formatDate(session.session_date) }}
                <span v-if="session.scheduled_time" class="ml-1 font-bold text-indigo-600">
                  ({{ formatTime(session.scheduled_time) }})
                </span>
              </span>
            </div>

            <div>
              <span class="block text-xs font-medium text-gray-500">Trạng thái chung</span>
              <div class="mt-1 flex flex-wrap items-center gap-2">
                <TrainingStatusBadge :status="session.status" />
                <select
                  v-model="sessionStatus"
                  class="rounded border-gray-300 py-1 text-xs focus:border-indigo-500 focus:ring-indigo-500"
                  @change="updateOverallStatus"
                >
                  <option v-for="option in sessionStatusOptions" :key="option.value" :value="option.value">
                    {{ option.label }}
                  </option>
                </select>
              </div>
            </div>

            <div>
              <span class="block text-xs font-medium text-gray-500">Tổng thời gian dự kiến</span>
              <span class="text-sm font-semibold text-gray-900">{{ session.total_minutes || 0 }} phút</span>
            </div>

            <div>
              <span class="block text-xs font-medium text-gray-500">Được tạo lúc</span>
              <span class="text-xs text-gray-600">{{ formatDateWithTime(session.created_at) }}</span>
            </div>
          </div>
        </div>

        <div class="flex flex-col justify-between rounded-lg border border-gray-100 bg-white p-6 shadow lg:col-span-2">
          <div>
            <h3 class="mb-3 border-b border-gray-200 pb-2 text-lg font-medium text-gray-900">
              Quan sát và ghi chú của chuyên viên
            </h3>
            <p class="whitespace-pre-line text-sm text-gray-700">
              {{ session.notes || 'Không có ghi chú hoặc quan sát nào được ghi lại.' }}
            </p>
          </div>

          <div v-if="!session.notes" class="mt-4 text-xs text-gray-400">
            Chọn chỉnh sửa buổi tập để thêm ghi chú cho toàn buổi.
          </div>
        </div>
      </div>

      <div class="rounded-lg border border-indigo-100 bg-indigo-50 p-4">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
          <div>
            <h3 class="text-sm font-semibold text-indigo-900">Ghi nhận hành vi trong buổi tập</h3>
            <p class="mt-1 text-sm text-indigo-700">
              Dùng khi bé có hành vi cần theo dõi trong lúc tập hoặc khi chuyển giữa các bài.
            </p>
          </div>
          <Link
            :href="route('behavior.create', { training_session_id: session.id })"
            class="inline-flex justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700"
          >
            Ghi nhận hành vi
          </Link>
        </div>
      </div>

      <div class="overflow-hidden rounded-lg border border-gray-100 bg-white shadow">
        <div class="flex items-center justify-between border-b border-gray-200 px-6 py-5">
          <div>
            <h3 class="text-lg font-medium text-gray-900">Danh sách bài tập</h3>
            <p class="mt-1 text-xs text-gray-500">Cập nhật trạng thái từng bài tập trong buổi.</p>
          </div>
        </div>

        <TrainingItemList
          :items="session.items"
          :read-only="true"
          :allow-interactive-status="true"
          :behavior-session-id="session.id"
          @update-item-status="handleUpdateItemStatus"
        />
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, watch } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '../../Components/layout/AppLayout.vue';
import TrainingStatusBadge from '../../Components/training/TrainingStatusBadge.vue';
import TrainingItemList from '../../Components/training/TrainingItemList.vue';
import { statusLabels, labelFor } from '@/Lib/labels';

const props = defineProps({
  session: {
    type: Object,
    required: true,
  },
});

const sessionStatus = ref(props.session.status);

const sessionStatusOptions = [
  { value: 'planned', label: labelFor(statusLabels, 'planned') },
  { value: 'in_progress', label: labelFor(statusLabels, 'in_progress') },
  { value: 'completed', label: labelFor(statusLabels, 'completed') },
  { value: 'skipped', label: labelFor(statusLabels, 'skipped') },
];

watch(
  () => props.session.status,
  (status) => {
    sessionStatus.value = status;
  }
);

function formatDate(dateStr) {
  if (!dateStr) return '';
  return new Date(dateStr).toLocaleDateString('vi-VN', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  });
}

function formatDateWithTime(dateStr) {
  if (!dateStr) return '';
  return new Date(dateStr).toLocaleString('vi-VN');
}

function formatTime(timeStr) {
  if (!timeStr) return '';
  return String(timeStr).slice(0, 5);
}

function confirmDelete() {
  if (confirm('Bạn có chắc chắn muốn xóa buổi tập này? Hành động này không thể hoàn tác.')) {
    router.delete(route('training.destroy', props.session.id));
  }
}

function updateOverallStatus() {
  router.patch(
    route('training.updateStatus', props.session.id),
    { status: sessionStatus.value },
    {
      preserveScroll: true,
      preserveState: false,
      onError: () => {
        sessionStatus.value = props.session.status;
      },
    }
  );
}

function handleUpdateItemStatus(item, newStatus) {
  router.patch(
    route('training.updateItemStatus', item.id),
    { status: newStatus },
    {
      preserveScroll: true,
      preserveState: false,
    }
  );
}

function sendTelegramSchedule() {
  router.post(route('training.telegramSend', props.session.id), {}, {
    preserveScroll: true,
  });
}
</script>
