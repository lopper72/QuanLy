<template>
  <div class="text-center py-12 px-4 sm:px-6 lg:px-8 border-2 border-dashed border-gray-300 rounded-lg bg-white shadow-sm">
    <div class="mx-auto h-12 w-12 text-gray-400 flex items-center justify-center bg-gray-50 rounded-full mb-4">
      <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V4a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
      </svg>
    </div>
    <h3 class="mt-2 text-sm font-medium text-gray-900 md:text-base">{{ title }}</h3>
    <p class="mt-1 text-xs md:text-sm text-gray-500 max-w-md mx-auto">
      {{ description }}
    </p>
    <div class="mt-6 flex justify-center gap-3">
      <slot />
    </div>
  </div>
</template>

<script setup>
defineProps({
  title: {
    type: String,
    default: 'Không tìm thấy dữ liệu'
  },
  description: {
    type: String,
    default: 'Hãy thử điều chỉnh bộ lọc hoặc tạo mới.'
  }
})
</script>