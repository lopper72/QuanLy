<template>
  <AppLayout>
    <div class="space-y-6">
      <div class="border-b border-gray-250 pb-5">
        <h1 class="text-3xl font-bold text-gray-900">Chỉnh sửa báo cáo</h1>
        <p class="mt-2 text-sm text-gray-500">
          Sửa đổi chi tiết tổng hợp hoặc nhận xét quan sát cho báo cáo tiến độ này.
        </p>
      </div>

      <ReportForm
        :report="report"
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
  report: {
    type: Object,
    required: true,
  },
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