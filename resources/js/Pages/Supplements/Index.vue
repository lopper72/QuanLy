<template>
  <AppLayout>
    <div class="space-y-6">
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Lịch bổ sung</h1>
          <p class="text-sm text-gray-600">Theo dõi lịch uống thuốc hoặc sản phẩm bổ sung cho từng bé.</p>
        </div>
        <Link :href="route('supplements.create')" class="inline-flex justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
          Thêm lịch bổ sung
        </Link>
      </div>

      <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
        {{ safetyNote }}
      </div>

      <section class="rounded-lg bg-white p-5 shadow">
        <h2 class="text-lg font-semibold text-gray-900">Nhắc lịch hôm nay</h2>
        <div v-if="todayReminders.length" class="mt-4 space-y-3">
          <article v-for="item in todayReminders" :key="item.id" class="flex flex-col gap-3 rounded-lg border border-gray-200 p-4 md:flex-row md:items-center md:justify-between">
            <div>
              <p class="font-semibold text-gray-900">{{ item.name }}</p>
              <p class="text-sm text-gray-600">{{ item.child_name }} · {{ item.display_time }}</p>
              <p v-if="item.dosage_note" class="mt-1 text-sm text-gray-500">{{ item.dosage_note }}</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
              <span class="rounded-full px-2.5 py-1 text-xs font-medium" :class="statusClass(item.status)">
                {{ statusLabel(item.status) }}
              </span>
              <button type="button" class="rounded-md bg-emerald-600 px-3 py-2 text-sm font-medium text-white hover:bg-emerald-700" @click="markTaken(item.id)">
                Đã uống
              </button>
              <button type="button" class="rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50" @click="skip(item.id)">
                Bỏ qua
              </button>
            </div>
          </article>
        </div>
        <p v-else class="mt-4 rounded-md border border-dashed border-gray-300 p-4 text-sm text-gray-500">
          Chưa có nhắc lịch bổ sung hôm nay.
        </p>
      </section>

      <section class="rounded-lg bg-white p-5 shadow">
        <h2 class="text-lg font-semibold text-gray-900">Tất cả lịch bổ sung</h2>
        <div class="mt-4 overflow-hidden rounded-lg border border-gray-200">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500">Bé</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500">Tên</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500">Thời điểm</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500">Trạng thái</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500">Thao tác</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
              <tr v-for="schedule in schedules" :key="schedule.id">
                <td class="px-4 py-3 text-sm text-gray-700">{{ schedule.child?.full_name }}</td>
                <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ schedule.name }}</td>
                <td class="px-4 py-3 text-sm text-gray-600">{{ scheduleDisplay(schedule) }}</td>
                <td class="px-4 py-3 text-sm text-gray-600">{{ scheduleStatusLabel(schedule.status) }}</td>
                <td class="px-4 py-3 text-right">
                  <Link :href="route('supplements.edit', schedule.id)" class="text-sm font-medium text-indigo-600 hover:underline">
                    Chỉnh sửa
                  </Link>
                </td>
              </tr>
              <tr v-if="schedules.length === 0">
                <td colspan="5" class="px-4 py-6 text-center text-sm text-gray-500">Chưa có lịch bổ sung.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>
    </div>
  </AppLayout>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Components/layout/AppLayout.vue';

defineProps({
  children: { type: Array, default: () => [] },
  schedules: { type: Array, default: () => [] },
  todayReminders: { type: Array, default: () => [] },
  safetyNote: { type: String, required: true },
});

function markTaken(id) {
  router.patch(route('supplements.taken', id), {}, { preserveScroll: true });
}

function skip(id) {
  router.patch(route('supplements.skip', id), {}, { preserveScroll: true });
}

function statusLabel(status) {
  return {
    pending: 'Chờ ghi nhận',
    taken: 'Đã uống',
    skipped: 'Bỏ qua',
  }[status] || 'Chờ ghi nhận';
}

function statusClass(status) {
  return {
    taken: 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200',
    skipped: 'bg-gray-100 text-gray-700 ring-1 ring-gray-200',
    pending: 'bg-amber-50 text-amber-700 ring-1 ring-amber-200',
  }[status] || 'bg-amber-50 text-amber-700 ring-1 ring-amber-200';
}

function scheduleStatusLabel(status) {
  return {
    active: 'Đang dùng',
    paused: 'Tạm dừng',
    completed: 'Đã hoàn tất',
  }[status] || status;
}

function scheduleDisplay(schedule) {
  if (schedule.timing_type === 'fixed_time' && schedule.scheduled_time) {
    return `${String(schedule.scheduled_time).slice(0, 5)} hằng ngày`;
  }

  return {
    before_breakfast: 'Trước bữa sáng',
    before_lunch: 'Trước bữa trưa',
    before_dinner: 'Trước bữa tối',
    after_breakfast: 'Sau bữa sáng',
    after_lunch: 'Sau bữa trưa',
    after_dinner: 'Sau bữa tối',
    bedtime: 'Trước khi ngủ',
    before_meal: 'Trước bữa ăn',
    after_meal: 'Sau bữa ăn',
  }[schedule.meal_relation || schedule.timing_type] || 'Theo lịch đã nhập';
}
</script>
