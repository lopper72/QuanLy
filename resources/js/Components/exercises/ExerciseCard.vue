<template>
  <div class="bg-white rounded-lg border border-slate-200 shadow-sm hover:shadow-md transition-shadow duration-200 flex flex-col justify-between overflow-hidden h-full">
    <!-- Thumbnail -->
    <div class="h-48 w-full overflow-hidden bg-slate-100">
      <ExerciseThumbnail :exercise="exercise" :alt="exercise.title" size="card" />
    </div>

    <!-- Card Header with status badge -->
    <div class="px-5 py-4 border-b border-slate-100 flex justify-between items-start gap-2">
      <span
        :class="[
          exercise.is_active ? 'bg-green-50 text-green-700 border-green-200' : 'bg-slate-50 text-slate-500 border-slate-200',
          'inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold border'
        ]"
      >
        {{ exercise.is_active ? 'Kích hoạt' : 'Chưa kích hoạt' }}
      </span>
      <span class="text-xs text-slate-400">#{{ exercise.id }}</span>
    </div>

    <!-- Card Body -->
    <div class="px-5 py-4 flex-grow space-y-3">
      <h3 class="text-lg font-bold text-slate-800 line-clamp-1">
        {{ exercise.title }}
      </h3>
      
      <!-- Meta badges -->
      <div class="flex flex-wrap gap-2">
        <!-- Category Badge -->
        <span
          :class="[
            categoryColor(exercise.category),
            'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border'
          ]"
        >
          {{ getCategoryLabel(exercise.category) }}
        </span>

        <!-- Difficulty Badge -->
        <span
          v-if="exercise.difficulty"
          :class="[
            difficultyColor(exercise.difficulty),
            'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border'
          ]"
        >
          {{ getDifficultyLabel(exercise.difficulty) }}
        </span>

        <!-- Time Badge -->
        <span
          v-if="exercise.estimated_minutes"
          class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-700 border border-slate-200"
        >
          <svg class="w-3.5 h-3.5 mr-1 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          {{ exercise.estimated_minutes }} phút
        </span>
      </div>

      <p class="text-sm text-slate-600 line-clamp-3">
        {{ exercise.instructions || 'Không có hướng dẫn.' }}
      </p>
    </div>

    <!-- Card Footer -->
    <div class="px-5 py-3.5 bg-slate-50 border-t border-slate-100 flex justify-between gap-2">
      <Link
        :href="`/exercises/${exercise.id}`"
        class="inline-flex items-center text-sm font-semibold text-indigo-600 hover:text-indigo-900 focus:outline-none"
      >
        Xem chi tiết
      </Link>
      <Link
        :href="`/exercises/${exercise.id}/edit`"
        class="inline-flex items-center text-sm font-semibold text-slate-600 hover:text-slate-900 focus:outline-none"
      >
        Chỉnh sửa
      </Link>
    </div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import ExerciseThumbnail from './ExerciseThumbnail.vue';

const props = defineProps({
  exercise: {
    type: Object,
    required: true,
  },
  categories: {
    type: Object,
    default: () => ({}),
  },
  difficulties: {
    type: Object,
    default: () => ({}),
  },
});

const defaultCategories = {
  gross_motor: 'Vận động thô',
  fine_motor: 'Vận động tinh',
  sensory: 'Giác quan',
  communication: 'Giao tiếp',
  cognitive: 'Nhận thức',
  social: 'Xã hội',
  self_care: 'Tự chăm sóc',
};

const defaultDifficulties = {
  easy: 'Dễ',
  medium: 'Trung bình',
  hard: 'Khó',
};

const getCategoryLabel = (cat) => {
  return props.categories[cat] || defaultCategories[cat] || cat;
};

const getDifficultyLabel = (diff) => {
  return props.difficulties[diff] || defaultDifficulties[diff] || diff;
};

const categoryColor = (cat) => {
  const colors = {
    gross_motor: 'bg-blue-50 text-blue-700 border-blue-200',
    fine_motor: 'bg-emerald-50 text-emerald-700 border-emerald-200',
    sensory: 'bg-purple-50 text-purple-700 border-purple-200',
    communication: 'bg-amber-50 text-amber-700 border-amber-200',
    cognitive: 'bg-pink-50 text-pink-700 border-pink-200',
    social: 'bg-sky-50 text-sky-700 border-sky-200',
    self_care: 'bg-teal-50 text-teal-700 border-teal-200',
  };
  return colors[cat] || 'bg-slate-50 text-slate-700 border-slate-200';
};

const difficultyColor = (diff) => {
  const colors = {
    easy: 'bg-green-50 text-green-700 border-green-200',
    medium: 'bg-yellow-50 text-yellow-700 border-yellow-200',
    hard: 'bg-red-50 text-red-700 border-red-200',
  };
  return colors[diff] || 'bg-slate-50 text-slate-700 border-slate-200';
};
</script>
