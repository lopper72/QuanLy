<template>
  <div>
    <EmptyState
      v-if="sessions.length === 0"
      title="Chưa có buổi tập"
      description="Bắt đầu bằng cách tạo lịch buổi tập mới."
    >
      <template #icon>
        <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
      </template>
    </EmptyState>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <TrainingSessionCard
        v-for="session in sessions"
        :key="session.id"
        :session="session"
      />
    </div>
  </div>
</template>

<script setup>
import TrainingSessionCard from './TrainingSessionCard.vue';
import EmptyState from '../ui/EmptyState.vue';

defineProps({
  sessions: {
    type: Array,
    required: true,
  },
});
</script>
