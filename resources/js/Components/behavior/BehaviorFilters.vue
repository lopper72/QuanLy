<template>
  <div class="bg-white p-4 rounded-lg shadow-sm border border-slate-100 mb-6">
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
      <div>
        <label for="filter-child-status" class="block text-xs font-semibold text-slate-500 mb-1">Trạng thái trẻ</label>
        <select
          id="filter-child-status"
          v-model="filters.child_status"
          class="block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-xs"
          @change="handleChildStatusChange"
        >
          <option value="active">Đang can thiệp</option>
          <option value="paused">Tạm nghỉ</option>
          <option value="voided">Ngừng can thiệp</option>
          <option value="all">Tất cả</option>
        </select>
      </div>

      <!-- Child Filter -->
      <div>
        <label for="filter-child" class="block text-xs font-semibold text-slate-500 mb-1">Trẻ</label>
        <select
          id="filter-child"
          v-model="filters.child_id"
          class="block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-xs"
          @change="applyFilters"
        >
          <option value="">Tất cả trẻ</option>
          <option v-for="child in children" :key="child.id" :value="child.id">
            {{ child.full_name }}
          </option>
        </select>
      </div>

      <!-- Type Filter -->
      <div>
        <label for="filter-type" class="block text-xs font-semibold text-slate-500 mb-1">Loại hành vi</label>
        <select
          id="filter-type"
          v-model="filters.behavior_type"
          class="block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-xs"
          @change="applyFilters"
        >
          <option value="">Tất cả loại hành vi</option>
          <option v-for="(label, key) in behaviorTypes" :key="key" :value="key">
            {{ label }}
          </option>
        </select>
      </div>

      <!-- Severity Filter -->
      <div>
        <label for="filter-severity" class="block text-xs font-semibold text-slate-500 mb-1">Mức độ</label>
        <select
          id="filter-severity"
          v-model="filters.severity"
          class="block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-xs"
          @change="applyFilters"
        >
          <option value="">Tất cả mức độ</option>
          <option v-for="(label, key) in severities" :key="key" :value="key">
            {{ label }}
          </option>
        </select>
      </div>

      <div>
        <label for="filter-date-from" class="block text-xs font-semibold text-slate-500 mb-1">Từ ngày</label>
        <input
          id="filter-date-from"
          type="date"
          v-model="filters.date_from"
          class="block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-xs"
          @change="applyFilters"
        />
      </div>

      <div>
        <label for="filter-date-to" class="block text-xs font-semibold text-slate-500 mb-1">Đến ngày</label>
        <input
          id="filter-date-to"
          type="date"
          v-model="filters.date_to"
          class="block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-xs"
          @change="applyFilters"
        />
      </div>
    </div>

    <!-- Actions -->
    <div v-if="hasActiveFilters" class="flex justify-end mt-4 pt-3 border-t border-slate-100">
      <button
        @click="clearFilters"
        class="inline-flex items-center px-3 py-1.5 border border-slate-300 text-xs font-medium rounded-md text-slate-700 bg-white hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
      >
        Xóa bộ lọc
      </button>
    </div>
  </div>
</template>

<script setup>
import { reactive, computed } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
  initialFilters: {
    type: Object,
    required: true,
  },
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

const filters = reactive({
  child_id: props.initialFilters.child_id || '',
  child_status: props.initialFilters.child_status || 'active',
  behavior_type: props.initialFilters.behavior_type || '',
  severity: props.initialFilters.severity || '',
  date_from: props.initialFilters.date_from || '',
  date_to: props.initialFilters.date_to || '',
});

const hasActiveFilters = computed(() => {
  return (
    filters.child_id !== '' ||
    filters.child_status !== 'active' ||
    filters.behavior_type !== '' ||
    filters.severity !== '' ||
    filters.date_from !== '' ||
    filters.date_to !== ''
  );
});

const handleChildStatusChange = () => {
  filters.child_id = '';
  applyFilters();
};

const applyFilters = () => {
  const query = {};
  if (filters.child_id) query.child_id = filters.child_id;
  if (filters.child_status) query.child_status = filters.child_status;
  if (filters.behavior_type) query.behavior_type = filters.behavior_type;
  if (filters.severity) query.severity = filters.severity;
  if (filters.date_from) query.date_from = filters.date_from;
  if (filters.date_to) query.date_to = filters.date_to;

  router.get(route('behavior.index'), query, {
    preserveState: true,
    replace: true,
  });
};

const clearFilters = () => {
  filters.child_id = '';
  filters.child_status = 'active';
  filters.behavior_type = '';
  filters.severity = '';
  filters.date_from = '';
  filters.date_to = '';
  
  router.get(route('behavior.index'), {}, {
    preserveState: true,
    replace: true,
  });
};
</script>
