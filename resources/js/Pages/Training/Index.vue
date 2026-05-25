<template>
  <AppLayout>
    <div class="space-y-6">
      <!-- Flash Messages -->
      <div v-if="$page.props.flash?.success" class="bg-green-50 border border-green-200 text-green-800 rounded-md p-4 text-sm">
        {{ $page.props.flash.success }}
      </div>

      <!-- Standard Page Header -->
      <PageHeader
        title="Lịch trình tập luyện"
        description="Theo dõi và quản lý các buổi tập của trẻ theo dòng thời gian."
      >
        <template #actions>
          <Link
            :href="route('training.create')"
            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-semibold rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 transition duration-150 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
          >
            Tạo buổi tập mới
          </Link>
        </template>
      </PageHeader>

      <!-- Statistics / Summary cards -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white border border-gray-200 rounded-lg p-4">
          <h4 class="text-xs font-medium text-gray-500">Tổng số buổi</h4>
          <span class="text-2xl font-bold text-gray-900">{{ totalSessions }}</span>
        </div>
        <div class="bg-white border border-gray-200 rounded-lg p-4">
          <h4 class="text-xs font-medium text-gray-500">Đã lên lịch</h4>
          <span class="text-2xl font-bold text-blue-600">{{ plannedCount }}</span>
        </div>
        <div class="bg-white border border-gray-200 rounded-lg p-4">
          <h4 class="text-xs font-medium text-gray-500">Đang thực hiện</h4>
          <span class="text-2xl font-bold text-yellow-600">{{ inProgressCount }}</span>
        </div>
        <div class="bg-white border border-gray-200 rounded-lg p-4">
          <h4 class="text-xs font-medium text-gray-500">Đã hoàn thành</h4>
          <span class="text-2xl font-bold text-green-600">{{ completedCount }}</span>
        </div>
      </div>

      <!-- Filters Section -->
      <div class="bg-white border border-gray-200 shadow-sm rounded-lg p-5">
        <div class="flex items-center justify-between border-b border-gray-200 pb-3 mb-4">
          <h3 class="text-sm font-semibold text-gray-900">Bộ lọc</h3>
          <button @click="clearFilters" class="text-xs text-indigo-600 hover:underline">
            Xóa bộ lọc
          </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          <div>
            <label for="filter-child" class="block text-xs font-medium text-gray-700 mb-1">
              Chọn trẻ
            </label>
            <select
              id="filter-child"
              v-model="localFilters.child_id"
              @change="applyFilters"
              class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-xs"
            >
              <option value="">Tất cả trẻ</option>
              <option v-for="child in allChildren" :key="child.id" :value="child.id">
                {{ child.full_name }}
              </option>
            </select>
          </div>

          <div>
            <label for="filter-status" class="block text-xs font-medium text-gray-700 mb-1">
              Trạng thái
            </label>
            <select
              id="filter-status"
              v-model="localFilters.status"
              @change="applyFilters"
              class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-xs"
            >
              <option value="">Tất cả trạng thái</option>
              <option value="planned">Đã lên lịch</option>
              <option value="in_progress">Đang thực hiện</option>
              <option value="completed">Đã hoàn thành</option>
              <option value="skipped">Đã bỏ qua</option>
            </select>
          </div>

          <div>
            <label for="filter-date-from" class="block text-xs font-medium text-gray-700 mb-1">
              Từ ngày
            </label>
            <input
              id="filter-date-from"
              v-model="localFilters.date_from"
              type="date"
              @change="applyFilters"
              class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-xs"
            />
          </div>

          <div>
            <label for="filter-date-to" class="block text-xs font-medium text-gray-700 mb-1">
              Đến ngày
            </label>
            <input
              id="filter-date-to"
              v-model="localFilters.date_to"
              type="date"
              @change="applyFilters"
              class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-xs"
            />
          </div>
        </div>
      </div>

      <!-- Timeline View -->
      <TrainingTimeline :grouped-sessions="groupedSessions" />
    </div>
  </AppLayout>
</template>

<script setup>
import { computed, reactive } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '../../Components/layout/AppLayout.vue';
import PageHeader from '../../Components/ui/PageHeader.vue';
import TrainingTimeline from '../../Components/training/TrainingTimeline.vue';

const props = defineProps({
  groupedSessions: {
    type: Array,
    required: true,
  },
  allChildren: {
    type: Array,
    required: true,
  },
  filters: {
    type: Object,
    default: () => ({}),
  },
});

const localFilters = reactive({
  child_id: props.filters.child_id || '',
  status: props.filters.status || '',
  date_from: props.filters.date_from || '',
  date_to: props.filters.date_to || '',
});

// Compute stats from all grouped sessions
const totalSessions = computed(() => {
  return props.groupedSessions.reduce((sum, g) => sum + (g.sessions?.length || 0), 0);
});

const plannedCount = computed(() => {
  return props.groupedSessions.reduce((sum, g) => sum + (g.sessions?.filter(s => s.status === 'planned').length || 0), 0);
});

const inProgressCount = computed(() => {
  return props.groupedSessions.reduce((sum, g) => sum + (g.sessions?.filter(s => s.status === 'in_progress').length || 0), 0);
});

const completedCount = computed(() => {
  return props.groupedSessions.reduce((sum, g) => sum + (g.sessions?.filter(s => s.status === 'completed').length || 0), 0);
});

function applyFilters() {
  const cleanFilters = {};
  Object.keys(localFilters).forEach(key => {
    if (localFilters[key]) {
      cleanFilters[key] = localFilters[key];
    }
  });

  router.get(route('training.index'), cleanFilters, {
    preserveState: true,
    replace: true,
  });
}

function clearFilters() {
  localFilters.child_id = '';
  localFilters.status = '';
  localFilters.date_from = '';
  localFilters.date_to = '';
  applyFilters();
}
</script>