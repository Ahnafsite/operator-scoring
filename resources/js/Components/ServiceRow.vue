<template>
  <div class="flex items-center gap-3 py-1.5">
    <!-- Status dot + label -->
    <div class="flex items-center gap-2 min-w-[80px]">
      <span class="w-2 h-2 rounded-full shrink-0"
            :class="statusDotClass"></span>
      <span class="text-xs font-medium text-[var(--color-text-secondary)]">{{ label }}</span>
    </div>

    <!-- Metrics -->
    <div class="flex-1 flex items-center gap-4 justify-end pr-2">
      <template v-if="status === 'online'">
        <MeterGauge 
          label="CPU" 
          :value="cpu" 
          :max="100" 
          :valueText="cpu.toFixed(1) + '%'" 
          color="var(--color-primary)" 
        />
        <MeterGauge 
          label="RAM" 
          :value="memory / 1048576" 
          :max="1024" 
          :valueText="formatMemoryShort(memory)" 
          color="var(--color-primary-light)" 
        />
      </template>
      <span v-else class="text-[10px] text-[var(--color-text-muted)] italic mr-4">
        {{ statusLabel }}
      </span>
    </div>

    <!-- Toggle button -->
    <button
      @click="handleToggle"
      :disabled="loading"
      class="p-1 rounded transition-colors cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed"
      :class="status === 'online'
        ? 'text-[var(--color-danger-text)] hover:bg-[var(--color-danger-bg)]'
        : 'text-[var(--color-success-text)] hover:bg-[var(--color-success-bg)]'"
      :title="status === 'online' ? 'Stop' : 'Start'"
    >
      <!-- Play icon (start) -->
      <svg v-if="status !== 'online'" class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
        <path d="M6.3 2.841A1.5 1.5 0 004 4.11V15.89a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z" />
      </svg>
      <!-- Stop icon -->
      <svg v-else class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
        <path d="M5.75 3A2.75 2.75 0 003 5.75v8.5A2.75 2.75 0 005.75 17h8.5A2.75 2.75 0 0017 14.25v-8.5A2.75 2.75 0 0014.25 3h-8.5z" />
      </svg>
      <!-- Loading spinner -->
      <svg v-if="loading" class="w-3.5 h-3.5 animate-spin absolute" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
      </svg>
    </button>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import MeterGauge from './MeterGauge.vue';

const props = defineProps({
  label: { type: String, required: true },
  icon: { type: String, default: 'server' },
  status: { type: String, default: 'stopped' },
  cpu: { type: Number, default: 0 },
  memory: { type: Number, default: 0 },
  loading: { type: Boolean, default: false },
});

const emit = defineEmits(['toggle']);

const statusDotClass = computed(() => {
  switch (props.status) {
    case 'online':
      return 'bg-[var(--color-success-dot)] animate-pulse-dot';
    case 'errored':
      return 'bg-[var(--color-danger-dot)]';
    case 'stopping':
      return 'bg-[var(--color-warning-dot)]';
    default:
      return 'bg-[var(--color-text-muted)]';
  }
});

const statusLabel = computed(() => {
  switch (props.status) {
    case 'online': return 'Online';
    case 'errored': return 'Errored';
    case 'stopping': return 'Stopping';
    case 'stopped': return 'Stopped';
    default: return 'Unknown';
  }
});

function handleToggle() {
  if (props.status === 'online') {
    emit('toggle', 'stop');
  } else {
    emit('toggle', 'start');
  }
}

function formatMemory(bytes) {
  if (bytes < 1024) return bytes + ' B';
  if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
  return (bytes / 1048576).toFixed(1) + ' MB';
}

function formatMemoryShort(bytes) {
  if (bytes < 1048576) return (bytes / 1024).toFixed(0) + 'K';
  return (bytes / 1048576).toFixed(0) + 'M';
}
</script>
