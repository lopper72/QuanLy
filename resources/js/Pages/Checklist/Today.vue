<template>
  <AppLayout>
    <div class="mx-auto max-w-3xl space-y-5 pb-20">
      <section class="rounded-2xl bg-indigo-700 px-5 py-6 text-white shadow-sm">
        <p class="text-sm text-indigo-100">{{ greeting }}</p>
        <h1 class="mt-1 text-2xl font-bold">Checklist hôm nay</h1>
        <p class="mt-2 text-sm text-indigo-100">
          Hôm nay có {{ summary.total_items }} hoạt động. Hoàn thành {{ summary.completed_items }} / {{ summary.total_items }} bài tập.
        </p>
        <div class="mt-5">
          <div class="flex items-center justify-between text-xs font-semibold text-indigo-100">
            <span>{{ summary.completion_percent }}%</span>
            <span>Còn {{ summary.remaining_items }} bài</span>
          </div>
          <div class="mt-2 h-3 rounded-full bg-indigo-900/40">
            <div class="h-3 rounded-full bg-emerald-300 transition-all" :style="{ width: `${summary.completion_percent}%` }"></div>
          </div>
        </div>
      </section>

      <section class="grid grid-cols-2 gap-3">
        <div class="rounded-xl border border-amber-100 bg-amber-50 p-4">
          <p class="text-xs font-semibold text-amber-700">Chuỗi hoàn thành</p>
          <p class="mt-2 text-xl font-bold text-amber-900">{{ bestStreakText }}</p>
        </div>
        <div class="rounded-xl border border-emerald-100 bg-emerald-50 p-4">
          <p class="text-xs font-semibold text-emerald-700">Phần thưởng</p>
          <p class="mt-2 text-sm font-semibold text-emerald-900">{{ rewardText }}</p>
        </div>
      </section>

      <section v-if="children.length" class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="flex items-center justify-between gap-3">
          <div>
            <h2 class="text-base font-bold text-slate-900">Tâm trạng của bé</h2>
            <p class="text-xs text-slate-500">Chọn nhanh để lưu vào báo cáo cuối ngày.</p>
          </div>
          <select v-model="selectedChildId" class="rounded-lg border-slate-300 text-sm">
            <option v-for="child in children" :key="child.id" :value="child.id">{{ child.full_name }}</option>
          </select>
        </div>
        <div class="mt-4 grid grid-cols-2 gap-2 sm:grid-cols-4">
          <button
            v-for="mood in moodOptions"
            :key="mood.value"
            type="button"
            class="rounded-xl border px-3 py-3 text-sm font-semibold transition"
            :class="selectedMood === mood.value ? 'border-indigo-500 bg-indigo-50 text-indigo-700' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50'"
            @click="saveMood(mood.value)"
          >
            <span class="block text-lg">{{ mood.icon }}</span>
            {{ mood.label }}
          </button>
        </div>
      </section>

      <section v-if="reminders.length" class="rounded-xl border border-sky-100 bg-sky-50 p-4">
        <h2 class="text-sm font-bold text-sky-900">Nhắc hẹn sắp tới</h2>
        <div class="mt-3 space-y-2">
          <div v-for="reminder in reminders" :key="reminder.id" class="rounded-lg bg-white px-3 py-2 text-sm text-sky-900">
            {{ reminder.minutes_until }} phút nữa tới bài: <span class="font-semibold">{{ reminder.exercise_title }}</span>
          </div>
        </div>
      </section>

      <section class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
          <div>
            <h2 class="text-lg font-bold text-slate-900">Hoạt động hôm nay</h2>
            <p class="text-sm text-slate-500">Chạm một lần để cập nhật trạng thái.</p>
          </div>
          <select v-model="contextMode" @change="changeContext" class="rounded-lg border-slate-300 text-sm">
            <option value="home">Ở nhà</option>
            <option value="supermarket">Đi siêu thị</option>
            <option value="travel">Đi du lịch</option>
            <option value="grandparents">Về ông bà</option>
            <option value="hospital">Đi bệnh viện</option>
          </select>
        </div>

        <div v-if="timeline.length" class="mt-4 space-y-4">
          <article
            v-for="item in timeline"
            :key="item.id"
            class="rounded-2xl border border-slate-200 bg-slate-50 p-3"
          >
            <div class="flex gap-3">
              <ExerciseThumbnail :exercise="item.exercise" size="lg" :alt="item.exercise?.title || 'Ảnh bài tập'" />
              <div class="min-w-0 flex-1">
                <div class="flex items-start justify-between gap-2">
                  <div class="min-w-0">
                    <p class="text-xs font-semibold text-indigo-600">{{ item.time || 'Linh hoạt' }} • {{ item.duration_minutes || 0 }} phút</p>
                    <h3 class="truncate text-base font-bold text-slate-900">{{ item.exercise?.title || 'Bài tập' }}</h3>
                    <p class="mt-1 line-clamp-2 text-xs text-slate-500">{{ item.short_instruction || item.therapist_note || 'Làm theo hướng dẫn của chuyên viên.' }}</p>
                  </div>
                  <span class="shrink-0 rounded-full px-2 py-1 text-xs font-semibold" :class="statusClass(item.status)">
                    {{ statusLabel(item.status) }}
                  </span>
                </div>

                <div class="mt-3 grid grid-cols-2 gap-2 sm:grid-cols-4">
                  <button
                    v-for="status in statusOptions.filter((option) => option.value !== 'not_started')"
                    :key="status.value"
                    type="button"
                    class="min-h-11 rounded-xl border px-2 py-2 text-xs font-semibold transition"
                    :class="item.status === status.value ? 'border-indigo-500 bg-indigo-600 text-white' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-100'"
                    @click="updateStatus(item, status.value)"
                  >
                    <span class="block text-base">{{ status.icon }}</span>
                    {{ status.label }}
                  </button>
                </div>

                <div class="mt-3 grid grid-cols-2 gap-2">
                  <button
                    type="button"
                    class="rounded-xl bg-emerald-600 px-3 py-3 text-sm font-bold text-white shadow-sm"
                    @click="quickComplete(item)"
                  >
                    Đã tập xong
                  </button>
                  <button
                    type="button"
                    class="rounded-xl border border-slate-200 bg-white px-3 py-3 text-sm font-semibold text-slate-700"
                    @click="carryOver(item)"
                    :disabled="item.status === 'completed' || item.carried_over_at"
                  >
                    Chuyển sang mai
                  </button>
                </div>

                <div class="mt-3 grid grid-cols-2 gap-2 sm:grid-cols-4">
                  <button
                    v-for="result in resultOptions"
                    :key="result.value"
                    type="button"
                    class="rounded-lg border px-2 py-2 text-xs font-semibold"
                    :class="item.performance_result === result.value ? 'border-emerald-500 bg-emerald-50 text-emerald-700' : 'border-slate-200 bg-white text-slate-600'"
                    @click="saveResult(item, result.value)"
                  >
                    {{ result.label }}
                  </button>
                </div>

                <div class="mt-3 flex gap-2">
                  <input
                    v-model="notes[item.id]"
                    type="text"
                    class="min-h-11 flex-1 rounded-xl border-slate-300 text-sm"
                    placeholder="Ghi chú nhanh..."
                  />
                  <button
                    type="button"
                    class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white"
                    @click="saveNote(item)"
                  >
                    Lưu
                  </button>
                </div>

                <div class="mt-3 flex flex-wrap gap-2 text-xs">
                  <Link
                    v-if="item.exercise?.id"
                    :href="route('exercises.show', item.exercise.id)"
                    class="rounded-full bg-white px-3 py-1.5 font-semibold text-indigo-700 ring-1 ring-indigo-100"
                  >
                    Xem nhanh 20 giây
                  </Link>
                  <span v-if="item.has_video" class="rounded-full bg-white px-3 py-1.5 font-semibold text-slate-600 ring-1 ring-slate-200">Có video hướng dẫn</span>
                </div>
              </div>
            </div>
          </article>
        </div>

        <div v-else class="mt-4 rounded-xl border border-dashed border-slate-300 p-8 text-center">
          <h3 class="text-base font-bold text-slate-900">Hôm nay chưa có hoạt động</h3>
          <p class="mt-1 text-sm text-slate-500">Khi chuyên viên lên lịch buổi tập, checklist sẽ tự xuất hiện ở đây.</p>
        </div>
      </section>

      <section v-if="suggestions.length" class="rounded-xl border border-rose-100 bg-rose-50 p-4">
        <h2 class="text-sm font-bold text-rose-900">Gợi ý điều chỉnh</h2>
        <p v-for="suggestion in suggestions" :key="suggestion.item_id" class="mt-2 text-sm text-rose-800">{{ suggestion.text }}</p>
      </section>

      <section v-if="children.length" class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <h2 class="text-base font-bold text-slate-900">Ghi nhận tiến bộ nhanh</h2>
        <div class="mt-3 flex gap-2">
          <input
            v-model="progressText"
            type="text"
            class="min-h-11 flex-1 rounded-xl border-slate-300 text-sm"
            placeholder="Ví dụ: Bé tự cất dép"
          />
          <button type="button" class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white" @click="saveProgressLog">
            Thêm
          </button>
        </div>
      </section>

      <section class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <h2 class="text-base font-bold text-slate-900">Dòng thời gian cuối ngày</h2>
        <div class="mt-4 space-y-3">
          <div v-for="item in timeline" :key="`line-${item.id}`" class="flex gap-3 text-sm">
            <span class="w-14 shrink-0 font-semibold text-slate-500">{{ item.time || '--:--' }}</span>
            <div>
              <p class="font-semibold text-slate-900">{{ statusIcon(item.status) }} {{ item.exercise?.title || 'Bài tập' }}</p>
              <p v-if="item.parent_note" class="text-xs text-slate-500">{{ item.parent_note }}</p>
            </div>
          </div>
        </div>
      </section>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed, reactive, ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Components/layout/AppLayout.vue';
