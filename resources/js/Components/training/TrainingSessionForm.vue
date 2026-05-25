<template>
  <form @submit.prevent="submit" class="space-y-6">
    <div class="bg-white shadow rounded-lg p-6 space-y-6">
      <h2 class="text-lg font-medium text-gray-900 border-b border-gray-200 pb-3">
        {{ isEdit ? 'Chỉnh sửa buổi tập' : 'Tạo buổi tập' }}
      </h2>

      <div v-if="!isEdit && children.length === 0" class="rounded-md border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
        Chưa có trẻ đang can thiệp. Vui lòng kích hoạt hồ sơ trẻ trước khi tạo hoạt động mới.
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Child Selection -->
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
              {{ child.first_name }} {{ child.last_name }}
            </option>
          </select>
          <div v-if="form.errors.child_id" class="text-red-500 text-xs mt-1">
            {{ form.errors.child_id }}
          </div>
        </div>

        <!-- Session Date + Time row -->
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
          <div v-if="form.errors.session_date" class="text-red-500 text-xs mt-1">
            {{ form.errors.session_date }}
          </div>
        </div>

        <!-- Scheduled Time -->
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
          <div v-if="form.errors.scheduled_time" class="text-red-500 text-xs mt-1">
            {{ form.errors.scheduled_time }}
          </div>
        </div>

        <!-- Duration Minutes -->
        <div>
          <label for="duration_minutes" class="block text-sm font-medium text-gray-700">
            Thời lượng (phút)
          </label>
          <input
            id="duration_minutes"
            v-model="form.duration_minutes"
            type="number"
            min="1"
            placeholder="30"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          />
          <div v-if="form.errors.duration_minutes" class="text-red-500 text-xs mt-1">
            {{ form.errors.duration_minutes }}
          </div>
        </div>

        <!-- Session Status -->
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
          <div v-if="form.errors.status" class="text-red-500 text-xs mt-1">
            {{ form.errors.status }}
          </div>
        </div>
      </div>

      <!-- Notes -->
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
        <div v-if="form.errors.notes" class="text-red-500 text-xs mt-1">
          {{ form.errors.notes }}
        </div>
      </div>

      <!-- Session Items / Exercises section -->
      <div class="border-t border-gray-200 pt-6">
        <div class="flex items-center justify-between mb-4">
          <div>
            <h3 class="text-md font-semibold text-gray-900">
              Bài tập trong buổi tập
            </h3>
            <p class="text-xs text-gray-500">
              Chọn và sắp xếp thứ tự bài tập trong buổi này.
            </p>
          </div>
        </div>

        <!-- Exercise picker component -->
        <TrainingExercisePicker
          :exercises="exercises"
          @add-exercise="handleAddExercise"
          class="mb-6"
        />

        <!-- Item list component -->
        <div class="border border-gray-200 rounded-lg overflow-hidden">
          <TrainingItemList
            v-model:items="form.items"
            @remove-item="handleRemoveItem"
          />
        </div>
        <div v-if="form.errors.items" class="text-red-500 text-xs mt-1">
          {{ form.errors.items }}
        </div>
      </div>
    </div>

    <!-- Actions -->
    <div class="flex justify-end space-x-3">
      <Link
        :href="route('training.index')"
        class="inline-flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
      >
        Hủy
      </Link>
      <button
        type="submit"
        :disabled="form.processing || (!isEdit && children.length === 0)"
        class="inline-flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
      >
        {{ form.processing ? 'Đang lưu...' : 'Lưu buổi tập' }}
      </button>
    </div>
  </form>
</template>

<script setup>
import { computed } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
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
});

const isEdit = computed(() => !!props.session);

// Format date to YYYY-MM-DD for HTML input
const getInitialDate = () => {
  if (props.session?.session_date) {
    return props.session.session_date.substring(0, 10);
  }
  return new Date().toISOString().substring(0, 10);
};

// Initial items
const getInitialItems = () => {
  if (props.session?.items) {
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
  }
  return [];
};

const form = useForm({
  child_id: props.session?.child_id ?? '',
  session_date: getInitialDate(),
  scheduled_time: props.session?.scheduled_time ?? '',
  duration_minutes: props.session?.total_minutes ?? null,
  status: props.session?.status ?? 'planned',
  notes: props.session?.notes ?? '',
  items: getInitialItems(),
});

const totalDuration = computed(() => {
  return form.items.reduce((sum, item) => sum + (item.duration_minutes || 0), 0);
});

function handleAddExercise(newItem) {
  newItem.sort_order = form.items.length + 1;
  form.items.push(newItem);
}

function handleRemoveItem(index) {
  form.items.splice(index, 1);
  // Reorder sort_order values
  form.items.forEach((item, idx) => {
    item.sort_order = idx + 1;
  });
}

function submit() {
  form.total_minutes = totalDuration.value;
  if (isEdit.value) {
    form.put(route('training.update', props.session.id));
  } else {
    form.post(route('training.store'));
  }
}
</script>

