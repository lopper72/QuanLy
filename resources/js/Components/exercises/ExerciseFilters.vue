<template>
  <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-5">
      <div>
        <label for="search" class="mb-1 block text-sm font-medium text-slate-700">Tìm kiếm</label>
        <input
          id="search"
          v-model="localFilters.search"
          type="text"
          placeholder="Tên bài, lợi ích, hướng dẫn..."
          class="block w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
          @input="debouncedFilter"
        />
      </div>

      <div>
        <label for="category" class="mb-1 block text-sm font-medium text-slate-700">Nhóm kỹ năng</label>
        <select
          id="category"
          v-model="localFilters.category"
          class="block w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
          @change="emitFilter"
        >
          <option value="">Tất cả nhóm</option>
          <option v-for="(label, key) in categories" :key="key" :value="key">{{ label }}</option>
        </select>
      </div>

      <div>
        <label for="target_skill" class="mb-1 block text-sm font-medium text-slate-700">Mục tiêu</label>
        <select
          id="target_skill"
          v-model="localFilters.target_skill"
          class="block w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
          @change="emitFilter"
        >
          <option value="">Tất cả mục tiêu</option>
          <option v-for="skill in targetSkills" :key="skill" :value="skill">{{ labelFor(skillLabels, skill, skill) }}</option>
        </select>
      </div>

      <div>
        <label for="difficulty" class="mb-1 block text-sm font-medium text-slate-700">Độ khó</label>
        <select
          id="difficulty"
          v-model="localFilters.difficulty"
          class="block w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
          @change="emitFilter"
        >
          <option value="">Tất cả độ khó</option>
          <option v-for="(label, key) in difficulties" :key="key" :value="key">{{ label }}</option>
        </select>
      </div>

      <div>
        <label for="age" class="mb-1 block text-sm font-medium text-slate-700">Tuổi của bé</label>
        <input
          id="age"
          v-model="localFilters.age"
          type="number"
          min="1"
          max="18"
          placeholder="VD: 5"
          class="block w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
          @input="debouncedFilter"
        />
      </div>
    </div>

    <div class="mt-4 flex justify-end border-t border-slate-100 pt-3">
      <button
        type="button"
        class="rounded-md border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
        @click="resetFilters"
      >
        Xóa bộ lọc
      </button>
    </div>
  </div>
</template>

<script setup>
import { computed, reactive, watch } from 'vue';
import { labelFor, skillLabels } from '@/Lib/labels';

const props = defineProps({
  filters: {
    type: Object,
    default: () => ({ search: '', category: '', difficulty: '', target_skill: '', age: '', is_active: '' }),
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
  target_skill: props.filters.target_skill ?? '',
  age: props.filters.age ?? '',
  is_active: props.filters.is_active ?? '',
});

const targetSkills = computed(() => [
  'gross_motor',
  'fine_motor',
  'communication',
  'cognitive',
  'sensory_processing',
  'social_interaction',
  'self_care',
  'attention',
  'self_regulation',
]);

watch(
  () => props.filters,
  (newFilters) => {
    localFilters.search = newFilters.search ?? '';
    localFilters.category = newFilters.category ?? '';
    localFilters.difficulty = newFilters.difficulty ?? '';
    localFilters.target_skill = newFilters.target_skill ?? '';
    localFilters.age = newFilters.age ?? '';
    localFilters.is_active = newFilters.is_active ?? '';
  },
  { deep: true }
);

let timeoutId = null;

const debouncedFilter = () => {
  clearTimeout(timeoutId);
  timeoutId = setTimeout(emitFilter, 300);
};

const emitFilter = () => {
  emit('filter', { ...localFilters });
};

const resetFilters = () => {
  localFilters.search = '';
  localFilters.category = '';
  localFilters.difficulty = '';
  localFilters.target_skill = '';
  localFilters.age = '';
  localFilters.is_active = '';
  emitFilter();
};
</script>
