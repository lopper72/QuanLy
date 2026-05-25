<template>
  <div class="md:grid md:grid-cols-3 md:gap-6 bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
    <div class="md:col-span-1 bg-gray-50 p-6 border-b md:border-b-0 md:border-r border-gray-200">
      <h3 class="text-lg font-bold text-gray-900 leading-6">{{ title }}</h3>
      <p v-if="description" class="mt-2 text-sm text-gray-500 leading-relaxed">
        {{ description }}
      </p>
      <slot name="aside" />
    </div>
    <div class="mt-5 md:mt-0 md:col-span-2 p-6">
      <form @submit.prevent="$emit('submit')">
        <div class="space-y-6">
          <slot />
        </div>
        <div v-if="$slots.actions" class="mt-8 pt-5 border-t border-gray-200 flex flex-wrap items-center justify-end gap-3">
          <slot name="actions" />
        </div>
      </form>
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

defineEmits(['submit'])
</script>