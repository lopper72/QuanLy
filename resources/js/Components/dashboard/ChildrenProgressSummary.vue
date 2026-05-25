<script setup>
import { Link } from '@inertiajs/vue3'
import { diagnosisLevelLabels, labelFor } from '@/Lib/labels'

defineProps({
  children: {
    type: Array,
    required: true
  }
})
</script>

<template>
  <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
    <div class="flex items-center justify-between border-b border-gray-50 pb-4">
      <div>
        <h3 class="text-base font-bold text-gray-900">Tóm tắt tiến bộ của trẻ</h3>
        <p class="text-xs text-gray-500">Tổng quan chỉ số phát triển theo từng trẻ</p>
      </div>
      <Link href="/children" class="text-xs font-semibold text-indigo-600 hover:text-indigo-500">
        Quản lý trẻ &rarr;
      </Link>
    </div>

    <div class="mt-4">
      <div v-if="children.length === 0" class="py-12 text-center text-sm text-gray-500">
        Chưa có hồ sơ trẻ trong hệ thống.
      </div>

      <div v-else class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-100">
          <thead>
            <tr class="text-left text-xs font-semibold text-gray-400">
              <th scope="col" class="py-3 pr-4">Tên trẻ</th>
              <th scope="col" class="py-3 px-4">Chẩn đoán</th>
              <th scope="col" class="py-3 px-4 text-center">Buổi tập (xong/tổng)</th>
              <th scope="col" class="py-3 px-4 text-center">Hành vi đã ghi nhận</th>
              <th scope="col" class="py-3 px-4 text-center">Đánh giá mới nhất</th>
              <th scope="col" class="py-3 pl-4 text-right">Thao tác</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50 text-sm">
            <tr v-for="child in children" :key="child.id" class="hover:bg-gray-50/55 transition-colors">
              <td class="py-3.5 pr-4 whitespace-nowrap">
                <Link :href="`/children/${child.id}`" class="font-semibold text-gray-900 hover:underline">
                  {{ child.full_name }}
                </Link>
              </td>
              <td class="py-3.5 px-4 whitespace-nowrap text-xs text-gray-600">
                <span class="inline-flex items-center rounded-md bg-gray-50 px-2 py-1 font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10">
                  {{ labelFor(diagnosisLevelLabels, child.diagnosis_level) }}
                </span>
              </td>
              <td class="py-3.5 px-4 whitespace-nowrap text-center text-xs font-medium text-gray-700">
                <div class="flex items-center justify-center gap-1.5">
                  <span class="text-indigo-600 font-bold">{{ child.completed_sessions }}</span>
                  <span class="text-gray-300">/</span>
                  <span class="text-gray-500">{{ child.total_sessions }}</span>
                </div>
              </td>
              <td class="py-3.5 px-4 whitespace-nowrap text-center">
                <span v-if="child.behavior_logs_count > 0" class="inline-flex items-center rounded-md bg-amber-50 px-2.5 py-0.5 text-xs font-medium text-amber-800 ring-1 ring-inset ring-amber-600/10">
                  {{ child.behavior_logs_count }} ghi nhận
                </span>
                <span v-else class="text-xs text-gray-400">Chưa có</span>
              </td>
              <td class="py-3.5 px-4 whitespace-nowrap text-center">
                <div v-if="child.latest_assessment_score !== null" class="flex flex-col items-center">
                  <span class="text-xs font-bold text-gray-800">{{ child.latest_assessment_score }} / 100</span>
                  <span class="text-[9px] text-gray-400">{{ child.latest_assessment_date }}</span>
                </div>
                <span v-else class="text-xs text-gray-400">—</span>
              </td>
              <td class="py-3.5 pl-4 whitespace-nowrap text-right">
                <Link :href="`/children/${child.id}`" class="text-xs font-semibold text-indigo-600 hover:text-indigo-900">
                  Xem hồ sơ
                </Link>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
