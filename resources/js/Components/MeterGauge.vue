<template>
  <div class="flex flex-col items-center justify-center gap-1">
    <div class="relative flex items-end justify-center w-12 h-6 overflow-hidden">
      <!-- Background track -->
      <svg viewBox="0 0 100 50" class="w-full h-full drop-shadow-sm">
        <path d="M 10 50 A 40 40 0 0 1 90 50" fill="none" stroke="var(--color-border)" stroke-width="12" stroke-linecap="round" />
        <!-- Foreground meter -->
        <path d="M 10 50 A 40 40 0 0 1 90 50" fill="none" :stroke="color" stroke-width="12" stroke-linecap="round"
              stroke-dasharray="125.6" :stroke-dashoffset="dashOffset" 
              class="transition-all duration-500 ease-out" />
      </svg>
      <!-- Value inside -->
      <span class="absolute bottom-0 text-[9px] font-bold" :style="{ color: color }">{{ valueText }}</span>
    </div>
    <span class="text-[9px] font-semibold text-[var(--color-text-muted)] uppercase tracking-wider">{{ label }}</span>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  label: { type: String, required: true },
  value: { type: Number, required: true },
  max: { type: Number, default: 100 },
  valueText: { type: String, required: true },
  color: { type: String, default: 'var(--color-primary)' }
});

const dashOffset = computed(() => {
  const percentage = Math.min(Math.max(props.value / props.max, 0), 1);
  return 125.6 - (125.6 * percentage);
});
</script>
