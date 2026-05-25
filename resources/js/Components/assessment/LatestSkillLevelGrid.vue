<script setup>
import SkillProgressCard from './SkillProgressCard.vue';

defineProps({
    latestSkillLevels: Array,
    skillTypes: Object,
});
</script>

<template>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
        <SkillProgressCard
            v-for="skill in latestSkillLevels"
            :key="skill.skill_name"
            :skill="skill"
            :skill-label="skillTypes[skill.skill_name]"
        />
    </div>
</template>