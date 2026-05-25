<template>
  <AppLayout>
    <div class="space-y-6">
      <!-- Breadcrumbs / Back button -->
      <div class="flex items-center justify-between">
        <Link href="/exercises" class="text-sm font-medium text-indigo-600 hover:text-indigo-900 flex items-center">
          <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          Quay lại thư viện bài tập
        </Link>
        <div class="flex space-x-3">
          <Link
            :href="`/exercises/${exercise.id}/edit`"
            class="inline-flex items-center px-4 py-2 border border-slate-300 rounded-md shadow-sm text-sm font-medium text-slate-700 bg-white hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
          >
            Chỉnh sửa bài tập
          </Link>
          <button
            type="button"
            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-rose-600 hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-rose-500"
            @click="deleteExercise"
          >
            Xóa bài tập
          </button>
        </div>
      </div>

      <!-- Main Info Card -->
      <div class="bg-white overflow-hidden shadow sm:rounded-lg border border-slate-100">
        <!-- Media Header -->
        <div v-if="exercise.thumbnail_path || exercise.video_path || exercise.video_url" class="grid grid-cols-1 md:grid-cols-2 border-b border-slate-100">
          <!-- Thumbnail/Video -->
          <div class="bg-slate-900 flex items-center justify-center aspect-video">
            <template v-if="exercise.video_path">
              <video controls class="w-full h-full">
                <source :src="`/storage/${exercise.video_path}`" type="video/mp4">
                Trình duyệt của bạn không hỗ trợ video.
              </video>
            </template>
            <template v-else-if="exercise.video_url">
              <iframe
                v-if="exercise.video_url.includes('youtube.com') || exercise.video_url.includes('youtu.be')"
                class="w-full h-full"
                :src="getYouTubeEmbedUrl(exercise.video_url)"
                frameborder="0"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                allowfullscreen
              ></iframe>
              <a v-else :href="exercise.video_url" target="_blank" class="text-white underline">Xem video hướng dẫn</a>
            </template>
            <template v-else-if="exercise.thumbnail_path">
              <img :src="`/storage/${exercise.thumbnail_path}`" class="w-full h-full object-contain" />
            </template>
          </div>
          
          <!-- Quick Info -->
          <div class="p-6 flex flex-col justify-center space-y-4">
            <h1 class="text-3xl font-bold leading-tight text-slate-900">{{ exercise.title }}</h1>
            <div class="flex flex-wrap gap-2">
              <span
                :class="[
                  exercise.is_active ? 'bg-green-100 text-green-800 border-green-200' : 'bg-slate-100 text-slate-500 border-slate-200',
                  'inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold border'
                ]"
              >
                {{ exercise.is_active ? 'Kích hoạt' : 'Chưa kích hoạt' }}
              </span>
              <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-500 border border-slate-200">
                #{{ exercise.id }}
              </span>
            </div>
            <p class="text-slate-600">{{ exercise.instructions }}</p>
          </div>
        </div>

        <div v-else class="px-6 py-5 border-b border-slate-100 flex justify-between items-center bg-slate-50">
          <div>
            <h1 class="text-3xl font-bold leading-tight text-slate-900">{{ exercise.title }}</h1>
            <p class="mt-1 text-sm text-slate-500">Mã bài tập: #{{ exercise.id }}</p>
          </div>
          <span
            :class="[
              exercise.is_active ? 'bg-green-100 text-green-800 border-green-200' : 'bg-slate-100 text-slate-500 border-slate-200',
              'inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold border'
            ]"
          >
            {{ exercise.is_active ? 'Kích hoạt' : 'Chưa kích hoạt' }}
          </span>
        </div>

        <div class="px-6 py-5 space-y-6">
          <!-- Metadata grid -->
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
            <!-- Category -->
            <div class="p-4 bg-slate-50 rounded-lg border border-slate-100">
              <span class="block text-xs font-semibold text-slate-500">Danh mục</span>
              <span
                :class="[
                  categoryColor(exercise.category),
                  'mt-1 inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium border'
                ]"
              >
                {{ getCategoryLabel(exercise.category) }}
              </span>
            </div>

            <!-- Difficulty -->
            <div class="p-4 bg-slate-50 rounded-lg border border-slate-100">
              <span class="block text-xs font-semibold text-slate-500">Độ khó</span>
              <span
                v-if="exercise.difficulty"
                :class="[
                  difficultyColor(exercise.difficulty),
                  'mt-1 inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium border'
                ]"
              >
                {{ getDifficultyLabel(exercise.difficulty) }}
              </span>
              <span v-else class="block mt-1 text-sm text-slate-400">Chưa xác định</span>
            </div>

            <!-- Estimated Minutes -->
            <div class="p-4 bg-slate-50 rounded-lg border border-slate-100">
              <span class="block text-xs font-semibold text-slate-500">Thời gian dự kiến</span>
              <div class="mt-1 flex items-center text-slate-700 font-medium">
                <svg class="w-4 h-4 mr-1 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                {{ exercise.estimated_minutes ? `${exercise.estimated_minutes} phút` : 'Chưa xác định' }}
              </div>
            </div>
          </div>

          <!-- URL / Slug -->
          <div v-if="exercise.slug" class="px-4 py-3 bg-slate-50 rounded-lg border border-slate-100 flex items-center space-x-2">
            <span class="text-xs font-semibold text-slate-500">Đường dẫn:</span>
            <code class="text-xs text-indigo-600">{{ exercise.slug }}</code>
          </div>

          <!-- Steps -->
          <div v-if="exercise.steps && exercise.steps.length > 0" class="space-y-4">
            <h2 class="text-xl font-bold text-slate-900 border-b border-slate-100 pb-2">Quy trình thực hiện</h2>
            <div class="grid grid-cols-1 gap-6">
              <div v-for="(step, index) in exercise.steps" :key="step.id" class="flex flex-col md:flex-row gap-6 p-4 bg-slate-50 rounded-xl border border-slate-100">
                <div class="flex-shrink-0">
                  <div class="h-10 w-10 rounded-full bg-indigo-600 text-white flex items-center justify-center font-bold text-lg">
                    {{ index + 1 }}
                  </div>
                </div>
                <div class="flex-grow space-y-2">
                  <h3 class="text-lg font-bold text-slate-800">{{ step.title }}</h3>
                  <p class="text-slate-600 leading-relaxed">{{ step.instruction }}</p>
                </div>
                <div v-if="step.image_path" class="md:w-1/3 flex-shrink-0">
                  <img :src="`/storage/${step.image_path}`" class="w-full rounded-lg shadow-sm border border-slate-200" />
                </div>
              </div>
            </div>
          </div>

          <!-- Instructions (Fallback if no steps) -->
          <div v-else-if="exercise.instructions" class="space-y-2">
            <h2 class="text-lg font-bold text-slate-900 border-b border-slate-100 pb-2">Hướng dẫn chi tiết</h2>
            <div class="text-slate-700 whitespace-pre-wrap leading-relaxed p-4 bg-slate-50 border border-slate-100 rounded-lg">
              {{ exercise.instructions }}
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '../../Components/layout/AppLayout.vue';

