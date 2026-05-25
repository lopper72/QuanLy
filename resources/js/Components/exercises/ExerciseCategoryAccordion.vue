<template>
  <div class="space-y-3">
    <section
      v-for="group in groups"
      :id="`category-${group.key}`"
      :key="group.key"
      class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm"
    >
      <button
        type="button"
        class="flex w-full items-start justify-between gap-4 p-5 text-left transition hover:bg-slate-50"
        :aria-expanded="expanded[group.key]"
        @click="toggle(group.key)"
      >
        <div class="flex min-w-0 gap-4">
          <div :class="['flex h-11 w-11 shrink-0 items-center justify-center rounded-lg border text-sm font-semibold', iconColor(group.key)]">
            {{ iconFor(group.key) }}
          </div>
          <div class="min-w-0 space-y-2">
            <div class="flex flex-wrap items-center gap-2">
              <h2 class="text-lg font-semibold text-slate-950">{{ group.label }}</h2>
              <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600">
                {{ group.count }} bài
              </span>
            </div>
            <p class="max-w-3xl text-sm leading-6 text-slate-600">{{ group.description }}</p>
            <div class="flex flex-wrap gap-2">
              <span
                v-for="benefit in group.benefits"
                :key="benefit"
                class="rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs text-slate-700"
              >
                {{ benefit }}
              </span>
            </div>
          </div>
        </div>
        <span
          class="mt-1 shrink-0 text-lg text-slate-400 transition-transform duration-200"
          :class="expanded[group.key] ? 'rotate-180' : ''"
          aria-hidden="true"
        >
          ˅
        </span>
      </button>

      <Transition
        enter-active-class="transition-all duration-300 ease-out"
        enter-from-class="max-h-0 opacity-0"
        enter-to-class="max-h-[3000px] opacity-100"
        leave-active-class="transition-all duration-200 ease-in"
        leave-from-class="max-h-[3000px] opacity-100"
        leave-to-class="max-h-0 opacity-0"
      >
        <div v-if="expanded[group.key]" class="border-t border-slate-100 bg-slate-50 p-4">
          <div v-if="group.exercises.length" class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
            <ExerciseCard
              v-for="exercise in group.exercises"
              :key="exercise.id"
              :exercise="exercise"
            />
          </div>
          <p v-else class="rounded-md bg-white p-4 text-sm text-slate-500">
            Chưa có bài tập trong nhóm này.
          </p>
        </div>
      </Transition>
    </section>
  </div>
</template>

<script setup>
import { reactive, watch } from 'vue';
import ExerciseCard from './ExerciseCard.vue';

const props = defineProps({
  groups: {
    type: Array,
    required: true,
  },
  expandFirst: {
    type: Boolean,
    default: true,
  },
});

const expanded = reactive({});

watch(
  () => props.groups,
  (groups) => {
    groups.forEach((group, index) => {
      if (expanded[group.key] === undefined) {
        expanded[group.key] = props.expandFirst && index === 0 && group.count > 0;
      }
    });
  },
  { immediate: true }
);

const toggle = (key) => {
  expanded[key] = !expanded[key];
};

const iconFor = (key) => ({
  gross_motor: 'VĐ',
  fine_motor: 'BT',
  communication: 'GT',
  cognitive: 'NT',
  sensory: 'GQ',
  social: 'XH',
  self_care: 'TL',
}[key] || '•');

const iconColor = (key) => ({
  gross_motor: 'bg-blue-50 text-blue-700 border-blue-200',
  fine_motor: 'bg-emerald-50 text-emerald-700 border-emerald-200',
  communication: 'bg-amber-50 text-amber-700 border-amber-200',
  cognitive: 'bg-pink-50 text-pink-700 border-pink-200',
  sensory: 'bg-violet-50 text-violet-700 border-violet-200',
  social: 'bg-sky-50 text-sky-700 border-sky-200',
  self_care: 'bg-teal-50 text-teal-700 border-teal-200',
}[key] || 'bg-slate-50 text-slate-700 border-slate-200');
</script>
