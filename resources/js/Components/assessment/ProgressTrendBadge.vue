<script setup>
import { computed } from 'vue';
import { 
    ArrowTrendingUpIcon, 
    ArrowTrendingDownIcon, 
    MinusIcon 
} from '@heroicons/vue/24/outline';

const props = defineProps({
    trend: {
        type: String,
        required: true,
        validator: (value) => ['improving', 'regressing', 'stable'].includes(value)
    }
});

const config = computed(() => {
    switch (props.trend) {
        case 'improving':
            return {
                label: 'Đang tiến bộ',
                classes: 'bg-green-100 text-green-800',
                icon: ArrowTrendingUpIcon
            };
        case 'regressing':
            return {
                label: 'Cần hỗ trợ thêm',
                classes: 'bg-red-100 text-red-800',
                icon: ArrowTrendingDownIcon
            };
        default:
            return {
                label: 'Ổn định',
                classes: 'bg-gray-100 text-gray-800',
                icon: MinusIcon
            };
    }
});
</script>

<template>
    <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium', config.classes]">
        <component :is="config.icon" class="w-3 h-3 mr-1" />
        {{ config.label }}
    </span>
</template>
