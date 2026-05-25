<script setup>
import { ref, computed } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/Components/layout/AppLayout.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';
import DataCard from '@/Components/ui/DataCard.vue';
import StatusBadge from '@/Components/ui/StatusBadge.vue';
import { childStatusLabels, labelFor } from '@/Lib/labels';

const props = defineProps({
    children: Array,
    reportData: Object,
    filters: Object,
});

const form = useForm({
    child_id: props.filters?.child_id || '',
    start_date: props.filters?.start_date || '',
    end_date: props.filters?.end_date || '',
});

const generateReport = () => {
    form.post(route('reports.weekly.generate'), {
        preserveScroll: true,
    });
};

const downloadPdf = () => {
    if (!props.reportData) return;
    
    const url = route('reports.weekly.download', {
        child: props.reportData.child.id,
        start_date: props.reportData.date_range.start,
        end_date: props.reportData.date_range.end,
    });
    
    window.open(url, '_blank');
};

const canGenerate = computed(() => {
    return form.child_id && form.start_date && form.end_date;
});

const selectedChild = computed(() => {
    return props.children?.find((child) => Number(child.id) === Number(form.child_id));
});

const childStatusText = (child) => {
    if (!child || child.status === 'active') return '';
    return ` - ${labelFor(childStatusLabels, child.status)}`;
};

const severityLabels = {
    low: 'Nhẹ',
    medium: 'Trung bình',
    high: 'Cao',
};

const behaviorLabels = {
    tantrum: 'Ăn vạ',
    avoidance: 'Né tránh',
    sensory_seeking: 'Tìm kiếm cảm giác',
    aggression: 'Hành vi gây hấn',
    self_stimulation: 'Tự kích thích',
    difficulty_transitioning: 'Khó chuyển hoạt động',
    poor_sleep: 'Ngủ kém',
    picky_eating: 'Kén ăn',
    withdrawal: 'Thu mình',
    hyperactivity: 'Tăng động',
    noncompliance: 'Không tuân thủ',
    other: 'Khác',
};

const behaviorLabel = (type) => {
    if (!type) return 'Chưa có';
    return behaviorLabels[type] || type;
};
</script>

<template>
    <Head title="Báo cáo tuần" />

    <AppLayout>
        <PageHeader 
            title="Tạo báo cáo tuần" 
            description="Tạo và tải báo cáo tiến bộ hằng tuần cho trẻ."
        />

        <div class="space-y-6">
            <!-- Generator Form -->
            <DataCard title="Thông tin báo cáo">
                <form @submit.prevent="generateReport" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Chọn trẻ</label>
                        <select 
                            v-model="form.child_id"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >
                            <option value="">Chọn trẻ...</option>
                            <option v-for="child in children" :key="child.id" :value="child.id">
                                {{ child.full_name }}{{ childStatusText(child) }}
                            </option>
                        </select>
                        <p v-if="selectedChild?.status === 'voided'" class="mt-1 text-xs text-slate-600">
                            Hồ sơ đã ngừng can thiệp. Báo cáo này chỉ dùng để xem lại lịch sử.
                        </p>
                        <p v-else-if="selectedChild?.status === 'paused'" class="mt-1 text-xs text-amber-700">
                            Trẻ đang tạm nghỉ. Báo cáo này dùng để xem lại lịch sử.
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Ngày bắt đầu</label>
                        <input 
                            type="date" 
                            v-model="form.start_date"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Ngày kết thúc</label>
                        <input 
                            type="date" 
                            v-model="form.end_date"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        />
                    </div>
                    <div>
                        <button 
                            type="submit"
                            :disabled="form.processing || !canGenerate"
                            class="w-full inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
                        >
                            {{ form.processing ? 'Đang tạo...' : 'Xem trước báo cáo' }}
                        </button>
                    </div>
                </form>
            </DataCard>

            <!-- Report Preview -->
            <div v-if="reportData" class="space-y-6">
                <div class="flex justify-between items-center">
                    <h2 class="text-lg font-medium text-gray-900">Xem trước báo cáo</h2>
                    <button 
                        @click="downloadPdf"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                    >
                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="ArrowDownTrayIcon" />
                        </svg>
                        Tải PDF
                    </button>
                </div>

                <!-- Summary Stats -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <DataCard title="Tóm tắt tập luyện">
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-500">Tỷ lệ hoàn thành</span>
                                <span class="font-bold text-indigo-600">{{ reportData.training.completion_rate }}%</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Tổng số buổi</span>
                                <span>{{ reportData.training.total_sessions }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Tổng thời lượng</span>
                                <span>{{ reportData.training.total_minutes }} phút</span>
                            </div>
                        </div>
                    </DataCard>

                    <DataCard title="Tóm tắt hành vi">
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-500">Tổng ghi nhận</span>
                                <span class="font-bold text-red-600">{{ reportData.behavior.total_incidents }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Mức độ cao</span>
                                <span>{{ reportData.behavior.severity_counts.high }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Hành vi thường gặp</span>
                                <span class="truncate ml-2">{{ behaviorLabel(Object.keys(reportData.behavior.top_behaviors)[0]) }}</span>
                            </div>
                        </div>
                    </DataCard>

                    <DataCard title="Tiến bộ đánh giá">
                        <div class="space-y-2">
                            <div v-for="skill in reportData.assessment.skills.slice(0, 3)" :key="skill.name" class="flex justify-between">
                                <span class="text-gray-500 truncate mr-2">{{ skill.name }}</span>
                                <span class="font-medium">{{ skill.score }}%</span>
                            </div>
                            <div v-if="reportData.assessment.skills.length === 0" class="text-gray-400 italic">
                                Chưa có dữ liệu đánh giá
                            </div>
                        </div>
                    </DataCard>
                </div>

                <!-- Detailed Tables (Simplified for Preview) -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <DataCard title="Buổi tập gần đây">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">Ngày</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <tr v-for="session in reportData.training.sessions.slice(0, 5)" :key="session.date">
                                    <td class="px-3 py-2 text-sm text-gray-900">{{ session.date }}</td>
                                    <td class="px-3 py-2 text-sm">
                                        <StatusBadge :status="session.status" />
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </DataCard>

                    <DataCard title="Ghi nhận hành vi gần đây">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">Loại</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">Mức độ</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <tr v-for="log in reportData.behavior.logs.slice(0, 5)" :key="log.date">
                                    <td class="px-3 py-2 text-sm text-gray-900">{{ log.type }}</td>
                                    <td class="px-3 py-2 text-sm">
                                        <span :class="{
                                            'px-2 py-0.5 rounded-full text-xs font-medium': true,
                                            'bg-green-100 text-green-800': log.severity === 'low',
                                            'bg-yellow-100 text-yellow-800': log.severity === 'medium',
                                            'bg-red-100 text-red-800': log.severity === 'high',
                                        }">
                                            {{ severityLabels[log.severity] || log.severity }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </DataCard>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else-if="!form.processing" class="bg-white rounded-lg border-2 border-dashed border-gray-300 p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="DocumentChartBarIcon" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Chưa tạo báo cáo</h3>
                <p class="mt-1 text-sm text-gray-500">Chọn trẻ và khoảng ngày để xem trước báo cáo tuần.</p>
            </div>
        </div>
    </AppLayout>
</template>
