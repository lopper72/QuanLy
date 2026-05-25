<template>
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-5 border-b border-gray-200 mb-6 gap-4">
    <div>
      <h1 class="text-2xl md:text-3xl font-bold text-gray-900">{{ title }}</h1>
      <p v-if="description" class="mt-2 text-sm md:text-base text-gray-500 max-w-2xl leading-relaxed">
        {{ description }}
      </p>
    </div>
    <div class="flex flex-wrap items-center gap-3 sm:self-center">
      <slot />
    </div>
  </div>
</template>

<script setup>
defineProps({
  title: {
    type: String,
    required: true
  },
  description: {
    type: String,
    default: ''
  }
})
</script>