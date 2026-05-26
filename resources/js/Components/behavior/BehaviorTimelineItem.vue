<template>
  <div class="relative pl-6">
    <div class="absolute left-0 top-2 h-3 w-3 rounded-full border-2 border-white bg-indigo-500 ring-2 ring-indigo-100"></div>
    <div class="rounded-md border border-slate-100 bg-slate-50 p-4">
      <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
        <div>
          <p class="text-sm font-semibold text-slate-900">{{ behaviorLabel }}</p>
          <p class="text-xs text-slate-500">{{ formattedDate }}</p>
        </div>
        <span class="inline-flex w-fit items-center rounded-full px-2 py-0.5 text-xs font-medium" :class="severityClass">
          {{ severityLabel }}
        </span>
      </div>

      <dl class="mt-3 grid grid-cols-1 gap-3 text-sm md:grid-cols-3">
        <div v-if="log.training_session">
          <dt class="text-xs font-semibold text-slate-400">Liên quan buổi tập</dt>
          <dd class="mt-1 text-slate-700">{{ formatTrainingSession(log.training_session) }}</dd>
        </div>
        <div v-if="log.training_session_item?.exercise">
          <dt class="text-xs font-semibold text-slate-400">Bài tập</dt>
          <dd class="mt-1 text-slate-700">{{ log.training_session_item.exercise.title }}</dd>
        </div>
        <div v-if="log.trigger">
          <dt class="text-xs font-semibold text-slate-400">Nguyên nhân/kích hoạt</dt>
          <dd class="mt-1 text-slate-700">{{ log.trigger }}</dd>
        </div>
        <div v-if="log.response">
          <dt class="text-xs font-semibold text-slate-400">Cách xử lý</dt>
          <dd class="mt-1 text-slate-700">{{ log.response }}</dd>
        </div>
        <div v-if="log.note">
          <dt class="text-xs font-semibold text-slate-400">Ghi chú</dt>
          <dd class="mt-1 text-slate-700">{{ log.note }}</dd>
        </div>
      </dl>

      <div class="mt-3 flex justify-end gap-3 text-sm">
        <Link :href="route('behavior.show', log.id)" class="font-medium text-slate-600 hover:text-indigo-700">Xem lịch sử</Link>
        <Link :href="route('behavior.edit', log.id)" class="font-medium text-indigo-600 hover:text-indigo-800">Sửa</Link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { behaviorTypeLabels, severityLabels, labelFor } from '@/Lib/labels';

const props = defineProps({
  log: {
    type: Object,
    required: true,
  },
});

const behaviorLabel = computed(() => labelFor(behaviorTypeLabels, props.log.behavior_type));
const severityLabel = computed(() => labelFor(severityLabels, props.log.severity, 'Chưa xác định'));

const formattedDate = computed(() => {
  if (!props.log.recorded_at) return 'Chưa xác định';
  return new Date(props.log.recorded_at).toLocaleString('vi-VN', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
});

const severityClass = computed(() => {
  const classes = {
    low: 'bg-emerald-50 text-emerald-700 border border-emerald-200',
    medium: 'bg-amber-50 text-amber-700 border border-amber-200',
    high: 'bg-rose-50 text-rose-700 border border-rose-200',
  };

  return classes[props.log.severity] || 'bg-slate-50 text-slate-700 border border-slate-200';
});

function formatTrainingSession(session) {
  const date = session.session_date
    ? new Date(session.session_date).toLocaleDateString('vi-VN')
    : 'chưa xác định';
  const time = session.scheduled_time ? ` ${String(session.scheduled_time).slice(0, 5)}` : '';

  return `${date}${time}`;
}
</script>
