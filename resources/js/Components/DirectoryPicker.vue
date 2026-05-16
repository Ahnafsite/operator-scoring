<template>
  <!-- Backdrop -->
  <Transition name="fade">
    <div v-if="open" class="picker-backdrop" @click.self="close">
      <!-- Modal -->
      <Transition name="slide-up">
        <div v-if="open" class="picker-modal">
          <!-- Header -->
          <div class="picker-header">
            <div class="picker-title-row">
              <svg class="picker-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>
              </svg>
              <h3>Select Project Directory</h3>
            </div>
            <button class="picker-close-btn" @click="close">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
              </svg>
            </button>
          </div>

          <!-- Breadcrumb Navigation -->
          <div class="picker-breadcrumbs">
            <button class="breadcrumb-item breadcrumb-root" @click="navigateTo('/')">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                <polyline points="9 22 9 12 15 12 15 22"/>
              </svg>
            </button>
            <template v-for="(crumb, i) in breadcrumbs" :key="crumb.path">
              <span class="breadcrumb-sep">/</span>
              <button
                class="breadcrumb-item"
                :class="{ 'breadcrumb-active': i === breadcrumbs.length - 1 }"
                @click="navigateTo(crumb.path)"
              >
                {{ crumb.name }}
              </button>
            </template>
          </div>

          <!-- Current path display -->
          <div class="picker-current-path">
            <span class="path-label">Path:</span>
            <code class="path-value">{{ currentPath }}</code>
          </div>

          <!-- Directory list -->
          <div class="picker-list-container">
            <!-- Loading state -->
            <div v-if="loading" class="picker-loading">
              <div class="spinner"></div>
              <span>Loading directories...</span>
            </div>

            <!-- Error state -->
            <div v-else-if="error" class="picker-error">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                <circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>
              </svg>
              <span>{{ error }}</span>
            </div>

            <!-- Empty state -->
            <div v-else-if="directories.length === 0" class="picker-empty">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="32" height="32">
                <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>
              </svg>
              <span>No subdirectories found</span>
            </div>

            <!-- Directory entries -->
            <div v-else class="picker-list">
              <!-- Go up -->
              <button
                v-if="currentPath !== '/'"
                class="picker-entry picker-entry-up"
                @click="goUp"
              >
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                  <polyline points="15 18 9 12 15 6"/>
                </svg>
                <span class="entry-name">..</span>
                <span class="entry-hint">Parent directory</span>
              </button>

              <button
                v-for="dir in directories"
                :key="dir.path"
                class="picker-entry"
                :class="{ 'picker-entry-selected': dir.path === selectedPath }"
                @click="selectDir(dir)"
                @dblclick="navigateTo(dir.path)"
              >
                <svg class="entry-icon" viewBox="0 0 24 24" fill="currentColor" width="18" height="18">
                  <path d="M10 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2h-8l-2-2z"/>
                </svg>
                <span class="entry-name">{{ dir.name }}</span>
                <svg class="entry-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                  <polyline points="9 18 15 12 9 6"/>
                </svg>
              </button>
            </div>
          </div>

          <!-- Footer actions -->
          <div class="picker-footer">
            <div class="picker-selected-display">
              <template v-if="selectedPath">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
                  <polyline points="20 6 9 17 4 12"/>
                </svg>
                <code>{{ selectedPath }}</code>
              </template>
              <template v-else>
                <span class="no-selection">Click a folder to select, double-click to open</span>
              </template>
            </div>
            <div class="picker-actions">
              <button class="btn-cancel" @click="close">Cancel</button>
              <button class="btn-use-current" @click="selectCurrent">
                Use Current Dir
              </button>
              <button class="btn-select" :disabled="!selectedPath" @click="confirmSelection">
                Select
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </div>
  </Transition>
</template>

<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
  open: { type: Boolean, default: false },
  initialPath: { type: String, default: '/' },
});

const emit = defineEmits(['close', 'select']);

const loading = ref(false);
const error = ref('');
const currentPath = ref('/');
const parentPath = ref('/');
const breadcrumbs = ref([]);
const directories = ref([]);
const selectedPath = ref('');

