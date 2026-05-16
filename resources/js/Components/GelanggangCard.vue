<template>
  <div class="bg-[var(--color-surface)] rounded-[var(--radius-card)] border border-[var(--color-border)] shadow-[var(--shadow-card)] transition-card overflow-hidden">
    <!-- Card Header -->
    <div class="px-5 pt-5 pb-3 flex items-start justify-between">
      <div class="flex items-center gap-3 min-w-0">
        <div class="w-10 h-10 rounded-lg flex items-center justify-center text-sm font-bold shrink-0"
             :class="overallOnline ? 'bg-[var(--color-success-bg)] text-[var(--color-success-text)]' : 'bg-[var(--color-border-light)] text-[var(--color-text-muted)]'">
          {{ gelanggang.name?.charAt(gelanggang.name.length - 1) || '#' }}
        </div>
        <div class="min-w-0">
          <h3 class="text-sm font-semibold text-[var(--color-text-primary)] truncate">{{ gelanggang.name }}</h3>
          <p class="text-xs text-[var(--color-text-muted)] truncate mt-0.5">
            {{ gelanggang.serve_host }}:{{ gelanggang.serve_port }}
          </p>
        </div>
      </div>

      <!-- Card Actions Menu -->
      <div class="relative shrink-0">
        <button @click="menuOpen = !menuOpen"
                :id="'btn-menu-' + gelanggang.id"
                class="p-1.5 rounded-md hover:bg-[var(--color-border-light)] text-[var(--color-text-muted)] transition-colors cursor-pointer">
          <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
            <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
          </svg>
        </button>
        <!-- Dropdown -->
        <div v-if="menuOpen"
             class="absolute right-0 mt-1 w-36 bg-[var(--color-surface)] border border-[var(--color-border)] rounded-lg shadow-lg py-1 z-20">
          <button @click="$emit('edit', gelanggang); menuOpen = false"
                  class="w-full text-left px-3 py-1.5 text-sm text-[var(--color-text-secondary)] hover:bg-[var(--color-border-light)] transition-colors cursor-pointer">
            Edit Config
          </button>
          <button @click="$emit('delete', gelanggang); menuOpen = false"
                  class="w-full text-left px-3 py-1.5 text-sm text-[var(--color-danger-text)] hover:bg-[var(--color-danger-bg)] transition-colors cursor-pointer">
            Delete
          </button>
        </div>
      </div>
    </div>

    <!-- Services Status -->
    <div class="px-5 pb-3 space-y-2">
      <ServiceRow
        v-for="svc in services"
        :key="svc.key"
        :label="svc.label"
        :icon="svc.icon"
        :status="status[svc.key]?.status || 'stopped'"
        :cpu="status[svc.key]?.cpu || 0"
        :memory="status[svc.key]?.memory || 0"
        :loading="loading[svc.key] || false"
        @toggle="handleServiceToggle(svc.key, $event)"
      />
    </div>

    <!-- Card Footer Actions -->
    <div class="px-5 py-3 border-t border-[var(--color-border-light)] flex items-center gap-2">
      <button
        :id="'btn-start-all-' + gelanggang.id"
        @click="$emit('start-all', gelanggang)"
        :disabled="loading.all || allOnline"
        class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-1.5 bg-[var(--color-success-bg)] hover:bg-[var(--color-success-border)] text-[var(--color-success-text)] text-xs font-medium rounded-[var(--radius-badge)] transition-colors duration-150 disabled:opacity-50 cursor-pointer disabled:cursor-not-allowed"
      >
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
        </svg>
        Start All
      </button>
      <button
        :id="'btn-stop-all-' + gelanggang.id"
        @click="$emit('stop-all', gelanggang)"
        :disabled="loading.all || allOffline"
        class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-1.5 bg-[var(--color-danger-bg)] hover:bg-[var(--color-danger-border)] text-[var(--color-danger-text)] text-xs font-medium rounded-[var(--radius-badge)] transition-colors duration-150 disabled:opacity-50 cursor-pointer disabled:cursor-not-allowed"
      >
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z" />
        </svg>
        Stop All
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import ServiceRow from './ServiceRow.vue';

const props = defineProps({
  gelanggang: { type: Object, required: true },
  status: { type: Object, default: () => ({}) },
  loading: { type: Object, default: () => ({}) },
});

const emit = defineEmits(['start-all', 'stop-all', 'toggle-process', 'edit', 'delete']);
const menuOpen = ref(false);

const services = [
  { key: 'serve',  label: 'Serve',  icon: 'server' },
  { key: 'reverb', label: 'Reverb', icon: 'broadcast' },
  { key: 'queue',  label: 'Queue',  icon: 'queue' },
];

const overallOnline = computed(() => {
  return services.some(
    svc => props.status[svc.key]?.status === 'online'
  );
});

const allOnline = computed(() => {
  return services.every(
    svc => props.status[svc.key]?.status === 'online'
  );
});

const allOffline = computed(() => {
  return services.every(
    svc => !props.status[svc.key]?.status || props.status[svc.key]?.status === 'stopped' || props.status[svc.key]?.status === 'errored'
  );
});

function handleServiceToggle(service, action) {
  emit('toggle-process', { gelanggang: props.gelanggang, service, action });
}
</script>
