<template>
  <div class="bg-white border border-gray-200 shadow rounded-lg p-5 hover:shadow-md transition-shadow">
    <div class="flex items-start justify-between">
      <div class="flex items-center space-x-3">
        <!-- Child Avatar / initials -->
        <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold text-sm">
          {{ childInitials }}
        </div>
        <div>
          <h3 class="text-sm font-semibold text-gray-900">
            {{ session.child?.first_name }} {{ session.child?.last_name }}
          </h3>
          <p class="text-xs text-gray-500">
            Date: {{ formatDate(session.session_date) }}
            <span v-if="session.scheduled_time" class="ml-1 font-medium text-indigo-600">
              • {{ session.scheduled_time.substring(0, 5) }}
            </span>
          </p>
        </div>
      </div>
      
      <TrainingStatusBadge :status="session.status" />
    </div>

    <div class="mt-4 grid grid-cols-2 gap-4 border-t border-b border-gray-100 py-3">
      <div>
        <p class="text-xs text-gray-500">Bài tập</p>
        <p class="text-sm font-semibold text-gray-900">
          {{ session.items?.length || 0 }} items
        </p>
      </div>
      <div>
        <p class="text-xs text-gray-500">Tổng thời lượng</p>
        <p class="text-sm font-semibold text-gray-900">
          {{ computedDuration }} phút
        </p>
      </div>
    </div>

    <div v-if="session.notes" class="mt-3">
      <p class="text-xs text-gray-500">Ghi chú</p>
      <p class="text-xs text-gray-700 line-clamp-2 italic">
        "{{ session.notes }}"
      </p>
    </div>

    <div class="mt-4 flex items-center justify-end space-x-2">
      <Link
        :href="route('training.show', session.id)"
        class="inline-flex items-center px-3 py-1.5 border border-gray-300 rounded-md text-xs font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
      >
        Xem
      </Link>
      <Link
        :href="route('training.edit', session.id)"
        class="inline-flex items-center px-3 py-1.5 border border-transparent rounded-md text-xs font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
      >
        Sửa
      </Link>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import TrainingStatusBadge from './TrainingStatusBadge.vue';

const props = defineProps({
  session: {
    type: Object,
    required: true,
  },
});

const childInitials = computed(() => {
  if (!props.session.child) return 'C';
  const f = props.session.child.first_name?.charAt(0) || '';
  const l = props.session.child.last_name?.charAt(0) || '';
  return (f + l).toUpperCase();
});

const computedDuration = computed(() => {
  if (props.session.total_minutes) return props.session.total_minutes;
  if (!props.session.items) return 0;
  return props.session.items.reduce((sum, item) => sum + (item.duration_minutes || 0), 0);
});

function formatDate(dateStr) {
  if (!dateStr) return '';
  const d = new Date(dateStr);
  return d.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
}
</script>
