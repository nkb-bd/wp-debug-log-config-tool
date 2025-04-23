<template>
  <div class="terminal-view">
    <Card>
      <template #title>
        WordPress Debug Terminal
      </template>
      <template #subtitle>
        Execute WordPress commands directly from your browser
      </template>
      <template #content>
        <div v-if="state.isLoading" class="loading-container">
          <ProgressSpinner />
          <p>Loading terminal settings...</p>
        </div>

        <div v-else-if="!state.terminalEnabled" class="terminal-disabled">
          <Message severity="warn" class="terminal-disabled-message">
            <template #container>
              <div class="flex flex-column align-items-center">
                <i class="pi pi-ban text-5xl mb-3"></i>
                <h3>Terminal is disabled</h3>
                <p>The terminal has been disabled by the administrator.</p>
                <Button label="Go to Terminal Settings" icon="pi pi-cog" @click="goToSettings" />
              </div>
            </template>
          </Message>
        </div>

        <div v-else>
          <DebugTerminal />

          <div class="terminal-description">
            <p>This terminal allows you to run WordPress-specific commands to help with debugging and site management.</p>
            <Message icon="false" severity="info">
              <span>Type <strong>wp</strong> to see available WP-CLI style commands.</span>
            </Message>
            <Message icon="false" severity="warn">
              <span>Commands are executed with WordPress admin privileges. Use with caution.</span>
            </Message>
            <Message v-if="state.dbCommandsEnabled" icon="false" severity="info">
              <span>Database commands are enabled. Type <code>wp db</code> to see available database commands.</span>
            </Message>
            <Message v-else icon="false" severity="info">
              <span>Database commands are disabled. Enable them in <router-link to="/terminal-settings">Terminal Settings</router-link> to use database commands.</span>
            </Message>
          </div>

          <div class="terminal-tips">
            <h4>Tips:</h4>
            <ul>
              <li>Use <strong>Up/Down</strong> arrow keys to navigate command history</li>
              <li>Use <strong>Tab</strong> key for command auto-completion</li>
              <li>Use <strong>clear</strong> command to clear the terminal</li>
              <li>Try WP-CLI commands like <code>wp core version</code>, <code>wp plugin list</code>, etc.</li>
              <li>Basic shell commands like <code>ls</code>, <code>cat</code>, <code>date</code> are also supported</li>
            </ul>
          </div>
        </div>
      </template>
    </Card>
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
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
}

.loading-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 40px;
}

.terminal-disabled {
  padding: 20px;
}

.terminal-disabled-message {
  text-align: center;
}

.terminal-description {
  margin-bottom: 20px;
}

.terminal-tips {
  margin-top: 20px;
  background-color: #f8f9fa;
  border-radius: 6px;
  padding: 15px;
}

.terminal-tips h4 {
  margin-top: 0;
  margin-bottom: 10px;
  color: #495057;
}

.terminal-tips ul {
  margin: 0;
  padding-left: 20px;
}

.terminal-tips li {
  margin-bottom: 5px;
  color: #495057;
}
</style>
