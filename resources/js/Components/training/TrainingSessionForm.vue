<template>
  <form @submit.prevent="submit" class="space-y-6">
    <div class="space-y-6 rounded-lg bg-white p-6 shadow">
      <h2 class="border-b border-gray-200 pb-3 text-lg font-medium text-gray-900">
        {{ isEdit ? 'Chỉnh sửa buổi tập' : 'Tạo buổi tập' }}
      </h2>

      <div v-if="!isEdit && children.length === 0" class="rounded-md border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
        Chưa có trẻ đang can thiệp. Vui lòng kích hoạt hồ sơ trẻ trước khi tạo hoạt động mới.
      </div>

      <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
        <div>
          <label for="child_id" class="block text-sm font-medium text-gray-700">
            Trẻ <span class="text-red-500">*</span>
          </label>
          <select
            id="child_id"
            v-model="form.child_id"
            required
            :disabled="isEdit"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          >
            <option value="" disabled>-- Chọn trẻ --</option>
            <option v-for="child in children" :key="child.id" :value="child.id">
              {{ child.full_name || `${child.first_name || ''} ${child.last_name || ''}`.trim() }}
            </option>
          </select>
          <div v-if="form.errors.child_id" class="mt-1 text-xs text-red-500">
            {{ form.errors.child_id }}
          </div>
        </div>

        <div>
          <label for="session_date" class="block text-sm font-medium text-gray-700">
            Ngày tập <span class="text-red-500">*</span>
          </label>
          <input
            id="session_date"
            v-model="form.session_date"
            type="date"
            required
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          />
          <div v-if="form.errors.session_date" class="mt-1 text-xs text-red-500">
            {{ form.errors.session_date }}
          </div>
        </div>

        <div>
          <label for="scheduled_time" class="block text-sm font-medium text-gray-700">
            Giờ bắt đầu
          </label>
          <input
            id="scheduled_time"
            v-model="form.scheduled_time"
            type="time"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          />
          <div v-if="form.errors.scheduled_time" class="mt-1 text-xs text-red-500">
            {{ form.errors.scheduled_time }}
          </div>
        </div>

        <div>
          <label for="duration_minutes" class="block text-sm font-medium text-gray-700">
            Thời lượng tùy chỉnh (phút)
          </label>
          <input
            id="duration_minutes"
            v-model="form.duration_minutes"
            type="number"
            min="1"
            :placeholder="String(totalEstimatedMinutes || 30)"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          />
          <p class="mt-1 text-xs text-gray-500">
            Tổng thời lượng dự kiến: {{ totalEstimatedMinutes }} phút
          </p>
          <div v-if="form.errors.duration_minutes" class="mt-1 text-xs text-red-500">
            {{ form.errors.duration_minutes }}
          </div>
        </div>

        <div>
          <label for="status" class="block text-sm font-medium text-gray-700">
            Trạng thái buổi tập
          </label>
          <select
            id="status"
            v-model="form.status"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          >
            <option value="planned">Đã lên lịch</option>
            <option value="in_progress">Đang thực hiện</option>
            <option value="completed">Hoàn thành</option>
            <option value="skipped">Bỏ qua</option>
          </select>
          <div v-if="form.errors.status" class="mt-1 text-xs text-red-500">
            {{ form.errors.status }}
          </div>
        </div>
      </div>

      <div>
        <label for="notes" class="block text-sm font-medium text-gray-700">
          Ghi chú chung của buổi tập
        </label>
        <textarea
          id="notes"
          v-model="form.notes"
          rows="3"
          placeholder="Nhập quan sát, điều kiện môi trường hoặc ghi chú chung của buổi tập..."
          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
        ></textarea>
        <div v-if="form.errors.notes" class="mt-1 text-xs text-red-500">
          {{ form.errors.notes }}
        </div>
      </div>

      <section v-if="!isEdit" class="border-t border-gray-200 pt-6">
        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
          <div>
            <h3 class="text-md font-semibold text-gray-900">Chọn combo bài tập</h3>
            <p class="text-xs text-gray-500">Combo gợi ý giúp thêm nhanh nhiều bài theo cùng một mục tiêu.</p>
          </div>
          <div class="w-fit rounded-full bg-indigo-50 px-3 py-1 text-xs font-medium text-indigo-700">
            Tổng thời lượng dự kiến: {{ totalEstimatedMinutes }} phút
          </div>
        </div>

        <div v-if="exerciseCombos.length" class="grid grid-cols-1 gap-4 lg:grid-cols-2">
          <article
            v-for="combo in exerciseCombos"
            :key="combo.id"
            class="rounded-lg border p-4 transition"
            :class="isComboSelected(combo.id) ? 'border-indigo-300 bg-indigo-50/60' : 'border-gray-200 bg-white hover:border-indigo-200'"
          >
            <div class="flex items-start justify-between gap-3">
              <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                  <h4 class="font-semibold text-gray-900">{{ combo.title }}</h4>
                  <span v-if="isComboSelected(combo.id)" class="rounded-full bg-indigo-100 px-2 py-0.5 text-xs font-medium text-indigo-700">
                    Đã chọn
                  </span>
                </div>
                <p class="mt-1 line-clamp-2 text-sm leading-5 text-gray-600">
                  {{ combo.description || 'Combo gợi ý cho buổi tập ngắn tại nhà.' }}
                </p>
              </div>
              <button
                type="button"
                class="shrink-0 rounded-md px-3 py-2 text-sm font-medium"
                :class="isComboSelected(combo.id) ? 'bg-white text-indigo-700 ring-1 ring-indigo-200' : 'bg-indigo-600 text-white hover:bg-indigo-700'"
                @click="toggleCombo(combo.id)"
              >
                {{ isComboSelected(combo.id) ? 'Bỏ chọn' : 'Chọn' }}
              </button>
            </div>

            <div class="mt-3 flex flex-wrap gap-2">
              <span class="rounded-full border border-gray-200 bg-white px-2.5 py-1 text-xs text-gray-700">
                {{ labelFor(skillLabels, combo.target_skill, 'Mục tiêu chung') }}
              </span>
              <span class="rounded-full border border-gray-200 bg-white px-2.5 py-1 text-xs text-gray-700">
                {{ combo.estimated_minutes || comboDuration(combo) }} phút
              </span>
              <span class="rounded-full border border-gray-200 bg-white px-2.5 py-1 text-xs text-gray-700">
                {{ combo.exercises?.length || 0 }} bài
              </span>
              <span v-if="combo.difficulty" class="rounded-full border border-gray-200 bg-white px-2.5 py-1 text-xs text-gray-700">
                {{ labelFor(difficultyLabels, combo.difficulty, combo.difficulty) }}
              </span>
            </div>

            <details class="mt-3">
              <summary class="cursor-pointer text-sm font-medium text-indigo-700">Xem bài trong combo</summary>
              <ul class="mt-2 space-y-1 text-sm text-gray-700">
                <li v-for="exercise in combo.exercises" :key="exercise.id" class="flex items-center justify-between gap-2 rounded bg-white px-3 py-2">
                  <span>{{ exercise.title }}</span>
                  <span class="shrink-0 text-xs text-gray-500">{{ exercise.estimated_minutes || 15 }} phút</span>
                </li>
              </ul>
            </details>
          </article>
        </div>
        <p v-else class="rounded-md border border-gray-200 bg-gray-50 p-4 text-sm text-gray-500">
          Chưa có combo gợi ý.
        </p>

        <div v-if="duplicateExerciseCount > 0" class="mt-4 rounded-md border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800">
          Đã tự động bỏ bài trùng: {{ duplicateExerciseCount }} bài.
        </div>

        <div v-if="selectedComboExercises.length" class="mt-4 rounded-lg border border-gray-200 bg-gray-50 p-4">
          <h4 class="text-sm font-semibold text-gray-900">Bài tập trong combo đã chọn</h4>
          <div class="mt-3 grid grid-cols-1 gap-2 md:grid-cols-2">
            <div v-for="exercise in selectedComboExercises" :key="exercise.id" class="rounded bg-white px-3 py-2 text-sm text-gray-700">
              {{ exercise.title }}
            </div>
          </div>
        </div>
      </section>

      <section class="border-t border-gray-200 pt-6">
        <div class="mb-4">
          <h3 class="text-md font-semibold text-gray-900">Thêm bài tập riêng lẻ</h3>
          <p class="text-xs text-gray-500">Chọn thêm bài ngoài combo nếu buổi tập cần mục tiêu bổ sung.</p>
        </div>

        <TrainingExercisePicker
          :exercises="exercises"
          class="mb-6"
          @add-exercise="handleAddExercise"
        />

        <div class="overflow-hidden rounded-lg border border-gray-200">
          <TrainingItemList
            v-model:items="form.items"
            @remove-item="handleRemoveItem"
          />
        </div>
        <div v-if="form.errors.items" class="mt-1 text-xs text-red-500">
          {{ form.errors.items }}
        </div>
      </section>
    </div>

    <div class="flex justify-end space-x-3">
      <Link
        :href="route('training.index')"
        class="inline-flex justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
      >
        Hủy
      </Link>
      <button
        type="submit"
        :disabled="form.processing || (!isEdit && children.length === 0)"
        class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50"
      >
        {{ form.processing ? 'Đang lưu...' : (isEdit ? 'Lưu buổi tập' : 'Tạo buổi tập') }}
      </button>
    </div>
  </form>
