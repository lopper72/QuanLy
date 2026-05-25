<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
  assessments: {
    type: Array,
    required: true
  }
})

const getScoreColor = (score) => {
  if (score >= 80) return 'text-green-600 bg-green-50'
  if (score >= 50) return 'text-yellow-600 bg-yellow-50'
  return 'text-red-600 bg-red-50'
}
</script>

<template>
  <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
    <div class="flex items-center justify-between border-b border-gray-50 pb-4">
      <div>
        <h3 class="text-sm font-bold text-gray-900">Đánh giá mới nhất</h3>
        <p class="text-xs text-gray-500">Đánh giá kỹ năng nhận thức và vận động</p>
      </div>
      <Link href="/assessment" class="text-xs font-semibold text-indigo-600 hover:text-indigo-500">
        Xem tất cả
      </Link>
    </div>

    <div class="mt-4">
      <div v-if="assessments.length === 0" class="py-6 text-center text-xs text-gray-500">
        Chưa có đánh giá gần đây.
      </div>

      <div v-else class="flow-root">
        <ul role="list" class="-my-5 divide-y divide-gray-50">
          <li v-for="assessment in assessments" :key="assessment.id" class="py-4">
            <div class="flex items-center justify-between space-x-4">
              <div class="min-w-0 flex-1">
                <p class="truncate text-sm font-semibold text-gray-900">
                  <Link :href="`/children/${assessment.child_id}`" class="hover:underline">
                    {{ assessment.child_name }}
                  </Link>
                </p>
                <p class="mt-0.5 text-xs text-gray-500">Ngày: {{ assessment.assessment_date }}</p>
                <p v-if="assessment.notes" class="mt-1 line-clamp-1 text-xs text-gray-400 italic">
                  "{{ assessment.notes }}"
                </p>
              </div>
              <div class="flex items-center space-x-2">
                <span class="inline-flex h-9 w-9 items-center justify-center rounded-full text-xs font-bold ring-1 ring-inset ring-gray-100" :class="getScoreColor(assessment.overall_score)">
                  {{ assessment.overall_score }}
                </span>
                <Link :href="`/assessment`" class="inline-flex items-center rounded-md bg-white px-2 py-1 text-xs font-semibold text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
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
