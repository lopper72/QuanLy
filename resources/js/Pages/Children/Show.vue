<template>
  <AppLayout>
    <div class="space-y-6">
      <!-- Top Alert for Flash messages -->
      <div v-if="flash?.success || $page.props.flash?.success" class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 border border-green-200" role="alert">
        <span class="font-medium">Thành công!</span> {{ flash?.success || $page.props.flash?.success }}
      </div>

      <div v-if="child.status === 'paused'" class="p-4 text-sm text-amber-800 rounded-lg bg-amber-50 border border-amber-200" role="status">
        Trẻ đang tạm nghỉ, không nên tạo hoạt động can thiệp mới.
      </div>
      <div v-if="child.status === 'stopped'" class="p-4 text-sm text-orange-800 rounded-lg bg-orange-50 border border-orange-200" role="status">
        Trẻ đang dừng can thiệp, không nên tạo hoạt động can thiệp mới.
      </div>
      <div v-if="child.status === 'voided'" class="p-4 text-sm text-slate-700 rounded-lg bg-slate-100 border border-slate-200" role="status">
        Hồ sơ đã ngừng can thiệp. Dữ liệu chỉ dùng để xem lại lịch sử.
      </div>

      <!-- Navigation & Action Header -->
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <Link href="/children" class="inline-flex items-center text-sm font-medium text-slate-600 hover:text-indigo-600">
          <svg class="mr-1 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
          Quay lại danh sách
        </Link>
        <div class="flex items-center gap-2 w-full sm:w-auto">
          <Link
            :href="`/children/${child.id}/edit`"
            class="flex-1 sm:flex-none text-center px-4 py-2 border border-slate-200 rounded-md text-sm font-medium text-slate-700 bg-white hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
          >
            Chỉnh sửa hồ sơ
          </Link>
          <button
            v-if="child.status === 'active'"
            @click="pauseChild"
            class="flex-1 sm:flex-none px-4 py-2 border border-amber-200 rounded-md text-sm font-medium text-amber-700 bg-amber-50 hover:bg-amber-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500"
          >
            Tạm nghỉ
          </button>
          <button
            v-if="canResume"
            @click="resumeChild"
            class="flex-1 sm:flex-none px-4 py-2 border border-emerald-200 rounded-md text-sm font-medium text-emerald-700 bg-emerald-50 hover:bg-emerald-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500"
          >
            Tiếp tục can thiệp
          </button>
          <button
            v-if="child.status !== 'voided'"
            @click="voidChild"
            class="flex-1 sm:flex-none px-4 py-2 border border-transparent rounded-md text-sm font-medium text-white bg-rose-600 hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-rose-500"
          >
            Ngừng can thiệp
          </button>
        </div>
      </div>

      <!-- Main Profile Layout -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Personal Information Column -->
        <div class="lg:col-span-1 space-y-6">
          <div class="bg-white rounded-lg shadow-sm border border-slate-100 p-6">
            <div class="text-center pb-6 border-b border-slate-100">
              <div class="h-20 w-20 rounded-full bg-indigo-50 text-indigo-700 mx-auto flex items-center justify-center text-3xl font-bold shadow-inner">
                {{ initials }}
              </div>
              <h2 class="mt-4 text-xl font-bold text-slate-800">{{ child.full_name }}</h2>
              <p v-if="child.nickname" class="text-sm text-slate-500">Biệt danh: "{{ child.nickname }}"</p>
              <p class="text-xs text-slate-400 mt-1">ID: #{{ child.id }}</p>
              <div class="mt-3 flex flex-wrap justify-center gap-2">
                <span
                  class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold border"
                  :class="childStatusClass"
                >
                  {{ childStatusLabel }}
                </span>
                <span
                  v-if="child.diagnosis_level"
                  class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-100"
                >
                  {{ diagnosisLabel }}
                </span>
              </div>
              <p v-if="child.status_note" class="mt-2 text-xs text-slate-500 italic">
                Ghi chú trạng thái: {{ child.status_note }}
              </p>
            </div>

            <!-- Profile Details List -->
            <div class="mt-6 space-y-4 text-sm text-slate-600">
              <div>
                <span class="block text-xs font-semibold text-slate-400">Tuổi</span>
                <span class="font-medium text-slate-800">{{ age }}</span>
              </div>
              <div>
                <span class="block text-xs font-semibold text-slate-400">Giới tính</span>
                <span class="font-medium text-slate-800">{{ genderLabel }}</span>
              </div>
              <div>
                <span class="block text-xs font-semibold text-slate-400">Ngày sinh</span>
                <span class="font-medium text-slate-800">{{ formattedDob }}</span>
              </div>
              <div>
                <span class="block text-xs font-semibold text-slate-400">Ngày tạo hồ sơ</span>
                <span class="font-medium text-slate-800">{{ formattedCreatedDate }}</span>
              </div>
            </div>
          </div>

          <!-- Notes Card -->
          <div class="bg-white rounded-lg shadow-sm border border-slate-100 p-6">
            <h3 class="text-sm font-bold text-slate-800 text-slate-400 mb-2">Ghi chú & Quan sát</h3>
            <p class="text-sm text-slate-700 leading-relaxed italic whitespace-pre-wrap">
              {{ child.notes || 'Không có ghi chú nào.' }}
            </p>
          </div>
        </div>

        <!-- Dynamic Activity / Programs Columns -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Daily Training Sessions -->
          <div class="bg-white rounded-lg shadow-sm border border-slate-100 p-6">
            <div class="flex justify-between items-center mb-4">
              <h3 class="text-lg font-bold text-slate-800">Tập luyện gần đây</h3>
              <a href="/training" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">Quản lý tập luyện &rarr;</a>
            </div>
            <div v-if="!child.training_sessions || child.training_sessions.length === 0" class="text-center py-6 bg-slate-50 rounded-lg border border-dashed border-slate-200">
              <p class="text-sm text-slate-500">Chưa có buổi tập nào được ghi nhận cho {{ child.full_name }}.</p>
            </div>
            <div v-else class="space-y-3">
                <div v-for="session in child.training_sessions" :key="session.id" class="p-3 bg-slate-50 rounded-md border border-slate-100 flex justify-between items-center text-sm">
                  <div>
                    <p class="font-semibold text-slate-800">{{ session.name }}</p>
                    <p class="text-xs text-slate-400">
                      {{ session.date }}
                      <span v-if="session.scheduled_time" class="ml-1 font-medium text-indigo-600">
                        • {{ session.scheduled_time }}
                      </span>
                    </p>
                    <div v-if="session.items?.length" class="mt-2 flex -space-x-2">
                      <ExerciseThumbnail
                        v-for="item in session.items.slice(0, 3)"
                        :key="item.id"
                        :exercise="item.exercise"
                        size="sm"
                        :alt="item.exercise?.title || 'Bài tập'"
                        class="ring-2 ring-white"
                      />
                    </div>
                  </div>
                  <span class="px-2 py-1 rounded text-xs font-semibold bg-green-50 text-green-700">{{ statusLabel(session.status) }}</span>
                </div>
            </div>
          </div>

          <!-- Assessments -->
          <div class="bg-white rounded-lg shadow-sm border border-slate-100 p-6">
            <div class="flex justify-between items-center mb-4">
              <h3 class="text-lg font-bold text-slate-800">Đánh giá gần đây</h3>
              <a href="/assessment" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">Đánh giá mới &rarr;</a>
            </div>
            <div v-if="!child.assessments || child.assessments.length === 0" class="text-center py-6 bg-slate-50 rounded-lg border border-dashed border-slate-200">
              <p class="text-sm text-slate-500">Không tìm thấy đánh giá nào cho {{ child.full_name }}.</p>
            </div>
            <div v-else class="space-y-3">
              <div v-for="assessment in child.assessments" :key="assessment.id" class="p-3 bg-slate-50 rounded-md border border-slate-100 flex justify-between items-center text-sm">
                <div>
                  <p class="font-semibold text-slate-800">{{ assessment.title }}</p>
                  <p class="text-xs text-slate-400">Điểm: {{ assessment.score }}%</p>
                </div>
                <span class="text-xs text-slate-500">{{ assessment.date }}</span>
              </div>
            </div>
          </div>

          <!-- Behavior Tracking Logs -->
          <div class="bg-white rounded-lg shadow-sm border border-slate-100 p-6">
            <div class="flex justify-between items-center mb-4">
              <h3 class="text-lg font-bold text-slate-800">Nhật ký hành vi gần đây</h3>
              <a href="/behavior" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">Ghi nhận hành vi &rarr;</a>
            </div>
            <div v-if="!child.behavior_logs || child.behavior_logs.length === 0" class="text-center py-6 bg-slate-50 rounded-lg border border-dashed border-slate-200">
              <p class="text-sm text-slate-500">Chưa có nhật ký hành vi nào được ghi nhận.</p>
            </div>
            <div v-else class="space-y-3">
              <div v-for="log in child.behavior_logs" :key="log.id" class="p-3 bg-slate-50 rounded-md border border-slate-100 text-sm">
                <div class="flex justify-between items-start">
                  <p class="font-semibold text-slate-800">{{ log.behavior_name }}</p>
                  <span class="text-xs font-semibold px-2 py-0.5 rounded" :class="severityClass(log.severity)">
                    {{ severityLabel(log.severity) }}
                  </span>
                </div>
                <p class="text-xs text-slate-400 mt-1">Đã ghi nhận vào: {{ log.date }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '../../Components/layout/AppLayout.vue';
import ExerciseThumbnail from '@/Components/exercises/ExerciseThumbnail.vue';
import { childStatusLabels, diagnosisLevelLabels, genderLabels, severityLabels, statusLabels, labelFor } from '@/Lib/labels';

const props = defineProps({
  child: {
    type: Object,
    required: true,
  },
  flash: {
    type: Object,
    default: () => ({ success: null }),
  },
});

const initials = computed(() => {
  if (!props.child.full_name) return '';
  return props.child.full_name
    .split(' ')
    .map((name) => name[0])
    .join('')
    .slice(0, 2);
});

const age = computed(() => {
  if (!props.child.date_of_birth) return 'Chưa xác định';
  const birthDate = new Date(props.child.date_of_birth);
  const today = new Date();
  let ageYears = today.getFullYear() - birthDate.getFullYear();
  const m = today.getMonth() - birthDate.getMonth();
  if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
    ageYears--;
  }
  return ageYears >= 0 ? `${ageYears} tuổi` : 'Chưa xác định';
});

