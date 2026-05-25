<template>
  <AppLayout>
    <div class="space-y-6 max-w-4xl mx-auto">
      <!-- Breadcrumbs & Actions -->
      <div class="flex justify-between items-center">
        <Link
          :href="route('behavior.index')"
          class="inline-flex items-center text-sm font-medium text-slate-500 hover:text-slate-700"
        >
          <span class="mr-2">&larr;</span> Quay lại theo dõi hành vi
        </Link>
        <div class="flex items-center space-x-3">
          <Link
            :href="route('behavior.edit', behaviorLog.id)"
            class="inline-flex items-center px-4 py-2 border border-slate-300 text-sm font-medium rounded-md text-slate-700 bg-white hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
          >
            Chỉnh sửa
          </Link>
          <button
            @click="deleteLog"
            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-rose-600 hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-rose-500"
          >
            Xóa
          </button>
        </div>
      </div>

      <!-- Header Card -->
      <div class="bg-white p-6 rounded-lg shadow-sm border border-slate-100">
        <div class="sm:flex sm:items-center sm:justify-between">
          <div>
            <span
              class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold mb-2"
              :class="getSeverityClass(behaviorLog.severity)"
            >
              {{ getSeverityLabel(behaviorLog.severity) }}
            </span>
            <h1 class="text-2xl font-bold text-slate-900">
              {{ getBehaviorLabel(behaviorLog.behavior_type) }}
            </h1>
            <p class="text-sm text-slate-500 mt-1">
              Cho
              <Link :href="route('children.show', behaviorLog.child.id)" class="font-semibold text-indigo-650 hover:underline">
                {{ behaviorLog.child?.full_name || 'N/A' }}
              </Link>
              được ghi nhận vào {{ formatDate(behaviorLog.recorded_at) }}
            </p>
          </div>
        </div>
      </div>

      <!-- ABC details block -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Antecedent (A) -->
        <div class="bg-white rounded-lg shadow-sm border border-slate-100 overflow-hidden flex flex-col">
          <div class="bg-amber-50 px-4 py-3 border-b border-amber-100 flex items-center justify-between">
            <h3 class="text-sm font-bold text-amber-900">A - Tiền đề</h3>
            <span class="text-xs bg-amber-200 text-amber-800 px-2 py-0.5 rounded font-bold">Tác nhân</span>
          </div>
          <div class="p-5 flex-grow text-sm text-slate-700 leading-relaxed whitespace-pre-wrap">
            {{ behaviorLog.trigger || 'Không có tiền đề hoặc tác nhân cụ thể nào được ghi lại.' }}
          </div>
        </div>

        <!-- Behavior (B) -->
        <div class="bg-white rounded-lg shadow-sm border border-slate-100 overflow-hidden flex flex-col">
          <div class="bg-rose-50 px-4 py-3 border-b border-rose-100 flex items-center justify-between">
            <h3 class="text-sm font-bold text-rose-900">B - Hành vi</h3>
            <span class="text-xs bg-rose-200 text-rose-800 px-2 py-0.5 rounded font-bold">Quan sát</span>
          </div>
          <div class="p-5 flex-grow text-sm text-slate-700 leading-relaxed">
            <p class="font-semibold mb-2 text-slate-800">
              {{ getBehaviorLabel(behaviorLog.behavior_type) }}
            </p>
            <p class="whitespace-pre-wrap">{{ behaviorLog.behavior_description || 'Không có mô tả hành vi chi tiết nào được ghi lại.' }}</p>
          </div>
        </div>

        <!-- Consequence (C) -->
        <div class="bg-white rounded-lg shadow-sm border border-slate-100 overflow-hidden flex flex-col">
          <div class="bg-emerald-50 px-4 py-3 border-b border-emerald-100 flex items-center justify-between">
            <h3 class="text-sm font-bold text-emerald-900">C - Hệ quả</h3>
            <span class="text-xs bg-emerald-200 text-emerald-800 px-2 py-0.5 rounded font-bold">Phản hồi</span>
          </div>
          <div class="p-5 flex-grow text-sm text-slate-700 leading-relaxed whitespace-pre-wrap">
            {{ behaviorLog.response || 'Không có can thiệp hoặc phản hồi nào được ghi lại.' }}
          </div>
        </div>
      </div>

      <!-- Additional Details Card -->
      <div class="bg-white rounded-lg shadow-sm border border-slate-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50">
          <h3 class="text-sm font-bold text-slate-700">Thông tin bổ sung</h3>
        </div>
        <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-y-4 gap-x-6 text-sm">
          <div>
            <dt class="font-semibold text-slate-500">Thời lượng</dt>
            <dd class="mt-1 text-slate-900">{{ behaviorLog.duration_minutes ? `${behaviorLog.duration_minutes} phút` : 'Chưa xác định' }}</dd>
          </div>
          <div>
            <dt class="font-semibold text-slate-500">Địa điểm</dt>
            <dd class="mt-1 text-slate-900">{{ behaviorLog.location || 'Chưa xác định' }}</dd>
          </div>
          <div class="sm:col-span-2">
            <dt class="font-semibold text-slate-500">Ghi chú nhân viên / Mục hành động</dt>
            <dd class="mt-1 text-slate-900 bg-slate-50 p-3 rounded border border-slate-100 whitespace-pre-wrap leading-relaxed">
              {{ behaviorLog.notes || 'Không có ghi chú bổ sung.' }}
            </dd>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '../../Components/layout/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';

const props = defineProps({
  behaviorLog: {
    type: Object,
    required: true,
  },
  behaviorTypes: {
    type: Object,
    required: true,
  },
  severities: {
    type: Object,
    required: true,
  },
});

const formatDate = (dateString) => {
  if (!dateString) return '—';
  const date = new Date(dateString);
  return date.toLocaleString('vi-VN', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
};

const getBehaviorLabel = (type) => {
  return props.behaviorTypes[type] || type;
};

const getSeverityLabel = (severity) => {
  return props.severities[severity] || severity;
};

const getSeverityClass = (severity) => {
  switch (severity) {
    case 'low':
      return 'bg-emerald-100 text-emerald-800';
    case 'medium':
      return 'bg-amber-100 text-amber-800';
    case 'high':
      return 'bg-rose-100 text-rose-800';
    default:
      return 'bg-slate-100 text-slate-850';
  }
};

const deleteLog = () => {
  if (confirm('Bạn có chắc chắn muốn xóa sự cố hành vi này? Hành động này không thể hoàn tác.')) {
    router.delete(route('behavior.destroy', props.behaviorLog.id));
  }
};
</script>