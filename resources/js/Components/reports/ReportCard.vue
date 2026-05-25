<template>
  <div class="bg-white overflow-hidden shadow rounded-lg border border-gray-200 hover:shadow-md transition-shadow duration-150 flex flex-col justify-between h-full">
    <div class="p-5">
      <div class="flex items-center justify-between">
        <ReportTypeBadge :type="report.report_type" />
        <span class="text-sm text-gray-500 font-medium">
          {{ formatDate(report.report_date) }}
        </span>
      </div>

      <div class="mt-4">
        <h3 class="text-lg font-bold text-gray-900 truncate">
          {{ report.child?.first_name }} {{ report.child?.last_name }}
        </h3>
        <p class="text-xs text-gray-500 mt-0.5">
          Mã trẻ: #{{ report.child_id }}
        </p>
      </div>

      <div class="mt-3 text-sm text-gray-600 line-clamp-3">
        {{ report.summary || 'Chưa có ghi chú tóm tắt cho báo cáo này.' }}
      </div>
    </div>

    <div class="bg-gray-50 px-5 py-3 flex items-center justify-between border-t border-gray-100">
      <Link
        :href="route('reports.show', report.id)"
        class="text-sm font-semibold text-indigo-600 hover:text-indigo-900"
      >
        Xem tóm tắt →
      </Link>
      <div class="flex items-center space-x-3">
        <Link
          :href="route('reports.edit', report.id)"
          class="text-xs font-medium text-gray-500 hover:text-gray-700"
        >
          Sửa
        </Link>
        <button
          @click="$emit('delete', report.id)"
          class="text-xs font-medium text-red-600 hover:text-red-900"
        >
          Xóa
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import ReportTypeBadge from './ReportTypeBadge.vue';

defineProps({
  report: {
    type: Object,
    required: true,
  },
});

defineEmits(['delete']);

const formatDate = (dateStr) => {
  if (!dateStr) return '';
  const date = new Date(dateStr);
  return date.toLocaleDateString('vi-VN', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  });
};
</script>
