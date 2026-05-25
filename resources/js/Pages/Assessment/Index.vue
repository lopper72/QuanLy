<template>
  <AppLayout>
    <div class="space-y-6">
      <!-- Flash Messages -->
      <div v-if="$page.props.flash?.success" class="bg-green-50 border border-green-200 text-green-800 rounded-md p-4 text-sm">
        {{ $page.props.flash.success }}
      </div>

      <!-- Standard Page Header -->
      <PageHeader
        title="Đánh giá"
        description="Thực hiện và phân tích các cột mốc phát triển/hành vi và các bài đánh giá."
      >
        <template #actions>
          <Link
            :href="route('assessment.create')"
            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-semibold rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 transition duration-150 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
          >
            Đánh giá mới
          </Link>
        </template>
      </PageHeader>

      <!-- Summary Cards -->
      <AssessmentSummaryCards :summary="summary" />

      <!-- Filters -->
      <AssessmentFilters :filters="filters" :children="children" />

      <!-- Assessment List -->
      <AssessmentList :assessments="assessments" />
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '../../Components/layout/AppLayout.vue';
import PageHeader from '../../Components/ui/PageHeader.vue';
import AssessmentFilters from '../../Components/assessment/AssessmentFilters.vue';
import AssessmentSummaryCards from '../../Components/assessment/AssessmentSummaryCards.vue';
import AssessmentList from '../../Components/assessment/AssessmentList.vue';
import { Link } from '@inertiajs/vue3';

defineProps({
  assessments: Object,
  summary: Object,
  children: Array,
  filters: Object,
});
</script>