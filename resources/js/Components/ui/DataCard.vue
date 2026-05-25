<template>
  <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden flex flex-col hover:shadow-md transition-shadow duration-200">
    <div v-if="title || $slots.header" class="px-5 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
      <slot name="header">
        <h3 class="text-sm md:text-base font-bold text-gray-800">{{ title }}</h3>
      </slot>
      <div v-if="$slots.headerActions" class="flex items-center gap-2">
        <slot name="headerActions" />
      </div>
    </div>
    <div class="p-5 flex-grow">
      <slot />
    </div>
    <div v-if="$slots.footer" class="px-5 py-3.5 bg-gray-50 border-t border-gray-100 flex items-center justify-end gap-3 text-xs md:text-sm">
      <slot name="footer" />
    </div>
  </div>
</template>

<script setup>
defineProps({
  title: {
    type: String,
    default: ''
  }
})
</script>