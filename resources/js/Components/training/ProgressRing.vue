<template>
  <div class="relative flex items-center justify-center">
    <svg :width="size" :height="size" class="transform -rotate-90">
      <!-- Background Circle -->
      <circle
        class="text-gray-200 stroke-current"
        :stroke-width="stroke"
        fill="transparent"
        :r="radius"
        :cx="center"
        :cy="center"
      />
      <!-- Progress Circle -->
      <circle
        class="text-primary-600 stroke-current transition-all duration-500 ease-in-out"
        :stroke-width="stroke"
        stroke-linecap="round"
        fill="transparent"
        :r="radius"
        :cx="center"
        :cy="center"
        :stroke-dasharray="circumference"
        :stroke-dashoffset="dashoffset"
      />
    </svg>
    <!-- Center Label -->
    <div class="absolute flex flex-col items-center justify-center text-center">
      <span class="text-xs font-semibold text-gray-700">
        {{ Math.round(percentage) }}%
      </span>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  percentage: {
    type: Number,
    required: true,
    default: 0
  },
  size: {
    type: Number,
    default: 60
  },
  stroke: {
    type: Number,
    default: 5
  }
});

const center = computed(() => props.size / 2);
const radius = computed(() => (props.size - props.stroke) / 2);
const circumference = computed(() => 2 * Math.PI * radius.value);
const dashoffset = computed(() => {
  const progress = Math.max(0, Math.min(100, props.percentage));
  return circumference.value - (progress / 100) * circumference.value;
});
</script>