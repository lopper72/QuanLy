<template>
  <AppLayout>
    <div class="space-y-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Lịch ăn uống</h1>
        <p class="text-sm text-gray-600">Thực đơn mẫu hỗ trợ thói quen ăn uống, uống nước và theo dõi đi tiêu cho bé.</p>
      </div>

      <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
        <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
          {{ safetyNote }}
        </div>
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-900">
          {{ supportNote }}
        </div>
      </div>

      <section class="rounded-lg bg-white p-5 shadow">
        <h2 class="text-lg font-semibold text-gray-900">Ghi nhận hôm nay</h2>
        <form class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2" @submit.prevent="submitLog">
          <div>
            <label class="block text-sm font-medium text-gray-700">Bé</label>
            <select v-model="logForm.child_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
              <option value="">Chọn bé</option>
              <option v-for="child in children" :key="child.id" :value="child.id">{{ child.full_name }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Trạng thái bữa ăn</label>
            <select v-model="logForm.status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
              <option value="done">Đã ăn</option>
              <option value="planned">Đã lên lịch</option>
              <option value="skipped">Bỏ qua</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Ghi nhận đi tiêu</label>
            <input v-model="logForm.stool_note" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" placeholder="Ví dụ: phân mềm, không đau" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Ghi nhận nước uống</label>
            <input v-model="logForm.water_note" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" placeholder="Ví dụ: uống nước chia nhỏ trong ngày" />
          </div>
          <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700">Ghi chú</label>
            <textarea v-model="logForm.notes" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></textarea>
          </div>
          <div class="md:col-span-2">
            <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700" :disabled="logForm.processing">
              Ghi nhận hôm nay
            </button>
          </div>
        </form>
      </section>

      <section class="grid grid-cols-1 gap-5">
        <article v-for="template in templates" :key="template.id" class="rounded-lg bg-white p-5 shadow">
          <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
            <div>
              <p class="text-xs font-semibold uppercase text-indigo-600">Tuần {{ template.week_number }}</p>
              <h2 class="mt-1 text-xl font-semibold text-gray-900">{{ template.title }}</h2>
              <p class="mt-1 text-sm text-gray-600">{{ template.description }}</p>
              <p class="mt-2 text-sm font-medium text-emerald-700">{{ goalLabel(template.goal) }}</p>
            </div>
            <form class="flex flex-col gap-2 sm:flex-row" @submit.prevent="applyTemplate(template.id)">
              <select v-model="applyForms[template.id].child_id" class="rounded-md border-gray-300 text-sm shadow-sm">
                <option value="">Chọn bé</option>
                <option v-for="child in children" :key="child.id" :value="child.id">{{ child.full_name }}</option>
              </select>
              <button type="submit" class="rounded-md bg-emerald-600 px-3 py-2 text-sm font-medium text-white hover:bg-emerald-700">
                Áp dụng cho bé
              </button>
            </form>
          </div>

          <div class="mt-5 grid grid-cols-1 gap-3 lg:grid-cols-7">
            <div v-for="day in days" :key="day.value" class="rounded-lg border border-gray-200 bg-gray-50 p-3">
              <h3 class="text-sm font-semibold text-gray-900">{{ day.label }}</h3>
              <div class="mt-3 space-y-2">
                <div v-for="item in itemsForDay(template, day.value)" :key="item.id" class="rounded bg-white p-3 text-sm">
                  <p class="font-medium text-gray-900">{{ timeLabel(item) }} - {{ mealTimeLabel(item.meal_time) }}</p>
                  <p class="mt-1 text-gray-700">{{ item.title }}</p>
                  <p class="mt-1 text-xs text-gray-500">{{ foodText(item.foods_json) }}</p>
                  <p v-if="item.constipation_support_note" class="mt-2 text-xs text-emerald-700">{{ item.constipation_support_note }}</p>
                  <p v-if="item.parent_tip" class="mt-1 text-xs text-gray-500">{{ item.parent_tip }}</p>
                </div>
                <p v-if="itemsForDay(template, day.value).length === 0" class="text-xs text-gray-400">Chưa có món mẫu.</p>
              </div>
            </div>
          </div>
        </article>
      </section>

      <section class="rounded-lg bg-white p-5 shadow">
        <h2 class="text-lg font-semibold text-gray-900">Ghi nhận gần đây</h2>
        <div class="mt-4 space-y-2">
          <div v-for="log in recentLogs" :key="log.id" class="rounded-md border border-gray-200 p-3 text-sm">
            <p class="font-medium text-gray-900">{{ log.child?.full_name }} · {{ statusLabel(log.status) }}</p>
            <p class="text-gray-600">{{ log.stool_note || 'Chưa ghi nhận đi tiêu' }} · {{ log.water_note || 'Chưa ghi nhận nước uống' }}</p>
          </div>
          <p v-if="recentLogs.length === 0" class="text-sm text-gray-500">Chưa có ghi nhận bữa ăn.</p>
        </div>
      </section>
    </div>
  </AppLayout>
</template>

<script setup>
import { reactive } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Components/layout/AppLayout.vue';

const props = defineProps({
  children: { type: Array, default: () => [] },
  templates: { type: Array, default: () => [] },
  recentLogs: { type: Array, default: () => [] },
  safetyNote: { type: String, required: true },
  supportNote: { type: String, required: true },
});

const days = [
  { value: 1, label: 'Thứ 2' },
  { value: 2, label: 'Thứ 3' },
  { value: 3, label: 'Thứ 4' },
  { value: 4, label: 'Thứ 5' },
  { value: 5, label: 'Thứ 6' },
  { value: 6, label: 'Thứ 7' },
  { value: 7, label: 'Chủ nhật' },
];

const applyForms = reactive(Object.fromEntries(
  props.templates.map(template => [template.id, { child_id: '' }])
));

const logForm = useForm({
  child_id: '',
  status: 'done',
  stool_note: '',
  water_note: '',
  notes: '',
});

function applyTemplate(templateId) {
  router.post(route('mealPlans.apply'), {
    meal_plan_template_id: templateId,
    child_id: applyForms[templateId].child_id,
  }, { preserveScroll: true });
}

function submitLog() {
  logForm.post(route('mealPlans.logs.store'), {
    preserveScroll: true,
    onSuccess: () => logForm.reset('stool_note', 'water_note', 'notes'),
  });
}

function itemsForDay(template, day) {
  return (template.items || []).filter(item => item.day_of_week === day);
}

function foodText(foods) {
  if (Array.isArray(foods)) {
    return foods.join(', ');
  }

  return foods || '';
}

function mealTimeLabel(value) {
  return {
    breakfast: 'Bữa sáng',
    snack: 'Bữa phụ',
    lunch: 'Bữa trưa',
    dinner: 'Bữa tối',
    water: 'Nhắc uống nước',
    toilet: 'Thói quen đi vệ sinh',
  }[value] || value;
}

function timeLabel(item) {
  return item.scheduled_time ? String(item.scheduled_time).slice(0, 5) : 'Chưa có giờ';
}

function goalLabel(value) {
  return {
    constipation_support: 'Hỗ trợ táo bón',
    picky_eating: 'Hỗ trợ kén ăn',
    sensory_tolerance: 'Tăng chịu đựng cảm giác',
    energy_balance: 'Cân bằng năng lượng',
  }[value] || 'Mục tiêu ăn uống';
}

function statusLabel(value) {
  return {
    planned: 'Đã lên lịch',
    done: 'Đã ăn',
    skipped: 'Bỏ qua',
  }[value] || value;
}
</script>
