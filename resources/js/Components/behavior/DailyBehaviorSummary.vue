<script setup>
defineProps({
  dailySummary: {
    type: Object,
    required: true,
  },
});
</script>

<template>
  <div class="bg-white rounded-3xl border border-gray-100 shadow-xl p-5 md:p-6">
    <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
      <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2 text-rose-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M3 3v18h18" />
        <path d="m19 9-5 5-4-4-3 3" />
      </svg>
      Tổng quan hành vi hôm nay
    </h3>

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3.5">
      <!-- Total Card -->
      <div class="bg-indigo-50/50 rounded-2xl border border-indigo-100/50 p-4 text-center">
        <span class="block text-xs font-bold text-indigo-400 mb-1">
          Tổng ghi nhận
        </span>
        <span class="text-3xl font-bold text-indigo-950">
          {{ dailySummary.total }}
        </span>
      </div>

      <!-- Low Severity Card -->
      <div class="bg-green-50/50 rounded-2xl border border-green-100/50 p-4 text-center">
        <span class="block text-xs font-bold text-green-500 mb-1">
          Nhẹ
        </span>
        <span class="text-3xl font-bold text-green-950">
          {{ dailySummary.low }}
        </span>
      </div>

      <!-- Medium Severity Card -->
      <div class="bg-amber-50/50 rounded-2xl border border-amber-100/50 p-4 text-center">
        <span class="block text-xs font-bold text-amber-500 mb-1">
          Trung bình
        </span>
        <span class="text-3xl font-bold text-amber-950">
          {{ dailySummary.medium }}
        </span>
      </div>

      <!-- High Severity Card -->
      <div class="bg-red-50/50 rounded-2xl border border-red-100/50 p-4 text-center">
        <span class="block text-xs font-bold text-red-500 mb-1">
          Cao
        </span>
        <span class="text-3xl font-bold text-red-950">
          {{ dailySummary.high }}
        </span>
      </div>
    </div>
  </div>
</template>
