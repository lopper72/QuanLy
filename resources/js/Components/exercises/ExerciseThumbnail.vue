<template>
  <img
    :src="imageUrl"
    :alt="alt"
    class="shrink-0 rounded-md object-cover bg-slate-100"
    :class="sizeClass"
    loading="lazy"
    @error="useFallback"
  />
</template>

<script setup>
import { computed, ref, watch } from 'vue';

const props = defineProps({
  exercise: {
    type: Object,
    default: null,
  },
  alt: {
    type: String,
    default: 'Ảnh bài tập',
  },
  size: {
    type: String,
    default: 'md',
  },
});

const fallbackUrl = '/images/exercise-placeholder.svg';
const failed = ref(false);

watch(
  () => props.exercise?.thumbnail_path,
  () => {
    failed.value = false;
  }
);

const imageUrl = computed(() => {
  if (failed.value || !props.exercise?.thumbnail_path) {
    return fallbackUrl;
  }

  return `/storage/${props.exercise.thumbnail_path}`;
});

const sizeClass = computed(() => {
  const sizes = {
    sm: 'h-10 w-10',
    md: 'h-12 w-12',
    lg: 'h-16 w-16',
    card: 'h-48 w-full rounded-none',
  };

  return sizes[props.size] || sizes.md;
});

const useFallback = () => {
  failed.value = true;
};
</script>