const diagnosisLabel = computed(() => labelFor(diagnosisLevelLabels, props.child.diagnosis_level));
const genderLabel = computed(() => labelFor(genderLabels, props.child.gender));
const childStatusLabel = computed(() => labelFor(childStatusLabels, props.child.status));
const childStatusClass = computed(() => {
  const classes = {
    active: 'bg-emerald-50 text-emerald-700 border-emerald-200',
    paused: 'bg-amber-50 text-amber-700 border-amber-200',
    stopped: 'bg-orange-50 text-orange-700 border-orange-200',
    voided: 'bg-slate-100 text-slate-600 border-slate-200',
  };

  return classes[props.child.status] || 'bg-slate-100 text-slate-600 border-slate-200';
});

const statusLabel = (status) => labelFor(statusLabels, status);

const severityLabel = (severity) => {
  const normalized = String(severity || '').toLowerCase();
  return labelFor(severityLabels, normalized, 'Chưa xác định');
};

const severityClass = (severity) => {
  const normalized = String(severity || '').toLowerCase();
  if (normalized === 'high') return 'bg-red-50 text-red-700';
  if (normalized === 'medium') return 'bg-amber-50 text-amber-700';
  return 'bg-green-50 text-green-700';
};

const formattedDob = computed(() => {
  if (!props.child.date_of_birth) return 'Chưa xác định';
  const date = new Date(props.child.date_of_birth);
  return date.toLocaleDateString('vi-VN', { year: 'numeric', month: 'long', day: 'numeric' });
});

const formattedCreatedDate = computed(() => {
  if (!props.child.created_at) return 'Chưa xác định';
  const date = new Date(props.child.created_at);
  return date.toLocaleDateString('vi-VN', { year: 'numeric', month: 'long', day: 'numeric' });
});

const pauseChild = () => {
  const statusNote = window.prompt('Nhập lý do tạm nghỉ nếu cần:') || null;
  router.patch(route('children.pause', props.child.id), { status_note: statusNote }, { preserveScroll: true });
};

const canResume = computed(() => ['paused', 'stopped'].includes(props.child.status));

const resumeChild = () => {
  if (!window.confirm(`Bạn có chắc chắn muốn tiếp tục can thiệp cho ${props.child.full_name}?`)) {
    return;
  }

  router.post(route('children.resume', props.child.id), {}, { preserveScroll: true });
};

const voidChild = () => {
  if (confirm(`Bạn có chắc chắn muốn ngừng can thiệp cho ${props.child.full_name}? Dữ liệu lịch sử vẫn được giữ lại.`)) {
    const statusNote = window.prompt('Nhập lý do ngừng can thiệp nếu cần:') || null;
    router.patch(route('children.void', props.child.id), { status_note: statusNote }, { preserveScroll: true });
  }
};
</script>
