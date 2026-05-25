<template>
  <div class="bg-slate-50 p-4 rounded-lg border border-slate-200 shadow-sm space-y-4">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <!-- Search Input -->
      <div>
        <label for="search" class="block text-xs font-semibold text-slate-700 mb-1">Tìm kiếm</label>
        <div class="relative rounded-md shadow-sm">
          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </div>
          <input
            id="search"
            v-model="localFilters.search"
            type="text"
            placeholder="Tìm kiếm bài tập..."
            class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-9 text-sm border-slate-300 rounded-md"
            @input="debouncedFilter"
          />
        </div>
      </div>

      <!-- Category Filter -->
      <div>
        <label for="category" class="block text-xs font-semibold text-slate-700 mb-1">Danh mục</label>
        <select
          id="category"
          v-model="localFilters.category"
          class="focus:ring-indigo-500 focus:border-indigo-500 block w-full text-sm border-slate-300 rounded-md"
          @change="emitFilter"
        >
          <option value="">Tất cả danh mục</option>
          <option v-for="(label, key) in categories" :key="key" :value="key">
            {{ label }}
          </option>
        </select>
      </div>

      <!-- Difficulty Filter -->
      <div>
        <label for="difficulty" class="block text-xs font-semibold text-slate-700 mb-1">Độ khó</label>
        <select
          id="difficulty"
          v-model="localFilters.difficulty"
          class="focus:ring-indigo-500 focus:border-indigo-500 block w-full text-sm border-slate-300 rounded-md"
          @change="emitFilter"
        >
          <option value="">Tất cả độ khó</option>
          <option v-for="(label, key) in difficulties" :key="key" :value="key">
            {{ label }}
          </option>
        </select>
      </div>

      <!-- Status Filter -->
      <div>
        <label for="status" class="block text-xs font-semibold text-slate-700 mb-1">Trạng thái</label>
        <select
          id="status"
          v-model="localFilters.is_active"
          class="focus:ring-indigo-500 focus:border-indigo-500 block w-full text-sm border-slate-300 rounded-md"
          @change="emitFilter"
        >
          <option value="">Tất cả trạng thái</option>
          <option value="1">Kích hoạt</option>
          <option value="0">Chưa kích hoạt</option>
        </select>
      </div>
    </div>

    <!-- Action Buttons -->
    <div class="flex justify-end gap-2 pt-2 border-t border-slate-200">
      <button
        type="button"
        class="inline-flex items-center px-3 py-1.5 border border-slate-300 text-xs font-medium rounded text-slate-700 bg-white hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
        @click="resetFilters"
      >
        Xóa bộ lọc
      </button>
    </div>
  </div>
</template>

<script setup>
import { reactive, watch } from 'vue';

const props = defineProps({
  filters: {
    type: Object,
    default: () => ({ search: '', category: '', difficulty: '', is_active: '' }),
  },
  categories: {
    type: Object,
    required: true,
  },
  difficulties: {
    type: Object,
    required: true,
  },
});

const emit = defineEmits(['filter']);

const localFilters = reactive({
  search: props.filters.search ?? '',
  category: props.filters.category ?? '',
  difficulty: props.filters.difficulty ?? '',
  is_active: props.filters.is_active ?? '',
});

watch(
  () => props.filters,
  (newFilters) => {
    localFilters.search = newFilters.search ?? '';
    localFilters.category = newFilters.category ?? '';
    localFilters.difficulty = newFilters.difficulty ?? '';
    localFilters.is_active = newFilters.is_active ?? '';
  },
  { deep: true }
);

let timeoutId = null;

const debouncedFilter = () => {
  if (timeoutId) {
    clearTimeout(timeoutId);
  }
  timeoutId = setTimeout(() => {
    emitFilter();
  }, 300);
};

const emitFilter = () => {
  emit('filter', { ...localFilters });
};

const resetFilters = () => {
  localFilters.search = '';
  localFilters.category = '';
  localFilters.difficulty = '';
  localFilters.is_active = '';
  emitFilter();
};
</script>