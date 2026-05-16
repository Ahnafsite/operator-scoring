<template>
  <!-- Backdrop -->
  <div class="fixed inset-0 z-40 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/30 backdrop-blur-sm" @click="$emit('close')"></div>

    <!-- Modal -->
    <div class="relative w-full max-w-md bg-[var(--color-surface)] rounded-xl border border-[var(--color-border)] shadow-xl z-50 overflow-hidden">
      <!-- Header -->
      <div class="px-6 py-4 border-b border-[var(--color-border-light)] flex items-center justify-between">
        <h2 class="text-base font-semibold text-[var(--color-text-primary)]">
          {{ isEditing ? 'Edit Gelanggang' : 'Add Gelanggang' }}
        </h2>
        <button @click="$emit('close')"
                class="p-1 rounded-md hover:bg-[var(--color-border-light)] text-[var(--color-text-muted)] transition-colors cursor-pointer">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Form -->
      <form @submit.prevent="handleSubmit" class="px-6 py-5 space-y-4">
        <!-- Name -->
        <div>
          <label for="config-name" class="block text-xs font-medium text-[var(--color-text-secondary)] mb-1.5">
            Nama Gelanggang
          </label>
          <input
            id="config-name"
            v-model="form.name"
            type="text"
            placeholder="e.g. Gelanggang A"
            required
            class="w-full px-3 py-2 text-sm bg-[var(--color-surface)] border border-[var(--color-border)] rounded-[var(--radius-btn)] text-[var(--color-text-primary)] placeholder:text-[var(--color-text-muted)] focus:outline-none focus:ring-2 focus:ring-[var(--color-primary)]/30 focus:border-[var(--color-primary)] transition-colors"
          />
        </div>

        <!-- Target Path (Directory Picker) -->
        <div>
          <label class="block text-xs font-medium text-[var(--color-text-secondary)] mb-1.5">
            Project Path
          </label>
          <button
            id="btn-browse-path"
            type="button"
            @click="showDirectoryPicker = true"
            class="w-full flex items-center gap-2 px-3 py-2 text-sm bg-[var(--color-surface)] border border-[var(--color-border)] rounded-[var(--radius-btn)] text-left transition-colors hover:border-[var(--color-primary)] hover:bg-[var(--color-primary)]/5 focus:outline-none focus:ring-2 focus:ring-[var(--color-primary)]/30 focus:border-[var(--color-primary)] cursor-pointer group"
          >
            <svg class="w-4 h-4 flex-shrink-0 text-[var(--color-text-muted)] group-hover:text-[var(--color-primary)] transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>
            </svg>
            <span
              v-if="form.target_path"
              class="flex-1 font-mono text-[var(--color-text-primary)] truncate"
            >{{ form.target_path }}</span>
            <span
              v-else
              class="flex-1 text-[var(--color-text-muted)] italic"
            >Click to select project folder...</span>
            <svg class="w-4 h-4 flex-shrink-0 text-[var(--color-text-muted)] group-hover:text-[var(--color-primary)] transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <polyline points="6 9 12 15 18 9" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" />
            </svg>
          </button>
          <p class="text-[10px] text-[var(--color-text-muted)] mt-1">Browse and select the Laravel project folder.</p>
          <p v-if="errors.target_path" class="text-[10px] text-[var(--color-danger-text)] mt-1">{{ errors.target_path[0] }}</p>
        </div>

        <!-- Directory Picker Modal -->
        <DirectoryPicker
          :open="showDirectoryPicker"
          :initial-path="form.target_path || '/Users'"
          @close="showDirectoryPicker = false"
          @select="onPathSelected"
        />

        <!-- IP & Port Row -->
        <div class="grid grid-cols-3 gap-3">
          <div class="col-span-1">
            <label for="config-host" class="block text-xs font-medium text-[var(--color-text-secondary)] mb-1.5">
              IP Address
            </label>
            <input
              id="config-host"
              v-model="form.serve_host"
              type="text"
              placeholder="192.168.1.10"
              required
              class="w-full px-3 py-2 text-sm font-mono bg-[var(--color-surface)] border border-[var(--color-border)] rounded-[var(--radius-btn)] text-[var(--color-text-primary)] placeholder:text-[var(--color-text-muted)] focus:outline-none focus:ring-2 focus:ring-[var(--color-primary)]/30 focus:border-[var(--color-primary)] transition-colors"
            />
          </div>
          <div>
            <label for="config-serve-port" class="block text-xs font-medium text-[var(--color-text-secondary)] mb-1.5">
              Serve Port
            </label>
            <input
              id="config-serve-port"
              v-model.number="form.serve_port"
              type="number"
              min="1024"
              max="65535"
              required
              class="w-full px-3 py-2 text-sm font-mono bg-[var(--color-surface)] border border-[var(--color-border)] rounded-[var(--radius-btn)] text-[var(--color-text-primary)] placeholder:text-[var(--color-text-muted)] focus:outline-none focus:ring-2 focus:ring-[var(--color-primary)]/30 focus:border-[var(--color-primary)] transition-colors"
            />
          </div>
          <div>
            <label for="config-reverb-port" class="block text-xs font-medium text-[var(--color-text-secondary)] mb-1.5">
              Reverb Port
            </label>
            <input
              id="config-reverb-port"
              v-model.number="form.reverb_port"
              type="number"
              min="1024"
              max="65535"
              required
              class="w-full px-3 py-2 text-sm font-mono bg-[var(--color-surface)] border border-[var(--color-border)] rounded-[var(--radius-btn)] text-[var(--color-text-primary)] placeholder:text-[var(--color-text-muted)] focus:outline-none focus:ring-2 focus:ring-[var(--color-primary)]/30 focus:border-[var(--color-primary)] transition-colors"
            />
          </div>
        </div>

        <!-- Error message -->
        <p v-if="generalError" class="text-xs text-[var(--color-danger-text)] bg-[var(--color-danger-bg)] px-3 py-2 rounded-[var(--radius-badge)]">
          {{ generalError }}
        </p>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-2 pt-2">
          <button
            type="button"
            @click="$emit('close')"
            class="px-4 py-2 text-sm font-medium text-[var(--color-text-secondary)] hover:bg-[var(--color-border-light)] rounded-[var(--radius-btn)] transition-colors cursor-pointer"
          >
            Cancel
          </button>
          <button
            id="btn-save-config"
            type="submit"
            :disabled="submitting"
            class="px-4 py-2 text-sm font-medium text-white bg-[var(--color-primary)] hover:bg-[var(--color-primary-hover)] rounded-[var(--radius-btn)] transition-colors disabled:opacity-50 cursor-pointer disabled:cursor-not-allowed"
          >
            {{ submitting ? 'Saving...' : (isEditing ? 'Update' : 'Create') }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue';
import DirectoryPicker from './DirectoryPicker.vue';

const props = defineProps({
  config: { type: Object, default: null },
});

const emit = defineEmits(['close', 'saved']);

const isEditing = computed(() => !!props.config?.id);

const form = reactive({
  name: props.config?.name || '',
  target_path: props.config?.target_path || '',
  serve_host: props.config?.serve_host || '127.0.0.1',
  serve_port: props.config?.serve_port || 8000,
  reverb_port: props.config?.reverb_port || 8080,
});

const submitting = ref(false);
const generalError = ref('');
const errors = reactive({});
const showDirectoryPicker = ref(false);

function onPathSelected(path) {
  form.target_path = path;
}

async function handleSubmit() {
  submitting.value = true;
  generalError.value = '';
  Object.keys(errors).forEach(k => delete errors[k]);

  try {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content
      || document.cookie.match(/XSRF-TOKEN=([^;]+)/)?.[1] || '';

    const url = isEditing.value
      ? `/gelanggang/${props.config.id}`
      : '/gelanggang';

    const method = isEditing.value ? 'PUT' : 'POST';

    const res = await fetch(url, {
      method,
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
      },
      body: JSON.stringify(form),
    });

    const data = await res.json();

    if (!res.ok) {
      if (data.errors) {
        Object.assign(errors, data.errors);
      }
      generalError.value = data.message || 'An error occurred.';
      return;
    }

    emit('saved', data.gelanggang);
  } catch (e) {
    generalError.value = 'Network error. Please try again.';
    console.error(e);
  } finally {
    submitting.value = false;
  }
}
</script>
