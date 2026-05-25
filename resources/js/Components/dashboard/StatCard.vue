<script setup>
defineProps({
  title: {
    type: String,
    required: true
  },
  value: {
    type: [String, Number],
    required: true
  },
  description: {
    type: String,
    default: ''
  },
  iconClass: {
    type: String,
    default: 'text-indigo-600 bg-indigo-50'
  }
})
</script>

<template>
  <div class="overflow-hidden rounded-xl bg-white border border-gray-100 p-6 shadow-sm transition-all duration-200 hover:shadow-md hover:border-gray-200">
    <div class="flex items-center justify-between">
      <div>
        <p class="text-xs font-semibold text-gray-500">{{ title }}</p>
        <h4 class="mt-2 text-3xl font-bold text-gray-900">{{ value }}</h4>
      </div>
      <div :class="['p-3 rounded-lg flex items-center justify-center', iconClass]">
        <slot name="icon">
          <!-- Default generic icon -->
          <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 002 2h2a2 2 0 002-2" />
          </svg>
        </slot>
      </div>
    </div>
    <div v-if="description" class="mt-4 flex items-center text-sm text-gray-600">
      <span>{{ description }}</span>
    </div>
  </div>
</template>