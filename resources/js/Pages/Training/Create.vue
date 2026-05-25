<template>
  <AppLayout>
    <div class="mx-auto max-w-7xl space-y-6">
      <div>
        <Link
          :href="route('training.index')"
          class="flex items-center space-x-1 text-xs font-semibold text-indigo-600 hover:underline"
        >
          <span>← Quay lại danh sách buổi tập</span>
        </Link>
        <h1 class="mt-2 text-3xl font-bold leading-tight text-gray-900">
          Tạo buổi tập mới
        </h1>
        <p class="text-sm text-gray-500">
          Chọn combo bài tập hoặc thêm từng bài riêng lẻ để lập lịch can thiệp cho bé.
        </p>
      </div>

      <TrainingSessionForm
        :children="children"
        :exercises="exercises"
        :exercise-combos="exerciseCombos"
      />
    </div>
  </AppLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import AppLayout from '../../Components/layout/AppLayout.vue';
import TrainingSessionForm from '../../Components/training/TrainingSessionForm.vue';

defineProps({
  children: {
    type: Array,
    required: true,
  },
  exercises: {
    type: Array,
    required: true,
  },
  exerciseCombos: {
    type: Array,
    default: () => [],
  },
});
</script>
