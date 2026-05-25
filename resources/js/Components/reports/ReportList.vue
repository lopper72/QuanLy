<template>
  <div>
    <EmptyState
      v-if="reports.data.length === 0"
      title="Chưa có báo cáo"
      description="Bắt đầu bằng cách tạo báo cáo tổng hợp tiến độ mới."
    >
      <template #icon>
        <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
      </template>
      <template #action>
        <Link
          :href="route('reports.create')"
          class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-semibold rounded-md text-white bg-indigo-600 hover:bg-indigo-700 transition duration-150 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
        >
          Tạo báo cáo
        </Link>
      </template>
    </EmptyState>

    <div v-else>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div v-for="report in reports.data" :key="report.id">
          <ReportCard :report="report" @delete="$emit('delete', $event)" />
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="reports.links && reports.links.length > 3" class="mt-8 flex justify-center">
        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
          <Link
            v-for="(link, index) in reports.links"
            :key="index"
            :href="link.url || '#'"
            :disabled="!link.url"
            class="relative inline-flex items-center px-3 py-2 border text-sm font-medium"
            :class="[
              link.active
                ? 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600'
                : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50',
              !link.url ? 'opacity-50 cursor-not-allowed' : '',
              index === 0 ? 'rounded-l-md' : '',
              index === reports.links.length - 1 ? 'rounded-r-md' : ''
            ]"
            v-html="link.label"
          />
        </nav>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import EmptyState from '../ui/EmptyState.vue';
import ReportCard from './ReportCard.vue';

defineProps({
  reports: {
    type: Object,
    required: true,
  },
});

defineEmits(['delete']);
</script>
