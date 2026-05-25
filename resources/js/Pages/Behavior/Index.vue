<template>
  <AppLayout>
    <div class="space-y-6">
      <!-- Standard Page Header -->
      <PageHeader
        title="Theo dõi hành vi"
        description="Ghi chép và phân tích các hành vi thách thức và các sự cố tích cực bằng phương pháp theo dõi ABC."
      >
        <template #actions>
          <div class="flex flex-wrap gap-2">
            <Link
              v-if="hasActiveChildren"
              :href="route('behavior.quick')"
              class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-semibold rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 transition duration-150 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            >
              Ghi nhanh hành vi
            </Link>
            <span
              v-else
              class="inline-flex items-center px-4 py-2 border border-slate-200 text-sm font-semibold rounded-md text-slate-400 bg-slate-50"
            >
              Ghi nhanh hành vi
            </span>
            <Link
              :href="route('behavior.create')"
              class="inline-flex items-center px-4 py-2 border border-slate-200 text-sm font-semibold rounded-md shadow-sm text-slate-700 bg-white hover:bg-slate-50 transition duration-150 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            >
              Ghi nhận hành vi
            </Link>
          </div>
        </template>
      </PageHeader>

      <div v-if="filters.child_status === 'voided'" class="rounded-md border border-slate-200 bg-slate-50 p-4 text-sm text-slate-700">
        Hồ sơ đã ngừng can thiệp. Chỉ hiển thị dữ liệu lịch sử.
      </div>
      <div v-if="filters.child_status === 'paused'" class="rounded-md border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
        Trẻ đang tạm nghỉ. Chỉ nên xem lại dữ liệu, không tạo ghi nhận mới.
      </div>

      <!-- Summary Cards -->
      <BehaviorSummaryCards :summary="summary" :behavior-types="behaviorTypes" />

      <!-- Filters -->
      <BehaviorFilters
        :initial-filters="filters"
        :children="children"
        :behavior-types="behaviorTypes"
        :severities="severities"
      />

      <BehaviorTimelineList :groups="behaviorGroups" />
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '../../Components/layout/AppLayout.vue';
import PageHeader from '../../Components/ui/PageHeader.vue';
import BehaviorSummaryCards from '../../Components/behavior/BehaviorSummaryCards.vue';
import BehaviorFilters from '../../Components/behavior/BehaviorFilters.vue';
import BehaviorTimelineList from '../../Components/behavior/BehaviorTimelineList.vue';
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
  behaviorLogs: {
    type: Array,
    required: true,
  },
  behaviorGroups: {
    type: Array,
    required: true,
  },
  summary: {
    type: Object,
    required: true,
  },
  children: {
    type: Array,
    required: true,
  },
  activeChildren: {
    type: Array,
    required: true,
  },
  behaviorTypes: {
    type: Object,
    required: true,
  },
  severities: {
    type: Object,
    required: true,
  },
  filters: {
    type: Object,
    required: true,
  },
});

const hasActiveChildren = computed(() => props.activeChildren.length > 0);
</script>
