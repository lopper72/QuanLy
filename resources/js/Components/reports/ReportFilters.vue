<template>
  <div class="bg-white rounded-lg border border-gray-200 p-4 mb-6 shadow-sm">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
      <div>
        <label for="child_id" class="block text-xs font-semibold text-gray-500 mb-1">Lọc theo trẻ</label>
        <select
          id="child_id"
          v-model="localFilters.child_id"
          class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
        >
          <option value="">Tất cả trẻ</option>
          <option v-for="child in children" :key="child.id" :value="child.id">
            {{ child.first_name }} {{ child.last_name }}
          </option>
        </select>
      </div>

      <div>
        <label for="report_type" class="block text-xs font-semibold text-gray-500 mb-1">Loại báo cáo</label>
        <select
          id="report_type"
          v-model="localFilters.report_type"
          class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
        >
          <option value="">Tất cả loại báo cáo</option>
          <option v-for="(label, val) in reportTypes" :key="val" :value="val">
            {{ label }}
          </option>
        </select>
      </div>

      <div>
        <label for="start_date" class="block text-xs font-semibold text-gray-500 mb-1">Từ ngày</label>
        <input
          id="start_date"
          type="date"
          v-model="localFilters.start_date"
          class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
        />
      </div>

      <div>
        <label for="end_date" class="block text-xs font-semibold text-gray-500 mb-1">Đến ngày</label>
        <input
          id="end_date"
          type="date"
          v-model="localFilters.end_date"
          class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
        />
      </div>
    </div>

    <div class="mt-4 flex justify-end space-x-2">
      <button
        type="button"
        @click="resetFilters"
        class="inline-flex items-center px-3 py-1.5 border border-gray-300 rounded-md shadow-sm text-xs font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
      >
        Xóa bộ lọc
      </button>
      <button
        type="button"
        @click="applyFilters"
        class="inline-flex items-center px-3 py-1.5 border border-transparent rounded-md shadow-sm text-xs font-medium text-white bg-indigo-600 hover:bg-indigo-750 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
      >
        Áp dụng
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
  children: {
    type: Array,
    required: true,
  },
  reportTypes: {
    type: Object,
    required: true,
  },
  filters: {
    type: Object,
    default: () => ({}),
  },
});

const emit = defineEmits(['filter']);

const localFilters = ref({
  child_id: props.filters.child_id || '',
  report_type: props.filters.report_type || '',
  start_date: props.filters.start_date || '',
  end_date: props.filters.end_date || '',
});

watch(() => props.filters, (newFilters) => {
  localFilters.value = {
    child_id: newFilters.child_id || '',
    report_type: newFilters.report_type || '',
    start_date: newFilters.start_date || '',
    end_date: newFilters.end_date || '',
  };
}, { deep: true });

const applyFilters = () => {
  emit('filter', { ...localFilters.value });
};

const resetFilters = () => {
  localFilters.value = {
    child_id: '',
    report_type: '',
    start_date: '',
    end_date: '',
  };
  emit('filter', { ...localFilters.value });
};
</script>
