<template>
  <AppLayout>
    <div class="space-y-6">
      <div class="border-b border-gray-250 pb-5">
        <h1 class="text-3xl font-bold text-gray-900">Tổng hợp báo cáo tiến độ</h1>
        <p class="mt-2 text-sm text-gray-500">
          Tạo báo cáo tổng hợp từ buổi tập hằng ngày, ghi nhận hành vi và mốc phát triển.
        </p>
      </div>

      <ReportForm
        :children="children"
        :report-types="reportTypes"
        :errors="errors"
      />
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '../../Components/layout/AppLayout.vue';
import ReportForm from '../../Components/reports/ReportForm.vue';

defineProps({
  children: {
    type: Array,
    required: true,
  },
  reportTypes: {
    type: Object,
    required: true,
  },
  errors: {
    type: Object,
    default: () => ({}),
  },
});
</script>
