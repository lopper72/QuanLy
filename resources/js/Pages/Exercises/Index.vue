<template>
  <AppLayout>
    <div class="space-y-6">
      <!-- Flash Alert -->
      <div v-if="flash?.success || $page.props.flash?.success" class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 border border-green-200" role="alert">
        <span class="font-medium">Thành công!</span> {{ flash?.success || $page.props.flash?.success }}
      </div>
      <div v-if="flash?.error || $page.props.flash?.error" class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 border border-red-200" role="alert">
        <span class="font-medium">Lỗi!</span> {{ flash?.error || $page.props.flash?.error }}
      </div>

      <!-- Standard Page Header -->
      <PageHeader
        title="Thư viện bài tập"
        description="Khám phá, tìm kiếm và quản lý các bài tập can thiệp tiêu chuẩn."
      >
        <template #actions>
          <Link
            href="/exercises/create"
            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-semibold rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 transition duration-150 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
          >
            Tạo bài tập mới
          </Link>
        </template>
      </PageHeader>

      <div class="bg-white overflow-hidden shadow sm:rounded-lg border border-slate-100">
        <div class="px-4 py-5 sm:p-6 space-y-6">
          <!-- Filters component -->
          <ExerciseFilters
            :filters="filters"
            :categories="categories"
            :difficulties="difficulties"
            @filter="handleFilter"
          />

          <!-- Exercises List component -->
          <ExerciseList
            :exercises="exercises"
            :search-active="isSearchActive"
            :categories="categories"
            :difficulties="difficulties"
          />
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '../../Components/layout/AppLayout.vue';
import PageHeader from '../../Components/ui/PageHeader.vue';
import ExerciseFilters from '../../Components/exercises/ExerciseFilters.vue';
import ExerciseList from '../../Components/exercises/ExerciseList.vue';

const props = defineProps({
  exercises: {
    type: Array,
    required: true,
  },
  filters: {
    type: Object,
    required: true,
  },
  categories: {
    type: Object,
    required: true,
  },
  difficulties: {
    type: Object,
    required: true,
  },
  flash: {
    type: Object,
    default: () => ({ success: null, error: null }),
  },
});

const isSearchActive = computed(() => {
  return !!(props.filters.search || props.filters.category || props.filters.difficulty || props.filters.is_active);
});

const handleFilter = (newFilters) => {
  router.get('/exercises', newFilters, {
    preserveState: true,
    replace: true,
  });
};
</script>