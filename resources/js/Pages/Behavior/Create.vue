<template>
  <AppLayout>
    <div class="space-y-6">
      <!-- Breadcrumbs / Back button -->
      <div>
        <Link
          :href="route('behavior.index')"
          class="inline-flex items-center text-sm font-medium text-slate-500 hover:text-slate-700"
        >
          <span class="mr-2">&larr;</span> Quay lại theo dõi hành vi
        </Link>
      </div>

      <!-- Header -->
      <div>
        <h1 class="text-3xl font-bold text-slate-900">Ghi nhận sự cố hành vi</h1>
        <p class="mt-2 text-sm text-slate-500">
          Ghi lại sự cố Tiền đề-Hành vi-Hệ quả để giúp theo dõi các mẫu hành vi của trẻ.
          Tất cả các trường có <span class="text-rose-500">*</span> là bắt buộc.
        </p>
      </div>

      <!-- Form Component -->
      <BehaviorForm
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