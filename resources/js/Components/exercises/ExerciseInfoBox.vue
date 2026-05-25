<template>
  <div class="rounded-lg border border-slate-200 bg-slate-50 p-3">
    <p class="text-xs font-medium text-slate-500">{{ title }}</p>
    <p class="mt-1 text-sm font-semibold text-slate-900">{{ value }}</p>
  </div>
</template>

<script setup>
defineProps({
  title: {
    type: String,
    required: true,
  },
  value: {
    type: String,
    required: true,
  },
});
</script>
