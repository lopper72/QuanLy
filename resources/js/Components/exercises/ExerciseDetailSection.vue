<template>
  <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
    <h2 class="text-lg font-semibold text-slate-950">{{ title }}</h2>
    <p class="mt-3 whitespace-pre-wrap text-sm leading-7 text-slate-700">{{ content || fallback }}</p>
  </section>
</template>

<script setup>
defineProps({
  title: {
    type: String,
    required: true,
  },
  content: {
    type: String,
    default: '',
  },
  fallback: {
    type: String,
    default: '',
  },
});
</script>
