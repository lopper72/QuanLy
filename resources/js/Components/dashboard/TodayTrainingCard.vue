<script setup>
import { Link, router } from '@inertiajs/vue3';
import TrainingStatusBadge from '@/Components/training/TrainingStatusBadge.vue';
import ExerciseThumbnail from '@/Components/exercises/ExerciseThumbnail.vue';

defineProps({
  sessions: {
    type: Array,
    required: true,
  },
});

function updateSessionStatus(session, status) {
  router.patch(
    route('training.updateStatus', session.id),
    { status },
    {
      preserveScroll: true,
      preserveState: false,
    }
  );
}
</script>

<template>
  <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
    <div class="flex items-center justify-between border-b border-gray-50 pb-4">
      <div>
        <h3 class="text-lg font-bold text-gray-900">Lịch tập hôm nay</h3>
        <p class="text-xs text-gray-500">Các bài tập trong ngày được sắp theo dòng thời gian.</p>
      </div>
      <Link
        href="/training/create"
        class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-indigo-500"
      >
        Lên lịch
      </Link>
    </div>

    <div class="mt-4">
      <div v-if="sessions.length === 0" class="flex flex-col items-center justify-center py-8 text-center">
        <div class="rounded-full bg-gray-50 p-3">
          <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75" />
          </svg>
        </div>
        <h3 class="mt-2 text-sm font-semibold text-gray-900">Hôm nay chưa có lịch tập</h3>
        <p class="mt-1 text-xs text-gray-500">Khi có lịch tập mới, các bài sẽ xuất hiện tại đây.</p>
      </div>

      <div v-else class="max-h-[420px] space-y-5 overflow-y-auto pr-1">
        <section v-for="group in sessions" :key="group.child_id" class="space-y-3">
          <div class="flex items-center justify-between">
            <Link :href="`/children/${group.child_id}`" class="text-sm font-bold text-gray-900 hover:underline">
              {{ group.child_name }}
            </Link>
            <span class="text-xs font-semibold text-gray-400">{{ group.sessions.length }} buổi</span>
          </div>

          <div class="space-y-3 border-l-2 border-indigo-100 pl-4">
            <div
              v-for="session in group.sessions"
              :key="session.id"
              class="relative rounded-lg border border-gray-100 bg-gray-50 p-3"
            >
              <span class="absolute -left-[23px] top-4 h-3 w-3 rounded-full bg-indigo-500 ring-4 ring-white"></span>

              <div class="flex items-center justify-between gap-3">
                <div class="min-w-0">
                  <div class="flex flex-wrap items-center gap-2">
                    <span class="text-sm font-bold text-indigo-700">
                      {{ String(session.scheduled_time || '--:--').slice(0, 5) }}
                    </span>
                    <TrainingStatusBadge :status="session.status || 'planned'" />
                  </div>
                  <p class="mt-1 text-xs text-gray-500">{{ session.total_minutes || 0 }} phút</p>
                </div>

                <div class="flex flex-wrap justify-end gap-2">
                  <button
                    v-if="session.status === 'planned'"
                    type="button"
                    class="rounded-md bg-amber-100 px-2.5 py-1.5 text-xs font-semibold text-amber-800 hover:bg-amber-200"
                    @click="updateSessionStatus(session, 'in_progress')"
                  >
                    Bắt đầu
                  </button>
                  <button
                    type="button"
                    class="rounded-md bg-emerald-100 px-2.5 py-1.5 text-xs font-semibold text-emerald-800 hover:bg-emerald-200"
                    @click="updateSessionStatus(session, 'completed')"
                  >
                    Hoàn thành
                  </button>
                  <button
                    type="button"
                    class="rounded-md bg-gray-100 px-2.5 py-1.5 text-xs font-semibold text-gray-800 hover:bg-gray-200"
                    @click="updateSessionStatus(session, 'skipped')"
                  >
                    Bỏ qua
                  </button>
                  <Link
                    :href="`/training/${session.id}`"
                    class="rounded-md bg-white px-2.5 py-1.5 text-xs font-semibold text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50"
                  >
                    Chi tiết
                  </Link>
                </div>
              </div>

              <div class="mt-3 space-y-2">
                <div
                  v-for="item in session.items"
                  :key="item.item_id"
                  class="flex items-center gap-3 rounded-md bg-white p-2"
                >
                  <ExerciseThumbnail :exercise="item.exercise" size="sm" :alt="item.exercise?.title || 'Bài tập'" />
                  <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-semibold text-gray-900">{{ item.exercise?.title || 'Bài tập' }}</p>
                    <p class="text-xs text-gray-500">{{ item.duration_minutes || 0 }} phút</p>
                  </div>
                  <TrainingStatusBadge :status="item.status || 'not_started'" type="item" />
                </div>
              </div>
            </div>
          </div>
        </section>
      </div>
    </div>
  </div>
</template>