import ExerciseThumbnail from '@/Components/exercises/ExerciseThumbnail.vue';

const props = defineProps({
  date: String,
  greeting: String,
  summary: Object,
  children: Array,
  timeline: Array,
  reminders: Array,
  streak: Array,
  moods: Object,
  progressLogs: Array,
  suggestions: Array,
  contextMode: String,
});

const selectedChildId = ref(props.children?.[0]?.id || null);
const contextMode = ref(props.contextMode || 'home');
const progressText = ref('');
const notes = reactive(Object.fromEntries((props.timeline || []).map((item) => [item.id, item.parent_note || ''])));

const statusOptions = [
  { value: 'pending', label: 'Chưa thực hiện', icon: '☐' },
  { value: 'not_started', label: 'Chưa làm', icon: '☐' },
  { value: 'in_progress', label: 'Đang làm', icon: '◐' },
  { value: 'completed', label: 'Hoàn thành', icon: '✅' },
  { value: 'refused', label: 'Bé từ chối', icon: '❌' },
];

const resultOptions = [
  { value: 'good', label: 'Làm tốt' },
  { value: 'needs_support', label: 'Cần hỗ trợ' },
  { value: 'not_cooperative', label: 'Không hợp tác' },
  { value: 'hard_to_focus', label: 'Khó tập trung' },
];

