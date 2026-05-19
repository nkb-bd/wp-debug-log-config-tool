<template>
  <div class="terminal-view">
    <div v-if="state.isLoading" class="loading-container">
      <ProgressSpinner />
      <p>Loading terminal settings...</p>
    </div>

    <div v-else-if="!state.terminalEnabled" class="terminal-disabled">
      <Message severity="warn" class="terminal-disabled-message">
        <template #container>
          <div class="terminal-disabled-content">
            <i class="pi pi-ban"></i>
            <div>
              <h3>Terminal is disabled</h3>
              <p>The terminal has been disabled by the administrator.</p>
            </div>
            <Button label="Settings" icon="pi pi-cog" @click="goToSettings" />
          </div>
        </template>
      </Message>
    </div>

    <div v-else class="terminal-shell">
      <DebugTerminal />

      <div class="terminal-support">
        <div class="terminal-notices">
          <span class="terminal-notice is-info">
            <i class="pi pi-info-circle"></i>
            Type <strong>wp</strong> for WP-CLI commands.
          </span>
          <span class="terminal-notice is-warning">
            <i class="pi pi-exclamation-triangle"></i>
            Runs with admin privileges.
          </span>
          <router-link
            v-if="!state.dbCommandsEnabled"
            to="/terminal-settings"
            class="terminal-notice is-info terminal-link-notice"
          >
            <i class="pi pi-database"></i>
            Enable database commands
          </router-link>
          <span v-else class="terminal-notice is-info">
            <i class="pi pi-database"></i>
            Database commands enabled.
          </span>
        </div>

        <details class="terminal-tips">
          <summary>Tips and examples</summary>
          <ul>
            <li><strong>Up/Down</strong> navigates history</li>
            <li><strong>Tab</strong> autocompletes commands</li>
            <li><code>clear</code> clears the terminal</li>
            <li><code>wp core version</code>, <code>wp plugin list</code>, <code>wp log tail</code></li>
            <li><code>ls</code>, <code>cat</code>, <code>date</code>, <code>pwd</code></li>
          </ul>
        </details>
      </div>
    </div>
  </div>
</template>

<script setup>
import DebugTerminal from '../components/DebugTerminal.vue';
import { onMounted, reactive } from 'vue';
import { useRouter } from 'vue-router';
import { $get } from '../request';
import { useToast } from 'primevue/usetoast';

const router = useRouter();

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
      console.error('Error fetching terminal settings:', error);
    }
  } catch (err) {
    console.error('Error fetching terminal settings:', err);
  } finally {
    state.isLoading = false;
  }
}

function goToSettings() {
  router.push('/terminal-settings');
}

onMounted(() => {
  fetchSettings();
});
</script>

<style scoped>
.terminal-view {
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

.terminal-disabled {
  padding: 0;
}

.terminal-disabled-message {
  text-align: center;
}

.terminal-disabled-content {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 18px;
}

.terminal-disabled-content i {
  color: #d97706;
  font-size: 24px;
}

.terminal-disabled-content h3,
.terminal-disabled-content p {
  margin: 0;
}

.terminal-disabled-content p {
  margin-top: 4px;
  color: #64748b;
}

.terminal-shell {
  display: grid;
  gap: 12px;
}

.terminal-support {
  display: grid;
  gap: 10px;
}

.terminal-notices {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.terminal-notice {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  min-height: 34px;
  padding: 0 10px;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 600;
  text-decoration: none;
}

.terminal-notice.is-info {
  background: #eff6ff;
  color: #2563eb;
}

.terminal-notice.is-warning {
  background: #fff7dc;
  color: #8a5a00;
}

.terminal-link-notice {
  border: 1px solid #bfdbfe;
}

.terminal-tips {
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  background-color: #fff;
}

.terminal-tips summary {
  cursor: pointer;
  padding: 10px 12px;
  color: #334155;
  font-weight: 700;
}

.terminal-tips ul {
  margin: 0;
  padding: 0 12px 12px 30px;
}

.terminal-tips li {
  margin-bottom: 4px;
  color: #495057;
  font-size: 13px;
}
</style>
