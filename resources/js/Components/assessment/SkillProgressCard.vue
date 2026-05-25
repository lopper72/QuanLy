<script setup>
import ProgressTrendBadge from './ProgressTrendBadge.vue';
import StatusBadge from '@/Components/ui/StatusBadge.vue';

defineProps({
    skill: Object,
    skillLabel: String,
});
</script>

<template>
    <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
        <div class="flex justify-between items-start mb-2">
            <h4 class="text-sm font-semibold text-gray-900">{{ skillLabel }}</h4>
            <ProgressTrendBadge :trend="skill.trend" />
        </div>
        
        <div v-if="skill.level">
            <div class="flex items-center justify-between mb-1">
                <span class="text-xs text-gray-500">Mức mới nhất</span>
                <StatusBadge :status="skill.level" />
            </div>
            <div class="flex items-center justify-between">
                <span class="text-xs text-gray-500">Điểm</span>
                <span class="text-sm font-medium text-gray-900">{{ skill.score }}/100</span>
            </div>
            <div class="mt-2 text-[10px] text-gray-400">
                Đánh giá gần nhất: {{ skill.date }}
            </div>
        </div>
        <div v-else class="py-4 text-center">
            <span class="text-xs text-gray-400 italic">Chưa có dữ liệu</span>
        </div>
    </div>
</template>
