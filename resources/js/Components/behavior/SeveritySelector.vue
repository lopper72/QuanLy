<script setup>
import { computed } from 'vue';

const props = defineProps({
  modelValue: {
    type: String,
    default: '',
  },
  severities: {
    type: Array,
    required: true,
  },
});

defineEmits(['update:modelValue']);

const getClasses = (key, color, isSelected) => {
  if (!isSelected) {
    return 'bg-white border-gray-200 text-gray-700 hover:bg-gray-50 active:bg-gray-100';
  }

  const selectedColors = {
    green: 'bg-green-600 border-green-600 text-white shadow-md focus:ring-green-500 scale-[1.02]',
    yellow: 'bg-amber-500 border-amber-500 text-white shadow-md focus:ring-amber-500 scale-[1.02]',
    red: 'bg-red-600 border-red-600 text-white shadow-md focus:ring-red-500 scale-[1.02]',
  };

  return selectedColors[color] || 'bg-gray-900 border-gray-900 text-white';
};
</script>

<template>
  <div class="grid grid-cols-3 gap-3 w-full">
    <button
      v-for="item in severities"
      :key="item.key"
      type="button"
      @click="$emit('update:modelValue', item.key)"
      class="flex flex-col items-center justify-center p-4 border rounded-2xl font-bold text-xs transition-all duration-200 min-h-[56px] touch-manipulation focus:outline-none focus:ring-2 focus:ring-offset-2"
      :class="getClasses(item.key, item.color, modelValue === item.key)"
    >
      <span>{{ item.label }}</span>
    </button>
  </div>
</template>