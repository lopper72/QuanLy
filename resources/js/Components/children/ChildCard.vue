<template>
  <div class="bg-white rounded-lg shadow-sm border border-slate-100 hover:shadow-md transition-shadow p-5 flex flex-col justify-between">
    <div>
      <div class="flex justify-between items-start">
        <div>
          <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
            {{ child.full_name }}
            <span v-if="child.nickname" class="text-sm font-normal text-slate-500">({{ child.nickname }})</span>
          </h3>
          <p class="text-xs text-slate-500 mt-1">ID: #{{ child.id }}</p>
        </div>
        <div class="flex flex-col items-end gap-1">
          <span
            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border"
            :class="statusClass"
          >
            {{ statusLabel }}
          </span>
          <span
            v-if="child.diagnosis_level"
            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-50 text-indigo-700 border border-indigo-100"
          >
            {{ diagnosisLabel }}
          </span>
        </div>
      </div>

      <div class="mt-4 grid grid-cols-2 gap-x-4 gap-y-2 text-sm text-slate-600">
        <div>
          <span class="block text-xs text-slate-400">Tuổi</span>
          <span class="font-medium">{{ age }}</span>
        </div>
        <div>
          <span class="block text-xs text-slate-400">Giới tính</span>
          <span class="font-medium">{{ genderLabel }}</span>
        </div>
        <div class="col-span-2 mt-1">
          <span class="block text-xs text-slate-400">Ngày sinh</span>
          <span class="font-medium">{{ formattedDob }}</span>
        </div>
      </div>

      <div class="mt-4 pt-3 border-t border-slate-50">
        <span class="block text-xs text-slate-400">Ghi chú</span>
        <p class="text-sm text-slate-600 line-clamp-2 mt-1 italic">
          {{ child.notes || 'Chưa có ghi chú chuyên môn.' }}
        </p>
      </div>
    </div>

    <div class="mt-6 flex flex-wrap items-center justify-end gap-2">
      <Link
        :href="`/children/${child.id}`"
        class="inline-flex items-center px-3 py-1.5 border border-transparent rounded-md text-xs font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
      >
        Xem hồ sơ &rarr;
      </Link>
      <Link
        v-if="child.status !== 'voided'"
        :href="`/children/${child.id}/edit`"
        class="inline-flex items-center px-3 py-1.5 border border-slate-200 rounded-md text-xs font-medium text-slate-700 bg-white hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
      >
        Sửa
      </Link>
      <button
        v-if="child.status === 'active'"
        type="button"
        @click="pauseChild"
        class="inline-flex items-center px-3 py-1.5 border border-amber-200 rounded-md text-xs font-medium text-amber-700 bg-amber-50 hover:bg-amber-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500"
      >
        Tạm nghỉ
      </button>
      <button
        v-if="canResume"
        type="button"
        @click="resumeChild"
        class="inline-flex items-center px-3 py-1.5 border border-emerald-200 rounded-md text-xs font-medium text-emerald-700 bg-emerald-50 hover:bg-emerald-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500"
      >
        Tiếp tục can thiệp
      </button>
      <button
        v-if="child.status !== 'voided'"
        type="button"
        @click="voidChild"
        class="inline-flex items-center px-3 py-1.5 border border-rose-200 rounded-md text-xs font-medium text-rose-700 bg-rose-50 hover:bg-rose-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-rose-500"
      >
        Ngừng can thiệp
      </button>
      <button
        type="button"
        @click="deleteChild"
        class="inline-flex items-center px-3 py-1.5 border border-red-200 rounded-md text-xs font-medium text-red-700 bg-red-50 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
      >
        Xóa
      </button>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { childStatusLabels, diagnosisLevelLabels, genderLabels, labelFor } from '@/Lib/labels';

const props = defineProps({
  child: {
    type: Object,
    required: true,
  },
});

const age = computed(() => {
  if (!props.child.date_of_birth) return 'Chưa có';
  const birthDate = new Date(props.child.date_of_birth);
  const today = new Date();
  let ageYears = today.getFullYear() - birthDate.getFullYear();
  const m = today.getMonth() - birthDate.getMonth();
  if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
    ageYears--;
  }
  return ageYears >= 0 ? `${ageYears} tuổi` : 'Chưa có';
});

const formattedDob = computed(() => {
  if (!props.child.date_of_birth) return 'Chưa xác định';
  const date = new Date(props.child.date_of_birth);
  return date.toLocaleDateString('vi-VN', { year: 'numeric', month: 'short', day: 'numeric' });
});

const genderLabel = computed(() => {
  return labelFor(genderLabels, props.child.gender);
});

const diagnosisLabel = computed(() => {
  return labelFor(diagnosisLevelLabels, props.child.diagnosis_level);
});

const statusLabel = computed(() => {
  return labelFor(childStatusLabels, props.child.status);
});

const statusClass = computed(() => {
  const classes = {
    active: 'bg-emerald-50 text-emerald-700 border-emerald-200',
    paused: 'bg-amber-50 text-amber-700 border-amber-200',
    stopped: 'bg-orange-50 text-orange-700 border-orange-200',
    voided: 'bg-slate-100 text-slate-600 border-slate-200',
  };

  return classes[props.child.status] || 'bg-slate-100 text-slate-600 border-slate-200';
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
  if (!window.confirm(`Bạn có chắc chắn muốn ngừng can thiệp cho ${props.child.full_name}? Dữ liệu lịch sử vẫn được giữ lại.`)) {
    return;
  }

  const statusNote = window.prompt('Nhập lý do ngừng can thiệp nếu cần:') || null;
  router.patch(route('children.void', props.child.id), { status_note: statusNote }, { preserveScroll: true });
};

const deleteChild = () => {
  if (!window.confirm('Bạn có chắc muốn xóa hồ sơ trẻ này không? Dữ liệu liên quan có thể bị ảnh hưởng.')) {
    return;
  }

  router.delete(route('children.destroy', props.child.id), { preserveScroll: true });
};
</script>
