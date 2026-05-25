<template>
  <AppLayout>
    <div class="bg-gray-50/50 min-h-screen py-6">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Ghi nhận đánh giá</h1>
          <p class="text-sm text-gray-500 mt-1">
            Thực hiện kiểm tra các cột mốc phát triển và năng lực kỹ năng cho trẻ.
          </p>
        </div>

        <AssessmentForm
          :children="children"
          :default-items="defaultItems"
          :skill-types="skillTypes"
          :levels="levels"
        />
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '../../Components/layout/AppLayout.vue';
import AssessmentForm from '../../Components/assessment/AssessmentForm.vue';

defineProps({
  children: Array,
  defaultItems: Array,
  skillTypes: Object,
  levels: Object,
});
</script>