const props = defineProps({
  exercise: {
    type: Object,
    required: true,
  },
  categories: {
    type: Object,
    default: () => ({}),
  },
  difficulties: {
    type: Object,
    default: () => ({}),
  },
});

const defaultCategories = {
  gross_motor: 'Vận động thô',
  fine_motor: 'Vận động tinh',
  sensory: 'Giác quan',
  communication: 'Giao tiếp',
  cognitive: 'Nhận thức',
  social: 'Xã hội',
  self_care: 'Tự chăm sóc',
};

const defaultDifficulties = {
  easy: 'Dễ',
  medium: 'Trung bình',
  hard: 'Khó',
};

const getCategoryLabel = (cat) => {
  return props.categories[cat] || defaultCategories[cat] || cat;
};

const getDifficultyLabel = (diff) => {
  return props.difficulties[diff] || defaultDifficulties[diff] || diff;
};

const categoryColor = (cat) => {
  const colors = {
    gross_motor: 'bg-blue-50 text-blue-700 border-blue-200',
    fine_motor: 'bg-emerald-50 text-emerald-700 border-emerald-200',
    sensory: 'bg-purple-50 text-purple-700 border-purple-200',
    communication: 'bg-amber-50 text-amber-700 border-amber-200',
    cognitive: 'bg-pink-50 text-pink-700 border-pink-200',
    social: 'bg-sky-50 text-sky-700 border-sky-200',
    self_care: 'bg-teal-50 text-teal-700 border-teal-200',
  };
  return colors[cat] || 'bg-slate-50 text-slate-700 border-slate-200';
};

const difficultyColor = (diff) => {
  const colors = {
    easy: 'bg-green-50 text-green-700 border-green-200',
    medium: 'bg-yellow-50 text-yellow-700 border-yellow-200',
    hard: 'bg-red-50 text-red-700 border-red-200',
  };
  return colors[diff] || 'bg-slate-50 text-slate-700 border-slate-200';
};

const getYouTubeEmbedUrl = (url) => {
  let videoId = '';
  if (url.includes('youtube.com/watch?v=')) {
    videoId = url.split('v=')[1].split('&')[0];
  } else if (url.includes('youtu.be/')) {
    videoId = url.split('youtu.be/')[1].split('?')[0];
  }
  return videoId ? `https://www.youtube.com/embed/${videoId}` : url;
};

const deleteExercise = () => {
  if (confirm('Bạn có chắc chắn muốn xóa vĩnh viễn bài tập này không?')) {
    router.delete(`/exercises/${props.exercise.id}`);
  }
};
</script>
