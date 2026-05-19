<template>
  <div class="terminal-settings-view">
    <div v-if="state.isLoading" class="loading-container">
      <ProgressSpinner />
      <p>Loading settings...</p>
    </div>

    <div v-else class="settings-container">
      <section class="settings-panel">
        <div class="setting-item">
          <div class="setting-icon"><i class="pi pi-code"></i></div>
          <div class="setting-info">
            <h3>Terminal</h3>
            <p>Allow access to the WordPress debug terminal.</p>
          </div>
          <div class="setting-control">
            <span :class="['setting-status', state.terminalEnabled ? 'is-on' : 'is-off']">
              {{ state.terminalEnabled ? 'Enabled' : 'Disabled' }}
            </span>
            <InputSwitch v-model="state.terminalEnabled" @change="updateSettings" />
          </div>
        </div>

        <div class="setting-item">
          <div class="setting-icon"><i class="pi pi-database"></i></div>
          <div class="setting-info">
            <h3>Database Commands</h3>
            <p>Allow super administrators to run terminal database commands.</p>
            <span class="setting-note">
              <i class="pi pi-exclamation-triangle"></i>
              Super admin only. Use with care.
            </span>
          </div>
          <div class="setting-control">
            <span :class="['setting-status', state.dbCommandsEnabled ? 'is-on' : 'is-off']">
              {{ state.dbCommandsEnabled ? 'Enabled' : 'Disabled' }}
            </span>
            <InputSwitch v-model="state.dbCommandsEnabled" @change="updateSettings" :disabled="!state.terminalEnabled" />
          </div>
        </div>
      </section>

      <details class="terminal-info">
        <summary>Command reference</summary>
        <div class="terminal-command-grid">
          <div>
            <strong>Examples</strong>
            <ul>
              <li><code>wp core version</code> <span>WordPress version</span></li>
              <li><code>wp plugin list</code> <span>Installed plugins</span></li>
              <li><code>wp theme list</code> <span>Installed themes</span></li>
              <li><code>wp db tables</code> <span>Database tables</span></li>
            </ul>
          </div>
          <div>
            <strong>Structure</strong>
            <p><code>wp &lt;command&gt; &lt;subcommand&gt; [options]</code></p>
            <p><code>wp:command:subcommand [options]</code></p>
          </div>
        </div>
      </details>
    </div>
  </div>
</template>

<script setup>
import { onMounted, reactive } from 'vue';
import { useToast } from 'primevue/usetoast';
import { $get, $post } from '../request';

const toast = useToast();

const state = reactive({
  isLoading: true,
  terminalEnabled: true,
  dbCommandsEnabled: false
});

async function fetchSettings() {
  try {
    state.isLoading = true;
    const args = {
      route: 'get_terminal_settings'
    };
    
    const { data, error } = await $get(args);
    
    if (data && data.value && data.value.data) {
      state.terminalEnabled = data.value.data.terminal_enabled;
      state.dbCommandsEnabled = data.value.data.db_commands_enabled;
    } else if (error) {
      showToast('error', 'Error', 'Failed to load terminal settings');
      console.error('Error fetching terminal settings:', error);
    }
  } catch (err) {
    showToast('error', 'Error', 'Failed to load terminal settings');
    console.error('Error fetching terminal settings:', err);
  } finally {
    state.isLoading = false;
  }
}

async function updateSettings() {
  try {
    const args = {
      route: 'update_terminal_settings',
      terminal_enabled: state.terminalEnabled,
      db_commands_enabled: state.dbCommandsEnabled
    };
    
    const { data, error } = await $post(args);
    
    if (data && data.value && data.value.data && data.value.data.success) {
      showToast('success', 'Success', data.value.data.message || 'Terminal settings updated successfully');
    } else if (error) {
      showToast('error', 'Error', 'Failed to update terminal settings');
      console.error('Error updating terminal settings:', error);
    }
  } catch (err) {
    showToast('error', 'Error', 'Failed to update terminal settings');
    console.error('Error updating terminal settings:', err);
  }
}

function showToast(severity, summary, detail) {
  toast.add({
    severity: severity,
    summary: summary,
    detail: detail,
    life: 3000
  });
}

onMounted(() => {
  fetchSettings();
});
</script>

<style scoped>
.terminal-settings-view {
  margin: 0;
  padding: 0;
}

.loading-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 40px;
}

.settings-container {
  display: grid;
  gap: 14px;
}

.settings-panel {
  overflow: hidden;
  border: 1px solid #dbeafe;
  border-radius: 8px;
  background: #fff;
}

.setting-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 14px;
  padding: 16px;
  border-bottom: 1px solid #e5e7eb;
}

.setting-item:last-child {
  border-bottom: 0;
}

.setting-icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  flex: 0 0 auto;
  width: 38px;
  height: 38px;
  border-radius: 8px;
  background: #eff6ff;
  color: #2563eb;
}

.setting-info {
  flex: 1;
  min-width: 0;
}

.setting-info h3 {
  margin: 0 0 4px;
  color: #1e293b;
  font-size: 15px;
}

.setting-info p {
  margin: 0;
  color: #64748b;
  font-size: 13px;
}

.setting-note {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  margin-top: 8px;
  color: #8a5a00;
  font-size: 12px;
  font-weight: 700;
}

.setting-control {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-left: 16px;
}

.setting-status {
  min-width: 68px;
  padding: 4px 8px;
  border-radius: 999px;
  text-align: center;
  font-size: 12px;
  font-weight: 700;
}

.setting-status.is-on {
  background: #ecfdf5;
  color: #047857;
}

.setting-status.is-off {
  background: #f1f5f9;
  color: #64748b;
}

.terminal-info {
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  background-color: #fff;
}

.terminal-info summary {
  cursor: pointer;
  padding: 12px 14px;
  color: #334155;
  font-weight: 700;
}

.terminal-command-grid {
  display: grid;
  grid-template-columns: minmax(0, 1.4fr) minmax(260px, 0.8fr);
  gap: 18px;
  padding: 0 14px 14px;
}

.terminal-command-grid strong {
  display: block;
  margin-bottom: 8px;
  color: #1e293b;
}

.terminal-info ul {
  margin: 0;
  padding-left: 0;
  list-style: none;
}

.terminal-info li {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 6px;
  color: #64748b;
  font-size: 13px;
}

.terminal-command-grid p {
  margin: 0 0 8px;
}

code {
  background-color: #eff6ff;
  color: #1e40af;
  padding: 2px 6px;
  border-radius: 4px;
  font-family: monospace;
}

@media (max-width: 782px) {
  .setting-item,
  .setting-control {
    align-items: flex-start;
  }

  .setting-item {
    flex-wrap: wrap;
  }

  .setting-control {
    width: 100%;
    justify-content: space-between;
    margin-left: 52px;
  }

  .terminal-command-grid {
    grid-template-columns: 1fr;
  }
}
</style>
