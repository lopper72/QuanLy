<template>
  <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-4 mb-6">
    <div class="flex items-center justify-between mb-3 md:mb-4">
      <div class="flex items-center gap-2">
        <svg class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 8.293A1 1 0 013 7.586V4z" />
        </svg>
        <span class="text-sm font-bold text-gray-700">Bộ lọc</span>
      </div>
      <button 
        v-if="hasActiveFilters" 
        @click="$emit('clear')" 
        class="text-xs text-indigo-600 hover:text-indigo-800 font-medium focus:outline-none focus:underline"
      >
        Xóa tất cả
      </button>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
      <slot />
    </div>
  </div>
</template>

<script setup>
defineProps({
  hasActiveFilters: {
    type: Boolean,
    default: false
  }
})

defineEmits(['clear'])
</script>
