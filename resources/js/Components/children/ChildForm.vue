<template>
  <form @submit.prevent="submit" class="space-y-6 max-w-2xl bg-white p-6 rounded-lg shadow-sm border border-slate-100">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <!-- Full Name -->
      <div>
        <label for="full_name" class="block text-sm font-medium text-slate-700">Họ và tên <span class="text-rose-500">*</span></label>
        <input
          id="full_name"
          v-model="form.full_name"
          type="text"
          class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          :class="{ 'border-rose-300 focus:border-rose-500 focus:ring-rose-500': form.errors.full_name }"
          required
          placeholder="Ví dụ: Nguyễn Minh Anh"
        />
        <p v-if="form.errors.full_name" class="mt-1 text-sm text-rose-600">{{ form.errors.full_name }}</p>
      </div>

      <!-- Nickname -->
      <div>
        <label for="nickname" class="block text-sm font-medium text-slate-700">Tên gọi ở nhà</label>
        <input
          id="nickname"
          v-model="form.nickname"
          type="text"
          class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          :class="{ 'border-rose-300 focus:border-rose-500 focus:ring-rose-500': form.errors.nickname }"
          placeholder="Ví dụ: Minh Anh"
        />
        <p v-if="form.errors.nickname" class="mt-1 text-sm text-rose-600">{{ form.errors.nickname }}</p>
      </div>

      <!-- Date of Birth -->
      <div>
        <label for="date_of_birth" class="block text-sm font-medium text-slate-700">Ngày sinh</label>
        <input
          id="date_of_birth"
          v-model="form.date_of_birth"
          type="date"
          class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          :class="{ 'border-rose-300 focus:border-rose-500 focus:ring-rose-500': form.errors.date_of_birth }"
        />
        <p v-if="form.errors.date_of_birth" class="mt-1 text-sm text-rose-600">{{ form.errors.date_of_birth }}</p>
      </div>

      <!-- Gender -->
      <div>
        <label for="gender" class="block text-sm font-medium text-slate-700">Giới tính</label>
        <select
          id="gender"
          v-model="form.gender"
          class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          :class="{ 'border-rose-300 focus:border-rose-500 focus:ring-rose-500': form.errors.gender }"
        >
          <option value="">Chọn giới tính</option>
          <option value="male">Nam</option>
          <option value="female">Nữ</option>
          <option value="non_binary">Khác</option>
        </select>
        <p v-if="form.errors.gender" class="mt-1 text-sm text-rose-600">{{ form.errors.gender }}</p>
      </div>

      <!-- Diagnosis Level -->
      <div class="md:col-span-2">
        <label for="diagnosis_level" class="block text-sm font-medium text-slate-700">Mức độ / trạng thái chẩn đoán</label>
        <input
          id="diagnosis_level"
          v-model="form.diagnosis_level"
          type="text"
          class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          :class="{ 'border-rose-300 focus:border-rose-500 focus:ring-rose-500': form.errors.diagnosis_level }"
          placeholder="Ví dụ: mức nhẹ, mức trung bình"
        />
        <p v-if="form.errors.diagnosis_level" class="mt-1 text-sm text-rose-600">{{ form.errors.diagnosis_level }}</p>
      </div>

      <!-- Notes -->
      <div class="md:col-span-2">
        <label for="notes" class="block text-sm font-medium text-slate-700">Ghi chú và mô tả chuyên môn</label>
        <textarea
          id="notes"
          v-model="form.notes"
          rows="4"
          class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          :class="{ 'border-rose-300 focus:border-rose-500 focus:ring-rose-500': form.errors.notes }"
          placeholder="Nhập ghi chú quan sát, hành vi hoặc mục tiêu can thiệp..."
        ></textarea>
        <p v-if="form.errors.notes" class="mt-1 text-sm text-rose-600">{{ form.errors.notes }}</p>
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
        {{ isEdit ? 'Cập nhật hồ sơ trẻ' : 'Thêm hồ sơ trẻ' }}
      </button>
    </div>
  </form>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
  child: {
    type: Object,
    default: null,
  },
});

const isEdit = computed(() => !!props.child);

const cancelUrl = computed(() => {
  return isEdit.value ? `/children/${props.child.id}` : '/children';
});

// Format date to YYYY-MM-DD for date input
const formatDateForInput = (dateStr) => {
  if (!dateStr) return '';
  const date = new Date(dateStr);
  if (isNaN(date.getTime())) return '';
  return date.toISOString().split('T')[0];
};

const form = useForm({
  full_name: props.child?.full_name ?? '',
  nickname: props.child?.nickname ?? '',
  date_of_birth: props.child?.date_of_birth ? formatDateForInput(props.child.date_of_birth) : '',
  gender: props.child?.gender ?? '',
  diagnosis_level: props.child?.diagnosis_level ?? '',
  notes: props.child?.notes ?? '',
});

const submit = () => {
  if (isEdit.value) {
    form.put(`/children/${props.child.id}`);
  } else {
    form.post('/children');
  }
};
</script>
