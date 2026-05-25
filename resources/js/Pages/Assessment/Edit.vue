<template>
  <AppLayout>
    <div class="bg-gray-50/50 min-h-screen py-6">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Chỉnh sửa đánh giá</h1>
          <p class="text-sm text-gray-500 mt-1">
            Cập nhật kiểm tra cột mốc phát triển đã ghi nhận cho {{ assessment.child?.first_name }} {{ assessment.child?.last_name }}.
          </p>
        </div>

        <AssessmentForm
          :assessment="assessment"
          :children="children"
          :skill-types="skillTypes"
          :levels="levels"
          :is-edit="true"
        />
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '../../Components/layout/AppLayout.vue';
import AssessmentForm from '../../Components/assessment/AssessmentForm.vue';

defineProps({
  assessment: Object,
  children: Array,
  skillTypes: Object,
  levels: Object,
});
</script>