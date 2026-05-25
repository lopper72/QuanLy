<template>
  <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
    <h3 class="text-sm font-semibold text-gray-900 mb-3">Thêm bài tập vào buổi tập</h3>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
      <div>
        <label for="exercise" class="block text-xs font-medium text-gray-700 mb-1">
          Chọn bài tập
        </label>
        <select
          id="exercise"
          v-model="selectedExerciseId"
          class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
        >
          <option value="" disabled>-- Chọn --</option>
          <option v-for="ex in exercises" :key="ex.id" :value="ex.id">
            [{{ categoryLabel(ex.category) }}] {{ ex.title }}
          </option>
        </select>
        <div v-if="selectedExercise" class="mt-2 flex items-center gap-2 rounded-md border border-gray-200 bg-white p-2">
          <ExerciseThumbnail :exercise="selectedExercise" size="sm" :alt="selectedExercise.title" />
          <div class="min-w-0">
            <p class="truncate text-sm font-medium text-gray-900">{{ selectedExercise.title }}</p>
            <p class="text-xs text-gray-500">{{ categoryLabel(selectedExercise.category) }}</p>
          </div>
        </div>
      </div>

      <div>
        <label for="duration" class="block text-xs font-medium text-gray-700 mb-1">
          Thời lượng (phút)
        </label>
        <input
          id="duration"
          v-model.number="duration"
          type="number"
          min="1"
          max="120"
          placeholder="Ví dụ: 15"
          class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
        />
      </div>

      <div>
        <button
          type="button"
          @click="addExercise"
          :disabled="!selectedExerciseId"
          class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          Thêm vào danh sách
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import ExerciseThumbnail from '@/Components/exercises/ExerciseThumbnail.vue';
import { categoryLabels, labelFor } from '@/Lib/labels';

const props = defineProps({
  exercises: {
    type: Array,
    required: true,
  },
});

const emit = defineEmits(['add-exercise']);

const selectedExerciseId = ref('');
const duration = ref(15);
const selectedExercise = computed(() => {
  if (!selectedExerciseId.value) return null;
  return props.exercises.find(e => e.id === Number(selectedExerciseId.value)) || null;
});

const categoryLabel = (category) => labelFor(categoryLabels, category, '');

function addExercise() {
  if (!selectedExerciseId.value) return;

  const exercise = props.exercises.find(e => e.id === Number(selectedExerciseId.value));
  if (exercise) {
    emit('add-exercise', {
      exercise_id: exercise.id,
      title: exercise.title,
      category: exercise.category,
      thumbnail_path: exercise.thumbnail_path,
      duration_minutes: duration.value,
      completion_status: 'not_started',
      therapist_note: '',
    });
    
    // Reset selected
    selectedExerciseId.value = '';
  }
}
</script>
