<template>
  <AppLayout>
    <div class="space-y-6">
      <!-- Breadcrumbs / Back button -->
      <div>
        <Link
          :href="route('behavior.show', behaviorLog.id)"
          class="inline-flex items-center text-sm font-medium text-slate-500 hover:text-slate-700"
        >
          <span class="mr-2">&larr;</span> Quay lại chi tiết
        </Link>
      </div>

      <!-- Header -->
      <div>
        <h1 class="text-3xl font-bold text-slate-900">Chỉnh sửa sự cố hành vi</h1>
        <p class="mt-2 text-sm text-slate-500">
          Sửa đổi các tham số Tiền đề-Hành vi-Hệ quả đã ghi lại.
          Tất cả các trường có <span class="text-rose-500">*</span> là bắt buộc.
        </p>
      </div>

      <!-- Form Component -->
      <BehaviorForm
        :behavior-log="behaviorLog"
        :children="children"
        :behavior-types="behaviorTypes"
        :severities="severities"
      />
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '../../Components/layout/AppLayout.vue';
import BehaviorForm from '../../Components/behavior/BehaviorForm.vue';
import { Link } from '@inertiajs/vue3';

defineProps({
  behaviorLog: {
    type: Object,
    required: true,
  },
  children: {
    type: Array,
    required: true,
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