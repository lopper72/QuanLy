<script setup>
defineProps({
  stats: {
    type: Object,
    required: true
  }
})
</script>

<template>
  <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
    <div class="flex items-center justify-between border-b border-gray-50 pb-4">
      <div>
        <h3 class="text-lg font-bold text-gray-900">Mức hoàn thành trong tuần</h3>
        <p class="text-xs text-gray-500">Tỷ lệ hoàn thành buổi tập trong 7 ngày qua</p>
      </div>
      <div class="text-right">
        <span class="text-2xl font-bold text-indigo-600">{{ stats.completion_rate }}%</span>
      </div>
    </div>

    <!-- Overall Progress Bar -->
    <div class="mt-4">
      <div class="flex items-center justify-between text-xs font-medium text-gray-500 mb-1">
        <span>Hoàn thành: {{ stats.completed_sessions }} / {{ stats.total_sessions }}</span>
        <span>Tiến độ tổng thể</span>
      </div>
      <div class="h-2.5 w-full rounded-full bg-gray-100">
        <div class="h-2.5 rounded-full bg-indigo-600 transition-all duration-300" :style="{ width: `${stats.completion_rate}%` }"></div>
      </div>
    </div>

    <!-- Daily Visual Breakdown Chart -->
    <div class="mt-6">
      <h4 class="text-xs font-semibold text-gray-400 mb-3">Hoạt động hằng ngày</h4>
      
      <div class="flex items-end justify-between h-28 pt-2 px-1">
        <div v-for="day in stats.daily_breakdown" :key="day.date" class="flex flex-col items-center flex-1 group">
          <!-- Tooltip on hover -->
          <div class="opacity-0 group-hover:opacity-100 absolute mb-14 bg-gray-900 text-white text-[10px] rounded py-1 px-1.5 pointer-events-none transition-opacity duration-200 shadow-md">
            {{ day.completed }}/{{ day.total }} ({{ day.rate }}%)
          </div>

          <!-- Bar container -->
          <div class="w-6 bg-gray-50 rounded-t-md h-20 flex flex-col justify-end overflow-hidden border border-gray-100">
            <!-- Completed portion -->
            <div 
              class="w-full bg-indigo-500 transition-all duration-300 group-hover:bg-indigo-600" 
              :style="{ height: `${day.rate}%` }"
            ></div>
          </div>
          
          <span class="mt-2 text-[10px] font-medium text-gray-500">{{ day.day_name }}</span>
        </div>
      </div>
    </div>
  </div>
</template>
