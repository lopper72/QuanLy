<script setup>
import AppLayout from '@/Components/layout/AppLayout.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';
import SkillFilter from '@/Components/assessment/SkillFilter.vue';
import LatestSkillLevelGrid from '@/Components/assessment/LatestSkillLevelGrid.vue';
import SkillProgressTimeline from '@/Components/assessment/SkillProgressTimeline.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    progressData: Array,
    latestSkillLevels: Array,
    children: Array,
    filters: Object,
    skillTypes: Object,
    levels: Object,
});
</script>

<template>
    <Head title="Tiến bộ kỹ năng" />

    <AppLayout>
        <PageHeader title="Theo dõi tiến bộ kỹ năng">
            <template #actions>
                <Link
                    :href="route('assessment.index')"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                >
                    Quay lại đánh giá
                </Link>
            </template>
        </PageHeader>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <SkillFilter 
                :children="children" 
                :skill-types="skillTypes" 
                :filters="filters" 
            />

            <div class="mb-8">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Mức kỹ năng mới nhất</h3>
                <LatestSkillLevelGrid 
                    :latest-skill-levels="latestSkillLevels" 
                    :skill-types="skillTypes" 
                />
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2">
                    <SkillProgressTimeline 
                        :progress-data="progressData" 
                        :skill-types="skillTypes" 
                    />
                </div>
                
                <div>
                    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Về theo dõi tiến bộ</h3>
                        <div class="prose prose-sm text-gray-500">
                            <p>
                                Trang này cho biết sự thay đổi kỹ năng theo thời gian dựa trên các lần đánh giá định kỳ.
                            </p>
                            <ul>
                                <li><strong>Tiến bộ:</strong> Điểm cao hơn lần đánh giá trước.</li>
                                <li><strong>Ổn định:</strong> Điểm tương đương lần đánh giá trước.</li>
                                <li><strong>Giảm sút:</strong> Điểm thấp hơn lần đánh giá trước.</li>
                            </ul>
                            <p>
                                Dùng bộ lọc phía trên để xem một trẻ hoặc một nhóm kỹ năng cụ thể.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script>
import { Link } from '@inertiajs/vue3';
export default {
    components: {
        Link
    }
}
</script>
