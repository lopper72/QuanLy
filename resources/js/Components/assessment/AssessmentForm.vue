<template>
  <form @submit.prevent="submit" class="space-y-6">
    <div class="bg-white p-6 rounded-lg shadow border border-gray-100 space-y-4">
      <h2 class="text-lg font-bold text-gray-900">
        {{ isEdit ? 'Chỉnh sửa đánh giá' : 'Tạo đánh giá' }}
      </h2>

      <div v-if="!isEdit && children.length === 0" class="rounded-md border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
        Chưa có trẻ đang can thiệp. Vui lòng kích hoạt hồ sơ trẻ trước khi tạo hoạt động mới.
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Child Select -->
        <div>
          <label for="child_id" class="block text-sm font-medium text-gray-700">Trẻ <span class="text-red-500">*</span></label>
          <select
            id="child_id"
            v-model="form.child_id"
            required
            :disabled="isEdit"
            class="mt-1 block w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
          >
            <option value="" disabled>Chọn trẻ</option>
            <option v-for="child in children" :key="child.id" :value="child.id">
              {{ child.first_name }} {{ child.last_name }}
            </option>
          </select>
          <p v-if="form.errors.child_id" class="mt-1 text-xs text-red-500">{{ form.errors.child_id }}</p>
        </div>

        <!-- Assessment Date -->
        <div>
          <label for="assessment_date" class="block text-sm font-medium text-gray-700">Ngày đánh giá <span class="text-red-500">*</span></label>
          <input
            id="assessment_date"
            v-model="form.assessment_date"
            type="date"
            required
            class="mt-1 block w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
          />
          <p v-if="form.errors.assessment_date" class="mt-1 text-xs text-red-500">{{ form.errors.assessment_date }}</p>
        </div>
      </div>

      <!-- Notes -->
      <div>
        <label for="notes" class="block text-sm font-medium text-gray-700">Ghi chú tổng quan / Quan sát</label>
        <textarea
          id="notes"
          v-model="form.notes"
          rows="3"
          placeholder="Nhập mục tiêu đánh giá, tóm tắt hoặc nhận xét chung..."
          class="mt-1 block w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
        ></textarea>
        <p v-if="form.errors.notes" class="mt-1 text-xs text-red-500">{{ form.errors.notes }}</p>
      </div>

      <!-- Overall Score Info -->
      <div class="bg-indigo-50 p-4 rounded-lg flex items-center justify-between">
        <div>
          <h4 class="text-sm font-semibold text-indigo-900">Điểm tổng thể tự tính</h4>
          <p class="text-xs text-indigo-700">Được tính bằng trung bình các điểm kỹ năng đã nhập.</p>
        </div>
        <span class="text-3xl font-bold text-indigo-600">{{ computedOverallScore }}%</span>
      </div>
    </div>

    <!-- Skill Areas Sections -->
    <div class="space-y-4">
      <h3 class="text-base font-bold text-gray-900">Điểm theo nhóm kỹ năng</h3>
      <p class="text-xs text-gray-500">Nhập điểm (0-100), mức đạt được và ghi chú ngắn cho từng nhóm phát triển.</p>

      <div class="grid grid-cols-1 gap-4">
        <div
          v-for="(item, index) in form.items"
          :key="index"
          class="bg-white p-4 rounded-lg shadow border border-gray-100 grid grid-cols-1 md:grid-cols-4 gap-4 items-center"
        >
          <!-- Skill Name Label -->
          <div>
            <span class="block text-sm font-bold text-gray-800">
              {{ skillTypes[item.skill_name] || item.skill_name }}
            </span>
          </div>

          <!-- Score Input -->
          <div>
            <label class="block text-xs font-semibold text-gray-500 mb-1">Điểm (0-100)</label>
            <input
              v-model.number="item.score"
              type="number"
              min="0"
              max="100"
              placeholder="Ví dụ: 75"
              class="w-full text-sm border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
            />
          </div>

          <!-- Acquisition Level -->
          <div>
            <label class="block text-xs font-semibold text-gray-500 mb-1">Mức độ</label>
            <select
              v-model="item.level"
              class="w-full text-sm border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
            >
              <option :value="null">Chưa đánh giá</option>
              <option v-for="(label, val) in levels" :key="val" :value="val">
                {{ label }}
              </option>
            </select>
          </div>

          <!-- Item Notes -->
          <div>
            <label class="block text-xs font-semibold text-gray-500 mb-1">Ghi chú / Mục tiêu chi tiết</label>
            <input
              v-model="item.note"
              type="text"
              placeholder="Ghi chú quan sát..."
              class="w-full text-sm border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- Actions -->
    <div class="flex justify-end space-x-3">
      <Link
        :href="isEdit ? route('assessment.show', assessment.id) : route('assessment.index')"
        class="bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-md shadow-sm text-sm font-medium hover:bg-gray-50"
      >
        Hủy
      </Link>
      <button
        type="submit"
        :disabled="form.processing || (!isEdit && children.length === 0)"
        class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
      >
        {{ form.processing ? 'Đang lưu...' : 'Lưu đánh giá' }}
      </button>
    </div>
  </form>
</template>

<script setup>
import { computed } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';

const props = defineProps({
  assessment: Object,
  children: Array,
  defaultItems: Array,
  skillTypes: Object,
  levels: Object,
  isEdit: {
    type: Boolean,
    default: false,
  },
});

const getTodayDateString = () => {
  return new Date().toISOString().split('T')[0];
};

const initialData = {
  child_id: props.assessment?.child_id || '',
  assessment_date: props.assessment?.assessment_date || getTodayDateString(),
  notes: props.assessment?.notes || '',
  items: props.assessment?.items || props.defaultItems || [],
};

const form = useForm(initialData);

const computedOverallScore = computed(() => {
  const scores = form.items.map((i) => i.score).filter((s) => s !== null && s !== undefined && s !== '');
  if (scores.length === 0) return 0;
  const sum = scores.reduce((a, b) => a + b, 0);
  return Math.round(sum / scores.length);
});

const submit = () => {
  // Pass overall_score as well in payload
  form.transform((data) => ({
    ...data,
    overall_score: computedOverallScore.value,
  }));

  if (props.isEdit) {
    form.put(route('assessment.update', props.assessment.id));
  } else {
    form.post(route('assessment.store'));
  }
};
</script>
