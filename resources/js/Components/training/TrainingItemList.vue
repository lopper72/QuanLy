<template>
  <div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
      <thead class="bg-gray-50">
        <tr>
          <th class="w-10 px-6 py-3 text-left text-xs font-medium text-gray-500">
            Thứ tự
          </th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">
            Bài tập
          </th>
          <th class="w-32 px-6 py-3 text-left text-xs font-medium text-gray-500">
            Thời lượng
          </th>
          <th class="w-44 px-6 py-3 text-left text-xs font-medium text-gray-500">
            Trạng thái
          </th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">
            Ghi chú chuyên viên
          </th>
          <th v-if="!readOnly" class="w-24 px-6 py-3 text-right text-xs font-medium text-gray-500">
            Thao tác
          </th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-200 bg-white">
        <tr v-if="items.length === 0">
          <td colspan="6" class="px-6 py-10 text-center text-sm text-gray-500">
            Chưa có bài tập nào trong buổi này.
          </td>
        </tr>

        <tr v-for="(item, index) in items" :key="item.id || index" class="hover:bg-gray-50">
          <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">
            <div class="flex items-center gap-1">
              <span class="font-medium">{{ index + 1 }}</span>
              <div v-if="!readOnly" class="flex flex-col">
                <button
                  type="button"
                  :disabled="index === 0"
                  class="text-gray-400 hover:text-gray-600 disabled:opacity-30"
                  title="Di chuyển lên"
                  @click="moveUp(index)"
                >
                  ▲
                </button>
                <button
                  type="button"
                  :disabled="index === items.length - 1"
                  class="text-gray-400 hover:text-gray-600 disabled:opacity-30"
                  title="Di chuyển xuống"
                  @click="moveDown(index)"
                >
                  ▼
                </button>
              </div>
            </div>
          </td>

          <td class="whitespace-nowrap px-6 py-4 text-sm">
            <div class="flex items-center gap-3">
              <ExerciseThumbnail :exercise="item.exercise || item" size="sm" :alt="item.title || item.exercise?.title || 'Bài tập'" />
              <div class="min-w-0">
                <div class="font-medium text-gray-900">
                  {{ item.title || item.exercise?.title }}
                </div>
                <div class="text-xs text-gray-500">
                  {{ categoryLabel(item.category || item.exercise?.category) }}
                </div>
              </div>
            </div>
          </td>

          <td class="whitespace-nowrap px-6 py-4 text-sm">
            <div v-if="!readOnly" class="flex items-center gap-1">
              <input
                v-model.number="item.duration_minutes"
                type="number"
                min="1"
                class="w-16 rounded border-gray-300 px-1.5 py-1 text-sm focus:border-indigo-500 focus:ring-indigo-500"
              />
              <span class="text-xs text-gray-500">phút</span>
            </div>
            <span v-else class="font-medium text-gray-900">
              {{ item.duration_minutes }} phút
            </span>
          </td>

          <td class="whitespace-nowrap px-6 py-4 text-sm">
            <select
              v-if="!readOnly || allowInteractiveStatus"
              v-model="item.completion_status"
              class="rounded border-gray-300 px-2 py-1 text-xs focus:border-indigo-500 focus:ring-indigo-500"
              @change="readOnly && $emit('update-item-status', item, item.completion_status)"
            >
              <option
                v-for="option in itemStatusOptions"
                :key="option.value"
                :value="option.value"
              >
                {{ option.label }}
              </option>
            </select>
            <TrainingStatusBadge v-else :status="item.completion_status" type="item" />
          </td>

          <td class="px-6 py-4 text-sm">
            <input
              v-if="!readOnly"
              v-model="item.therapist_note"
              type="text"
              placeholder="Thêm ghi chú chuyên viên..."
              class="w-full rounded border-gray-300 px-2 py-1 text-sm focus:border-indigo-500 focus:ring-indigo-500"
            />
            <span v-else class="block max-w-xs break-words text-xs italic text-gray-700">
              {{ item.therapist_note || 'Chưa có ghi chú' }}
            </span>
          </td>

          <td v-if="!readOnly" class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
            <button type="button" class="text-red-600 hover:text-red-900" @click="removeItem(index)">
              Xóa
            </button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
import TrainingStatusBadge from './TrainingStatusBadge.vue';
import ExerciseThumbnail from '@/Components/exercises/ExerciseThumbnail.vue';
import { categoryLabels, itemStatusLabels, labelFor } from '@/Lib/labels';

const props = defineProps({
  items: {
    type: Array,
    required: true,
  },
  readOnly: {
    type: Boolean,
    default: false,
  },
  allowInteractiveStatus: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['update:items', 'remove-item', 'update-item-status']);

const itemStatusOptions = [
  { value: 'not_started', label: labelFor(itemStatusLabels, 'not_started') },
  { value: 'completed', label: labelFor(itemStatusLabels, 'completed') },
  { value: 'partially_completed', label: labelFor(itemStatusLabels, 'partially_completed') },
  { value: 'skipped', label: labelFor(itemStatusLabels, 'skipped') },
];

function removeItem(index) {
  emit('remove-item', index);
}

function categoryLabel(category) {
  return labelFor(categoryLabels, category, '');
}

function moveUp(index) {
  if (index === 0) return;
  const list = [...props.items];
  const item = list[index];
  list.splice(index, 1);
  list.splice(index - 1, 0, item);
  list.forEach((itm, idx) => {
    itm.sort_order = idx + 1;
  });
  emit('update:items', list);
}

function moveDown(index) {
  if (index === props.items.length - 1) return;
  const list = [...props.items];
  const item = list[index];
  list.splice(index, 1);
  list.splice(index + 1, 0, item);
  list.forEach((itm, idx) => {
    itm.sort_order = idx + 1;
  });
  emit('update:items', list);
}
</script>
