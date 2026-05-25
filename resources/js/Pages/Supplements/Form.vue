<template>
  <AppLayout>
    <div class="mx-auto max-w-3xl space-y-6">
      <div>
        <Link :href="route('supplements.index')" class="text-sm font-medium text-indigo-600 hover:underline">← Quay lại lịch bổ sung</Link>
        <h1 class="mt-2 text-2xl font-bold text-gray-900">{{ isEdit ? 'Chỉnh sửa lịch bổ sung' : 'Thêm lịch bổ sung' }}</h1>
      </div>

      <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
        {{ safetyNote }}
      </div>

      <form class="space-y-5 rounded-lg bg-white p-6 shadow" @submit.prevent="submit">
        <div>
          <label class="block text-sm font-medium text-gray-700">Bé</label>
          <select v-model="form.child_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            <option value="">Chọn bé</option>
            <option v-for="child in children" :key="child.id" :value="child.id">{{ child.full_name }}</option>
          </select>
          <p v-if="form.errors.child_id" class="mt-1 text-xs text-red-600">{{ form.errors.child_id }}</p>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
          <div>
            <label class="block text-sm font-medium text-gray-700">Tên thuốc hoặc bổ sung</label>
            <input v-model="form.name" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" placeholder="Ví dụ: DHA" />
            <p v-if="form.errors.name" class="mt-1 text-xs text-red-600">{{ form.errors.name }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Loại</label>
            <input v-model="form.type" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" placeholder="Thuốc, bổ sung, vitamin..." />
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Ghi chú liều dùng</label>
          <textarea v-model="form.dosage_note" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" placeholder="Nhập đúng theo bác sĩ hoặc nhãn sản phẩm"></textarea>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
          <div>
            <label class="block text-sm font-medium text-gray-700">Kiểu thời điểm</label>
            <select v-model="form.timing_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
              <option value="fixed_time">Giờ cố định</option>
              <option value="before_meal">Trước bữa ăn</option>
              <option value="after_meal">Sau bữa ăn</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Giờ uống</label>
            <input v-model="form.scheduled_time" type="time" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Liên quan bữa ăn</label>
            <select v-model="form.meal_relation" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
              <option value="">Không chọn</option>
              <option value="before_meal">Trước bữa ăn</option>
              <option value="before_breakfast">Trước bữa sáng</option>
              <option value="before_lunch">Trước bữa trưa</option>
              <option value="before_dinner">Trước bữa tối</option>
              <option value="after_breakfast">Sau bữa sáng</option>
              <option value="after_lunch">Sau bữa trưa</option>
              <option value="after_dinner">Sau bữa tối</option>
              <option value="bedtime">Trước khi ngủ</option>
            </select>
          </div>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
          <div>
            <label class="block text-sm font-medium text-gray-700">Tần suất</label>
            <input v-model="form.frequency" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" placeholder="hằng ngày" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Ngày bắt đầu</label>
            <input v-model="form.start_date" type="date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Trạng thái</label>
            <select v-model="form.status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
              <option value="active">Đang dùng</option>
              <option value="paused">Tạm dừng</option>
              <option value="completed">Đã hoàn tất</option>
            </select>
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Ghi chú</label>
          <textarea v-model="form.notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></textarea>
        </div>

        <div class="flex justify-end gap-3">
          <Link :href="route('supplements.index')" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700">Hủy</Link>
          <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700" :disabled="form.processing">
            {{ isEdit ? 'Lưu lịch bổ sung' : 'Tạo lịch bổ sung' }}
          </button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Components/layout/AppLayout.vue';

const props = defineProps({
  children: { type: Array, default: () => [] },
  schedule: { type: Object, default: null },
  safetyNote: { type: String, required: true },
});

const isEdit = computed(() => !!props.schedule);

const form = useForm({
  child_id: props.schedule?.child_id ?? '',
  name: props.schedule?.name ?? '',
  type: props.schedule?.type ?? '',
  dosage_note: props.schedule?.dosage_note ?? '',
  timing_type: props.schedule?.timing_type ?? 'fixed_time',
  scheduled_time: props.schedule?.scheduled_time ? String(props.schedule.scheduled_time).slice(0, 5) : '',
  meal_relation: props.schedule?.meal_relation ?? '',
  frequency: props.schedule?.frequency ?? 'hằng ngày',
  start_date: props.schedule?.start_date ? String(props.schedule.start_date).slice(0, 10) : '',
  end_date: props.schedule?.end_date ? String(props.schedule.end_date).slice(0, 10) : '',
  status: props.schedule?.status ?? 'active',
  notes: props.schedule?.notes ?? '',
});

function submit() {
  if (isEdit.value) {
    form.put(route('supplements.update', props.schedule.id));
    return;
  }

  form.post(route('supplements.store'));
}
</script>
