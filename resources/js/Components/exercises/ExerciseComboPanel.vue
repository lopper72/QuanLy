<template>
  <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
    <div class="mb-4">
      <h2 class="text-lg font-semibold text-slate-950">Combo bài tập gợi ý</h2>
      <p class="mt-1 text-sm text-slate-600">Mỗi combo ghép nhiều bài nhỏ theo một mục tiêu can thiệp để phụ huynh dễ tập tại nhà.</p>
    </div>

    <div v-if="combos.length" class="grid grid-cols-1 gap-4 lg:grid-cols-2">
      <article v-for="combo in combos" :key="combo.id" class="rounded-lg border border-slate-200 bg-slate-50 p-4">
        <div class="flex items-start justify-between gap-3">
          <div>
            <h3 class="font-semibold text-slate-950">{{ combo.title }}</h3>
            <p class="mt-1 text-sm leading-6 text-slate-600">{{ combo.description }}</p>
          </div>
          <span class="shrink-0 rounded-full bg-white px-2.5 py-1 text-xs font-medium text-slate-700">
            {{ combo.estimated_minutes || 0 }} phút
          </span>
        </div>
        <div class="mt-3 flex flex-wrap gap-2 text-xs">
          <span class="rounded-full border border-indigo-100 bg-indigo-50 px-2.5 py-1 text-indigo-700">
            {{ combo.target_skill }}
          </span>
          <span class="rounded-full border border-slate-200 bg-white px-2.5 py-1 text-slate-700">
            {{ combo.recommended_frequency || 'Tập đều trong tuần' }}
          </span>
        </div>
        <ol class="mt-3 space-y-2 text-sm text-slate-700">
          <li v-for="exercise in combo.exercises" :key="exercise.id" class="flex gap-2">
            <span class="text-slate-400">{{ exercise.pivot?.sort_order || '' }}</span>
            <span>{{ exercise.title }}</span>
          </li>
        </ol>
        <p v-if="combo.parent_instructions" class="mt-3 rounded-md bg-white p-3 text-sm leading-6 text-slate-600">
          {{ combo.parent_instructions }}
        </p>
      </article>
    </div>
    <p v-else class="rounded-md bg-slate-50 p-4 text-sm text-slate-500">Chưa có combo bài tập.</p>
  </section>
</template>

<script setup>
defineProps({
  combos: {
    type: Array,
    default: () => [],
  },
});
</script>
