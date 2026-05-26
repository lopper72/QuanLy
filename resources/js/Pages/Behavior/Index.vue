<template>
  <AppLayout>
    <div class="space-y-6">
      <PageHeader
        title="Theo dõi hành vi"
        description="Ghi chép và phân tích các hành vi cần theo dõi để phụ huynh và chuyên viên có dữ liệu rõ ràng."
      >
        <template #actions>
          <div class="flex flex-wrap items-center gap-2">
            <Link
              v-if="hasActiveChildren"
              :href="route('behavior.create')"
              class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
            >
              + Ghi nhận hành vi
            </Link>
            <span
              v-else
              class="inline-flex items-center rounded-md border border-slate-200 bg-slate-50 px-4 py-2 text-sm font-semibold text-slate-400"
            >
              + Ghi nhận hành vi
            </span>
            <Link
              v-if="hasActiveChildren"
              :href="route('behavior.quick')"
              class="inline-flex items-center rounded-md border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
            >
              Ghi nhanh hành vi
            </Link>
          </div>
        </template>
      </PageHeader>

      <div
        v-if="!hasActiveChildren"
        class="rounded-md border border-amber-200 bg-amber-50 p-4 text-sm font-medium text-amber-800"
      >
        Chưa có trẻ đang can thiệp để ghi nhận hành vi.
      </div>

      <div v-if="filters.child_status === 'voided'" class="rounded-md border border-slate-200 bg-slate-50 p-4 text-sm text-slate-700">
        Hồ sơ đã ngừng can thiệp. Chỉ hiển thị dữ liệu lịch sử.
      </div>
      <div v-if="filters.child_status === 'paused'" class="rounded-md border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
        Trẻ đang tạm nghỉ. Chỉ nên xem lại dữ liệu, không tạo ghi nhận mới.
      </div>

      <BehaviorSummaryCards :summary="summary" :behavior-types="behaviorTypes" />

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
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import AppLayout from '../../Components/layout/AppLayout.vue';
import PageHeader from '../../Components/ui/PageHeader.vue';
import BehaviorSummaryCards from '../../Components/behavior/BehaviorSummaryCards.vue';
import BehaviorFilters from '../../Components/behavior/BehaviorFilters.vue';
import BehaviorTimelineList from '../../Components/behavior/BehaviorTimelineList.vue';

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
