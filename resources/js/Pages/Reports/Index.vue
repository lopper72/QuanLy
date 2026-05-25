<template>
  <AppLayout>
    <div class="space-y-6">
      <PageHeader
        title="Báo cáo & Phân tích"
        description="Tạo, tổng hợp và quản lý các đánh giá tiến độ đa hệ thống trong các lĩnh vực tập luyện, hành vi và đánh giá cột mốc."
      >
        <template #actions>
          <Link
            :href="route('reports.create')"
            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-semibold rounded-md text-white bg-indigo-600 hover:bg-indigo-700 transition duration-150 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
          >
            Tạo báo cáo mới
          </Link>
        </template>
      </PageHeader>

      <!-- Filters -->
      <ReportFilters
        :children="children"
        :report-types="reportTypes"
        :filters="filters"
        @filter="handleFilter"
      />

      <!-- Report List -->
      <ReportList
        :reports="reports"
        @delete="deleteReport"
      />
    </div>
  </AppLayout>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '../../Components/layout/AppLayout.vue';
import PageHeader from '../../Components/ui/PageHeader.vue';
import ReportFilters from '../../Components/reports/ReportFilters.vue';
import ReportList from '../../Components/reports/ReportList.vue';

const props = defineProps({
  reports: {
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
  filters: {
    type: Object,
    default: () => ({}),
  },
});

const handleFilter = (newFilters) => {
  router.get(route('reports.index'), newFilters, {
    preserveState: true,
    replace: true,
  });
};

const deleteReport = (id) => {
  if (confirm('Bạn có chắc chắn muốn xóa báo cáo tiến độ đã tổng hợp này không?')) {
    router.delete(route('reports.destroy', id));
  }
};
</script>