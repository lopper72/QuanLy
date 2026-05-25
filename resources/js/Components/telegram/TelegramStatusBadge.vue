<template>
  <span :class="classes">
    {{ label }}
  </span>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  status: {
    type: String,
    default: 'pending',
  },
});

const labels = {
  sent: 'Đã gửi',
  failed: 'Thất bại',
  pending: 'Đang gửi',
  received: 'Đã nhận',
};

const styles = {
  sent: 'bg-emerald-50 text-emerald-700 ring-emerald-200',
  failed: 'bg-rose-50 text-rose-700 ring-rose-200',
  pending: 'bg-amber-50 text-amber-700 ring-amber-200',
  received: 'bg-sky-50 text-sky-700 ring-sky-200',
};

const label = computed(() => labels[props.status] ?? 'Đang gửi');
const classes = computed(() => [
  styles[props.status] ?? styles.pending,
  'inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium ring-1 ring-inset',
]);
</script>
