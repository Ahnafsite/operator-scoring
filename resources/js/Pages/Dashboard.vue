<template>
  <div class="min-h-screen bg-[var(--color-surface-alt)] font-sans text-[var(--color-text-primary)]">
    <!-- Header -->
    <header class="bg-[var(--color-surface)] border-b border-[var(--color-border)] sticky top-0 z-30 shadow-[var(--shadow-card)]">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
          <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-[var(--color-primary)] to-red-600 flex items-center justify-center shadow-[var(--shadow-glow)]">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2" />
            </svg>
          </div>
          <div>
            <h1 class="text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-white to-gray-400">PM2 Arena Controller</h1>
            <p class="text-xs font-medium text-[var(--color-text-muted)] tracking-wider uppercase mt-0.5">Pencak Silat Services</p>
          </div>
        </div>

        <div class="flex items-center gap-6">
          <!-- Global Stats Monitor -->
          <div class="hidden md:flex items-center gap-4 px-4 py-2 bg-[var(--color-border-light)] rounded-xl border border-[var(--color-border)]">
            <div class="flex flex-col">
              <span class="text-[10px] text-[var(--color-text-muted)] uppercase font-semibold">Total CPU</span>
              <span class="text-sm font-mono font-medium" :class="globalStats.cpu > 50 ? 'text-[var(--color-warning-text)]' : 'text-[var(--color-success-text)]'">
                {{ globalStats.cpu.toFixed(1) }}%
              </span>
            </div>
            <div class="w-px h-6 bg-[var(--color-border)]"></div>
            <div class="flex flex-col">
              <span class="text-[10px] text-[var(--color-text-muted)] uppercase font-semibold">Total RAM</span>
              <span class="text-sm font-mono font-medium text-[var(--color-primary)]">
                {{ formatMemory(globalStats.memory) }}
              </span>
            </div>
            <div class="w-px h-6 bg-[var(--color-border)]"></div>
            <div class="flex items-center gap-1.5 text-xs font-medium" :class="globalStats.activeProcesses > 0 ? 'text-[var(--color-success-text)]' : 'text-[var(--color-text-muted)]'">
              <span class="w-2 h-2 rounded-full" :class="globalStats.activeProcesses > 0 ? 'bg-[var(--color-success-dot)] animate-pulse-dot shadow-[0_0_8px_var(--color-success-dot)]' : 'bg-[var(--color-border)]'"></span>
              {{ globalStats.activeProcesses }} Online
            </div>
          </div>

          <!-- Add Gelanggang button -->
          <button
            id="btn-add-gelanggang"
            @click="showConfigModal = true; editingConfig = null"
            class="inline-flex items-center gap-2 px-4 py-2 bg-[var(--color-primary)] hover:bg-[var(--color-primary-hover)] text-white text-sm font-medium rounded-xl transition-all duration-200 cursor-pointer shadow-[0_4px_12px_rgba(99,102,241,0.3)] hover:shadow-[0_6px_16px_rgba(99,102,241,0.4)] hover:-translate-y-0.5"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Add Gelanggang
          </button>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Empty State -->
      <div v-if="localGelanggangs.length === 0"
           class="flex flex-col items-center justify-center py-24 text-center">
        <div class="w-20 h-20 rounded-full bg-[var(--color-surface)] border border-[var(--color-border)] shadow-[var(--shadow-card)] flex items-center justify-center mb-6">
          <svg class="w-10 h-10 text-[var(--color-text-muted)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
          </svg>
        </div>
        <h2 class="text-xl font-bold text-[var(--color-text-primary)] mb-2">No Gelanggang Configured</h2>
        <p class="text-[var(--color-text-secondary)] mb-8 max-w-md mx-auto leading-relaxed">
          Start by adding your first arena to manage and monitor Pencak Silat scoring services with PM2.
        </p>
        <button
          @click="showConfigModal = true; editingConfig = null"
          class="inline-flex items-center gap-2 px-6 py-3 bg-[var(--color-primary)] hover:bg-[var(--color-primary-hover)] text-white font-medium rounded-xl transition-all duration-200 cursor-pointer shadow-[0_4px_12px_rgba(99,102,241,0.3)] hover:shadow-[0_6px_16px_rgba(99,102,241,0.4)] hover:-translate-y-0.5"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Add First Gelanggang
        </button>
      </div>

      <!-- Grid of Gelanggang Cards -->
      <div v-else class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        <GelanggangCard
          v-for="gelanggang in localGelanggangs"
          :key="gelanggang.id"
          :gelanggang="gelanggang"
          :status="localStatuses[gelanggang.id] || {}"
          :loading="loadingStates[gelanggang.id] || {}"
          @start-all="handleStartAll"
          @stop-all="handleStopAll"
          @toggle-process="handleToggleProcess"
          @edit="openEditModal"
          @delete="handleDelete"
        />
      </div>
    </main>

    <!-- Config Modal -->
    <ConfigModal
      v-if="showConfigModal"
      :config="editingConfig"
      @close="showConfigModal = false"
      @saved="handleConfigSaved"
    />
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onUnmounted } from 'vue';
import GelanggangCard from '../Components/GelanggangCard.vue';
import ConfigModal from '../Components/ConfigModal.vue';

