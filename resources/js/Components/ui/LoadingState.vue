<template>
  <div class="flex flex-col items-center justify-center p-8 bg-white border border-gray-100 rounded-lg shadow-sm">
    <div class="relative flex items-center justify-center">
      <div class="animate-spin rounded-full h-10 w-10 border-4 border-indigo-100 border-t-indigo-600"></div>
    </div>
    <p v-if="text" class="mt-4 text-sm text-gray-500 font-medium">{{ text }}</p>
  </div>
</template>

<script setup>
defineProps({
  text: {
    type: String,
    default: 'Đang tải dữ liệu, vui lòng chờ...'
  }
})
</script>
