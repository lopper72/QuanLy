<template>
  <AppLayout>
    <div class="space-y-6">
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <Link href="/exercises" class="text-sm font-medium text-indigo-700 hover:text-indigo-900">
          Quay lại chương trình can thiệp
        </Link>
        <div class="flex gap-2">
          <Link
            :href="`/exercises/${exercise.id}/edit`"
            class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50"
          >
            Chỉnh sửa
          </Link>
          <button
            type="button"
            class="rounded-md bg-rose-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-rose-700"
            @click="deleteExercise"
          >
            Xóa bài tập
          </button>
        </div>
      </div>

      <section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="grid grid-cols-1 lg:grid-cols-[1.1fr_1fr]">
          <div class="flex min-h-72 items-center justify-center bg-slate-900">
            <template v-if="exercise.video_path">
              <video controls class="h-full w-full">
                <source :src="`/storage/${exercise.video_path}`" type="video/mp4">
                Trình duyệt của bạn không hỗ trợ video.
              </video>
            </template>
            <template v-else-if="exercise.video_url">
              <iframe
                v-if="exercise.video_url.includes('youtube.com') || exercise.video_url.includes('youtu.be')"
                class="h-full min-h-72 w-full"
                :src="getYouTubeEmbedUrl(exercise.video_url)"
                title="Video hướng dẫn"
                frameborder="0"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                allowfullscreen
              ></iframe>
              <a v-else :href="exercise.video_url" target="_blank" class="text-white underline">Xem video hướng dẫn</a>
            </template>
            <template v-else-if="exercise.thumbnail_path">
              <img :src="`/storage/${exercise.thumbnail_path}`" class="h-full w-full object-contain" :alt="exercise.title" />
            </template>
            <div v-else class="p-8 text-center text-slate-200">
              <p class="text-lg font-semibold">Khu vực hình ảnh hoặc video hướng dẫn</p>
              <p class="mt-2 text-sm text-slate-400">Có thể bổ sung ảnh minh họa hoặc video khi cần.</p>
            </div>
          </div>

          <div class="space-y-5 p-6">
            <div>
              <div class="mb-3 flex flex-wrap gap-2">
                <span class="rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs font-medium text-slate-700">
                  {{ labelFor(categoryLabels, exercise.category) }}
                </span>
                <span v-if="exercise.difficulty" class="rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs font-medium text-slate-700">
                  {{ labelFor(difficultyLabels, exercise.difficulty) }}
                </span>
                <span v-if="exercise.estimated_minutes" class="rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs font-medium text-slate-700">
                  {{ exercise.estimated_minutes }} phút
                </span>
              </div>
              <h1 class="text-3xl font-bold leading-tight text-slate-950">{{ exercise.title }}</h1>
              <p class="mt-3 text-base leading-7 text-slate-600">{{ exercise.description || exercise.instructions }}</p>
            </div>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
              <ExerciseInfoBox title="Phù hợp cho mục tiêu" :value="labelFor(skillLabels, exercise.target_skill, 'Đang cập nhật')" />
              <ExerciseInfoBox title="Độ tuổi gợi ý" :value="exercise.recommended_age || 'Theo khả năng của bé'" />
              <ExerciseInfoBox title="Dụng cụ cần chuẩn bị" :value="exercise.required_tools || 'Đồ dùng đơn giản trong gia đình'" />
              <ExerciseInfoBox title="Trạng thái" :value="exercise.is_active ? 'Đang sử dụng' : 'Tạm ẩn'" />
            </div>
          </div>
        </div>
      </section>

      <div class="grid grid-cols-1 gap-6 lg:grid-cols-[1fr_340px]">
        <main class="space-y-6">
          <ExerciseDetailSection title="Lợi ích của bài tập" :content="exercise.expected_benefits" fallback="Bài tập giúp bé luyện kỹ năng nền tảng và tăng khả năng hợp tác trong sinh hoạt hằng ngày." />
          <ExerciseDetailSection title="Hướng dẫn cho phụ huynh" :content="exercise.parent_tips" fallback="Tập ngắn, vui vẻ, khen đúng lúc và giảm yêu cầu khi bé căng thẳng." />
          <ExerciseDetailSection title="Sau khoảng 1 tuần tập đều, bé có thể cải thiện" :content="exercise.weekly_expectation" fallback="Bé có thể chú ý tốt hơn, hợp tác hơn và quen dần với hoạt động." />
          <ExerciseDetailSection title="Lưu ý an toàn" :content="exercise.safety_notes" fallback="Luôn có người lớn quan sát và dừng lại nếu bé khó chịu hoặc mệt." />

          <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-950">Các bước thực hiện</h2>
            <div v-if="exercise.steps?.length" class="mt-4 space-y-4">
              <div v-for="(step, index) in exercise.steps" :key="step.id" class="flex gap-4 rounded-lg bg-slate-50 p-4">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-indigo-600 text-sm font-bold text-white">
                  {{ index + 1 }}
                </div>
                <div>
                  <h3 class="font-semibold text-slate-900">{{ step.title }}</h3>
                  <p class="mt-1 text-sm leading-6 text-slate-600">{{ step.instruction }}</p>
                </div>
              </div>
            </div>
            <div v-else class="mt-4 whitespace-pre-wrap rounded-lg bg-slate-50 p-4 text-sm leading-6 text-slate-700">
              {{ exercise.instructions || 'Hướng dẫn chi tiết đang được cập nhật.' }}
            </div>
          </section>
        </main>

        <aside class="space-y-6">
          <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-950">Combo gợi ý</h2>
            <div v-if="suggestedCombos.length" class="mt-3 space-y-3">
              <div v-for="combo in suggestedCombos" :key="combo.id" class="rounded-md bg-slate-50 p-3">
                <p class="font-medium text-slate-900">{{ combo.title }}</p>
                <p class="mt-1 text-sm text-slate-600">{{ combo.estimated_minutes || 0 }} phút · {{ combo.recommended_frequency || 'Tập đều trong tuần' }}</p>
              </div>
            </div>
            <p v-else class="mt-3 text-sm text-slate-500">Chưa có combo liên quan.</p>
          </section>

          <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-950">Bài tập liên quan</h2>
            <div v-if="relatedExercises.length" class="mt-3 space-y-3">
              <Link
                v-for="item in relatedExercises"
                :key="item.id"
                :href="`/exercises/${item.id}`"
                class="block rounded-md bg-slate-50 p-3 text-sm font-medium text-slate-800 hover:bg-slate-100"
              >
                {{ item.title }}
              </Link>
            </div>
            <p v-else class="mt-3 text-sm text-slate-500">Chưa có bài tập liên quan.</p>
          </section>

          <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-950">Lịch mẫu phù hợp</h2>
            <div v-if="suggestedWeeklyPlans.length" class="mt-3 space-y-3">
              <div v-for="plan in suggestedWeeklyPlans" :key="plan.id" class="rounded-md bg-slate-50 p-3">
                <p class="font-medium text-slate-900">{{ plan.title }}</p>
                <p class="mt-1 text-sm leading-6 text-slate-600">{{ plan.description }}</p>
              </div>
            </div>
            <p v-else class="mt-3 text-sm text-slate-500">Chưa có lịch mẫu phù hợp.</p>
          </section>
        </aside>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '../../Components/layout/AppLayout.vue';
import ExerciseDetailSection from '../../Components/exercises/ExerciseDetailSection.vue';
import ExerciseInfoBox from '../../Components/exercises/ExerciseInfoBox.vue';
import { categoryLabels, difficultyLabels, labelFor, skillLabels } from '@/Lib/labels';

const props = defineProps({
  exercise: {
    type: Object,
    required: true,
  },
  relatedExercises: {
    type: Array,
    default: () => [],
  },
  suggestedCombos: {
    type: Array,
    default: () => [],
  },
  suggestedWeeklyPlans: {
    type: Array,
    default: () => [],
  },
});

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
  if (confirm('Bạn có chắc muốn xóa bài tập này không?')) {
    router.delete(`/exercises/${props.exercise.id}`);
  }
};
</script>