const moodOptions = [
  { value: 'good', label: 'Tốt', icon: '🙂' },
  { value: 'normal', label: 'Bình thường', icon: '😐' },
  { value: 'tired', label: 'Mệt', icon: '😣' },
  { value: 'upset', label: 'Khó chịu', icon: '😡' },
];

const selectedMood = computed(() => props.moods?.[selectedChildId.value]?.mood || '');
const bestStreak = computed(() => Math.max(0, ...(props.streak || []).map((item) => item.current_streak || 0)));
const bestStreakText = computed(() => `${bestStreak.value} ngày liên tiếp`);
const rewardText = computed(() => bestStreak.value >= 7 ? 'Hoàn thành đủ checklist 7 ngày' : 'Cố gắng hoàn thành hôm nay');

function updateStatus(item, status) {
  router.patch(route('checklist.items.update', item.id), { status }, { preserveScroll: true });
}

function quickComplete(item) {
  router.patch(route('checklist.items.quickComplete', item.id), {}, { preserveScroll: true });
}

function saveResult(item, performanceResult) {
  router.patch(route('checklist.items.update', item.id), {
    status: item.status,
    performance_result: performanceResult,
    parent_note: notes[item.id] || '',
  }, { preserveScroll: true });
}

function saveNote(item) {
  router.patch(route('checklist.items.update', item.id), {
    status: item.status,
    performance_result: item.performance_result,
    parent_note: notes[item.id] || '',
  }, { preserveScroll: true });
}

function carryOver(item) {
  if (item.status === 'completed' || item.carried_over_at) return;
  if (!window.confirm('Chuyển bài tập này sang ngày mai?')) return;
  router.post(route('checklist.items.carryOver', item.id), {}, { preserveScroll: true });
}

function saveMood(mood) {
  if (!selectedChildId.value) return;
  router.post(route('checklist.children.mood', selectedChildId.value), { mood }, { preserveScroll: true });
}

function saveProgressLog() {
  if (!selectedChildId.value || !progressText.value.trim()) return;
  router.post(route('checklist.children.progressLog', selectedChildId.value), {
    title: progressText.value.trim(),
  }, {
    preserveScroll: true,
    onSuccess: () => {
      progressText.value = '';
    },
  });
}

function changeContext() {
  router.get(route('today'), { context_mode: contextMode.value }, { preserveState: true, preserveScroll: true });
}

function statusLabel(status) {
  return statusOptions.find((item) => item.value === status)?.label || 'Chưa làm';
}

function statusIcon(status) {
  return statusOptions.find((item) => item.value === status)?.icon || '☐';
}

function statusClass(status) {
  if (status === 'completed') return 'bg-emerald-100 text-emerald-700';
  if (status === 'in_progress') return 'bg-amber-100 text-amber-700';
  if (status === 'refused') return 'bg-rose-100 text-rose-700';
  if (status === 'missed') return 'bg-red-100 text-red-700';
  return 'bg-slate-100 text-slate-600';
}
</script>
