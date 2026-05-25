<script setup>
import { behaviorTypeLabels, severityLabels, labelFor } from '@/Lib/labels';

const props = defineProps({
  recentBehaviors: {
    type: Array,
    required: true,
  },
});

const formatTime = (timeString) => {
  if (!timeString) return '';
  const date = new Date(timeString);
  return date.toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' });
};

const formatDate = (timeString) => {
  if (!timeString) return '';
  const date = new Date(timeString);
  return date.toLocaleDateString('vi-VN', { month: 'short', day: 'numeric' });
};

const formatBehaviorType = (type) => {
  return labelFor(behaviorTypeLabels, type);
};

const getSeverityBadgeClass = (severity) => {
  const classes = {
    low: 'bg-green-100 text-green-800 border-green-200',
    medium: 'bg-amber-100 text-amber-800 border-amber-200',
    high: 'bg-red-100 text-red-800 border-red-200',
  };
  return classes[severity] || 'bg-gray-100 text-gray-800 border-gray-200';
};
</script>

<template>
  <div class="bg-white rounded-3xl border border-gray-100 shadow-xl p-5 md:p-6">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-lg font-bold text-gray-800 flex items-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2 text-indigo-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M12 8v4l3 3" />
          <circle cx="12" cy="12" r="9" />
        </svg>
        Lịch sử hoạt động gần đây
      </h3>
      <span class="text-xs font-semibold text-gray-400 bg-gray-50 px-2.5 py-1 rounded-full border border-gray-100">
        10 ghi nhận gần nhất
      </span>
    </div>

    <!-- Empty State -->
    <div v-if="recentBehaviors.length === 0" class="py-8 text-center text-gray-400">
      <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 mx-auto mb-2 text-gray-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
        <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
        <line x1="16" y1="2" x2="16" y2="6" />
        <line x1="8" y1="2" x2="8" y2="6" />
        <line x1="3" y1="10" x2="21" y2="10" />
      </svg>
      <p class="text-sm font-semibold">Chưa có ghi nhận hành vi gần đây</p>
      <p class="text-xs text-gray-400 mt-1">Các ghi nhận trong ngày sẽ hiển thị tại đây.</p>
    </div>

    <!-- Activity Feed -->
    <div v-else class="space-y-3 max-h-[400px] overflow-y-auto pr-1">
      <div
        v-for="log in recentBehaviors"
        :key="log.id"
        class="p-3.5 rounded-2xl border border-gray-50 bg-gray-50/50 flex flex-col sm:flex-row sm:items-center sm:justify-between transition-all hover:bg-gray-50"
      >
        <div class="flex items-start space-x-3">
          <!-- Icon indicator -->
          <div class="mt-0.5 p-2 bg-white rounded-xl border border-gray-100 flex-shrink-0">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-indigo-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
              <polyline points="14 2 14 8 20 8" />
              <line x1="16" y1="13" x2="8" y2="13" />
              <line x1="16" y1="17" x2="8" y2="17" />
              <polyline points="10 9 9 9 8 9" />
            </svg>
          </div>

          <div>
            <div class="flex flex-wrap items-center gap-1.5">
              <span class="font-bold text-gray-800 text-sm">
                {{ formatBehaviorType(log.behavior_type) }}
              </span>
              <span class="text-xs text-gray-400 font-medium">
                của {{ log.child?.full_name || 'Chưa rõ trẻ' }}
              </span>
            </div>
            
            <p v-if="log.note" class="text-xs text-gray-500 mt-1 italic font-medium bg-white px-2.5 py-1 rounded-lg border border-gray-100 inline-block">
              "{{ log.note }}"
            </p>
          </div>
        </div>

        <div class="flex items-center justify-between sm:justify-end space-x-2.5 mt-2 sm:mt-0 pt-2 sm:pt-0 border-t sm:border-t-0 border-gray-100">
          <!-- Severity Badge -->
          <span
            v-if="log.severity"
            class="px-2 py-0.5 rounded-full text-[10px] font-bold border"
            :class="getSeverityBadgeClass(log.severity)"
          >
            {{ labelFor(severityLabels, log.severity) }}
          </span>

          <!-- Time -->
          <span class="text-xs text-gray-400 font-semibold flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 mr-1 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10" />
              <polyline points="12 6 12 12 16 14" />
            </svg>
            {{ formatDate(log.recorded_at) }}, {{ formatTime(log.recorded_at) }}
          </span>
        </div>
      </div>
    </div>
  </div>
</template>
