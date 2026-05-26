<template>
  <div class="max-w-3xl">
    <div
      v-if="children.length === 0"
      class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm font-medium text-amber-800"
    >
      Chưa có trẻ đang can thiệp để ghi nhận hành vi.
    </div>

    <form
      v-else
      class="space-y-6 rounded-lg border border-slate-100 bg-white p-6 shadow-sm"
      @submit.prevent="submit"
    >
      <section class="space-y-3">
        <div>
          <h2 class="text-base font-semibold text-slate-900">Chọn nhanh loại hành vi</h2>
          <p class="mt-1 text-sm text-slate-500">Bấm một nhãn để điền nhanh trường loại hành vi.</p>
        </div>
        <div class="flex flex-wrap gap-2">
          <button
            v-for="preset in quickPresets"
            :key="preset.value"
            type="button"
            class="rounded-full border px-3 py-1.5 text-sm font-medium transition"
            :class="form.behavior_type === preset.value
              ? 'border-indigo-600 bg-indigo-50 text-indigo-700'
              : 'border-slate-200 bg-white text-slate-700 hover:border-indigo-300 hover:text-indigo-700'"
            @click="form.behavior_type = preset.value"
          >
            {{ preset.label }}
          </button>
        </div>
      </section>

      <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
        <div>
          <label for="child_id" class="block text-sm font-medium text-slate-700">
            Trẻ <span class="text-rose-500">*</span>
          </label>
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

        <div>
          <label for="behavior_type" class="block text-sm font-medium text-slate-700">
            Loại hành vi <span class="text-rose-500">*</span>
          </label>
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

        <div>
          <label for="recorded_at" class="block text-sm font-medium text-slate-700">
            Thời điểm xảy ra <span class="text-rose-500">*</span>
          </label>
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

        <div class="md:col-span-2">
          <label for="trigger" class="block text-sm font-medium text-slate-700">Nguyên nhân/kích hoạt</label>
          <textarea
            id="trigger"
            v-model="form.trigger"
            rows="3"
            class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            :class="{ 'border-rose-300 focus:border-rose-500 focus:ring-rose-500': form.errors.trigger }"
            placeholder="Ví dụ: bé phải dừng đồ chơi yêu thích, chuyển hoạt động đột ngột, môi trường quá ồn."
          ></textarea>
          <p v-if="form.errors.trigger" class="mt-1 text-sm text-rose-600">{{ form.errors.trigger }}</p>
        </div>

        <div class="md:col-span-2">
          <label for="response" class="block text-sm font-medium text-slate-700">Cách xử lý</label>
          <textarea
            id="response"
            v-model="form.response"
            rows="3"
            class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            :class="{ 'border-rose-300 focus:border-rose-500 focus:ring-rose-500': form.errors.response }"
            placeholder="Ví dụ: cho bé nghỉ ngắn, nhắc lại chỉ dẫn đơn giản, dùng lịch trực quan."
          ></textarea>
          <p v-if="form.errors.response" class="mt-1 text-sm text-rose-600">{{ form.errors.response }}</p>
        </div>

        <div class="md:col-span-2">
          <label for="note" class="block text-sm font-medium text-slate-700">Ghi chú</label>
          <textarea
            id="note"
            v-model="form.note"
            rows="3"
            class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            :class="{ 'border-rose-300 focus:border-rose-500 focus:ring-rose-500': form.errors.note }"
            placeholder="Ghi thêm thời lượng, phản ứng của bé hoặc điều cần theo dõi lần sau."
          ></textarea>
          <p v-if="form.errors.note" class="mt-1 text-sm text-rose-600">{{ form.errors.note }}</p>
        </div>
      </div>

      <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-4 sm:flex-row sm:items-center sm:justify-end">
        <Link
          :href="cancelUrl"
          class="inline-flex justify-center rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
        >
          Hủy
        </Link>
        <button
          type="submit"
          :disabled="form.processing"
          class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50"
        >
          {{ isEdit ? 'Cập nhật ghi nhận' : 'Ghi nhận hành vi' }}
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';

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

const emit = defineEmits(['submit']);

const quickPresets = [
  { value: 'tantrum', label: 'Ăn vạ' },
  { value: 'avoidance', label: 'Né tránh' },
  { value: 'sensory_seeking', label: 'Tìm kiếm cảm giác' },
  { value: 'difficulty_transitioning', label: 'Khó chuyển hoạt động' },
  { value: 'picky_eating', label: 'Kén ăn' },
];

const isEdit = computed(() => !!props.behaviorLog);

const formatDateForInput = (dateString) => {
  if (!dateString) {
    const now = new Date();
    const timezoneOffset = now.getTimezoneOffset() * 60000;
    return new Date(Date.now() - timezoneOffset).toISOString().slice(0, 16);
  }

  const date = new Date(dateString);
  const pad = (value) => String(value).padStart(2, '0');

  return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`;
};

const form = useForm({
  child_id: props.behaviorLog?.child_id ?? '',
  behavior_type: props.behaviorLog?.behavior_type ?? '',
  severity: props.behaviorLog?.severity ?? '',
  recorded_at: formatDateForInput(props.behaviorLog?.recorded_at),
  trigger: props.behaviorLog?.trigger ?? '',
  response: props.behaviorLog?.response ?? '',
  note: props.behaviorLog?.note ?? '',
});

const cancelUrl = computed(() => {
  return isEdit.value ? route('behavior.show', props.behaviorLog.id) : route('behavior.index');
});

const submit = () => {
  if (isEdit.value) {
    form.put(route('behavior.update', props.behaviorLog.id), {
      onSuccess: () => emit('submit', form),
    });

    return;
  }

  form.post(route('behavior.store'), {
    onSuccess: () => emit('submit', form),
  });
};
</script>
