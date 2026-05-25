<script setup>
import StatusBadge from '@/Components/ui/StatusBadge.vue';

defineProps({
    progressData: Array,
    skillTypes: Object,
});
</script>

<template>
    <div class="bg-white shadow-sm border border-gray-200 rounded-lg overflow-hidden">
        <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
            <h3 class="text-lg font-medium leading-6 text-gray-900">Dòng thời gian tiến bộ</h3>
        </div>
        <div class="p-6">
            <div v-if="progressData.length > 0" class="flow-root">
                <ul role="list" class="-mb-8">
                    <li v-for="(item, itemIdx) in progressData" :key="item.id">
                        <div class="relative pb-8">
                            <span v-if="itemIdx !== progressData.length - 1" class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                            <div class="relative flex space-x-3">
                                <div>
                                    <span class="h-8 w-8 rounded-full bg-indigo-500 flex items-center justify-center ring-8 ring-white">
                                        <span class="text-white text-xs font-bold">{{ item.score }}</span>
                                    </span>
                                </div>
                                <div class="flex-1 min-w-0 pt-1.5 flex justify-between space-x-4">
                                    <div>
                                        <p class="text-sm text-gray-500">
                                            <span class="font-medium text-gray-900">{{ skillTypes[item.skill_name] }}</span>
                                            được đánh giá ở mức
                                            <StatusBadge :status="item.level" class="ml-1" />
                                        </p>
                                        <p v-if="item.note" class="mt-1 text-sm text-gray-600 italic">
                                            "{{ item.note }}"
                                        </p>
                                    </div>
                                    <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                        <time :datetime="item.date">{{ item.date }}</time>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
            <div v-else class="text-center py-12">
                <p class="text-gray-500">Chưa có dữ liệu tiến bộ phù hợp với bộ lọc đã chọn.</p>
            </div>
        </div>
    </div>
</template>
