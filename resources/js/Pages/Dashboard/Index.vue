<script setup>
import AppLayout from '@/Components/layout/AppLayout.vue'
import StatCard from '@/Components/dashboard/StatCard.vue'
import TodayTrainingCard from '@/Components/dashboard/TodayTrainingCard.vue'
import WeeklyCompletionCard from '@/Components/dashboard/WeeklyCompletionCard.vue'
import RecentSessionsList from '@/Components/dashboard/RecentSessionsList.vue'
import RecentBehaviorList from '@/Components/dashboard/RecentBehaviorList.vue'
import LatestAssessmentsList from '@/Components/dashboard/LatestAssessmentsList.vue'
import ChildrenProgressSummary from '@/Components/dashboard/ChildrenProgressSummary.vue'
import QuickLinks from '@/Components/dashboard/QuickLinks.vue'

defineProps({
  overview_stats: {
    type: Object,
    required: true
  },
  today_training_summary: {
    type: Array,
    required: true
  },
  weekly_training_completion: {
    type: Object,
    required: true
  },
  recent_training_sessions: {
    type: Array,
    required: true
  },
  recent_behavior_logs: {
    type: Array,
    required: true
  },
  latest_assessments: {
    type: Array,
    required: true
  },
  children_progress_summary: {
    type: Array,
    required: true
  },
  today_supplement_reminders: {
    type: Array,
    default: () => []
  },
  today_meal_reminders: {
    type: Array,
    default: () => []
  },
  latest_stool_note: {
    type: Object,
    default: null
  }
})
</script>

<template>
  <AppLayout>
    <div class="space-y-8 pb-12">
      <!-- Welcome Header -->
      <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Bảng điều khiển</h1>
          <p class="text-sm text-gray-500">Hệ thống Quản lý Can thiệp & Phát triển Trẻ em</p>
        </div>
        <div class="flex items-center gap-3">
          <span class="inline-flex items-center gap-1.5 rounded-full bg-green-50 px-3 py-1.5 text-xs font-semibold text-green-700 ring-1 ring-inset ring-green-600/20">
            <span class="h-1.5 w-1.5 rounded-full bg-green-500"></span>
            Hệ thống Trực tuyến
          </span>
        </div>
      </div>

      <!-- Stats Overview Row -->
      <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <StatCard 
          title="Tổng số trẻ" 
          :value="overview_stats.total_children" 
          description="Trẻ em đã đăng ký trong chương trình"
          icon-class="text-indigo-600 bg-indigo-50"
        >
          <template #icon>
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.109A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
            </svg>
          </template>
        </StatCard>

        <StatCard 
          title="Bài tập hoạt động" 
          :value="overview_stats.active_exercises" 
          description="Các mẫu bài tập trong thư viện"
          icon-class="text-blue-600 bg-blue-50"
        >
          <template #icon>
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
            </svg>
          </template>
        </StatCard>

        <StatCard 
          title="Buổi tập hôm nay" 
          :value="overview_stats.today_sessions_count" 
          description="Tổng số buổi tập đã lên lịch hôm nay"
          icon-class="text-emerald-600 bg-emerald-50"
        >
          <template #icon>
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
            </svg>
          </template>
        </StatCard>

        <StatCard 
          title="Đã hoàn thành" 
          :value="overview_stats.today_completed_count" 
          description="Các buổi tập đã hoàn thành hôm nay"
          icon-class="text-purple-600 bg-purple-50"
        >
          <template #icon>
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </template>
        </StatCard>
      </div>

      <div class="grid grid-cols-1 gap-3 rounded-lg border border-gray-100 bg-white p-4 shadow-sm sm:grid-cols-3">
        <div>
          <p class="text-xs font-semibold text-gray-400">Đang can thiệp</p>
          <p class="mt-1 text-2xl font-bold text-emerald-700">{{ overview_stats.active_children_count }}</p>
        </div>
        <div>
          <p class="text-xs font-semibold text-gray-400">Tạm nghỉ</p>
          <p class="mt-1 text-2xl font-bold text-amber-700">{{ overview_stats.paused_children_count }}</p>
        </div>
        <div>
          <p class="text-xs font-semibold text-gray-400">Ngừng can thiệp</p>
          <p class="mt-1 text-2xl font-bold text-slate-600">{{ overview_stats.voided_children_count }}</p>
        </div>
      </div>

      <!-- Main Operational Row (Today's Training vs. Weekly Completion Chart) -->
      <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <TodayTrainingCard :sessions="today_training_summary" />
        <WeeklyCompletionCard :stats="weekly_training_completion" />
      </div>

      <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <section class="rounded-lg bg-white p-5 shadow">
          <h2 class="text-lg font-semibold text-gray-900">Lịch bổ sung hôm nay</h2>
          <div class="mt-4 space-y-3">
            <div v-for="item in today_supplement_reminders" :key="item.id" class="rounded-md border border-gray-200 p-3">
              <p class="text-sm font-semibold text-gray-900">{{ item.name }}</p>
              <p class="text-xs text-gray-500">{{ item.child_name }} · {{ item.display_time }}</p>
            </div>
            <p v-if="today_supplement_reminders.length === 0" class="text-sm text-gray-500">Chưa có lịch bổ sung hôm nay.</p>
          </div>
        </section>

        <section class="rounded-lg bg-white p-5 shadow">
          <h2 class="text-lg font-semibold text-gray-900">Lịch ăn uống hôm nay</h2>
          <div class="mt-4 space-y-3">
            <div v-for="item in today_meal_reminders" :key="item.id" class="rounded-md border border-gray-200 p-3">
              <p class="text-sm font-semibold text-gray-900">{{ item.meal_time }}</p>
              <p class="text-xs text-gray-500">{{ item.title }}</p>
            </div>
            <p v-if="today_meal_reminders.length === 0" class="text-sm text-gray-500">Chưa có lịch ăn uống hôm nay.</p>
          </div>
        </section>

        <section class="rounded-lg bg-white p-5 shadow">
          <h2 class="text-lg font-semibold text-gray-900">Ghi nhận đi tiêu gần nhất</h2>
          <div v-if="latest_stool_note" class="mt-4 rounded-md border border-gray-200 p-3">
            <p class="text-sm font-semibold text-gray-900">{{ latest_stool_note.child_name }}</p>
            <p class="mt-1 text-sm text-gray-600">{{ latest_stool_note.stool_note }}</p>
            <p v-if="latest_stool_note.water_note" class="mt-1 text-xs text-gray-500">{{ latest_stool_note.water_note }}</p>
          </div>
          <p v-else class="mt-4 text-sm text-gray-500">Chưa có ghi nhận đi tiêu.</p>
        </section>
      </div>

      <!-- Full-Width Progress Table -->
      <ChildrenProgressSummary :children="children_progress_summary" />

      <!-- Historic Logs / Timelines grid -->
      <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <RecentSessionsList :sessions="recent_training_sessions" />
        <RecentBehaviorList :logs="recent_behavior_logs" />
        <LatestAssessmentsList :assessments="latest_assessments" />
      </div>

      <!-- Quick Action Buttons -->
      <QuickLinks />
    </div>
  </AppLayout>
</template>
