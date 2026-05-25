<template>
  <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
    <!-- Total Incidents -->
    <div class="bg-white p-4 rounded-lg shadow-sm border border-slate-100 flex flex-col justify-between">
      <div>
        <p class="text-xs font-semibold text-slate-500">Tổng ghi nhận</p>
        <h3 class="text-2xl font-bold text-slate-800 mt-1">{{ summary.total_incidents }}</h3>
      </div>
      <div class="mt-2 text-xs text-slate-400">
        Tất cả hành vi đã theo dõi
      </div>
    </div>

    <!-- Low Severity -->
    <div class="bg-white p-4 rounded-lg shadow-sm border border-slate-100 flex flex-col justify-between">
      <div>
        <p class="text-xs font-semibold text-emerald-600">Mức độ nhẹ</p>
        <h3 class="text-2xl font-bold text-emerald-700 mt-1">{{ summary.low_count }}</h3>
      </div>
      <div class="mt-2 text-xs text-slate-400">
        Sự kiện nhẹ
      </div>
    </div>

    <!-- Medium Severity -->
    <div class="bg-white p-4 rounded-lg shadow-sm border border-slate-100 flex flex-col justify-between">
      <div>
        <p class="text-xs font-semibold text-amber-600">Mức độ trung bình</p>
        <h3 class="text-2xl font-bold text-amber-700 mt-1">{{ summary.medium_count }}</h3>
      </div>
      <div class="mt-2 text-xs text-slate-400">
        Sự kiện trung bình
      </div>
    </div>

    <!-- High Severity -->
    <div class="bg-white p-4 rounded-lg shadow-sm border border-slate-100 flex flex-col justify-between">
      <div>
        <p class="text-xs font-semibold text-rose-600">Mức độ cao</p>
        <h3 class="text-2xl font-bold text-rose-700 mt-1">{{ summary.high_count }}</h3>
      </div>
      <div class="mt-2 text-xs text-slate-400">
        Sự kiện nghiêm trọng
      </div>
    </div>

    <!-- Most Frequent Type -->
    <div class="bg-white p-4 rounded-lg shadow-sm border border-slate-100 flex flex-col justify-between col-span-2 sm:col-span-1">
      <div>
        <p class="text-xs font-semibold text-indigo-600">Thường gặp nhất</p>
        <h3 class="text-sm font-bold text-indigo-900 mt-1 truncate" :title="getFrequentLabel">
          {{ getFrequentLabel }}
        </h3>
      </div>
      <div class="mt-2 text-xs text-slate-400">
        Loại yếu tố kích hoạt phổ biến
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  summary: {
    type: Object,
    required: true,
  },
  behaviorTypes: {
    type: Object,
    required: true,
  },
});

const getFrequentLabel = computed(() => {
  const typeKey = props.summary.most_frequent_type;
  if (!typeKey || typeKey === 'N/A') return 'Chưa có';
  return props.behaviorTypes[typeKey] || typeKey;
});
</script>
