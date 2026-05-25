<template>
  <div>
    <!-- Empty State -->
    <EmptyState
      v-if="exercises.length === 0"
      title="Không tìm thấy bài tập"
      :description="searchActive ? 'Không có bài tập nào phù hợp với tiêu chí tìm kiếm của bạn.' : 'Bắt đầu bằng cách tạo một bài tập mới trong thư viện.'"
    >
      <template #icon>
        <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
        </svg>
      </template>
      <template #action v-if="!searchActive">
        <Link
          href="/exercises/create"
          class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-semibold rounded-md text-white bg-indigo-600 hover:bg-indigo-700 transition duration-150 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
        >
          Tạo bài tập mới
        </Link>
      </template>
    </EmptyState>

    <!-- Exercises Grid -->
    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <ExerciseCard
        v-for="exercise in exercises"
        :key="exercise.id"
        :exercise="exercise"
        :categories="categories"
        :difficulties="difficulties"
      />
    </div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import ExerciseCard from './ExerciseCard.vue';
import EmptyState from '../ui/EmptyState.vue';

defineProps({
  exercises: {
    type: Array,
    required: true,
  },
  searchActive: {
    type: Boolean,
    default: false,
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
</script>