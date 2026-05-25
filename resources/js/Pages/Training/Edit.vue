<template>
  <AppLayout>
    <div class="max-w-7xl mx-auto space-y-6">
      <!-- Breadcrumb / Back button -->
      <div>
        <Link
          :href="route('training.show', session.id)"
          class="text-xs font-semibold text-indigo-600 hover:underline flex items-center space-x-1"
        >
          <span>← Quay lại chi tiết</span>
        </Link>
        <h1 class="text-3xl font-bold leading-tight text-gray-900 mt-2">
          Chỉnh sửa buổi tập
        </h1>
        <p class="text-sm text-gray-500">
          Sửa đổi kế hoạch, ngày tập, ghi chú chung, hoặc sắp xếp lại, thêm và xóa bài tập.
        </p>
      </div>

      <!-- Training Session Form -->
      <TrainingSessionForm
        :session="session"
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
  session: {
    type: Object,
    required: true,
  },
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