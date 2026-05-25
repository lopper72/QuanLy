<template>
  <section class="rounded-lg bg-white p-4 ring-1 ring-gray-200">
    <h2 class="text-base font-semibold text-gray-900">Kiểm thử chức năng 2 chiều</h2>
    <p class="mt-1 text-sm text-gray-600">Gửi lịch tập hôm nay và giả lập phản hồi từ nút Telegram.</p>

    <a
      href="/telegram/webhook-info"
      target="_blank"
      class="mt-4 inline-flex w-full justify-center rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50"
    >
      Kiểm tra webhook
    </a>

    <form class="mt-4 space-y-3" @submit.prevent="sendTodayTraining">
      <div>
        <label class="block text-sm font-medium text-gray-700" for="training-child">Trẻ nhận lịch</label>
        <select
          id="training-child"
          v-model="sendForm.child_id"
          class="mt-1 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
          required
        >
          <option value="">Chọn trẻ</option>
          <option v-for="child in children" :key="child.id" :value="child.id">
            {{ child.full_name }}
          </option>
        </select>
      </div>
      <button
        type="submit"
        class="inline-flex w-full justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-60"
        :disabled="sendForm.processing"
      >
        Gửi lịch tập hôm nay
      </button>
    </form>

    <form class="mt-5 space-y-3 border-t border-gray-200 pt-4" @submit.prevent>
      <div>
        <label class="block text-sm font-medium text-gray-700" for="training-session">Buổi tập giả lập</label>
        <select
          id="training-session"
          v-model="callbackForm.training_session_id"
          class="mt-1 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
        >
          <option value="">Chọn buổi tập</option>
          <option v-for="session in sessions" :key="session.id" :value="session.id">
            {{ session.child_name }} - {{ statusLabel(session.status) }}
          </option>
        </select>
      </div>

      <div class="grid grid-cols-1 gap-2">
        <button v-for="action in actions" :key="action.value" type="button" class="rounded-md border border-gray-300 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50" @click="simulate(action.value)">
          {{ action.label }}
        </button>
      </div>
    </form>

    <dl class="mt-5 space-y-3 border-t border-gray-200 pt-4 text-sm">
      <div>
        <dt class="text-gray-500">Nội dung đã gửi</dt>
        <dd class="mt-1 line-clamp-4 font-medium text-gray-900">{{ lastOutbound?.message_text || 'Chưa có dữ liệu' }}</dd>
      </div>
      <div>
        <dt class="text-gray-500">Phản hồi nhận về</dt>
        <dd class="mt-1 font-medium text-gray-900">{{ lastInbound?.message_text || 'Chưa có dữ liệu' }}</dd>
      </div>
      <div>
        <dt class="text-gray-500">Dữ liệu phản hồi</dt>
        <dd class="mt-1 break-all font-medium text-gray-900">{{ lastInbound?.callback_data || 'Chưa có dữ liệu' }}</dd>
      </div>
      <div class="grid grid-cols-2 gap-3">
        <div>
          <dt class="text-gray-500">Trạng thái trước</dt>
          <dd class="font-medium text-gray-900">{{ statusLabel(lastInbound?.payload_json?.status_before) }}</dd>
        </div>
        <div>
          <dt class="text-gray-500">Trạng thái sau</dt>
          <dd class="font-medium text-gray-900">{{ statusLabel(lastInbound?.payload_json?.status_after) }}</dd>
        </div>
      </div>
      <div>
        <dt class="text-gray-500">Log webhook gần nhất</dt>
        <dd class="mt-1 font-medium text-gray-900">{{ lastWebhook?.message_text || 'Chưa có dữ liệu' }}</dd>
      </div>
    </dl>
  </section>
</template>

<script setup>
import { computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { labelFor, statusLabels } from '@/Lib/labels';

const props = defineProps({
  trainingTest: {
    type: Object,
    required: true,
  },
  messages: {
    type: Array,
    default: () => [],
  },
});

const children = computed(() => props.trainingTest.children || []);
const sessions = computed(() => props.trainingTest.sessions || []);
const lastWebhook = computed(() => props.trainingTest.last_webhook);
const lastOutbound = computed(() => [...props.messages].reverse().find((message) => message.message_type === 'training_schedule'));
const lastInbound = computed(() => [...props.messages].reverse().find((message) => message.message_type === 'training_callback'));

const sendForm = useForm({
  child_id: '',
});

const callbackForm = useForm({
  training_session_id: '',
  action: '',
});

const actions = [
  { value: 'completed', label: 'Giả lập phản hồi: Hoàn thành' },
  { value: 'not_completed', label: 'Giả lập phản hồi: Chưa hoàn thành' },
  { value: 'skipped', label: 'Giả lập phản hồi: Bỏ qua' },
  { value: 'need_help', label: 'Giả lập phản hồi: Cần hỗ trợ' },
];

function statusLabel(status) {
  return labelFor(statusLabels, status, 'Chưa có dữ liệu');
}

function sendTodayTraining() {
  sendForm.post('/telegram/training/send-today', {
    preserveScroll: true,
  });
}

function simulate(action) {
  callbackForm.action = action;
  callbackForm.post('/telegram/training/simulate-callback', {
    preserveScroll: true,
  });
}
</script>
