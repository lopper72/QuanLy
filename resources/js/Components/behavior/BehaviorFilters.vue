<template>
  <div class="mb-6 rounded-lg border border-slate-100 bg-white p-4 shadow-sm">
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5">
      <div>
        <label for="filter-child" class="mb-1 block text-xs font-semibold text-slate-500">Trẻ</label>
        <select
          id="filter-child"
          v-model="filters.child_id"
          class="block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-xs"
          @change="applyFilters"
        >
          <option value="">Tất cả trẻ đang can thiệp</option>
          <option v-for="child in safeChildren" :key="child.id" :value="child.id">
            {{ child.full_name }}
          </option>
        </select>
      </div>

      <div>
        <label for="filter-type" class="mb-1 block text-xs font-semibold text-slate-500">Loại hành vi</label>
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

      <div>
        <label for="filter-severity" class="mb-1 block text-xs font-semibold text-slate-500">Mức độ</label>
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
        <label for="filter-date-from" class="mb-1 block text-xs font-semibold text-slate-500">Từ ngày</label>
        <input
          id="filter-date-from"
          v-model="filters.date_from"
          type="date"
          class="block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-xs"
          @change="applyFilters"
        />
      </div>

      <div>
        <label for="filter-date-to" class="mb-1 block text-xs font-semibold text-slate-500">Đến ngày</label>
        <input
          id="filter-date-to"
          v-model="filters.date_to"
          type="date"
          class="block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-xs"
          @change="applyFilters"
        />
      </div>
    </div>

    <div v-if="hasActiveFilters" class="mt-4 flex justify-end border-t border-slate-100 pt-3">
      <button
        class="inline-flex items-center rounded-md border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
        @click="clearFilters"
      >
        Xóa bộ lọc
      </button>
    </div>
  </div>
</template>

<script setup>
import { computed, reactive } from 'vue';
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

const safeChildren = computed(() => props.children.filter((child) => {
  return child && child.status === 'active' && !child.deleted_at;
}));

const filters = reactive({
  child_id: props.initialFilters.child_id || '',
  behavior_type: props.initialFilters.behavior_type || '',
  severity: props.initialFilters.severity || '',
  date_from: props.initialFilters.date_from || '',
  date_to: props.initialFilters.date_to || '',
});

const hasActiveFilters = computed(() => {
  return filters.child_id !== ''
    || filters.behavior_type !== ''
    || filters.severity !== ''
    || filters.date_from !== ''
    || filters.date_to !== '';
});

const buildQuery = () => {
  const query = {};
  if (filters.child_id) query.child_id = filters.child_id;
  if (filters.behavior_type) query.behavior_type = filters.behavior_type;
  if (filters.severity) query.severity = filters.severity;
  if (filters.date_from) query.date_from = filters.date_from;
  if (filters.date_to) query.date_to = filters.date_to;
  return query;
};

const applyFilters = () => {
  router.get(route('behavior.index'), buildQuery(), {
    preserveState: true,
    replace: true,
  });
};

const clearFilters = () => {
  filters.child_id = '';
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
