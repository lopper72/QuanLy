<template>
  <div>
    <EmptyState
      v-if="behaviorLogs.length === 0"
      title="Không tìm thấy ghi nhận hành vi"
      description="Không có ghi nhận hành vi nào phù hợp với bộ lọc. Hãy bắt đầu bằng cách ghi nhận một hành vi."
    >
      <template #icon>
        <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
      </template>
      <template #action>
        <Link
          :href="route('behavior.create')"
          class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-semibold rounded-md text-white bg-indigo-600 hover:bg-indigo-700 transition duration-150 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
        >
          Ghi nhận hành vi
        </Link>
      </template>
    </EmptyState>

    <div v-else class="bg-white rounded-lg shadow-sm border border-slate-100 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200">
          <thead class="bg-slate-50">
            <tr>
              <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500">Ngày giờ</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500">Trẻ</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500">Loại hành vi</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500">Mức độ</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500">Yếu tố kích hoạt</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500">Cách hỗ trợ</th>
              <th scope="col" class="relative px-6 py-3">
                <span class="sr-only">Thao tác</span>
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-slate-200">
            <tr v-for="log in behaviorLogs" :key="log.id" class="hover:bg-slate-50 transition-colors">
              <!-- Date & Time -->
              <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900 font-medium">
                {{ formatDate(log.recorded_at) }}
              </td>
              <!-- Child -->
              <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                <Link :href="route('children.show', log.child.id)" class="text-indigo-600 hover:text-indigo-900 hover:underline">
                  {{ log.child?.full_name || 'Chưa có' }}
                </Link>
              </td>
              <!-- Type -->
              <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                {{ getBehaviorLabel(log.behavior_type) }}
              </td>
              <!-- Severity -->
              <td class="px-6 py-4 whitespace-nowrap text-sm">
                <span
                  class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium"
                  :class="getSeverityClass(log.severity)"
                >
                  {{ getSeverityLabel(log.severity) }}
                </span>
              </td>
              <!-- Trigger -->
              <td class="px-6 py-4 text-sm text-slate-500 max-w-xs truncate" :title="log.trigger">
                {{ log.trigger || '—' }}
              </td>
              <!-- Response -->
              <td class="px-6 py-4 text-sm text-slate-500 max-w-xs truncate" :title="log.response">
                {{ log.response || '—' }}
              </td>
              <!-- Actions -->
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-3">
                <Link :href="route('behavior.show', log.id)" class="text-slate-600 hover:text-indigo-650">
                  Xem
                </Link>
                <Link :href="route('behavior.edit', log.id)" class="text-indigo-600 hover:text-indigo-900">
                  Sửa
                </Link>
                <button @click="deleteLog(log.id)" class="text-rose-600 hover:text-rose-900 bg-none border-none">
                  Xóa
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3';
import EmptyState from '../ui/EmptyState.vue';

const props = defineProps({
  behaviorLogs: {
    type: Array,
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
  return date.toLocaleString([], {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
};

const getBehaviorLabel = (type) => {
  return props.behaviorTypes[type] || type;
};

const getSeverityLabel = (severity) => {
  return props.severities[severity] || severity || 'Chưa có';
};

const getSeverityClass = (severity) => {
  switch (severity) {
    case 'low':
      return 'bg-emerald-50 text-emerald-700 border border-emerald-250';
    case 'medium':
      return 'bg-amber-50 text-amber-700 border border-amber-250';
    case 'high':
      return 'bg-rose-50 text-rose-700 border border-rose-250';
    default:
      return 'bg-slate-50 text-slate-700 border border-slate-200';
  }
};

const deleteLog = (id) => {
  if (confirm('Bạn có chắc chắn muốn xóa ghi nhận hành vi này? Hành động này không thể hoàn tác.')) {
    router.delete(route('behavior.destroy', id));
  }
};
</script>