// Load directories when modal opens
watch(() => props.open, (isOpen) => {
  if (isOpen) {
    const startPath = props.initialPath || '/';
    selectedPath.value = '';
    navigateTo(startPath);
  }
});

async function navigateTo(path) {
  loading.value = true;
  error.value = '';
  selectedPath.value = '';

  try {
    const res = await fetch(`/api/directories?path=${encodeURIComponent(path)}`);
    const data = await res.json();

    if (!res.ok) {
      error.value = data.error || 'Failed to load directory.';
      return;
    }

    currentPath.value = data.current;
    parentPath.value = data.parent;
    breadcrumbs.value = data.breadcrumbs;
    directories.value = data.directories;
  } catch (e) {
    error.value = 'Network error. Could not load directories.';
  } finally {
    loading.value = false;
  }
}

function goUp() {
  navigateTo(parentPath.value);
}

function selectDir(dir) {
  selectedPath.value = dir.path;
}

function selectCurrent() {
  emit('select', currentPath.value);
  close();
}

function confirmSelection() {
  if (selectedPath.value) {
    emit('select', selectedPath.value);
    close();
  }
}

function close() {
  emit('close');
}
</script>

<style scoped>
/* Backdrop */
.picker-backdrop {
  position: fixed;
  inset: 0;
  z-index: 100;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(0, 0, 0, 0.7);
  backdrop-filter: blur(4px);
}

/* Modal */
.picker-modal {
  background: var(--color-surface);
  border: 1px solid var(--color-border);
  border-radius: 16px;
  width: 680px;
  max-width: 95vw;
  max-height: 85vh;
  display: flex;
  flex-direction: column;
  box-shadow:
    0 20px 60px rgba(0, 0, 0, 0.3),
    0 0 0 1px rgba(255, 255, 255, 0.05);
  overflow: hidden;
  color: var(--color-text-primary);
}

/* Header */
.picker-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 20px 24px;
  border-bottom: 1px solid var(--color-border);
  background: var(--color-surface-alt);
}

.picker-title-row {
  display: flex;
  align-items: center;
  gap: 10px;
}

.picker-title-row h3 {
  font-size: 1.05rem;
  font-weight: 600;
  color: var(--color-text-primary);
  margin: 0;
}

.picker-icon {
  width: 22px;
  height: 22px;
  color: var(--color-primary);
}

.picker-close-btn {
  padding: 6px;
  border-radius: 8px;
  border: none;
  background: transparent;
  color: var(--color-text-muted);
  cursor: pointer;
  transition: all 0.15s;
}
.picker-close-btn:hover {
  background: var(--color-border);
  color: var(--color-text-primary);
}

/* Breadcrumbs */
.picker-breadcrumbs {
  display: flex;
  align-items: center;
  gap: 2px;
  padding: 12px 24px;
  background: var(--color-surface);
  border-bottom: 1px solid var(--color-border);
  overflow-x: auto;
  flex-wrap: nowrap;
}

.breadcrumb-item {
  padding: 4px 8px;
  border-radius: 6px;
  border: none;
  background: transparent;
  color: var(--color-text-secondary);
  cursor: pointer;
  font-size: 0.8rem;
  font-weight: 500;
  white-space: nowrap;
  transition: all 0.15s;
  display: flex;
  align-items: center;
}
.breadcrumb-item:hover {
  background: var(--color-border);
  color: var(--color-text-primary);
}
.breadcrumb-active {
  color: var(--color-primary);
  font-weight: 600;
}
.breadcrumb-root {
  padding: 5px 7px;
}
.breadcrumb-sep {
  color: var(--color-text-muted);
  font-size: 0.75rem;
  user-select: none;
}

