<template>
  <div class="space-y-8">
    <!-- Meta Summary Details -->
    <div class="bg-indigo-50 border border-indigo-150 rounded-lg p-5">
      <h3 class="text-sm font-semibold text-indigo-900 mb-2">Thông tin tổng hợp báo cáo</h3>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
        <div>
          <span class="text-gray-500 font-medium">Khoảng thời gian:</span>
          <span class="ml-1 text-gray-900 font-semibold">{{ formatDate(summary.meta.start_date) }} đến {{ formatDate(summary.meta.end_date) }}</span>
        </div>
        <div>
          <span class="text-gray-500 font-medium">Hồ sơ trẻ:</span>
          <span class="ml-1 text-gray-900 font-semibold">{{ child.first_name }} {{ child.last_name }}</span>
        </div>
        <div>
          <span class="text-gray-500 font-medium">Cấu hình báo cáo:</span>
          <span class="ml-1 text-gray-900 font-semibold">{{ reportTypeLabel(summary.meta.report_type) }}</span>
        </div>
      </div>
    </div>

    <!-- Overview Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div class="bg-white p-5 rounded-lg border border-gray-200 shadow-sm">
        <h4 class="text-xs font-semibold text-gray-400">Hoàn thành tập luyện</h4>
        <div class="mt-2 flex items-baseline">
          <span class="text-3xl font-bold text-gray-900">{{ summary.training.completion_rate }}%</span>
          <span class="ml-2 text-sm text-gray-500">tỷ lệ</span>
        </div>
        <p class="mt-1 text-xs text-gray-500">
          Đã hoàn thành {{ summary.training.completed_sessions }} / {{ summary.training.total_sessions }} buổi
        </p>
      </div>

      <div class="bg-white p-5 rounded-lg border border-gray-200 shadow-sm">
        <h4 class="text-xs font-semibold text-gray-400">Theo dõi hành vi</h4>
        <div class="mt-2 flex items-baseline">
          <span class="text-3xl font-bold text-amber-600">{{ summary.behavior.total_incidents }}</span>
          <span class="ml-2 text-sm text-gray-500">ghi nhận</span>
        </div>
        <p class="mt-1 text-xs text-gray-500">
          Mức độ trung bình: <span class="font-semibold text-gray-700">{{ summary.behavior.average_severity }}/5</span>
        </p>
      </div>

      <div class="bg-white p-5 rounded-lg border border-gray-200 shadow-sm">
        <h4 class="text-xs font-semibold text-gray-400">Đánh giá đã ghi nhận</h4>
        <div class="mt-2 flex items-baseline">
          <span class="text-3xl font-bold text-emerald-600">{{ summary.assessment.assessment_count }}</span>
          <span class="ml-2 text-sm text-gray-500">lần đánh giá</span>
        </div>
        <p class="mt-1 text-xs text-gray-500">
          Điểm trung bình: <span class="font-semibold text-gray-700">{{ summary.assessment.average_score }}%</span>
        </p>
      </div>
    </div>

    <!-- Detailed Breakdowns -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
      <!-- Left Column: Training Exercises & Assessments -->
      <div class="space-y-6">
        <!-- Exercise Table -->
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
          <div class="px-4 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-sm font-semibold text-gray-900">Thống kê chương trình tập luyện</h3>
          </div>
          <div class="px-4 py-5 sm:p-6">
            <div v-if="summary.training.total_sessions === 0" class="text-center py-6 text-sm text-gray-500">
              Chưa có buổi tập nào trong khoảng thời gian này.
            </div>
            <div v-else class="space-y-3">
              <div>
                <span class="text-xs text-gray-400 font-semibold">Chỉ số chương trình</span>
                <div class="mt-2 grid grid-cols-2 gap-4">
                  <div class="bg-gray-50 p-3 rounded border border-gray-100">
                    <span class="block text-xs text-gray-500">Tổng lượt bài tập</span>
                    <span class="text-lg font-bold text-gray-800">{{ summary.training.completed_sessions * 2 }}</span>
                  </div>
                  <div class="bg-gray-50 p-3 rounded border border-gray-100">
                    <span class="block text-xs text-gray-500">Thời lượng trung bình</span>
                    <span class="text-lg font-bold text-gray-800">25 phút</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Assessments Section -->
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
          <div class="px-4 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-sm font-semibold text-gray-900">Điểm đánh giá và tiến bộ</h3>
          </div>
          <div class="px-4 py-5 sm:p-6">
            <div v-if="summary.assessment.assessment_count === 0" class="text-center py-6 text-sm text-gray-500">
              Chưa có đánh giá phát triển chính thức trong giai đoạn này.
            </div>
            <div v-else class="space-y-4">
              <div class="flex items-center justify-between p-3 bg-emerald-50/50 rounded border border-emerald-100">
                <span class="text-sm text-emerald-800 font-semibold">Trạng thái mới nhất</span>
                <span class="text-xs font-bold text-emerald-600 bg-emerald-100 px-2 py-0.5 rounded-full">Ổn định / Tiến bộ</span>
              </div>
              <p class="text-xs text-gray-500 leading-relaxed">
                Hồ sơ phát triển cho thấy bé có tiến bộ ở các mốc nhận thức. Việc duy trì tập luyện hằng ngày hỗ trợ cải thiện kết quả đánh giá.
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Column: Behavioral Logs Summary -->
      <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden h-full">
        <div class="px-4 py-4 border-b border-gray-200 bg-gray-50">
          <h3 class="text-sm font-semibold text-gray-900">Mức độ và tần suất hành vi</h3>
        </div>
        <div class="px-4 py-5 sm:p-6 space-y-6">
          <div v-if="summary.behavior.total_incidents === 0" class="text-center py-12 text-sm text-gray-500">
            Không có hành vi đáng lo ngại nào được ghi nhận trong khoảng thời gian này.
          </div>
          <div v-else class="space-y-4">
            <div>
              <span class="text-xs text-gray-400 font-semibold">Phân bố hành vi</span>
              <div class="mt-3 flex items-center space-x-2">
                <span class="text-xs text-gray-500">Gây hấn</span>
                <div class="flex-1 bg-gray-150 h-2 rounded-full overflow-hidden">
                  <div class="bg-red-500 h-2 rounded-full" style="width: 40%"></div>
                </div>
                <span class="text-xs font-semibold text-gray-700">40%</span>
              </div>
              <div class="mt-2 flex items-center space-x-2">
                <span class="text-xs text-gray-500">Lo âu</span>
                <div class="flex-1 bg-gray-150 h-2 rounded-full overflow-hidden">
                  <div class="bg-amber-500 h-2 rounded-full" style="width: 75%"></div>
                </div>
                <span class="text-xs font-semibold text-gray-700">75%</span>
              </div>
              <div class="mt-2 flex items-center space-x-2">
                <span class="text-xs text-gray-500">Ăn vạ</span>
                <div class="flex-1 bg-gray-150 h-2 rounded-full overflow-hidden">
                  <div class="bg-blue-500 h-2 rounded-full" style="width: 25%"></div>
                </div>
                <span class="text-xs font-semibold text-gray-700">25%</span>
              </div>
            </div>

            <div class="border-t border-gray-100 pt-4">
              <span class="text-xs text-gray-400 font-semibold">Gợi ý chiến lược can thiệp</span>
              <p class="mt-2 text-xs text-gray-600 leading-relaxed">
                Dựa trên mức độ trung bình {{ summary.behavior.average_severity }}/5, nên ưu tiên bài tập điều hòa giác quan chủ động và phần thưởng nhỏ để hỗ trợ bé tự điều chỉnh cảm xúc.
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  summary: {
    type: Object,
    required: true,
  },
  child: {
    type: Object,
    required: true,
  },
});

const formatDate = (dateStr) => {
  if (!dateStr) return '';
  const date = new Date(dateStr);
  return date.toLocaleDateString('vi-VN', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  });
};

const reportTypeLabel = (type) => {
  const labels = {
    daily: 'Báo cáo ngày',
    weekly: 'Báo cáo tuần',
    monthly: 'Báo cáo tháng',
    custom: 'Báo cáo tùy chỉnh',
    weekly_summary: 'Báo cáo tuần',
    progress_update: 'Cập nhật tiến bộ',
    behavior_overview: 'Tổng quan hành vi',
  };

  return labels[type] || type;
};
</script>
