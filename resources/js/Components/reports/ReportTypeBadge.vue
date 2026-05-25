<template>
  <span :class="classes">
    {{ label }}
  </span>
</template>

<script setup>
import { computed } from 'vue';
import { reportTypeLabels, labelFor } from '@/Lib/labels';

const props = defineProps({
  type: {
    type: String,
    required: true,
  },
});

const label = computed(() => {
  return labelFor(reportTypeLabels, props.type);
});

const classes = computed(() => {
  const base = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium';
  const styles = {
    daily: 'bg-emerald-100 text-emerald-800',
    weekly: 'bg-indigo-100 text-indigo-800',
    monthly: 'bg-blue-100 text-blue-800',
    custom: 'bg-amber-100 text-amber-800',
  };
  return `${base} ${styles[props.type] || 'bg-gray-100 text-gray-800'}`;
});
</script>
