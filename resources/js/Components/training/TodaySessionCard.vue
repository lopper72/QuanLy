<template>
  <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden hover:shadow-md transition-shadow duration-200">
    <!-- Header Area -->
    <div class="p-5 flex items-center justify-between border-b border-gray-100 flex-wrap gap-4">
      <div class="flex items-center space-x-3">
        <!-- Avatar/Initials -->
        <div class="w-10 h-10 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center font-bold text-sm">
          {{ childInitials }}
        </div>
        <div>
          <h3 class="font-bold text-gray-900 text-base">
            {{ session.child?.first_name }} {{ session.child?.last_name }}
          </h3>
          <p class="text-xs text-gray-500 flex items-center mt-0.5">
            <svg class="w-3.5 h-3.5 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <span v-if="session.scheduled_time" class="font-bold text-indigo-600 mr-2">{{ session.scheduled_time }}</span>
            Session #{{ session.id }} • {{ session.items?.length || 0 }} Items
          </p>
        </div>
      </div>

      <!-- Right section: Progress + Actions -->
      <div class="flex items-center space-x-4">
        <RemainingCount :items="session.items || []" />
        
        <ProgressRing :percentage="completionPercentage" :size="48" :stroke="4" />

        <button
          type="button"
          @click="isExpanded = !isExpanded"
          class="text-gray-400 hover:text-gray-600 focus:outline-none p-1 bg-gray-50 rounded-lg border border-gray-200 hover:bg-gray-100 transition-colors"
        >
          <svg
            :class="['w-5 h-5 transform transition-transform duration-200', isExpanded ? 'rotate-180' : '']"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
          </svg>
        </button>
      </div>
    </div>

    <!-- Collapsible Body Area -->
    <div v-show="isExpanded" class="p-5 bg-gray-50/50 space-y-4">
      <!-- Exercise Checklist -->
      <div>
        <h4 class="text-xs font-bold text-gray-400 mb-2">Danh sách bài tập</h4>
        <TodayExerciseChecklist v-if="session.items?.length > 0" :items="session.items" />
        <p v-else class="text-sm text-gray-500 italic">Chưa có bài tập nào cho buổi này.</p>
      </div>

      <!-- Quick Session Note -->
      <div class="border-t border-gray-100 pt-4 mt-4">
        <label :for="'notes-' + session.id" class="block text-xs font-bold text-gray-400 mb-2">
          Ghi chú chuyên viên / buổi tập
        </label>
        <div class="flex space-x-2">
          <textarea
            :id="'notes-' + session.id"
            v-model="localNotes"
            rows="2"
            class="flex-1 text-sm border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500 placeholder-gray-400"
            placeholder="Thêm ghi chú chuyên môn, điểm đạt được hoặc quan sát hành vi trong buổi tập hôm nay..."
          ></textarea>
          <button
            type="button"
            :disabled="!isNotesDirty || isSaving"
            @click="saveNotes"
            class="inline-flex items-center px-3.5 py-2 border border-transparent text-sm font-semibold rounded-lg shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:opacity-50 disabled:cursor-not-allowed flex-shrink-0 align-bottom h-fit"
          >
            {{ isSaving ? 'Đang lưu...' : 'Lưu' }}
          </button>
        </div>
        <p v-if="isNotesSaved" class="text-xs text-green-600 mt-1 flex items-center">
          <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
          </svg>
          Đã lưu ghi chú.
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import ProgressRing from '@/Components/training/ProgressRing.vue';
import RemainingCount from '@/Components/training/RemainingCount.vue';
import TodayExerciseChecklist from '@/Components/training/TodayExerciseChecklist.vue';

const props = defineProps({
  session: {
    type: Object,
    required: true
  }
});

const isExpanded = ref(true);
const localNotes = ref(props.session.notes || '');
const isSaving = ref(false);
const isNotesSaved = ref(false);

// Track if notes changed from initial/original
const isNotesDirty = computed(() => {
  return localNotes.value !== (props.session.notes || '');
});

// Update local notes if session notes change externally (e.g. page reloads)
watch(() => props.session.notes, (newVal) => {
  localNotes.value = newVal || '';
});

const childInitials = computed(() => {
  const first = props.session.child?.first_name?.[0] || '';
  const last = props.session.child?.last_name?.[0] || '';
  return (first + last).toUpperCase();
});

const completionPercentage = computed(() => {
  const items = props.session.items || [];
  if (items.length === 0) return 0;
  
  const processed = items.filter(
    item => ['completed', 'skipped'].includes(item.completion_status)
  ).length;

  return (processed / items.length) * 100;
});

const saveNotes = () => {
  if (isSaving.value) return;
  isSaving.value = true;
  isNotesSaved.value = false;

  router.patch(route('training.quickNote', props.session.id), {
    notes: localNotes.value
  }, {
    preserveScroll: true,
    onSuccess: () => {
      isNotesSaved.value = true;
      setTimeout(() => {
        isNotesSaved.value = false;
      }, 3000);
    },
    onFinish: () => {
      isSaving.value = false;
    }
  });
};
</script>
