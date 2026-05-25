<template>
  <div class="bg-white p-4 rounded-lg shadow mb-6 border border-gray-100">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
      <!-- Search Input -->
      <div>
        <label for="search" class="block text-xs font-semibold text-gray-500 mb-1">Tìm trẻ</label>
        <input
          id="search"
          v-model="form.search"
          type="text"
          placeholder="Nhập họ tên trẻ..."
          class="w-full text-sm border-gray-300 rounded-md focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
          @input="debouncedSearch"
        />
      </div>

      <!-- Child Filter -->
      <div>
        <label for="child_id" class="block text-xs font-semibold text-gray-500 mb-1">Lọc theo trẻ</label>
        <select
          id="child_id"
          v-model="form.child_id"
          class="w-full text-sm border-gray-300 rounded-md focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
          @change="submit"
        >
          <option value="">Tất cả trẻ</option>
          <option v-for="child in children" :key="child.id" :value="child.id">
            {{ child.full_name }}
          </option>
        </select>
      </div>

      <!-- Start Date -->
      <div>
        <label for="start_date" class="block text-xs font-semibold text-gray-500 mb-1">Ngày bắt đầu</label>
        <input
          id="start_date"
          v-model="form.start_date"
          type="date"
          class="w-full text-sm border-gray-300 rounded-md focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
          @change="submit"
        />
      </div>

      <!-- End Date -->
      <div>
        <label for="end_date" class="block text-xs font-semibold text-gray-500 mb-1">Ngày kết thúc</label>
        <input
          id="end_date"
          v-model="form.end_date"
          type="date"
          class="w-full text-sm border-gray-300 rounded-md focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
          @change="submit"
        />
      </div>
    </div>

    <!-- Reset Button -->
    <div class="flex justify-end mt-4" v-if="hasFilters">
      <button
        @click="resetFilters"
        class="text-xs font-medium text-indigo-600 hover:text-indigo-800 transition duration-150"
      >
        Xóa bộ lọc
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, computed } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
  filters: [Object, Array],
  children: Array,
});

const isArray = Array.isArray(props.filters);
const form = ref({
  search: (!isArray && props.filters?.search) || '',
  child_id: (!isArray && props.filters?.child_id) || '',
  start_date: (!isArray && props.filters?.start_date) || '',
  end_date: (!isArray && props.filters?.end_date) || '',
});

const hasFilters = computed(() => {
  return form.value.search || form.value.child_id || form.value.start_date || form.value.end_date;
});

const cleanQuery = (query) => Object.fromEntries(
  Object.entries(query).filter(([, value]) => value !== null && value !== undefined && value !== '')
);

let timeout = null;
const debouncedSearch = () => {
  clearTimeout(timeout);
  timeout = setTimeout(() => {
    submit();
  }, 300);
};

const submit = () => {
  router.get(
    route('assessment.index'),
    cleanQuery({
      search: form.value.search,
      child_id: form.value.child_id,
      start_date: form.value.start_date,
      end_date: form.value.end_date,
    }),
    {
      preserveState: true,
      replace: true,
    }
  );
};

const resetFilters = () => {
  form.value.search = '';
  form.value.child_id = '';
  form.value.start_date = '';
  form.value.end_date = '';
  router.get(route('assessment.index'), {}, {
    preserveState: true,
    replace: true,
  });
};
</script>
