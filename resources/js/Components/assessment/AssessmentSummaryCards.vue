<template>
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-4 mb-6">
    <!-- Total Assessments -->
    <div class="bg-white p-4 rounded-lg shadow border border-gray-100 flex flex-col justify-between">
      <span class="text-xs font-semibold text-gray-500">Tổng đánh giá</span>
      <span class="text-2xl font-bold text-gray-900 mt-2">{{ summary.total_assessments }}</span>
    </div>

    <!-- Avg Score -->
    <div class="bg-white p-4 rounded-lg shadow border border-gray-100 flex flex-col justify-between">
      <span class="text-xs font-semibold text-gray-500">Điểm trung bình</span>
      <span class="text-2xl font-bold text-indigo-600 mt-2">{{ summary.average_score }}%</span>
    </div>

    <!-- Emerging -->
    <div class="bg-white p-4 rounded-lg shadow border border-gray-100 flex flex-col justify-between">
      <span class="text-xs font-semibold text-yellow-600">Kỹ năng mới xuất hiện</span>
      <span class="text-2xl font-bold text-yellow-600 mt-2">{{ summary.level_counts?.emerging || 0 }}</span>
    </div>

    <!-- Developing -->
    <div class="bg-white p-4 rounded-lg shadow border border-gray-100 flex flex-col justify-between">
      <span class="text-xs font-semibold text-blue-600">Kỹ năng đang phát triển</span>
      <span class="text-2xl font-bold text-blue-600 mt-2">{{ summary.level_counts?.developing || 0 }}</span>
    </div>

    <!-- Achieved -->
    <div class="bg-white p-4 rounded-lg shadow border border-gray-100 flex flex-col justify-between">
      <span class="text-xs font-semibold text-green-600">Kỹ năng đã đạt</span>
      <span class="text-2xl font-bold text-green-600 mt-2">{{ summary.level_counts?.achieved || 0 }}</span>
    </div>

    <!-- Regression -->
    <div class="bg-white p-4 rounded-lg shadow border border-gray-100 flex flex-col justify-between">
      <span class="text-xs font-semibold text-red-600">Kỹ năng giảm sút</span>
      <span class="text-2xl font-bold text-red-600 mt-2">{{ summary.level_counts?.regression || 0 }}</span>
    </div>
  </div>
</template>

<script setup>
defineProps({
  summary: {
    type: Object,
    required: true,
  },
});
</script>
