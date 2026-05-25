<script setup>
import { Link } from '@inertiajs/vue3'
import TrainingStatusBadge from '@/Components/training/TrainingStatusBadge.vue'
import ExerciseThumbnail from '@/Components/exercises/ExerciseThumbnail.vue'

defineProps({
  sessions: {
    type: Array,
    required: true
  }
})
</script>

<template>
  <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
    <div class="flex items-center justify-between border-b border-gray-50 pb-4">
      <div>
        <h3 class="text-sm font-bold text-gray-900">Can thiệp gần đây</h3>
        <p class="text-xs text-gray-500">Lịch sử các buổi tập mới nhất</p>
      </div>
      <Link href="/training" class="text-xs font-semibold text-indigo-600 hover:text-indigo-500">
        Xem tất cả
      </Link>
    </div>

    <div class="mt-4">
      <div v-if="sessions.length === 0" class="py-6 text-center text-xs text-gray-500">
        Chưa có buổi tập gần đây.
      </div>
      
      <div v-else class="flow-root">
        <ul role="list" class="-my-5 divide-y divide-gray-50">
          <li v-for="session in sessions" :key="session.id" class="py-4">
            <div class="flex items-center space-x-4">
              <div class="min-w-0 flex-1">
                <p class="truncate text-sm font-semibold text-gray-900">
                  <Link :href="`/children/${session.child_id}`" class="hover:underline">
                    {{ session.child_name }}
                  </Link>
                </p>
                <div class="mt-1 flex items-center space-x-2 text-xs text-gray-500">
                  <span>{{ session.session_date }}</span>
                  <span v-if="session.scheduled_time" class="font-medium text-indigo-600">
                    &bull; {{ session.scheduled_time }}
                  </span>
                  <span>&bull;</span>
                  <span>{{ session.total_minutes }} phút</span>
                </div>
                <div v-if="session.exercise_thumbnails?.length" class="mt-2 flex -space-x-2">
                  <ExerciseThumbnail
                    v-for="exercise in session.exercise_thumbnails"
                    :key="exercise.id"
                    :exercise="exercise"
                    size="sm"
                    :alt="exercise.title"
                    class="ring-2 ring-white"
                  />
                </div>
              </div>
              <div class="flex items-center space-x-2">
                <TrainingStatusBadge :status="session.status" />
                <Link :href="`/training/${session.id}`" class="inline-flex items-center rounded-md bg-white px-2 py-1 text-xs font-semibold text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                  Xem
                </Link>
              </div>
            </div>
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>