const props = defineProps({
  gelanggangs: { type: Array, default: () => [] },
  statuses: { type: Object, default: () => ({}) },
});

// Local reactive copies so we can update without full Inertia reload
const localGelanggangs = ref([...props.gelanggangs]);
const localStatuses = ref({ ...props.statuses });
const showConfigModal = ref(false);
const editingConfig = ref(null);
const polling = ref(true);
const loadingStates = reactive({});

// Global Stats Calculation
const globalStats = computed(() => {
  let cpu = 0;
  let memory = 0;
  let activeProcesses = 0;

  Object.values(localStatuses.value).forEach(gelanggangServices => {
    Object.values(gelanggangServices).forEach(service => {
      if (service.status === 'online') {
        cpu += service.cpu || 0;
        memory += service.memory || 0;
        activeProcesses++;
      }
    });
  });

  return { cpu, memory, activeProcesses };
});

function formatMemory(bytes) {
  if (bytes < 1024) return bytes + ' B';
  if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
  if (bytes < 1073741824) return (bytes / 1048576).toFixed(1) + ' MB';
  return (bytes / 1073741824).toFixed(2) + ' GB';
}

let pollInterval = null;

// ── Polling ───────────────────────────────────────────────────────────
function startPolling() {
  pollInterval = setInterval(async () => {
    if (!polling.value) return;
    try {
      const res = await fetch('/api/status');
      const data = await res.json();
      localStatuses.value = data.statuses;
    } catch (e) {
      console.warn('Polling failed:', e);
    }
  }, 3000);
}

onMounted(() => startPolling());
onUnmounted(() => {
  if (pollInterval) clearInterval(pollInterval);
});

// ── PM2 Actions ───────────────────────────────────────────────────────
async function handleStartAll(gelanggang) {
  setLoading(gelanggang.id, 'all', true);
  try {
    const res = await fetch(`/pm2/${gelanggang.id}/start`, { method: 'POST', headers: csrfHeaders() });
    const data = await res.json();
    if (!data.success) console.warn(data.message);
  } catch (e) {
    console.error('Start all failed:', e);
  } finally {
    setLoading(gelanggang.id, 'all', false);
  }
}

async function handleStopAll(gelanggang) {
  setLoading(gelanggang.id, 'all', true);
  try {
    const res = await fetch(`/pm2/${gelanggang.id}/stop`, { method: 'POST', headers: csrfHeaders() });
    const data = await res.json();
    if (!data.success) console.warn(data.message);
  } catch (e) {
    console.error('Stop all failed:', e);
  } finally {
    setLoading(gelanggang.id, 'all', false);
  }
}

async function handleToggleProcess({ gelanggang, service, action }) {
  setLoading(gelanggang.id, service, true);
  try {
    const res = await fetch(`/pm2/${gelanggang.id}/toggle`, {
      method: 'POST',
      headers: { ...csrfHeaders(), 'Content-Type': 'application/json' },
      body: JSON.stringify({ service, action }),
    });
    const data = await res.json();
    if (!data.success) console.warn(data.message);
  } catch (e) {
    console.error('Toggle failed:', e);
  } finally {
    setLoading(gelanggang.id, service, false);
  }
}

// ── Config CRUD ───────────────────────────────────────────────────────
function openEditModal(gelanggang) {
  editingConfig.value = { ...gelanggang };
  showConfigModal.value = true;
}

async function handleConfigSaved(config) {
  showConfigModal.value = false;

  // Refresh the gelanggang list
  const idx = localGelanggangs.value.findIndex(g => g.id === config.id);
  if (idx >= 0) {
    localGelanggangs.value[idx] = config;
  } else {
    localGelanggangs.value.push(config);
  }
}

async function handleDelete(gelanggang) {
  if (!confirm(`Delete "${gelanggang.name}"? This will also remove its PM2 processes.`)) return;

  try {
    // Delete PM2 processes first
    await fetch(`/pm2/${gelanggang.id}/delete`, { method: 'POST', headers: csrfHeaders() });

    // Then delete config
    await fetch(`/gelanggang/${gelanggang.id}`, { method: 'DELETE', headers: csrfHeaders() });

    localGelanggangs.value = localGelanggangs.value.filter(g => g.id !== gelanggang.id);
  } catch (e) {
    console.error('Delete failed:', e);
  }
}

// ── Helpers ───────────────────────────────────────────────────────────
function csrfHeaders() {
  const token = document.querySelector('meta[name="csrf-token"]')?.content
    || document.cookie.match(/XSRF-TOKEN=([^;]+)/)?.[1];
  return {
    'X-CSRF-TOKEN': token || '',
    'Accept': 'application/json',
  };
}

function setLoading(id, key, value) {
  if (!loadingStates[id]) loadingStates[id] = {};
  loadingStates[id][key] = value;
}
</script>
