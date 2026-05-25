<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '../../Components/layout/AppLayout.vue';
import PageHeader from '../../Components/ui/PageHeader.vue';
import QuickBehaviorCard from '../../Components/behavior/QuickBehaviorCard.vue';
import RecentBehaviorList from '../../Components/behavior/RecentBehaviorList.vue';
import DailyBehaviorSummary from '../../Components/behavior/DailyBehaviorSummary.vue';

const props = defineProps({
  children: {
    type: Array,
    required: true,
  },
  defaultChildId: {
    type: [Number, String],
    default: null,
  },
  selectedChildId: {
    type: [Number, String],
    default: null,
  },
  presets: {
    type: Array,
    required: true,
  },
  severities: {
    type: Array,
    required: true,
  },
  recentBehaviors: {
    type: Array,
    required: true,
  },
  dailySummary: {
    type: Object,
    required: true,
  },
});
</script>

<template>
  <Head title="Ghi nhận hành vi nhanh" />

  <AppLayout>
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">
      <!-- Custom Header with Navigation Switcher -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <PageHeader
            title="Ghi nhận hành vi nhanh"
            description="Tối ưu cho việc ghi nhận hành vi theo thời gian thực trên điện thoại."
          />
        </div>
        
        <div class="flex items-center space-x-3">
          <Link
            :href="route('behavior.index')"
            class="inline-flex items-center px-4 py-2.5 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 active:bg-gray-100 text-sm font-semibold text-gray-700 transition shadow-sm"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-2 text-gray-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M3 12h18" />
              <path d="M3 6h18" />
              <path d="M3 18h18" />
            </svg>
            Toàn bộ ghi nhận
          </Link>
        </div>
      </div>

      <!-- Quick Summary Cards (Daily Count) -->
      <DailyBehaviorSummary :daily-summary="dailySummary" />

      <!-- Main Layout Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <!-- Left Column: The Logging Engine (Spans 7) -->
        <div class="lg:col-span-7 space-y-6">
          <div class="flex items-center justify-between px-2">
            <h2 class="text-base font-bold text-gray-900 flex items-center">
              <span class="w-2.5 h-2.5 rounded-full bg-blue-500 mr-2 animate-pulse"></span>
              Bảng ghi nhận nhanh
            </h2>
            <span class="text-xs text-gray-400 font-semibold bg-gray-50 px-2 py-1 rounded-md border border-gray-100">
              Phiên đang mở
            </span>
          </div>

          <QuickBehaviorCard
            :children="children"
            :presets="presets"
            :severities="severities"
            :initial-child-id="selectedChildId || defaultChildId"
          />
        </div>

        <!-- Right Column: Recent logs / Stats (Spans 5) -->
        <div class="lg:col-span-5 space-y-6">
          <RecentBehaviorList :recent-behaviors="recentBehaviors" />
          
          <!-- Quick User Help/Tips card -->
          <div class="bg-gradient-to-br from-indigo-900 to-blue-950 rounded-3xl p-6 text-white shadow-xl relative overflow-hidden">
            <!-- Background accent glow -->
            <div class="absolute right-0 top-0 w-32 h-32 bg-white/5 rounded-full blur-3xl -mr-8 -mt-8 pointer-events-none"></div>

            <h4 class="font-bold text-sm text-indigo-300 mb-2 flex items-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10" />
                <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3" />
                <line x1="12" y1="17" x2="12.01" y2="17" />
              </svg>
              Gợi ý ghi nhận nhanh
            </h4>
            
            <ul class="text-xs text-indigo-100/90 space-y-2 font-medium list-disc list-inside">
              <li>Ghim trang này trên màn hình chính của điện thoại để truy cập nhanh.</li>
              <li>Chọn trẻ để xem thống kê và lịch sử ghi nhận riêng.</li>
              <li>Các ghi nhận tại đây được tự động gắn thời gian chính xác.</li>
              <li>Thêm ghi chú ngắn để hỗ trợ phân tích đánh giá sau này.</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
