<template>
  <div class="space-y-3">
    <div
      v-for="item in items"
      :key="item.id"
      :class="[
        'flex items-center justify-between p-4 bg-white rounded-lg border transition-all duration-200',
        item.completion_status === 'completed'
          ? 'border-green-200 bg-green-50/20'
          : item.completion_status === 'skipped'
          ? 'border-gray-200 bg-gray-50/40 opacity-75'
          : 'border-gray-200 hover:border-primary-200 hover:shadow-sm'
      ]"
    >
      <div class="flex items-start space-x-3 flex-1 min-w-0 mr-4">
        <ExerciseThumbnail :exercise="item.exercise" size="sm" :alt="item.exercise?.title || 'Bài tập'" />

        <!-- Exercise Details -->
        <div class="min-w-0 flex-1">
          <div class="flex items-center space-x-2 flex-wrap">
            <span
              :class="[
                'font-medium text-sm text-gray-900 truncate',
                (item.completion_status === 'completed' || item.completion_status === 'skipped') && 'line-through text-gray-500'
              ]"
            >
              {{ item.exercise?.title || 'Chưa rõ bài tập' }}
            </span>
            <span class="text-xs text-gray-500 bg-gray-100 px-1.5 py-0.5 rounded flex items-center">
              <svg class="w-3.5 h-3.5 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              {{ item.duration_minutes || 0 }} phút
            </span>
          </div>

          <p
            v-if="item.therapist_note"
            class="text-xs text-gray-500 mt-1 italic line-clamp-2"
          >
            "{{ item.therapist_note }}"
          </p>
        </div>
      </div>

      <!-- Quick Action Buttons -->
      <div class="flex items-center space-x-2 flex-shrink-0">
        <!-- Skip Button -->
        <QuickStatusButton
          action="skip"
          :active="item.completion_status === 'skipped'"
          :disabled="processingId === item.id"
          @click="toggleSkip(item)"
        />

        <!-- Complete Button -->
        <QuickStatusButton
          action="complete"
          :active="item.completion_status === 'completed'"
          :disabled="processingId === item.id"
          @click="toggleComplete(item)"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import QuickStatusButton from '@/Components/training/QuickStatusButton.vue';
import ExerciseThumbnail from '@/Components/exercises/ExerciseThumbnail.vue';

const props = defineProps({
  items: {
    type: Array,
    required: true,
    default: () => []
  }
});

const processingId = ref(null);

const toggleComplete = (item) => {
  if (processingId.value) return;
  processingId.value = item.id;

  // If already completed, set status back to planned/not_started via standard status route
  if (item.completion_status === 'completed') {
    router.patch(route('training.updateItemStatus', item.id), {
      status: 'not_started'
    }, {
      preserveScroll: true,
      onFinish: () => {
        processingId.value = null;
      }
    });
  } else {
    // Quick complete PATCH
    router.patch(route('trainingSessionItem.quickComplete', item.id), {}, {
      preserveScroll: true,
      onFinish: () => {
        processingId.value = null;
      }
    });
  }
};

const toggleSkip = (item) => {
  if (processingId.value) return;
  processingId.value = item.id;

  // If already skipped, set status back to planned/not_started
  if (item.completion_status === 'skipped') {
    router.patch(route('training.updateItemStatus', item.id), {
      status: 'not_started'
    }, {
      preserveScroll: true,
      onFinish: () => {
        processingId.value = null;
      }
    });
  } else {
    // Quick skip PATCH
    router.patch(route('trainingSessionItem.quickSkip', item.id), {}, {
      preserveScroll: true,
      onFinish: () => {
        processingId.value = null;
      }
    });
  }
};
</script>
