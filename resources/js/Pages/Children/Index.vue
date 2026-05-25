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
        title="Danh sách trẻ" 
        description="Quản lý và theo dõi hồ sơ can thiệp của trẻ."
      >
        <template #actions>
          <Link
            href="/children/create"
            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-semibold rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 transition duration-150 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
          >
            Đăng ký trẻ mới
          </Link>
        </template>
      </PageHeader>

      <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 p-6">
        <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-[minmax(0,1fr)_220px]">
          <div>
            <label for="search" class="sr-only">Tìm kiếm trẻ</label>
            <div class="relative rounded-md shadow-sm">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
              </div>
              <input
                id="search"
                v-model="searchQuery"
                type="text"
                placeholder="Tìm kiếm theo tên hoặc biệt danh..."
                class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 text-sm border-gray-300 rounded-md"
                @input="doSearch"
              />
            </div>
          </div>

          <div>
            <label for="status" class="sr-only">Lọc trạng thái</label>
            <select
              id="status"
              v-model="statusFilter"
              class="focus:ring-indigo-500 focus:border-indigo-500 block w-full text-sm border-gray-300 rounded-md"
              @change="applyFilters"
            >
              <option value="all">Tất cả</option>
              <option value="">Đang can thiệp và tạm nghỉ</option>
              <option value="active">Đang can thiệp</option>
              <option value="paused">Tạm nghỉ</option>
              <option value="stopped">Dừng can thiệp</option>
              <option value="voided">Ngừng can thiệp</option>
            </select>
          </div>
        </div>

        <!-- Child list -->
        <ChildList
          :children="children"
          :search-active="hasActiveFilters"
        />
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed, ref, watch } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '../../Components/layout/AppLayout.vue';
import PageHeader from '../../Components/ui/PageHeader.vue';
import ChildList from '../../Components/children/ChildList.vue';

const props = defineProps({
  children: {
    type: Array,
    required: true,
  },
  filters: {
    type: Object,
    default: () => ({ search: '', status: '' }),
  },
  flash: {
    type: Object,
    default: () => ({ success: null, error: null }),
  },
});

const searchQuery = ref(props.filters?.search ?? '');
const statusFilter = ref(props.filters?.status ?? '');

let timeoutId = null;

const doSearch = () => {
  if (timeoutId) {
    clearTimeout(timeoutId);
  }
  timeoutId = setTimeout(() => {
    applyFilters();
  }, 300);
};

const applyFilters = () => {
  router.get(
    '/children',
    {
      search: searchQuery.value || undefined,
      status: statusFilter.value || undefined,
    },
    { preserveState: true, replace: true }
  );
};

const hasActiveFilters = computed(() => {
  return !!(props.filters?.search || props.filters?.status);
});

// Keep search input updated when filters change
watch(
  () => props.filters?.search,
  (newVal) => {
    searchQuery.value = newVal ?? '';
  }
);

watch(
  () => props.filters?.status,
  (newVal) => {
    statusFilter.value = newVal ?? '';
  }
);
</script>
