<template>
  <AppLayout>
    <div class="space-y-6">
      <!-- Flash Messages -->
      <div v-if="$page.props.flash?.success" class="bg-green-50 border border-green-200 text-green-800 rounded-md p-4 text-sm flex items-center shadow-sm">
        <svg class="w-4 h-4 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        {{ $page.props.flash.success }}
      </div>

      <!-- Page Header -->
      <PageHeader
        title="Danh sách tập luyện hôm nay"
        description="Giao diện thực hiện hàng ngày tần suất cao. Hoàn thành nhanh các nhiệm vụ, bỏ qua các mục không áp dụng và ghi chú của chuyên gia một cách nhanh chóng."
      >
        <template #actions>
          <Link
            :href="route('training.index')"
            class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-semibold rounded-md text-gray-700 bg-white hover:bg-gray-50 transition duration-150 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
          >
            <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            Lịch đầy đủ
          </Link>
          <button
            type="button"
            class="inline-flex items-center px-4 py-2 border border-emerald-300 shadow-sm text-sm font-semibold rounded-md text-emerald-700 bg-white hover:bg-emerald-50 transition duration-150 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500"
            @click="sendTodayTelegram"
          >
            Gửi lịch hôm nay qua Telegram
          </button>
        </template>
      </PageHeader>

      <!-- Today's Stats Bar -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
          <h4 class="text-xs font-semibold text-gray-400">Tổng số hôm nay</h4>
          <span class="text-2xl font-bold text-gray-900 mt-1 block">{{ sessions.length }}</span>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
          <h4 class="text-xs font-semibold text-gray-400">Đã hoàn thành hôm nay</h4>
          <span class="text-2xl font-bold text-green-600 mt-1 block">
            {{ sessions.filter(s => s.status === 'completed').length }}
          </span>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
          <h4 class="text-xs font-semibold text-gray-400">Đang hoạt động</h4>
          <span class="text-2xl font-bold text-amber-600 mt-1 block">
            {{ sessions.filter(s => s.status === 'in_progress').length }}
          </span>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
          <h4 class="text-xs font-semibold text-gray-400">Tổng số bài tập</h4>
          <span class="text-2xl font-bold text-primary-600 mt-1 block">
            {{ totalExercisesCount }}
          </span>
        </div>
      </div>

      <!-- Main Sessions List -->
      <div v-if="sessions.length > 0" class="space-y-6">
        <div v-for="session in sessions" :key="session.id">
          <TodaySessionCard :session="session" />
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="bg-white border border-gray-200 rounded-xl p-12 text-center shadow-sm">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
        </svg>
        <h3 class="mt-2 text-sm font-bold text-gray-900">Không có buổi tập nào được lên lịch cho hôm nay</h3>
        <p class="mt-1 text-sm text-gray-500">Bắt đầu bằng cách lên lịch một buổi tập mới cho hôm nay.</p>
        <div class="mt-6">
          <Link
            :href="route('training.create')"
            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-semibold rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Lên lịch buổi tập hôm nay
          </Link>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Components/layout/AppLayout.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';
import TodaySessionCard from '@/Components/training/TodaySessionCard.vue';

const props = defineProps({
  sessions: {
    type: Array,
    required: true,
    default: () => []
  }
});

const totalExercisesCount = computed(() => {
  return props.sessions.reduce((acc, session) => acc + (session.items?.length || 0), 0);
});

function sendTodayTelegram() {
  router.post(route('training.today.telegramSend'), {}, {
    preserveScroll: true,
  });
}
</script>
