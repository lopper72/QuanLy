<template>
  <form @submit.prevent="submit" class="space-y-6">
    <div class="bg-white px-4 py-5 shadow sm:rounded-lg sm:p-6">
      <div class="md:grid md:grid-cols-3 md:gap-6">
        <div class="md:col-span-1">
          <h3 class="text-lg font-semibold leading-6 text-gray-900">Thông tin báo cáo</h3>
          <p class="mt-1 text-sm text-gray-500">
            Chọn trẻ, khoảng thời gian và ngày tham chiếu cho báo cáo. Hệ thống sẽ tổng hợp buổi tập, ghi nhận hành vi và điểm đánh giá liên quan.
          </p>
        </div>
        <div class="mt-5 md:col-span-2 md:mt-0 space-y-6">
          <div v-if="!isEdit && children.length === 0" class="rounded-md border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
            Chưa có trẻ đang can thiệp. Vui lòng kích hoạt hồ sơ trẻ trước khi tạo hoạt động mới.
          </div>

          <div class="grid grid-cols-6 gap-6">
            <div class="col-span-6 sm:col-span-3">
              <label for="child_id" class="block text-sm font-medium text-gray-700">Trẻ</label>
              <select
                id="child_id"
                v-model="form.child_id"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                :disabled="isEdit"
              >
                <option value="" disabled>Chọn trẻ...</option>
                <option v-for="child in children" :key="child.id" :value="child.id">
                  {{ child.first_name }} {{ child.last_name }}
                </option>
              </select>
              <p v-if="errors.child_id" class="mt-1 text-xs text-red-600">{{ errors.child_id }}</p>
            </div>

            <div class="col-span-6 sm:col-span-3">
              <label for="report_type" class="block text-sm font-medium text-gray-700">Loại báo cáo</label>
              <select
                id="report_type"
                v-model="form.report_type"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              >
                <option value="" disabled>Chọn loại báo cáo...</option>
                <option v-for="(label, val) in reportTypes" :key="val" :value="val">
                  {{ label }}
                </option>
              </select>
              <p v-if="errors.report_type" class="mt-1 text-xs text-red-600">{{ errors.report_type }}</p>
            </div>

            <div class="col-span-6 sm:col-span-3">
              <label for="report_date" class="block text-sm font-medium text-gray-700">Ngày báo cáo</label>
              <input
                type="date"
                id="report_date"
                v-model="form.report_date"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              />
              <p v-if="errors.report_date" class="mt-1 text-xs text-red-600">{{ errors.report_date }}</p>
            </div>
          </div>

          <div>
            <label for="summary" class="block text-sm font-medium text-gray-700">Tóm tắt / Ghi chú</label>
            <div class="mt-1">
              <textarea
                id="summary"
                rows="4"
                v-model="form.summary"
                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                placeholder="Nhập quan sát chính, ghi chú hướng dẫn phụ huynh hoặc khuyến nghị..."
              ></textarea>
            </div>
            <p v-if="errors.summary" class="mt-1 text-xs text-red-600">{{ errors.summary }}</p>
          </div>
        </div>
      </div>
    </div>

    <div class="flex justify-end space-x-3">
      <Link
        :href="route('reports.index')"
        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
      >
        Hủy
      </Link>
      <button
        type="submit"
        :disabled="form.processing || (!isEdit && children.length === 0)"
        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-750 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
      >
        {{ isEdit ? 'Cập nhật báo cáo' : 'Tạo báo cáo' }}
      </button>
    </div>
  </form>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
  report: {
    type: Object,
    default: null,
  },
  children: {
    type: Array,
    required: true,
  },
  reportTypes: {
    type: Object,
    required: true,
  },
  errors: {
    type: Object,
    default: () => ({}),
  },
});

const isEdit = !!props.report;

const form = useForm({
  child_id: props.report?.child_id || '',
  report_type: props.report?.report_type || 'weekly',
  report_date: props.report?.report_date || new Date().toISOString().substring(0, 10),
  summary: props.report?.summary || '',
});

const submit = () => {
  if (isEdit) {
    form.put(route('reports.update', props.report.id));
  } else {
    form.post(route('reports.store'));
  }
};
</script>
