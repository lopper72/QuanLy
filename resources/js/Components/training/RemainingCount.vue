<template>
  <span :class="classes">
    <slot :remaining="remaining">
      <template v-if="remaining === 0">
        Đã xử lý tất cả bài tập
      </template>
      <template v-else>
        Còn {{ remaining }} bài tập
      </template>
    </slot>
  </span>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  items: {
    type: Array,
    required: true,
    default: () => []
  }
});

const remaining = computed(() => {
  return props.items.filter(
    item => !['completed', 'skipped'].includes(item.completion_status)
  ).length;
});

const classes = computed(() => {
  if (remaining.value === 0) {
    return 'text-green-600 font-medium text-xs bg-green-50 px-2 py-0.5 rounded-full';
  }
  return 'text-amber-700 font-medium text-xs bg-amber-50 px-2 py-0.5 rounded-full';
});
</script>
