<template>
  <div>
    <!-- Empty State -->
    <EmptyState
      v-if="children.length === 0"
      title="Chưa có hồ sơ trẻ"
      :description="searchActive ? 'Không có trẻ nào phù hợp với tiêu chí tìm kiếm.' : 'Bắt đầu bằng cách thêm hồ sơ trẻ mới vào hệ thống.'"
    >
      <template #icon>
        <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
        </svg>
      </template>
      <template #action v-if="!searchActive">
        <Link
          href="/children/create"
          class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-semibold rounded-md text-white bg-indigo-600 hover:bg-indigo-700 transition duration-150 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
        >
          Thêm hồ sơ trẻ
        </Link>
      </template>
    </EmptyState>

    <!-- Children Grid -->
    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <ChildCard
        v-for="child in children"
        :key="child.id"
        :child="child"
      />
    </div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import ChildCard from './ChildCard.vue';
import EmptyState from '../ui/EmptyState.vue';

defineProps({
  children: {
    type: Array,
    required: true,
  },
  searchActive: {
    type: Boolean,
    default: false,
  },
});
</script>