</template>

<script setup>
import { computed } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import { difficultyLabels, labelFor, skillLabels } from '@/Lib/labels';
import TrainingExercisePicker from './TrainingExercisePicker.vue';
import TrainingItemList from './TrainingItemList.vue';

const props = defineProps({
  session: {
    type: Object,
    default: null,
  },
  children: {
    type: Array,
    required: true,
  },
  exercises: {
    type: Array,
    required: true,
  },
  exerciseCombos: {
    type: Array,
    default: () => [],
  },
});

const isEdit = computed(() => !!props.session);

const getInitialDate = () => {
  if (props.session?.session_date) {
    return props.session.session_date.substring(0, 10);
  }

  return new Date().toISOString().substring(0, 10);
};

const getInitialItems = () => {
  if (!props.session?.items) {
    return [];
  }

  return props.session.items.map(item => ({
    id: item.id,
    exercise_id: item.exercise_id,
    title: item.exercise?.title || '',
    category: item.exercise?.category || '',
    duration_minutes: item.duration_minutes,
    completion_status: item.completion_status,
    therapist_note: item.therapist_note || '',
    sort_order: item.sort_order,
  }));
};

const form = useForm({
  child_id: props.session?.child_id ?? '',
  session_date: getInitialDate(),
  scheduled_time: props.session?.scheduled_time ?? '',
  duration_minutes: props.session?.total_minutes ?? null,
  status: props.session?.status ?? 'planned',
  notes: props.session?.notes ?? '',
  combo_ids: [],
  items: getInitialItems(),
});

