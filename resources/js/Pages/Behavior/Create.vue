<template>
  <AppLayout>
    <div class="space-y-6">
      <div>
        <Link
          :href="route('behavior.index')"
          class="inline-flex items-center text-sm font-medium text-slate-500 hover:text-slate-700"
        >
          <span class="mr-2">&larr;</span> Quay lại theo dõi hành vi
        </Link>
      </div>

      <div>
        <h1 class="text-3xl font-bold text-slate-900">Ghi nhận hành vi</h1>
        <p class="mt-2 max-w-3xl text-sm text-slate-500">
          Ghi lại thời điểm, nguyên nhân/kích hoạt và cách xử lý để phụ huynh hoặc chuyên viên theo dõi mẫu hành vi của bé.
          Các trường có <span class="text-rose-500">*</span> là bắt buộc.
        </p>
      </div>

      <BehaviorForm
        :children="children"
        :training-sessions="trainingSessions"
        :selected-training-session="selectedTrainingSession"
        :selected-training-session-item="selectedTrainingSessionItem"
        :behavior-types="behaviorTypes"
        :severities="severities"
      />
    </div>
  </AppLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import AppLayout from '../../Components/layout/AppLayout.vue';
import BehaviorForm from '../../Components/behavior/BehaviorForm.vue';

defineProps({
  children: {
    type: Array,
    required: true,
  },
  trainingSessions: {
    type: Array,
    default: () => [],
  },
  selectedTrainingSession: {
    type: Object,
    default: null,
  },
  selectedTrainingSessionItem: {
    type: Object,
    default: null,
  },
  behaviorTypes: {
    type: Object,
    required: true,
  },
  severities: {
    type: Object,
    required: true,
  },
});
</script>
