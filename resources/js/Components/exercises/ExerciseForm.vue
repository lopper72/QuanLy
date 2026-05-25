<template>
  <form @submit.prevent="submit" class="space-y-6 max-w-4xl bg-white p-6 rounded-lg shadow-sm border border-slate-100">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <!-- Title -->
      <div>
        <label for="title" class="block text-sm font-medium text-slate-700">Tên bài tập <span class="text-rose-500">*</span></label>
        <input
          id="title"
          v-model="form.title"
          type="text"
          class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          :class="{ 'border-rose-300 focus:border-rose-500 focus:ring-rose-500': form.errors.title }"
          required
          placeholder="VD: Xếp vòng theo kích thước"
        />
        <p v-if="form.errors.title" class="mt-1 text-sm text-rose-600">{{ form.errors.title }}</p>
      </div>

      <!-- Slug -->
      <div>
        <label for="slug" class="block text-sm font-medium text-slate-700">Đường dẫn (Slug)</label>
        <input
          id="slug"
          v-model="form.slug"
          type="text"
          class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          :class="{ 'border-rose-300 focus:border-rose-500 focus:ring-rose-500': form.errors.slug }"
          placeholder="VD: xep-vong-theo-kich-thuoc (không bắt buộc)"
        />
        <p v-if="form.errors.slug" class="mt-1 text-sm text-rose-600">{{ form.errors.slug }}</p>
        <p class="mt-1 text-xs text-slate-400">Để trống để tự động tạo từ tên bài tập.</p>
      </div>

      <!-- Category -->
      <div>
        <label for="category" class="block text-sm font-medium text-slate-700">Danh mục <span class="text-rose-500">*</span></label>
        <select
          id="category"
          v-model="form.category"
          class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          :class="{ 'border-rose-300 focus:border-rose-500 focus:ring-rose-500': form.errors.category }"
          required
        >
          <option value="">Chọn danh mục</option>
          <option v-for="(label, key) in categories" :key="key" :value="key">
            {{ label }}
          </option>
        </select>
        <p v-if="form.errors.category" class="mt-1 text-sm text-rose-600">{{ form.errors.category }}</p>
      </div>

      <!-- Difficulty -->
      <div>
        <label for="difficulty" class="block text-sm font-medium text-slate-700">Độ khó <span class="text-rose-500">*</span></label>
        <select
          id="difficulty"
          v-model="form.difficulty"
          class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          :class="{ 'border-rose-300 focus:border-rose-500 focus:ring-rose-500': form.errors.difficulty }"
          required
        >
          <option value="">Chọn độ khó</option>
          <option v-for="(label, key) in difficulties" :key="key" :value="key">
            {{ label }}
          </option>
        </select>
        <p v-if="form.errors.difficulty" class="mt-1 text-sm text-rose-600">{{ form.errors.difficulty }}</p>
      </div>

      <!-- Estimated Minutes -->
      <div>
        <label for="estimated_minutes" class="block text-sm font-medium text-slate-700">Thời gian dự kiến (phút)</label>
        <input
          id="estimated_minutes"
          v-model="form.estimated_minutes"
          type="number"
          min="1"
          max="180"
          class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          :class="{ 'border-rose-300 focus:border-rose-500 focus:ring-rose-500': form.errors.estimated_minutes }"
          placeholder="VD: 15"
        />
        <p v-if="form.errors.estimated_minutes" class="mt-1 text-sm text-rose-600">{{ form.errors.estimated_minutes }}</p>
      </div>

      <!-- Status (Active / Inactive) -->
      <div class="flex items-center pt-6">
        <div class="flex items-center h-5">
          <input
            id="is_active"
            v-model="form.is_active"
            type="checkbox"
            class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-slate-300 rounded"
          />
        </div>
        <div class="ml-3 text-sm">
          <label for="is_active" class="font-medium text-slate-700">Kích hoạt</label>
          <p class="text-slate-500 text-xs">Cho phép bài tập này được chọn trong các buổi tập luyện.</p>
        </div>
      </div>

      <!-- Instructions -->
      <div class="md:col-span-2">
        <label for="instructions" class="block text-sm font-medium text-slate-700">Mô tả tổng quan</label>
        <textarea
          id="instructions"
          v-model="form.instructions"
          rows="3"
          class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          :class="{ 'border-rose-300 focus:border-rose-500 focus:ring-rose-500': form.errors.instructions }"
          placeholder="Mô tả ngắn gọn về bài tập..."
        ></textarea>
        <p v-if="form.errors.instructions" class="mt-1 text-sm text-rose-600">{{ form.errors.instructions }}</p>
      </div>

      <div class="md:col-span-2 grid grid-cols-1 gap-6 border-t border-slate-100 pt-6 md:grid-cols-2">
        <div>
          <label for="description" class="block text-sm font-medium text-slate-700">Mô tả ngắn cho phụ huynh</label>
          <textarea id="description" v-model="form.description" rows="3" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Bài tập này giúp bé luyện kỹ năng gì và nên tập khi nào..."></textarea>
          <p v-if="form.errors.description" class="mt-1 text-sm text-rose-600">{{ form.errors.description }}</p>
        </div>

        <div>
          <label for="target_skill" class="block text-sm font-medium text-slate-700">Mục tiêu phát triển</label>
          <input id="target_skill" v-model="form.target_skill" type="text" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="VD: attention, communication, self_care" />
          <p v-if="form.errors.target_skill" class="mt-1 text-sm text-rose-600">{{ form.errors.target_skill }}</p>
        </div>

        <div>
          <label for="recommended_age" class="block text-sm font-medium text-slate-700">Độ tuổi gợi ý</label>
          <input id="recommended_age" v-model="form.recommended_age" type="text" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="VD: 3-6 tuổi" />
          <p v-if="form.errors.recommended_age" class="mt-1 text-sm text-rose-600">{{ form.errors.recommended_age }}</p>
        </div>

        <div>
          <label for="required_tools" class="block text-sm font-medium text-slate-700">Dụng cụ cần chuẩn bị</label>
          <textarea id="required_tools" v-model="form.required_tools" rows="3" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Thảm mềm, bóng, tranh ảnh..."></textarea>
          <p v-if="form.errors.required_tools" class="mt-1 text-sm text-rose-600">{{ form.errors.required_tools }}</p>
        </div>

        <div>
          <label for="expected_benefits" class="block text-sm font-medium text-slate-700">Lợi ích kỳ vọng</label>
          <textarea id="expected_benefits" v-model="form.expected_benefits" rows="3" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Tăng chú ý, phối hợp tay mắt, giảm né tránh cảm giác..."></textarea>
          <p v-if="form.errors.expected_benefits" class="mt-1 text-sm text-rose-600">{{ form.errors.expected_benefits }}</p>
        </div>

        <div>
          <label for="weekly_expectation" class="block text-sm font-medium text-slate-700">Cải thiện sau 1 tuần</label>
          <textarea id="weekly_expectation" v-model="form.weekly_expectation" rows="3" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Sau khoảng 1 tuần tập đều, bé có thể..."></textarea>
          <p v-if="form.errors.weekly_expectation" class="mt-1 text-sm text-rose-600">{{ form.errors.weekly_expectation }}</p>
        </div>

        <div>
          <label for="safety_notes" class="block text-sm font-medium text-slate-700">Lưu ý an toàn</label>
          <textarea id="safety_notes" v-model="form.safety_notes" rows="3" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Khi nào cần dừng, người lớn cần quan sát gì..."></textarea>
          <p v-if="form.errors.safety_notes" class="mt-1 text-sm text-rose-600">{{ form.errors.safety_notes }}</p>
        </div>

        <div>
          <label for="parent_tips" class="block text-sm font-medium text-slate-700">Gợi ý cho phụ huynh</label>
          <textarea id="parent_tips" v-model="form.parent_tips" rows="3" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Cách khuyến khích bé, cách giảm trợ giúp..."></textarea>
          <p v-if="form.errors.parent_tips" class="mt-1 text-sm text-rose-600">{{ form.errors.parent_tips }}</p>
        </div>
      </div>

      <!-- Media Section -->
      <div class="md:col-span-2 border-t border-slate-100 pt-6">
        <h3 class="text-lg font-medium text-slate-900 mb-4">Hình ảnh & Video</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Thumbnail -->
          <div>
            <label class="block text-sm font-medium text-slate-700">Ảnh đại diện</label>
            <div class="mt-1 flex items-center space-x-4">
              <div v-if="thumbnailPreview || exercise?.thumbnail_path" class="h-24 w-24 flex-shrink-0 overflow-hidden rounded-md border border-slate-200">
                <img :src="thumbnailPreview || `/storage/${exercise.thumbnail_path}`" class="h-full w-full object-cover" />
              </div>
              <div class="flex-1">
                <input
                  type="file"
                  @input="form.thumbnail = $event.target.files[0]; handleThumbnailPreview($event)"
                  accept="image/*"
                  class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                />
                <p class="mt-1 text-xs text-slate-400">PNG, JPG tối đa 2MB.</p>
              </div>
            </div>
            <p v-if="form.errors.thumbnail" class="mt-1 text-sm text-rose-600">{{ form.errors.thumbnail }}</p>
          </div>

          <!-- Video -->
          <div>
            <label class="block text-sm font-medium text-slate-700">Video hướng dẫn (Tải lên)</label>
            <input
              type="file"
              @input="form.video = $event.target.files[0]"
              accept="video/mp4,video/quicktime"
              class="mt-1 block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
            />
            <p class="mt-1 text-xs text-slate-400">MP4, MOV tối đa 20MB.</p>
            <p v-if="form.errors.video" class="mt-1 text-sm text-rose-600">{{ form.errors.video }}</p>
            <div v-if="exercise?.video_path" class="mt-2 text-xs text-indigo-600">
              Đã có video: {{ exercise.video_path.split('/').pop() }}
            </div>
          </div>

          <!-- Video URL -->
          <div class="md:col-span-2">
            <label for="video_url" class="block text-sm font-medium text-slate-700">Hoặc Link Video (YouTube, Vimeo...)</label>
            <input
              id="video_url"
              v-model="form.video_url"
              type="url"
              class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              placeholder="https://www.youtube.com/watch?v=..."
            />
            <p v-if="form.errors.video_url" class="mt-1 text-sm text-rose-600">{{ form.errors.video_url }}</p>
          </div>
        </div>
      </div>

      <!-- Steps Section -->
      <div class="md:col-span-2 border-t border-slate-100 pt-6">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-medium text-slate-900">Quy trình thực hiện (Từng bước)</h3>
          <button
            type="button"
            @click="addStep"
            class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
          >
            + Thêm bước
          </button>
        </div>

        <div class="space-y-4">
          <div v-for="(step, index) in form.steps" :key="index" class="p-4 bg-slate-50 rounded-lg border border-slate-200 relative">
            <button
              type="button"
              @click="removeStep(index)"
              class="absolute top-2 right-2 text-slate-400 hover:text-rose-500"
            >
              <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="6 18L18 6M6 6l12 12" />
              </svg>
            </button>

            <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
              <div class="md:col-span-1 flex items-center justify-center">
                <span class="h-8 w-8 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold">
                  {{ index + 1 }}
                </span>
              </div>
              
              <div class="md:col-span-7 space-y-3">
                <div>
                  <input
                    v-model="step.title"
                    type="text"
                    placeholder="Tiêu đề bước..."
                    class="block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    required
                  />
                </div>
                <div>
                  <textarea
                    v-model="step.instruction"
                    rows="2"
                    placeholder="Hướng dẫn chi tiết..."
                    class="block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                  ></textarea>
                </div>
              </div>

              <div class="md:col-span-4">
                <div class="flex flex-col items-center space-y-2">
                  <div v-if="stepPreviews[index] || step.image_path" class="h-20 w-full overflow-hidden rounded-md border border-slate-200">
                    <img :src="stepPreviews[index] || `/storage/${step.image_path}`" class="h-full w-full object-cover" />
                  </div>
                  <input
                    type="file"
                    @input="handleStepImage(index, $event)"
                    accept="image/*"
                    class="block w-full text-xs text-slate-500 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                  />
                </div>
              </div>
            </div>
          </div>

          <div v-if="form.steps.length === 0" class="text-center py-8 border-2 border-dashed border-slate-200 rounded-lg text-slate-400">
            Chưa có bước nào. Nhấn "Thêm bước" để bắt đầu.
          </div>
        </div>
      </div>
    </div>

    <!-- Actions -->
    <div class="flex items-center justify-end space-x-3 pt-4 border-t border-slate-100">
      <Link
        :href="cancelUrl"
        class="inline-flex items-center px-4 py-2 border border-slate-300 rounded-md shadow-sm text-sm font-medium text-slate-700 bg-white hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
      >
        Hủy
      </Link>
      <button
        type="submit"
        :disabled="form.processing"
        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
      >
        <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        {{ isEdit ? 'Cập nhật bài tập' : 'Tạo bài tập mới' }}
      </button>
    </div>
  </form>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
  exercise: {
    type: Object,
    default: null,
  },
  categories: {
    type: Object,
    required: true,
  },
  difficulties: {
    type: Object,
    required: true,
  },
});

