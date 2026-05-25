<template>
  <AppLayout>
    <div class="max-w-7xl mx-auto space-y-6">
      <!-- Breadcrumb / Back button -->
      <div>
        <Link
          :href="route('training.index')"
          class="text-xs font-semibold text-indigo-600 hover:underline flex items-center space-x-1"
        >
          <span>← Quay lại danh sách buổi tập</span>
        </Link>
        <h1 class="text-3xl font-bold leading-tight text-gray-900 mt-2">
          Tạo buổi tập mới
        </h1>
        <p class="text-sm text-gray-500">
          Lên lịch và sắp xếp chuỗi bài tập kỹ năng hằng ngày cho trẻ.
        </p>
      </div>

      <!-- Training Session Form -->
      <TrainingSessionForm
        :children="children"
        :exercises="exercises"
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
});
</script>
