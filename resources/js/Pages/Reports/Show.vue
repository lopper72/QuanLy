<template>
  <AppLayout>
    <div class="space-y-6">
      <div class="sm:flex sm:items-center sm:justify-between border-b border-gray-200 pb-5">
        <div>
          <nav class="flex mb-2" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3 text-xs font-semibold text-gray-500">
              <li>
                <Link :href="route('reports.index')" class="hover:text-indigo-600">Báo cáo</Link>
              </li>
              <li>
                <span class="mx-1">/</span>
              </li>
              <li class="text-gray-900 font-bold">Chi tiết báo cáo</li>
            </ol>
          </nav>
          <h1 class="text-3xl font-bold text-gray-900">Tổng quan báo cáo tiến độ</h1>
          <p class="mt-2 text-sm text-gray-500">
            Dữ liệu được tổng hợp động từ tất cả hồ sơ can thiệp của trẻ.
          </p>
        </div>
        <div class="mt-4 sm:mt-0 flex space-x-2">
          <button
            @click="printReport"
            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
          >
            In báo cáo
          </button>
          <Link
            :href="route('reports.edit', report.id)"
            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-750 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
          >
            Chỉnh sửa tham số
          </Link>
        </div>
      </div>

      <!-- Report Card Detail & Comments -->
      <div class="bg-white shadow overflow-hidden sm:rounded-lg border border-gray-200">
        <div class="px-4 py-5 sm:px-6 flex items-center justify-between bg-gray-50">
          <div>
            <h3 class="text-lg leading-6 font-bold text-gray-900">
              Quan sát của chuyên gia & Tổng kết
            </h3>
            <p class="mt-1 max-w-2xl text-xs text-gray-500">
              Ghi chú định tính được cá nhân hóa được cung cấp tại thời điểm tổng hợp.
            </p>
          </div>
          <ReportTypeBadge :type="report.report_type" />
        </div>
        <div class="border-t border-gray-200 px-4 py-5 sm:px-6">
          <div class="prose prose-sm max-w-none text-gray-700 whitespace-pre-line">
            {{ report.summary || 'Không có quan sát tổng kết nào được biên soạn. Tham khảo ma trận dữ liệu bên dưới để biết các chỉ số định lượng.' }}
          </div>
        </div>
      </div>

      <!-- Dynamic Summary Component -->
      <div class="mt-8">
        <h2 class="text-xl font-bold text-gray-900 mb-4 border-b border-gray-100 pb-2">Ma trận hiệu suất đã tổng hợp</h2>
        <ReportSummary :summary="summaryData" :child="report.child" />
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import AppLayout from '../../Components/layout/AppLayout.vue';
import ReportTypeBadge from '../../Components/reports/ReportTypeBadge.vue';
import ReportSummary from '../../Components/reports/ReportSummary.vue';

const props = defineProps({
  report: {
    type: Object,
    required: true,
  },
});

const summaryData = computed(() => {
  // If report has already JSON summary string, let's parse it, otherwise default
  if (typeof props.report.summary_data === 'string') {
    try {
      return JSON.parse(props.report.summary_data);
    } catch (e) {
      // Return empty format
    }
  } else if (typeof props.report.summary_data === 'object' && props.report.summary_data !== null) {
    return props.report.summary_data;
  }
  return {
    meta: {
      report_type: props.report.report_type,
      start_date: props.report.report_date,
      end_date: props.report.report_date,
    },
    training: { total_sessions: 0, completed_sessions: 0, completion_rate: 0 },
    behavior: { total_incidents: 0, average_severity: 0 },
    assessment: { assessment_count: 0, average_score: 0 },
  };
});

const printReport = () => {
  window.print();
};
</script>
