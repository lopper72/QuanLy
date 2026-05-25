<script setup>
import { computed } from 'vue';

const props = defineProps({
  preset: {
    type: Object,
    required: true,
  },
  isSelected: {
    type: Boolean,
    default: false,
  },
});

defineEmits(['select']);

const colorClasses = computed(() => {
  const colors = {
    red: 'bg-red-50 text-red-700 border-red-200 hover:bg-red-100 active:bg-red-200 focus:ring-red-500',
    rose: 'bg-rose-50 text-rose-700 border-rose-200 hover:bg-rose-100 active:bg-rose-200 focus:ring-rose-500',
    indigo: 'bg-indigo-50 text-indigo-700 border-indigo-200 hover:bg-indigo-100 active:bg-indigo-200 focus:ring-indigo-500',
    amber: 'bg-amber-50 text-amber-700 border-amber-200 hover:bg-amber-100 active:bg-amber-200 focus:ring-amber-500',
    purple: 'bg-purple-50 text-purple-700 border-purple-200 hover:bg-purple-100 active:bg-purple-200 focus:ring-purple-500',
    blue: 'bg-blue-50 text-blue-700 border-blue-200 hover:bg-blue-100 active:bg-blue-200 focus:ring-blue-500',
    emerald: 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100 active:bg-emerald-200 focus:ring-emerald-500',
    orange: 'bg-orange-50 text-orange-700 border-orange-200 hover:bg-orange-100 active:bg-orange-200 focus:ring-orange-500',
  };

  const selectedColors = {
    red: 'bg-red-600 text-white border-red-600 focus:ring-red-500',
    rose: 'bg-rose-600 text-white border-rose-600 focus:ring-rose-500',
    indigo: 'bg-indigo-600 text-white border-indigo-600 focus:ring-indigo-500',
    amber: 'bg-amber-500 text-white border-amber-500 focus:ring-amber-500',
    purple: 'bg-purple-600 text-white border-purple-600 focus:ring-purple-500',
    blue: 'bg-blue-600 text-white border-blue-600 focus:ring-blue-500',
    emerald: 'bg-emerald-600 text-white border-emerald-600 focus:ring-emerald-500',
    orange: 'bg-orange-600 text-white border-orange-600 focus:ring-orange-500',
  };

  return props.isSelected
    ? selectedColors[props.preset.color] || 'bg-gray-900 text-white border-gray-900'
    : colors[props.preset.color] || 'bg-gray-50 text-gray-700 border-gray-200 hover:bg-gray-100';
});
</script>

<template>
  <button
    type="button"
    @click="$emit('select', preset.key)"
    class="flex flex-col items-center justify-center p-4 border rounded-2xl transition-all duration-200 text-center focus:outline-none focus:ring-2 focus:ring-offset-2 min-h-[96px] touch-manipulation shadow-sm"
    :class="colorClasses"
  >
    <!-- Icons -->
    <div class="mb-2">
      <!-- FlameIcon (red) -->
      <svg v-if="preset.icon === 'FlameIcon'" xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z" />
      </svg>
      <!-- ShieldAlertIcon (rose) -->
      <svg v-else-if="preset.icon === 'ShieldAlertIcon'" xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M20 13c0 5-3.5 7.5-7.66 9.7a1 1 0 0 1-.68 0C7.5 20.5 4 18 4 13V6a1 1 0 0 1 .76-.97l8-2a1 1 0 0 1 .48 0l8 2A1 1 0 0 1 20 6z" />
        <line x1="12" y1="9" x2="12" y2="13" />
        <line x1="12" y1="17" x2="12.01" y2="17" />
      </svg>
      <!-- SparklesIcon (indigo) -->
      <svg v-else-if="preset.icon === 'SparklesIcon'" xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z" />
        <path d="m5 3 1 2.5L8.5 6 6 7 5 9.5 4 7 1.5 6 4 5 5 3Z" opacity="0.6" />
        <path d="m19 17 1 2.5 2.5.5-2.5 1-1 2.5-1-2.5-2.5-1 2.5-1 1-2.5Z" opacity="0.6" />
      </svg>
      <!-- UndoIcon (amber) -->
      <svg v-else-if="preset.icon === 'UndoIcon'" xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M3 7v6h6" />
        <path d="M21 17a9 9 0 0 0-9-9 9 9 0 0 0-6 2.3L3 13" />
      </svg>
      <!-- ActivityIcon (purple) -->
      <svg v-else-if="preset.icon === 'ActivityIcon'" xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <polyline points="22 12 18 12 15 21 9 3 6 12 2 12" />
      </svg>
      <!-- MoonIcon (blue) -->
      <svg v-else-if="preset.icon === 'MoonIcon'" xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z" />
      </svg>
      <!-- UtensilsIcon (emerald) -->
      <svg v-else-if="preset.icon === 'UtensilsIcon'" xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2" />
        <line x1="7" y1="2" x2="7" y2="8" />
        <line x1="5" y1="2" x2="5" y2="8" />
        <line x1="9" y1="2" x2="9" y2="8" />
        <path d="M15 2v10a3 3 0 0 0 3 3h3v-2H18c-.6 0-1-.4-1-1V2h-2z" />
        <path d="M19 17v5" />
        <path d="M15 17v5" />
        <path d="M11 17v5" />
      </svg>
      <!-- RefreshCwIcon (orange) -->
      <svg v-else-if="preset.icon === 'RefreshCwIcon'" xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8" />
        <path d="M21 3v5h-5" />
        <path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16" />
        <path d="M3 21v-5h5" />
      </svg>
    </div>

    <!-- Label -->
    <span class="text-xs font-semibold leading-tight">{{ preset.label }}</span>
  </button>
</template>