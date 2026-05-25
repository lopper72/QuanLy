<script setup>
import { ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import QuickBehaviorButton from './QuickBehaviorButton.vue';
import SeveritySelector from './SeveritySelector.vue';

const props = defineProps({
  children: {
    type: Array,
    required: true,
  },
  presets: {
    type: Array,
    required: true,
  },
  severities: {
    type: Array,
    required: true,
  },
  initialChildId: {
    type: [Number, String],
    default: null,
  },
});

const emit = defineEmits(['save-success']);

const form = useForm({
  child_id: props.initialChildId || '',
  behavior_type: '',
  severity: '',
  note: '',
  recorded_at: '',
});

// Keep child_id in sync if initial value changes or loads later
watch(
  () => props.initialChildId,
  (newId) => {
    if (newId && !form.child_id) {
      form.child_id = newId;
    }
  },
  { immediate: true }
);

const showSuccessAnimation = ref(false);

const handlePresetSelect = (key) => {
  form.behavior_type = key;
};

const submitBehavior = () => {
  if (!form.child_id) {
    alert('Vui lòng chọn trẻ.');
    return;
  }
  if (!form.behavior_type) {
    alert('Vui lòng chọn hành vi.');
    return;
  }

  form.post(route('behavior.quickStore'), {
    preserveScroll: true,
    onSuccess: () => {
      // Clear presets/severities for subsequent quick actions, keep child selected for continuity
      form.behavior_type = '';
      form.severity = '';
      form.note = '';
      form.recorded_at = '';
      
      // Trigger success flash
      showSuccessAnimation.value = true;
      setTimeout(() => {
        showSuccessAnimation.value = false;
      }, 2000);
      
      emit('save-success');
    },
  });
};
</script>

<template>
  <div class="bg-white rounded-3xl border border-gray-100 shadow-xl overflow-hidden relative transition-all duration-300">
    <!-- Success overlay flash -->
    <div
      v-if="showSuccessAnimation"
      class="absolute inset-0 bg-emerald-500 bg-opacity-95 z-50 flex flex-col items-center justify-center text-white transition-opacity duration-300"
    >
      <div class="p-4 bg-white/20 rounded-full mb-3 animate-bounce">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
          <polyline points="20 6 9 17 4 12" />
        </svg>
      </div>
      <h3 class="text-xl font-bold">Đã ghi nhận thành công!</h3>
      <p class="text-emerald-100 text-sm mt-1">Sẵn sàng cho ghi nhận tiếp theo.</p>
    </div>

    <form @submit.prevent="submitBehavior" class="p-5 md:p-6 space-y-6">
      <!-- 1. Child Selection (One-tap friendly row/select) -->
      <div>
        <label class="block text-xs font-bold text-gray-400 mb-2">
          Chọn trẻ
        </label>
        
        <!-- Button Row for fast tap (ideal when <= 4 children) -->
        <div class="flex flex-wrap gap-2 mb-2 max-h-[120px] overflow-y-auto pr-1">
          <button
            v-for="child in children"
            :key="child.id"
            type="button"
            @click="form.child_id = child.id"
            class="px-4 py-2.5 rounded-full border text-sm font-semibold transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500"
            :class="form.child_id === child.id
              ? 'bg-blue-600 border-blue-600 text-white shadow-sm'
              : 'bg-gray-50 border-gray-200 text-gray-700 hover:bg-gray-100'
            "
          >
            {{ child.full_name }}
          </button>
        </div>

        <div v-if="children.length === 0" class="text-sm text-gray-500">
          Chưa có trẻ đang can thiệp. Vui lòng kích hoạt hồ sơ trẻ trước khi tạo hoạt động mới.
        </div>
        
        <span v-if="form.errors.child_id" class="text-xs text-red-600 block mt-1">
          {{ form.errors.child_id }}
        </span>
      </div>

      <!-- 2. Behavior Presets -->
      <div>
        <label class="block text-xs font-bold text-gray-400 mb-3">
          1. Chọn hành vi
        </label>
        
        <div class="grid grid-cols-2 xs:grid-cols-3 sm:grid-cols-4 gap-3">
          <QuickBehaviorButton
            v-for="preset in presets"
            :key="preset.key"
            :preset="preset"
            :is-selected="form.behavior_type === preset.key"
            @select="handlePresetSelect"
          />
        </div>
        
        <span v-if="form.errors.behavior_type" class="text-xs text-red-600 block mt-2">
          {{ form.errors.behavior_type }}
        </span>
      </div>

      <!-- 3. Severity Level -->
      <div>
        <label class="block text-xs font-bold text-gray-400 mb-3">
          2. Chọn mức độ
        </label>
        
        <SeveritySelector
          v-model="form.severity"
          :severities="severities"
        />

        <span v-if="form.errors.severity" class="text-xs text-red-600 block mt-2">
          {{ form.errors.severity }}
        </span>
      </div>

      <!-- 4. Quick Note & Timestamp Section (Collapsible/Mobile Friendly) -->
      <div class="border-t border-gray-100 pt-5 space-y-4">
        <div>
          <label for="quick-note" class="block text-xs font-bold text-gray-400 mb-2">
            3. Ghi chú nhanh tùy chọn
          </label>
          <input
            id="quick-note"
            v-model="form.note"
            type="text"
            placeholder="Ví dụ: Xảy ra khi chuyển hoạt động, bé ném bút chì"
            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm transition-all shadow-sm"
          />
          <span v-if="form.errors.note" class="text-xs text-red-600 block mt-1">
            {{ form.errors.note }}
          </span>
        </div>

        <div class="flex items-center justify-between text-xs text-gray-400 px-1">
          <span class="flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10" />
              <polyline points="12 6 12 12 16 14" />
            </svg>
            Thời gian tự động: <strong class="ml-1 text-gray-500">Hiện tại</strong>
          </span>
          
          <button 
            type="button" 
            @click="form.note = ''" 
            v-if="form.note" 
            class="text-red-500 hover:text-red-700 font-semibold"
          >
            Xóa ghi chú
          </button>
        </div>
      </div>

      <!-- 5. Submit Button (Sticky/Large Target) -->
      <button
        type="submit"
        :disabled="form.processing || !form.child_id || !form.behavior_type"
        class="w-full py-4 px-6 rounded-2xl font-bold text-white shadow-lg transition-all duration-200 text-center flex items-center justify-center space-x-2 text-base touch-manipulation"
        :class="(!form.child_id || !form.behavior_type)
          ? 'bg-gray-300 cursor-not-allowed shadow-none'
          : 'bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 active:scale-[0.98]'
        "
      >
        <span v-if="form.processing">Đang ghi nhận...</span>
        <span v-else class="flex items-center">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z" />
            <polyline points="17 21 17 13 7 13 7 21" />
            <polyline points="7 3 7 8 15 8" />
          </svg>
          Ghi nhận ngay
        </span>
      </button>
    </form>
  </div>
</template>
