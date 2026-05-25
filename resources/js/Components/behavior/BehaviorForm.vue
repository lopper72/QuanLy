<template>
  <form @submit.prevent="submit" class="space-y-6 max-w-2xl bg-white p-6 rounded-lg shadow-sm border border-slate-100">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <!-- Child selection -->
      <div>
        <label for="child_id" class="block text-sm font-medium text-slate-700">Trẻ <span class="text-rose-500">*</span></label>
        <select
          id="child_id"
          v-model="form.child_id"
          class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          :class="{ 'border-rose-300 focus:border-rose-500 focus:ring-rose-500': form.errors.child_id }"
          required
        >
          <option value="">Chọn trẻ</option>
          <option v-for="child in children" :key="child.id" :value="child.id">
            {{ child.full_name }}
          </option>
        </select>
        <p v-if="form.errors.child_id" class="mt-1 text-sm text-rose-600">{{ form.errors.child_id }}</p>
      </div>

      <!-- Behavior Type -->
      <div>
        <label for="behavior_type" class="block text-sm font-medium text-slate-700">Loại hành vi <span class="text-rose-500">*</span></label>
        <select
          id="behavior_type"
          v-model="form.behavior_type"
          class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          :class="{ 'border-rose-300 focus:border-rose-500 focus:ring-rose-500': form.errors.behavior_type }"
          required
        >
          <option value="">Chọn loại hành vi</option>
          <option v-for="(label, key) in behaviorTypes" :key="key" :value="key">
            {{ label }}
          </option>
        </select>
        <p v-if="form.errors.behavior_type" class="mt-1 text-sm text-rose-600">{{ form.errors.behavior_type }}</p>
      </div>

      <!-- Severity -->
      <div>
        <label for="severity" class="block text-sm font-medium text-slate-700">Mức độ</label>
        <select
          id="severity"
          v-model="form.severity"
          class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          :class="{ 'border-rose-300 focus:border-rose-500 focus:ring-rose-500': form.errors.severity }"
        >
          <option value="">Chọn mức độ</option>
          <option v-for="(label, key) in severities" :key="key" :value="key">
            {{ label }}
          </option>
        </select>
        <p v-if="form.errors.severity" class="mt-1 text-sm text-rose-600">{{ form.errors.severity }}</p>
      </div>

      <!-- Recorded At (Date & Time) -->
      <div>
        <label for="recorded_at" class="block text-sm font-medium text-slate-700">Ngày giờ ghi nhận <span class="text-rose-500">*</span></label>
        <input
          id="recorded_at"
          v-model="form.recorded_at"
          type="datetime-local"
          class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          :class="{ 'border-rose-300 focus:border-rose-500 focus:ring-rose-500': form.errors.recorded_at }"
          required
        />
        <p v-if="form.errors.recorded_at" class="mt-1 text-sm text-rose-600">{{ form.errors.recorded_at }}</p>
      </div>

      <!-- Trigger -->
      <div class="md:col-span-2">
        <label for="trigger" class="block text-sm font-medium text-slate-700">Yếu tố kích hoạt</label>
        <textarea
          id="trigger"
          v-model="form.trigger"
          rows="3"
          class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          :class="{ 'border-rose-300 focus:border-rose-500 focus:ring-rose-500': form.errors.trigger }"
          placeholder="Điều gì xảy ra ngay trước hành vi? Ví dụ: yêu cầu tắt iPad, chuyển sang bữa tối"
        ></textarea>
        <p v-if="form.errors.trigger" class="mt-1 text-sm text-rose-600">{{ form.errors.trigger }}</p>
      </div>

      <!-- Response -->
      <div class="md:col-span-2">
        <label for="response" class="block text-sm font-medium text-slate-700">Cách hỗ trợ / Can thiệp</label>
        <textarea
          id="response"
          v-model="form.response"
          rows="3"
          class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          :class="{ 'border-rose-300 focus:border-rose-500 focus:ring-rose-500': form.errors.response }"
          placeholder="Bạn hoặc chuyên viên đã hỗ trợ thế nào? Ví dụ: hướng dẫn bé đến góc yên tĩnh, dùng lịch trực quan"
        ></textarea>
        <p v-if="form.errors.response" class="mt-1 text-sm text-rose-600">{{ form.errors.response }}</p>
      </div>

      <!-- Notes -->
      <div class="md:col-span-2">
        <label for="note" class="block text-sm font-medium text-slate-700">Ghi chú / Quan sát thêm</label>
        <textarea
          id="note"
          v-model="form.note"
          rows="3"
          class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          :class="{ 'border-rose-300 focus:border-rose-500 focus:ring-rose-500': form.errors.note }"
          placeholder="Chi tiết khác hoặc thời lượng, ví dụ: kéo dài 15 phút, ổn định sau khi hít thở sâu"
        ></textarea>
        <p v-if="form.errors.note" class="mt-1 text-sm text-rose-600">{{ form.errors.note }}</p>
      </div>
    </div>

    <!-- Actions -->
    <div class="flex items-center justify-end space-x-3 pt-4 border-t border-slate-100">
      <Link
        :href="cancelUrl"
        class="inline-flex items-center px-4 py-2 border border-slate-300 text-sm font-medium rounded-md shadow-sm text-slate-700 bg-white hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
      >
        Hủy
      </Link>
      <button
        type="submit"
        :disabled="form.processing"
        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-750 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
      >
        {{ isEdit ? 'Cập nhật ghi nhận' : 'Ghi nhận hành vi' }}
      </button>
    </div>
  </form>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
  behaviorLog: {
    type: Object,
    default: null,
  },
  children: {
    type: Array,
    required: true,
  },
  behaviorTypes: {
    type: Object,
    required: true,
  },
  severities: {
    type: Object,
    required: true,
  },
});

const isEdit = computed(() => !!props.behaviorLog);

// Date formatting for datetime-local
const formatDateForInput = (dateString) => {
  if (!dateString) {
    const now = new Date();
    const tzOffset = now.getTimezoneOffset() * 60000; // offset in milliseconds
    const localISOTime = (new Date(Date.now() - tzOffset)).toISOString().slice(0, 16);
    return localISOTime;
  }
  const date = new Date(dateString);
  const pad = (num) => String(num).padStart(2, '0');
  const year = date.getFullYear();
  const month = pad(date.getMonth() + 1);
  const day = pad(date.getDate());
  const hours = pad(date.getHours());
  const minutes = pad(date.getMinutes());
  return `${year}-${month}-${day}T${hours}:${minutes}`;
};

const form = useForm({
  child_id: props.behaviorLog?.child_id ?? '',
  behavior_type: props.behaviorLog?.behavior_type ?? '',
  severity: props.behaviorLog?.severity ?? '',
  trigger: props.behaviorLog?.trigger ?? '',
  response: props.behaviorLog?.response ?? '',
  note: props.behaviorLog?.note ?? '',
  recorded_at: formatDateForInput(props.behaviorLog?.recorded_at),
});

const cancelUrl = computed(() => {
  return isEdit.value
    ? route('behavior.show', props.behaviorLog.id)
    : route('behavior.index');
});

const emit = defineEmits(['submit']);

const submit = () => {
  if (isEdit.value) {
    form.put(route('behavior.update', props.behaviorLog.id), {
      onSuccess: () => emit('submit', form),
    });
  } else {
    form.post(route('behavior.store'), {
      onSuccess: () => emit('submit', form),
    });
  }
};
</script>
