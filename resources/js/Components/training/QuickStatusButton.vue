<template>
  <button
    type="button"
    :disabled="disabled"
    :class="[
      'inline-flex items-center justify-center p-2 rounded-full border transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2',
      colorsClass,
      disabled ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'
    ]"
    :title="action === 'complete' ? 'Đánh dấu hoàn thành' : 'Bỏ qua bài tập'"
  >
    <!-- Complete Icon -->
    <svg
      v-if="action === 'complete'"
      class="w-5 h-5"
      fill="none"
      stroke="currentColor"
      viewBox="0 0 24 24"
    >
      <path
        stroke-linecap="round"
        stroke-linejoin="round"
        stroke-width="2.5"
        d="M5 13l4 4L19 7"
      />
    </svg>
    <!-- Skip/Close Icon -->
    <svg
      v-else
      class="w-5 h-5"
      fill="none"
      stroke="currentColor"
      viewBox="0 0 24 24"
    >
      <path
        stroke-linecap="round"
        stroke-linejoin="round"
        stroke-width="2.5"
        d="M6 18L18 6M6 6l12 12"
      />
    </svg>
  </button>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  action: {
    type: String,
    required: true,
    validator: (value) => ['complete', 'skip'].includes(value)
  },
  active: {
    type: Boolean,
    default: false
  },
  disabled: {
    type: Boolean,
    default: false
  }
});

const colorsClass = computed(() => {
  if (props.action === 'complete') {
    if (props.active) {
      return 'bg-green-600 border-green-600 text-white hover:bg-green-700 focus:ring-green-500 shadow-sm';
    }
    return 'bg-white border-gray-300 text-gray-400 hover:text-green-600 hover:bg-green-50 hover:border-green-300 focus:ring-green-500';
  } else {
    // skip action
    if (props.active) {
      return 'bg-gray-600 border-gray-600 text-white hover:bg-gray-700 focus:ring-gray-500 shadow-sm';
    }
    return 'bg-white border-gray-300 text-gray-400 hover:text-gray-600 hover:bg-gray-50 hover:border-gray-400 focus:ring-gray-500';
  }
});
</script>
