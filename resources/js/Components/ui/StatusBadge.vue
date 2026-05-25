<template>
  <span :class="badgeClasses">
    <span v-if="dot" class="mr-1.5 h-2 w-2 rounded-full" :class="dotClasses"></span>
    <slot>{{ label }}</slot>
  </span>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  type: {
    type: String,
    default: 'neutral' // success, warning, danger, info, primary, neutral
  },
  label: {
    type: String,
    default: ''
  },
  status: {
    type: String,
    default: ''
  },
  dot: {
    type: Boolean,
    default: false
  }
})

const badgeClasses = computed(() => {
  const base = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border'
  const colors = {
    success: 'bg-green-50 text-green-700 border-green-200',
    warning: 'bg-yellow-50 text-yellow-700 border-yellow-200',
    danger: 'bg-red-50 text-red-700 border-red-200',
    info: 'bg-blue-50 text-blue-700 border-blue-200',
    primary: 'bg-indigo-50 text-indigo-700 border-indigo-200',
    neutral: 'bg-gray-50 text-gray-700 border-gray-200'
  }
  return `${base} ${colors[props.type] || colors.neutral}`
})

const label = computed(() => {
  const labels = {
    planned: 'Đã lên lịch',
    in_progress: 'Đang thực hiện',
    completed: 'Hoàn thành',
    skipped: 'Bỏ qua',
    not_started: 'Chưa bắt đầu',
    partially_completed: 'Hoàn thành một phần',
    low: 'Nhẹ',
    medium: 'Trung bình',
    high: 'Cao',
    emerging: 'Đang hình thành',
    developing: 'Đang phát triển',
    achieved: 'Đã đạt được',
    regression: 'Thoái lui',
    stable: 'Ổn định',
    improving: 'Tiến bộ',
    regressing: 'Giảm sút',
  }

  return props.label || labels[props.status] || props.status
})

const dotClasses = computed(() => {
  const colors = {
    success: 'bg-green-400',
    warning: 'bg-yellow-400',
    danger: 'bg-red-400',
    info: 'bg-blue-400',
    primary: 'bg-indigo-400',
    neutral: 'bg-gray-400'
  }
  return colors[props.type] || colors.neutral
})
</script>
