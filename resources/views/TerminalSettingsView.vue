<template>
  <div class="terminal-settings-view">
    <Card>
      <template #title>
        Terminal Settings
      </template>
      <template #subtitle>
        Configure terminal access and database commands
      </template>
      <template #content>
        <div v-if="state.isLoading" class="loading-container">
          <ProgressSpinner />
          <p>Loading settings...</p>
        </div>
        
        <div v-else class="settings-container">
          <div class="setting-item">
            <div class="setting-info">
              <h3>Enable Terminal</h3>
              <p>Allow access to the WordPress Debug Terminal</p>
            </div>
            <div class="setting-control">
              <InputSwitch v-model="state.terminalEnabled" @change="updateSettings" />
            </div>
          </div>
          
          <Divider />
          
          <div class="setting-item">
            <div class="setting-info">
              <h3>Enable Database Commands</h3>
              <p>Allow super administrators to execute database commands in the terminal</p>
              <Message severity="warn">
                <span>Database commands can only be executed by super administrators for security reasons.</span>
              </Message>
            </div>
            <div class="setting-control">
              <InputSwitch v-model="state.dbCommandsEnabled" @change="updateSettings" :disabled="!state.terminalEnabled" />
            </div>
          </div>
          
          <Divider />
          
          <div class="terminal-info">
            <h3>Available Commands</h3>
            <p>The terminal supports WP-CLI style commands. Type <code>wp</code> to see available WordPress commands.</p>
            
            <h4>Examples:</h4>
            <ul>
              <li><code>wp core version</code> - Show WordPress version</li>
              <li><code>wp plugin list</code> - List installed plugins</li>
              <li><code>wp theme list</code> - List installed themes</li>
              <li><code>wp db tables</code> - List database tables (requires database commands enabled)</li>
            </ul>
            
            <h4>Command Structure:</h4>
            <p>Commands follow the WP-CLI structure: <code>wp &lt;command&gt; &lt;subcommand&gt; [options]</code></p>
            <p>You can also use the colon syntax: <code>wp:command:subcommand [options]</code></p>
          </div>
        </div>
      </template>
    </Card>
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

.settings-container {
  padding: 10px;
}

.setting-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.setting-info {
  flex: 1;
}

.setting-info h3 {
  margin-top: 0;
  margin-bottom: 10px;
}

.setting-info p {
  margin-top: 0;
  color: #666;
}

.setting-control {
  margin-left: 20px;
}

.terminal-info {
  background-color: #f8f9fa;
  border-radius: 6px;
  padding: 20px;
  margin-top: 20px;
}

.terminal-info h3 {
  margin-top: 0;
  margin-bottom: 15px;
}

.terminal-info h4 {
  margin-top: 20px;
  margin-bottom: 10px;
}

.terminal-info ul {
  margin: 0;
  padding-left: 20px;
}

.terminal-info li {
  margin-bottom: 5px;
}

code {
  background-color: #e9ecef;
  padding: 2px 6px;
  border-radius: 4px;
  font-family: monospace;
}
</style>
