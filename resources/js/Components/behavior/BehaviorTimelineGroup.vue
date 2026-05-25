<template>
  <section class="rounded-lg border border-slate-100 bg-white shadow-sm">
    <header class="flex flex-col gap-3 border-b border-slate-100 p-5 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h3 class="text-base font-bold text-slate-900">{{ group.child?.full_name || 'Chưa xác định' }}</h3>
        <p class="mt-1 text-sm text-slate-500">Dòng thời gian hành vi</p>
      </div>
      <span class="inline-flex w-fit items-center rounded-full border px-2.5 py-0.5 text-xs font-medium" :class="statusClass">
        {{ statusLabel }}
      </span>
    </header>

    <div class="space-y-4 p-5">
      <BehaviorTimelineItem
        v-for="log in group.logs"
        :key="log.id"
        :log="log"
      />
    </div>
  </section>
</template>

<script setup>
import { computed } from 'vue';
import BehaviorTimelineItem from './BehaviorTimelineItem.vue';
import { childStatusLabels, labelFor } from '@/Lib/labels';

const props = defineProps({
  group: {
    type: Object,
    required: true,
  },
});

const childStatus = computed(() => props.group.child?.status || '');
const statusLabel = computed(() => labelFor(childStatusLabels, childStatus.value));
const statusClass = computed(() => {
  const classes = {
    active: 'bg-emerald-50 text-emerald-700 border-emerald-200',
    paused: 'bg-amber-50 text-amber-700 border-amber-200',
    stopped: 'bg-orange-50 text-orange-700 border-orange-200',
    voided: 'bg-slate-100 text-slate-600 border-slate-200',
  };

  return classes[childStatus.value] || 'bg-slate-100 text-slate-600 border-slate-200';
});
</script>
