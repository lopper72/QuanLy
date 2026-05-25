<script setup>
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    children: Array,
    skillTypes: Object,
    filters: Object,
});

const childId = ref(props.filters.child_id || '');
const skillName = ref(props.filters.skill_name || '');

const cleanQuery = (query) => Object.fromEntries(
    Object.entries(query).filter(([, value]) => value !== null && value !== undefined && value !== '')
);

watch([childId, skillName], () => {
    router.get(route('assessment.progress'), cleanQuery({
        child_id: childId.value,
        skill_name: skillName.value,
    }), {
        preserveState: true,
        replace: true,
    });
});

const resetFilters = () => {
    childId.value = '';
    skillName.value = '';
};
</script>

<template>
    <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Trẻ</label>
                <select
                    v-model="childId"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
                    <option value="">Tất cả trẻ</option>
                    <option v-for="child in children" :key="child.id" :value="child.id">
                        {{ child.first_name }} {{ child.last_name }}
                    </option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kỹ năng</label>
                <select
                    v-model="skillName"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
                    <option value="">Tất cả kỹ năng</option>
                    <option v-for="(label, key) in skillTypes" :key="key" :value="key">
                        {{ label }}
                    </option>
                </select>
            </div>

            <div>
                <button
                    @click="resetFilters"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md transition-colors"
                >
                    Xóa bộ lọc
                </button>
            </div>
        </div>
    </div>
</template>