const selectedCombos = computed(() => props.exerciseCombos.filter(combo => form.combo_ids.includes(combo.id)));

const selectedComboExercisesRaw = computed(() => selectedCombos.value.flatMap(combo => combo.exercises || []));

const selectedComboExercises = computed(() => {
  const seen = new Set();

  return selectedComboExercisesRaw.value.filter((exercise) => {
    if (seen.has(exercise.id)) {
      return false;
    }

    seen.add(exercise.id);
    return true;
  });
});

const duplicateExerciseCount = computed(() => {
  const allIds = [
    ...selectedComboExercisesRaw.value.map(exercise => exercise.id),
    ...form.items.map(item => item.exercise_id),
  ];

  return allIds.length - new Set(allIds).size;
});

const totalEstimatedMinutes = computed(() => {
  const comboMinutes = selectedComboExercises.value.reduce((sum, exercise) => sum + (exercise.estimated_minutes || 15), 0);
  const manualMinutes = form.items.reduce((sum, item) => {
    const duplicatedByCombo = selectedComboExercises.value.some(exercise => exercise.id === item.exercise_id);
    return duplicatedByCombo ? sum : sum + (item.duration_minutes || 0);
  }, 0);

  return comboMinutes + manualMinutes;
});

const comboDuration = combo => (combo.exercises || []).reduce((sum, exercise) => sum + (exercise.estimated_minutes || 15), 0);

const isComboSelected = comboId => form.combo_ids.includes(comboId);

function toggleCombo(comboId) {
  if (isComboSelected(comboId)) {
    form.combo_ids = form.combo_ids.filter(id => id !== comboId);
    return;
  }

  form.combo_ids = [...form.combo_ids, comboId];
}

function handleAddExercise(newItem) {
  if (form.items.some(item => item.exercise_id === newItem.exercise_id)) {
    return;
  }

  newItem.sort_order = form.items.length + 1;
  form.items.push(newItem);
}

function handleRemoveItem(index) {
  form.items.splice(index, 1);
  form.items.forEach((item, idx) => {
    item.sort_order = idx + 1;
  });
}

function submit() {
  if (isEdit.value) {
    form.put(route('training.update', props.session.id));
    return;
  }

  form.post(route('training.store'));
}
</script>