/* Current path */
.picker-current-path {
  padding: 8px 24px;
  display: flex;
  align-items: center;
  gap: 8px;
  border-bottom: 1px solid var(--color-border);
}
.path-label {
  font-size: 0.72rem;
  font-weight: 600;
  color: var(--color-text-muted);
  text-transform: uppercase;
  letter-spacing: 0.05em;
}
.path-value {
  font-size: 0.78rem;
  color: var(--color-text-secondary);
  background: var(--color-surface-alt);
  padding: 3px 10px;
  border-radius: 6px;
  font-family: 'SF Mono', 'Fira Code', monospace;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

/* Directory list container */
.picker-list-container {
  flex: 1;
  overflow-y: auto;
  min-height: 280px;
  max-height: 400px;
  background: var(--color-surface);
}

/* Loading */
.picker-loading {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 12px;
  padding: 48px 24px;
  color: var(--color-text-muted);
  font-size: 0.85rem;
}
.spinner {
  width: 28px;
  height: 28px;
  border: 3px solid var(--color-border);
  border-top-color: var(--color-primary);
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* Error */
.picker-error {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 48px 24px;
  color: var(--color-danger-text);
  font-size: 0.85rem;
}

/* Empty */
.picker-empty {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 48px 24px;
  color: var(--color-text-muted);
  font-size: 0.85rem;
}

/* List */
.picker-list {
  display: flex;
  flex-direction: column;
}

.picker-entry {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 11px 24px;
  border: none;
  background: transparent;
  cursor: pointer;
  transition: all 0.12s;
  text-align: left;
  border-bottom: 1px solid var(--color-border);
}
.picker-entry:hover {
  background: var(--color-surface-alt);
}
.picker-entry-selected {
  background: var(--color-primary-light) !important;
  border-left: 3px solid var(--color-primary);
  padding-left: 21px;
}

.picker-entry-up {
  color: var(--color-text-muted);
  font-style: italic;
  border-bottom: 1px solid var(--color-border);
  background: var(--color-surface-alt);
}
.picker-entry-up:hover {
  background: var(--color-border);
}

.entry-icon {
  color: #f59e0b; /* keep yellow for folders, or change to neutral */
  flex-shrink: 0;
}
.picker-entry-selected .entry-icon {
  color: var(--color-primary);
}

.entry-name {
  flex: 1;
  font-size: 0.88rem;
  font-weight: 500;
  color: var(--color-text-primary);
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.entry-hint {
  font-size: 0.75rem;
  color: var(--color-text-muted);
  font-style: italic;
}

.entry-chevron {
  color: var(--color-text-muted);
  flex-shrink: 0;
  transition: transform 0.15s;
}
.picker-entry:hover .entry-chevron {
  transform: translateX(2px);
  color: var(--color-text-secondary);
}

/* Footer */
.picker-footer {
  padding: 16px 24px;
  border-top: 1px solid var(--color-border);
  background: var(--color-surface-alt);
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.picker-selected-display {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 0.78rem;
  color: var(--color-success-text);
  min-height: 22px;
}
.picker-selected-display code {
  background: var(--color-success-bg);
  padding: 2px 8px;
  border-radius: 4px;
  font-family: 'SF Mono', 'Fira Code', monospace;
  color: var(--color-success-text);
  font-size: 0.75rem;
  border: 1px solid var(--color-success-border);
}
.no-selection {
  color: var(--color-text-muted);
  font-style: italic;
}

.picker-actions {
  display: flex;
  justify-content: flex-end;
  gap: 8px;
}

.btn-cancel {
  padding: 8px 18px;
  border-radius: 8px;
  border: 1px solid var(--color-border);
  background: var(--color-surface);
  color: var(--color-text-secondary);
  font-size: 0.82rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.15s;
}
.btn-cancel:hover {
  background: var(--color-border-light);
  color: var(--color-text-primary);
}

.btn-use-current {
  padding: 8px 18px;
  border-radius: 8px;
  border: 1px solid var(--color-primary-light);
  background: var(--color-primary-light);
  color: var(--color-text-primary);
  font-size: 0.82rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.15s;
}
.btn-use-current:hover {
  background: var(--color-primary);
}

.btn-select {
  padding: 8px 24px;
  border-radius: 8px;
  border: none;
  background: var(--color-primary);
  color: #fff;
  font-size: 0.82rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.15s;
}
.btn-select:hover:not(:disabled) {
  background: var(--color-primary-hover);
}
.btn-select:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

/* Transitions */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.slide-up-enter-active {
  transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}
.slide-up-leave-active {
  transition: all 0.15s ease-in;
}
.slide-up-enter-from {
  opacity: 0;
  transform: translateY(20px) scale(0.97);
}
.slide-up-leave-to {
  opacity: 0;
  transform: translateY(10px) scale(0.98);
}
</style>
