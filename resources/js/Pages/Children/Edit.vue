<template>
  <AppLayout>
    <div class="space-y-6">
      <!-- Breadcrumbs / Back button -->
      <div>
        <Link :href="`/children/${child.id}`" class="inline-flex items-center text-sm font-medium text-slate-600 hover:text-indigo-600">
          <svg class="mr-1 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
          Quay lại hồ sơ
        </Link>
      </div>

      <div class="bg-white overflow-hidden shadow sm:rounded-lg border border-slate-100 p-6">
        <div class="mb-6">
          <h1 class="text-3xl font-bold leading-tight text-slate-900">Chỉnh sửa hồ sơ trẻ</h1>
          <p class="mt-1 text-sm text-slate-500">Cập nhật hồ sơ can thiệp cho {{ child.full_name }}.</p>
        </div>

        <ChildForm :child="child" />
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import AppLayout from '../../Components/layout/AppLayout.vue';
import ChildForm from '../../Components/children/ChildForm.vue';

defineProps({
  child: {
    type: Object,
    required: true,
  },
});
</script>