const isEdit = computed(() => !!props.exercise);

const cancelUrl = computed(() => {
  return isEdit.value ? `/exercises/${props.exercise.id}` : '/exercises';
});

const form = useForm({
  _method: isEdit.value ? 'PUT' : 'POST',
  title: props.exercise?.title ?? '',
  slug: props.exercise?.slug ?? '',
  category: props.exercise?.category ?? '',
  difficulty: props.exercise?.difficulty ?? '',
  estimated_minutes: props.exercise?.estimated_minutes ?? '',
  is_active: props.exercise ? !!props.exercise.is_active : true,
  instructions: props.exercise?.instructions ?? '',
  description: props.exercise?.description ?? '',
  target_skill: props.exercise?.target_skill ?? '',
  recommended_age: props.exercise?.recommended_age ?? '',
  required_tools: props.exercise?.required_tools ?? '',
  expected_benefits: props.exercise?.expected_benefits ?? '',
  safety_notes: props.exercise?.safety_notes ?? '',
  parent_tips: props.exercise?.parent_tips ?? '',
  weekly_expectation: props.exercise?.weekly_expectation ?? '',
  thumbnail: null,
  video: null,
  video_url: props.exercise?.video_url ?? '',
  steps: props.exercise?.steps?.map(s => ({
    title: s.title,
    instruction: s.instruction,
    image_path: s.image_path,
    image: null
  })) ?? [],
});

const thumbnailPreview = ref(null);
const stepPreviews = ref({});

const handleThumbnailPreview = (event) => {
  const file = event.target.files[0];
  if (file) {
    thumbnailPreview.value = URL.createObjectURL(file);
  }
};

const handleStepImage = (index, event) => {
  const file = event.target.files[0];
  if (file) {
    form.steps[index].image = file;
    stepPreviews.value[index] = URL.createObjectURL(file);
  }
};

const addStep = () => {
  form.steps.push({
    title: '',
    instruction: '',
    image_path: null,
    image: null
  });
};

const removeStep = (index) => {
  form.steps.splice(index, 1);
  delete stepPreviews.value[index];
};

const submit = () => {
  if (isEdit.value) {
    // Use post with _method PUT for file uploads
    form.post(`/exercises/${props.exercise.id}`, {
      forceFormData: true,
    });
  } else {
    form.post('/exercises');
  }
};
</script>
