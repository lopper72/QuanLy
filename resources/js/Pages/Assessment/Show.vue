<template>
  <AppLayout>
    <div class="bg-gray-50/50 min-h-screen py-6">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
          <div>
            <div class="flex items-center space-x-2">
              <Link :href="route('assessment.index')" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">
                &larr; Quay lại danh sách đánh giá
              </Link>
            </div>
            <h1 class="text-3xl font-bold text-gray-900 mt-1">
              Chi tiết đánh giá
            </h1>
            <p class="text-sm text-gray-500 mt-1">
              Được ghi nhận vào {{ formatDate(assessment.assessment_date) }} cho {{ assessment.child?.first_name }} {{ assessment.child?.last_name }}
            </p>
          </div>

          <div class="flex space-x-2">
            <Link
              :href="route('assessment.edit', assessment.id)"
              class="bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-md shadow-sm text-sm font-medium hover:bg-gray-50 transition duration-150"
            >
              Chỉnh sửa
            </Link>
            <button
              @click="deleteAssessment"
              class="bg-red-600 text-white px-4 py-2 rounded-md shadow-sm text-sm font-medium hover:bg-red-700 transition duration-150"
            >
              Xóa
            </button>
          </div>
        </div>

        <!-- Main Info -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <!-- Overall score card -->
          <div class="bg-white p-6 rounded-lg shadow border border-gray-100 flex flex-col items-center justify-center text-center">
            <span class="text-xs font-semibold text-gray-500">Điểm tổng thể</span>
            <div class="relative flex items-center justify-center my-6">
              <!-- Score text -->
              <span class="text-5xl font-bold" :class="getScoreColor(assessment.overall_score)">
                {{ assessment.overall_score }}%
              </span>
            </div>
            <span class="text-sm font-medium text-gray-700">
              {{ getScoreSummaryLabel(assessment.overall_score) }}
            </span>
          </div>

          <!-- Notes Card -->
          <div class="bg-white p-6 rounded-lg shadow border border-gray-100 md:col-span-2 flex flex-col justify-between">
            <div>
              <h3 class="text-sm font-semibold text-gray-500 mb-2">Quan sát & Ghi chú</h3>
              <p class="text-sm text-gray-700 leading-relaxed whitespace-pre-line">
                {{ assessment.notes || 'Không có ghi chú nào được ghi lại cho đánh giá này.' }}
              </p>
            </div>
            <div class="mt-4 pt-4 border-t border-gray-100 text-xs text-gray-400">
              Mã đánh giá: #{{ assessment.id }} &bull; Mã trẻ: #{{ assessment.child_id }}
            </div>
          </div>
        </div>

        <!-- Skill Domains List -->
        <div class="space-y-4">
          <h3 class="text-lg font-bold text-gray-900">Kết quả theo kỹ năng</h3>
          <div class="bg-white rounded-lg shadow border border-gray-100 overflow-hidden divide-y divide-gray-100">
            <div
              v-for="item in assessment.items"
              :key="item.id"
              class="p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:bg-gray-50/50 transition duration-150"
            >
              <div class="space-y-1 sm:max-w-md">
                <h4 class="text-sm font-bold text-gray-900">
                  {{ skillTypes[item.skill_name] || item.skill_name }}
                </h4>
                <p class="text-xs text-gray-500 leading-relaxed" v-if="item.note">
                  {{ item.note }}
                </p>
                <p class="text-xs text-gray-300 italic" v-else>
                  Không có ghi chú kỹ năng cụ thể.
                </p>
              </div>

              <div class="flex items-center space-x-4">
                <!-- Level Badge -->
                <span
                  class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full"
                  :class="getLevelBadgeClass(item.level)"
                >
                  {{ levels[item.level] || 'Chưa xếp hạng' }}
                </span>

                <!-- Score Meter -->
                <div class="flex items-center space-x-2">
                  <span class="text-sm font-bold text-gray-900 w-10 text-right">
                    {{ item.score !== null ? item.score + '%' : 'N/A' }}
                  </span>
                  <div class="w-24 bg-gray-200 rounded-full h-2">
                    <div
                      class="h-2 rounded-full"
                      :class="getScoreBgColor(item.score)"
                      :style="{ width: (item.score || 0) + '%' }"
                    ></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '../../Components/layout/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';

const props = defineProps({
  assessment: Object,
  skillTypes: Object,
  levels: Object,
});

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
  if (score === null) return 'bg-gray-300';
  if (score >= 80) return 'bg-green-600';
  if (score >= 50) return 'bg-blue-600';
  if (score >= 30) return 'bg-yellow-500';
  return 'bg-red-600';
};

const getScoreSummaryLabel = (score) => {
  if (score === null) return 'Chưa đánh giá';
  if (score >= 80) return 'Thành thạo / Có năng lực tốt';
  if (score >= 50) return 'Đạt yêu cầu / Đang tiến bộ';
  if (score >= 30) return 'Đang hình thành / Cần tập trung';
  return 'Nguy cấp / Cần can thiệp ngay';
};

const getLevelBadgeClass = (level) => {
  switch (level) {
    case 'achieved':
      return 'bg-green-100 text-green-800';
    case 'developing':
      return 'bg-blue-100 text-blue-800';
    case 'emerging':
      return 'bg-yellow-100 text-yellow-800';
    case 'regression':
      return 'bg-red-100 text-red-800';
    default:
      return 'bg-gray-100 text-gray-800';
  }
};

const deleteAssessment = () => {
  if (confirm(`Bạn có chắc chắn muốn xóa đánh giá của ${props.assessment.child?.first_name}?`)) {
    router.delete(route('assessment.destroy', props.assessment.id));
  }
};
</script>