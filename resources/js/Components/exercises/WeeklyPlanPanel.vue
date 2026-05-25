<template>
  <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
    <div class="mb-4">
      <h2 class="text-lg font-semibold text-slate-950">Lịch tập mẫu 1 tuần</h2>
      <p class="mt-1 text-sm text-slate-600">Gợi ý lịch tập thực tế theo nhu cầu thường gặp. Có thể dùng làm khung tham khảo khi lập lịch cho từng bé.</p>
    </div>

    <div v-if="plans.length" class="space-y-4">
      <details v-for="plan in plans" :key="plan.id" class="rounded-lg border border-slate-200 bg-slate-50 p-4">
        <summary class="cursor-pointer text-base font-semibold text-slate-950">
          {{ plan.title }}
          <span class="ml-2 text-sm font-normal text-slate-500">{{ plan.recommended_age }}</span>
        </summary>
        <p class="mt-2 text-sm leading-6 text-slate-600">{{ plan.description }}</p>
        <div class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-3">
          <div v-for="item in plan.items" :key="item.id" class="rounded-md bg-white p-3 text-sm">
            <div class="flex items-center justify-between gap-2">
              <span class="font-medium text-slate-900">{{ dayLabel(item.day_of_week) }} · {{ timeLabel(item.session_time) }}</span>
              <span class="text-xs text-slate-500">{{ item.estimated_minutes || 0 }} phút</span>
            </div>
            <p class="mt-2 text-slate-700">{{ item.combo?.title || item.exercise?.title || 'Hoạt động đang cập nhật' }}</p>
            <p v-if="item.notes" class="mt-2 text-xs leading-5 text-slate-500">{{ item.notes }}</p>
          </div>
        </div>
      </details>
    </div>
    <p v-else class="rounded-md bg-slate-50 p-4 text-sm text-slate-500">Chưa có lịch tập mẫu.</p>
  </section>
</template>

<script setup>
defineProps({
  plans: {
    type: Array,
    default: () => [],
  },
});

const dayLabel = (day) => ({
  monday: 'Thứ 2',
  tuesday: 'Thứ 3',
  wednesday: 'Thứ 4',
  thursday: 'Thứ 5',
  friday: 'Thứ 6',
  saturday: 'Thứ 7',
  sunday: 'Chủ nhật',
}[day] || day);

const timeLabel = (time) => ({
  morning: 'Buổi sáng',
  afternoon: 'Buổi chiều',
  evening: 'Buổi tối',
}[time] || time);
</script>
