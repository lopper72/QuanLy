<template>
  <div>
    <h1>Chỉnh sửa can thiệp</h1>
    <InterventionForm :intervention-id="interventionId" />
  </div>
</template>

<script setup>
import { defineProps } from 'vue';
import InterventionForm from '@/Components/InterventionForm.vue';

const props = defineProps({
  interventionId: Number,
});
</script>
