<template>
  <AppLayout>
    <div class="space-y-6">
      <!-- Breadcrumbs / Back button -->
      <div>
        <Link href="/children" class="inline-flex items-center text-sm font-medium text-slate-600 hover:text-indigo-600">
          <svg class="mr-1 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
          Quay lại danh sách
        </Link>
      </div>

      <div class="bg-white overflow-hidden shadow sm:rounded-lg border border-slate-100 p-6">
        <div class="mb-6">
          <h1 class="text-3xl font-bold leading-tight text-slate-900">Đăng ký trẻ mới</h1>
          <p class="mt-1 text-sm text-slate-500">Tạo hồ sơ can thiệp để bắt đầu quản lý các buổi tập luyện, đánh giá và theo dõi hành vi.</p>
        </div>

        <ChildForm />
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import AppLayout from '../../Components/layout/AppLayout.vue';
import ChildForm from '../../Components/children/ChildForm.vue';
</script>