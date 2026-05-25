<template>
  <div class="flex h-full flex-col overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm transition hover:shadow-md">
    <div class="h-40 w-full bg-slate-100">
      <ExerciseThumbnail :exercise="exercise" :alt="exercise.title" size="card" />
    </div>

    <div class="flex flex-1 flex-col gap-3 p-4">
      <div class="flex flex-wrap items-center gap-2">
        <span :class="['rounded-full border px-2.5 py-1 text-xs font-medium', categoryColor(exercise.category)]">
          {{ labelFor(categoryLabels, exercise.category) }}
        </span>
        <span v-if="exercise.difficulty" :class="['rounded-full border px-2.5 py-1 text-xs font-medium', difficultyColor(exercise.difficulty)]">
          {{ labelFor(difficultyLabels, exercise.difficulty) }}
        </span>
        <span v-if="exercise.estimated_minutes" class="rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs font-medium text-slate-700">
          {{ exercise.estimated_minutes }} phút
        </span>
      </div>

      <div class="space-y-2">
        <h3 class="line-clamp-2 text-base font-semibold text-slate-950">{{ exercise.title }}</h3>
        <p class="line-clamp-3 text-sm leading-6 text-slate-600">
          {{ exercise.description || exercise.instructions || 'Bài tập đang được cập nhật hướng dẫn.' }}
        </p>
      </div>

      <div v-if="exercise.target_skill || exercise.required_tools" class="space-y-2 rounded-md bg-slate-50 p-3 text-sm text-slate-700">
        <p v-if="exercise.target_skill">
          <span class="font-medium">Phù hợp cho mục tiêu:</span>
          {{ labelFor(skillLabels, exercise.target_skill, exercise.target_skill) }}
        </p>
        <p v-if="exercise.required_tools" class="line-clamp-2">
          <span class="font-medium">Dụng cụ:</span>
          {{ exercise.required_tools }}
        </p>
      </div>

      <div v-if="exercise.weekly_expectation" class="rounded-md border border-emerald-100 bg-emerald-50 p-3 text-sm leading-6 text-emerald-900">
        <span class="font-medium">Sau 1 tuần:</span>
        {{ exercise.weekly_expectation }}
      </div>

      <div class="mt-auto flex items-center justify-between border-t border-slate-100 pt-3">
        <Link :href="`/exercises/${exercise.id}`" class="text-sm font-semibold text-indigo-700 hover:text-indigo-900">
          Xem hướng dẫn
        </Link>
        <Link :href="`/exercises/${exercise.id}/edit`" class="text-sm font-medium text-slate-600 hover:text-slate-900">
          Chỉnh sửa
        </Link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import { categoryLabels, difficultyLabels, labelFor, skillLabels } from '@/Lib/labels';
import ExerciseThumbnail from './ExerciseThumbnail.vue';

defineProps({
  exercise: {
    type: Object,
    required: true,
  },
});

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
