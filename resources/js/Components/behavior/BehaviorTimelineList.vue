<template>
  <div class="space-y-4">
    <EmptyState
      v-if="safeGroups.length === 0"
      title="Chưa có ghi nhận hành vi"
      description="Không có ghi nhận hành vi nào phù hợp với bộ lọc hiện tại."
    >
      <template #icon>
        <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v10m8-10v10M5 11h14M5 15h14" />
        </svg>
      </template>
    </EmptyState>

    <template v-else>
      <BehaviorTimelineGroup
        v-for="group in safeGroups"
        :key="group.child?.id || 'unknown'"
        :group="group"
      />
    </template>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import EmptyState from '../ui/EmptyState.vue';
import BehaviorTimelineGroup from './BehaviorTimelineGroup.vue';

const props = defineProps({
  groups: {
    type: Array,
    required: true,
  },
});

const safeGroups = computed(() => props.groups.filter((group) => {
  return group.child && group.child.status === 'active' && !group.child.deleted_at;
}));
</script>
