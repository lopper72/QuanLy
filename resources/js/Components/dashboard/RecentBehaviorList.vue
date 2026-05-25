<script setup>
import { Link } from '@inertiajs/vue3'
import { behaviorTypeLabels, severityLabels, labelFor } from '@/Lib/labels'

defineProps({
  logs: {
    type: Array,
    required: true
  }
})

const getSeverityClass = (severity) => {
  const s = String(severity).toLowerCase()
  if (s.includes('high') || s.includes('severe')) {
    return 'bg-red-50 text-red-700 ring-red-600/10'
  }
  if (s.includes('medium') || s.includes('moderate')) {
    return 'bg-yellow-50 text-yellow-800 ring-yellow-600/12'
  }
  return 'bg-green-50 text-green-700 ring-green-600/10'
}

const severityLabel = (severity) => {
  return labelFor(severityLabels, severity, 'Bình thường')
}

const behaviorLabel = (type) => {
  return labelFor(behaviorTypeLabels, type, 'Chưa xác định')
}
</script>

<template>
  <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
    <div class="flex items-center justify-between border-b border-gray-50 pb-4">
      <div>
        <h3 class="text-sm font-bold text-gray-900">Ghi nhận hành vi gần đây</h3>
        <p class="text-xs text-gray-500">Các hành vi mới được theo dõi</p>
      </div>
      <Link href="/behavior" class="text-xs font-semibold text-indigo-600 hover:text-indigo-500">
        Xem tất cả
      </Link>
    </div>

    <div class="mt-4">
      <div v-if="logs.length === 0" class="py-6 text-center text-xs text-gray-500">
        Chưa có ghi nhận hành vi gần đây.
      </div>

      <div v-else class="flow-root">
        <ul role="list" class="-my-5 divide-y divide-gray-50">
          <li v-for="log in logs" :key="log.id" class="py-4">
            <div class="flex items-start space-x-3">
              <div class="min-w-0 flex-1">
                <div class="flex items-center justify-between">
                  <p class="truncate text-sm font-semibold text-gray-900">
                    <Link :href="`/children/${log.child_id}`" class="hover:underline">
                      {{ log.child_name }}
                    </Link>
                  </p>
                  <span class="inline-flex items-center rounded-md px-1.5 py-0.5 text-xs font-medium ring-1 ring-inset" :class="getSeverityClass(log.severity)">
                    {{ severityLabel(log.severity) }}
                  </span>
                </div>
                <p class="mt-1 text-xs font-medium text-gray-800">{{ behaviorLabel(log.behavior_type) }}</p>
                <p v-if="log.note" class="mt-1 line-clamp-1 text-xs text-gray-500">{{ log.note }}</p>
                <div class="mt-1.5 text-[10px] text-gray-400">
                  {{ log.recorded_at }}
                </div>
              </div>
            </div>
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>
