<template>
  <AppLayout>
    <div class="space-y-6">
      <!-- Page Header -->
      <div class="flex items-center space-x-2">
        <Link :href="`/exercises/${exercise.id}`" class="text-sm font-medium text-indigo-600 hover:text-indigo-900 flex items-center">
          <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          Quay lại chi tiết bài tập
        </Link>
      </div>

      <div class="bg-white overflow-hidden shadow sm:rounded-lg border border-slate-100 p-6 space-y-6">
        <div>
          <h1 class="text-3xl font-bold leading-tight text-slate-900">Chỉnh sửa bài tập</h1>
          <p class="mt-1 text-sm text-slate-500">Cập nhật cấu hình, trạng thái hoặc hướng dẫn cho bài tập này.</p>
        </div>

        <ExerciseForm
          :exercise="exercise"
          :categories="categories"
          :difficulties="difficulties"
        />
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import AppLayout from '../../Components/layout/AppLayout.vue';
import ExerciseForm from '../../Components/exercises/ExerciseForm.vue';

defineProps({
  exercise: {
    type: Object,
    required: true,
  },
  categories: {
    type: Object,
    required: true,
  },
  difficulties: {
    type: Object,
    required: true,
  },
});
</script>