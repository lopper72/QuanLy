<template>
  <AppLayout>
    <div class="space-y-6">
      <div v-if="flash?.success || $page.props.flash?.success" class="rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-800" role="alert">
        <span class="font-medium">Thành công!</span> {{ flash?.success || $page.props.flash?.success }}
      </div>
      <div v-if="flash?.error || $page.props.flash?.error" class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800" role="alert">
        <span class="font-medium">Lỗi!</span> {{ flash?.error || $page.props.flash?.error }}
      </div>

      <PageHeader
        title="Chương trình can thiệp"
        description="Thư viện bài tập được nhóm theo mục tiêu phát triển, có combo gợi ý và lịch tập mẫu để phụ huynh dễ thực hành tại nhà."
      >
        <template #actions>
          <Link
            href="/exercises/create"
            class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
          >
            Tạo bài tập mới
          </Link>
        </template>
      </PageHeader>

      <div class="grid grid-cols-1 gap-6 xl:grid-cols-[220px_1fr]">
        <aside class="xl:sticky xl:top-6 xl:self-start">
          <nav class="rounded-lg border border-slate-200 bg-white p-3 shadow-sm">
            <p class="mb-2 px-2 text-sm font-semibold text-slate-900">Nhóm bài tập</p>
            <a
              v-for="group in groupedExercises"
              :key="group.key"
              :href="`#category-${group.key}`"
              class="flex items-center justify-between rounded-md px-2 py-2 text-sm text-slate-700 hover:bg-slate-50"
            >
              <span>{{ group.label }}</span>
              <span class="text-xs text-slate-400">{{ group.count }}</span>
            </a>
          </nav>
        </aside>

        <main class="space-y-6">
          <ExerciseFilters
            :filters="filters"
            :categories="categories"
            :difficulties="difficulties"
            @filter="handleFilter"
          />

          <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
              <p class="text-sm text-slate-500">Tổng bài tập</p>
              <p class="mt-1 text-2xl font-bold text-slate-950">{{ exercises.length }}</p>
            </div>
            <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
              <p class="text-sm text-slate-500">Combo gợi ý</p>
              <p class="mt-1 text-2xl font-bold text-slate-950">{{ combos.length }}</p>
            </div>
            <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
              <p class="text-sm text-slate-500">Lịch mẫu</p>
              <p class="mt-1 text-2xl font-bold text-slate-950">{{ weeklyPlans.length }}</p>
            </div>
          </div>

          <div v-if="exercises.length === 0" class="rounded-lg border border-slate-200 bg-white p-8 text-center shadow-sm">
            <h2 class="text-lg font-semibold text-slate-950">Không tìm thấy bài tập phù hợp</h2>
            <p class="mt-2 text-sm text-slate-600">Hãy đổi từ khóa hoặc xóa bớt bộ lọc để xem thêm bài tập.</p>
          </div>
          <ExerciseCategoryAccordion
            v-else
            :groups="groupedExercises"
            :expand-first="!isSearchActive"
          />

          <ExerciseComboPanel :combos="combos" />
          <WeeklyPlanPanel :plans="weeklyPlans" />
        </main>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '../../Components/layout/AppLayout.vue';
import PageHeader from '../../Components/ui/PageHeader.vue';
import ExerciseCategoryAccordion from '../../Components/exercises/ExerciseCategoryAccordion.vue';
import ExerciseComboPanel from '../../Components/exercises/ExerciseComboPanel.vue';
import ExerciseFilters from '../../Components/exercises/ExerciseFilters.vue';
import WeeklyPlanPanel from '../../Components/exercises/WeeklyPlanPanel.vue';

const props = defineProps({
  exercises: {
    type: Array,
    required: true,
  },
  groupedExercises: {
    type: Array,
    required: true,
  },
  combos: {
    type: Array,
    default: () => [],
  },
  weeklyPlans: {
    type: Array,
    default: () => [],
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

const isSearchActive = computed(() => (
  props.filters.search ||
  props.filters.category ||
  props.filters.difficulty ||
  props.filters.target_skill ||
  props.filters.age ||
  props.filters.is_active
));

const cleanFilters = (filters) => Object.fromEntries(
  Object.entries(filters).filter(([, value]) => value !== '' && value !== null && value !== undefined)
);

const handleFilter = (newFilters) => {
  router.get('/exercises', cleanFilters(newFilters), {
    preserveState: true,
    replace: true,
  });
};
</script>
