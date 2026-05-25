<template>
  <article class="flex h-full min-h-[430px] flex-col overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm transition hover:border-indigo-200 hover:shadow-md">
    <div class="h-36 w-full shrink-0 bg-slate-100 sm:h-40">
      <ExerciseThumbnail :exercise="exercise" :alt="exercise.title" size="card" />
    </div>

    <div class="flex min-h-0 flex-1 flex-col p-4">
      <div class="mb-3 flex flex-wrap items-center gap-2">
        <span :class="['rounded-full border px-2.5 py-1 text-xs font-medium leading-none', categoryColor(exercise.category)]">
          {{ labelFor(categoryLabels, exercise.category) }}
        </span>
        <span v-if="exercise.difficulty" :class="['rounded-full border px-2.5 py-1 text-xs font-medium leading-none', difficultyColor(exercise.difficulty)]">
          {{ labelFor(difficultyLabels, exercise.difficulty) }}
        </span>
        <span v-if="exercise.estimated_minutes" class="rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs font-medium leading-none text-slate-700">
          {{ exercise.estimated_minutes }} phút
        </span>
      </div>

      <h3 class="line-clamp-2 min-h-[48px] text-base font-semibold leading-6 text-slate-950">
        {{ exercise.title }}
      </h3>

      <p class="line-clamp-2 mt-2 min-h-[44px] text-sm leading-5 text-slate-600">
        {{ compactSummary }}
      </p>

      <div class="mt-3 min-h-[44px] rounded-md border border-emerald-100 bg-emerald-50 px-3 py-2">
        <p class="line-clamp-2 text-sm leading-5 text-emerald-900">
          <span class="font-medium">Lợi ích:</span>
          {{ quickBenefit }}
        </p>
      </div>

      <div class="mt-auto flex flex-col gap-2 border-t border-slate-100 pt-4 sm:flex-row sm:items-center sm:justify-between">
        <Link
          :href="`/exercises/${exercise.id}`"
          class="inline-flex min-h-10 items-center justify-center rounded-md bg-indigo-600 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
        >
          Xem chi tiết
        </Link>
        <Link
          :href="`/exercises/${exercise.id}/edit`"
          class="inline-flex min-h-10 items-center justify-center rounded-md border border-slate-300 bg-white px-3 py-2 text-center text-sm font-medium text-slate-700 shadow-sm transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
        >
          Chỉnh sửa
        </Link>
      </div>
    </div>
  </article>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { categoryLabels, difficultyLabels, labelFor } from '@/Lib/labels';
import ExerciseThumbnail from './ExerciseThumbnail.vue';

const props = defineProps({
  exercise: {
    type: Object,
    required: true,
  },
});

const compactSummary = computed(() => (
  props.exercise.description ||
  props.exercise.instructions ||
  'Bài tập đang được cập nhật mô tả ngắn.'
));

const quickBenefit = computed(() => (
  props.exercise.expected_benefits ||
  props.exercise.weekly_expectation ||
  'Giúp bé luyện kỹ năng nền tảng và tăng khả năng hợp tác.'
));

const categoryColor = (category) => ({
  gross_motor: 'bg-blue-50 text-blue-700 border-blue-200',
  fine_motor: 'bg-emerald-50 text-emerald-700 border-emerald-200',
  communication: 'bg-amber-50 text-amber-700 border-amber-200',
  cognitive: 'bg-pink-50 text-pink-700 border-pink-200',
  sensory: 'bg-violet-50 text-violet-700 border-violet-200',
  social: 'bg-sky-50 text-sky-700 border-sky-200',
  self_care: 'bg-teal-50 text-teal-700 border-teal-200',
}[category] || 'bg-slate-50 text-slate-700 border-slate-200');

const difficultyColor = (difficulty) => ({
  easy: 'bg-green-50 text-green-700 border-green-200',
  medium: 'bg-yellow-50 text-yellow-700 border-yellow-200',
  hard: 'bg-red-50 text-red-700 border-red-200',
}[difficulty] || 'bg-slate-50 text-slate-700 border-slate-200');
</script>
