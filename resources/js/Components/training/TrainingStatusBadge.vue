<template>
  <span :class="badgeClasses">
    {{ formattedStatus }}
  </span>
</template>

<script setup>
import { computed } from 'vue';
import { itemStatusLabels, labelFor, statusLabels } from '@/Lib/labels';

const props = defineProps({
  status: {
    type: String,
    required: true,
  },
  type: {
    type: String,
    default: 'session',
  },
});

const formattedStatus = computed(() => {
  const labels = props.type === 'item' ? itemStatusLabels : statusLabels;
  return labelFor(labels, props.status);
});

const badgeClasses = computed(() => {
  const base = 'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold';
  const colors = {
    planned: 'bg-blue-100 text-blue-800',
    in_progress: 'bg-yellow-100 text-yellow-800',
    completed: 'bg-green-100 text-green-800',
    skipped: 'bg-gray-100 text-gray-800',
    not_started: 'bg-gray-100 text-gray-800',
    partially_completed: 'bg-orange-100 text-orange-800',
    missed: 'bg-red-100 text-red-800',
    pending: 'bg-gray-100 text-gray-800',
  };

  return `${base} ${colors[props.status] || 'bg-gray-100 text-gray-800'}`;
});
</script>
