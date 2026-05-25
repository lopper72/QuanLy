<template>
  <div>
    <EmptyState
      v-if="!assessments.data || assessments.data.length === 0"
      title="Chưa có dữ liệu đánh giá"
      description="Thực hiện đánh giá các mốc phát triển hoặc hành vi cho trẻ."
    >
      <template #icon>
        <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
        </svg>
      </template>
      <template #action>
        <Link
          :href="route('assessment.create')"
          class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-semibold rounded-md text-white bg-indigo-600 hover:bg-indigo-700 transition duration-150 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
        >
          Tạo đánh giá
        </Link>
      </template>
    </EmptyState>

    <div v-else class="bg-white rounded-lg shadow overflow-hidden border border-gray-100">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500">Trẻ</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500">Ngày</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500">Điểm tổng thể</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500">Ghi chú</th>
              <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500">Thao tác</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="assessment in assessments.data" :key="assessment.id" class="hover:bg-gray-50 transition duration-150">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">
                  {{ childName(assessment.child) }}
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-500">
                  {{ formatDate(assessment.assessment_date) }}
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <span class="text-sm font-semibold mr-2" :class="getScoreColor(assessment.overall_score)">
                    {{ assessment.overall_score !== null ? assessment.overall_score + '%' : 'Chưa có' }}
                  </span>
                  <div class="w-16 bg-gray-200 rounded-full h-2" v-if="assessment.overall_score !== null">
                    <div
                      class="h-2 rounded-full"
                      :class="getScoreBgColor(assessment.overall_score)"
                      :style="{ width: assessment.overall_score + '%' }"
                    ></div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4">
                <div class="text-sm text-gray-500 max-w-xs truncate">
                  {{ assessment.notes || '-' }}
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <div class="flex justify-end space-x-2">
                  <Link
                    :href="route('assessment.show', assessment.id)"
                    class="text-indigo-600 hover:text-indigo-900 bg-indigo-50 hover:bg-indigo-100 px-2 py-1 rounded transition duration-150"
                  >
                    Xem chi tiết
                  </Link>
                  <Link
                    :href="route('assessment.edit', assessment.id)"
                    class="text-amber-600 hover:text-amber-900 bg-amber-50 hover:bg-amber-100 px-2 py-1 rounded transition duration-150"
                  >
                    Sửa
                  </Link>
                  <button
                    @click="deleteAssessment(assessment)"
                    class="text-rose-600 hover:text-rose-900 bg-rose-50 hover:bg-rose-100 px-2 py-1 rounded transition duration-150"
                  >
                    Xóa
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex items-center justify-between" v-if="assessments.links && assessments.links.length > 3">
        <div class="flex-1 flex justify-between sm:hidden">
          <Link
            v-if="assessments.prev_page_url"
            :href="assessments.prev_page_url"
            class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition duration-150"
          >
            Trước
          </Link>
          <span
            v-else
            class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-400 bg-white opacity-50"
          >
            Trước
          </span>
          <Link
            v-if="assessments.next_page_url"
            :href="assessments.next_page_url"
            class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition duration-150"
          >
            Sau
          </Link>
          <span
            v-else
            class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-400 bg-white opacity-50"
          >
            Sau
          </span>
        </div>
        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
          <div>
            <p class="text-sm text-gray-700">
              Hiển thị
              <span class="font-medium">{{ assessments.from || 0 }}</span>
              đến
              <span class="font-medium">{{ assessments.to || 0 }}</span>
              trong tổng số
              <span class="font-medium">{{ assessments.total || 0 }}</span>
              kết quả
            </p>
          </div>
          <div>
            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Phân trang">
              <Link
                v-for="(link, index) in availableLinks"
                :key="index"
                :href="link.url"
                class="relative inline-flex items-center px-3 py-2 border text-sm font-medium transition duration-150"
                :class="link.active
                  ? 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600'
                  : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'"
              >
                {{ paginationLabel(link.label) }}
              </Link>
            </nav>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import EmptyState from '../ui/EmptyState.vue';

const props = defineProps({
  assessments: {
    type: Object,
    required: true,
  },
});

const availableLinks = computed(() => (props.assessments.links || []).filter((link) => link.url));

const childName = (child) => {
  if (!child) return 'Chưa có';
  return child.full_name || `${child.first_name || ''} ${child.last_name || ''}`.trim() || 'Chưa có';
};

const paginationLabel = (label) => {
  if (label.includes('Previous') || label.includes('&laquo;')) return 'Trước';
  if (label.includes('Next') || label.includes('&raquo;')) return 'Sau';
  return label;
};

const formatDate = (dateString) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  return date.toLocaleDateString('vi-VN', { year: 'numeric', month: 'long', day: 'numeric' });
};

const getScoreColor = (score) => {
  if (score === null) return 'text-gray-500';
  if (score >= 80) return 'text-green-600';
  if (score >= 50) return 'text-blue-600';
  if (score >= 30) return 'text-yellow-600';
  return 'text-red-600';
};

const getScoreBgColor = (score) => {
  if (score >= 80) return 'bg-green-600';
  if (score >= 50) return 'bg-blue-600';
  if (score >= 30) return 'bg-yellow-500';
  return 'bg-red-600';
};

const deleteAssessment = (assessment) => {
  if (confirm(`Bạn có chắc chắn muốn xóa đánh giá của ${childName(assessment.child)} không?`)) {
    router.delete(route('assessment.destroy', assessment.id));
  }
};
</script>
