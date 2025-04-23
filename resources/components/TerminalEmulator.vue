<template>
  <div class="terminal-container">
    <div class="terminal-header">
      <div class="terminal-title">WordPress Debug Terminal</div>
      <div class="terminal-controls">
        <Button icon="pi pi-copy" @click="copyOutput" severity="secondary" text />
        <Button icon="pi pi-trash" @click="clearTerminal" severity="secondary" text />
      </div>
    </div>
    <div class="terminal-window" ref="terminalWindow">
      <div class="terminal-output">
        <div v-for="(line, index) in outputLines" :key="index" class="terminal-line" :class="{ 'terminal-error': line.type === 'error', 'terminal-success': line.type === 'success', 'terminal-info': line.type === 'info' }">
          <span v-html="formatOutput(line.text)"></span>
        </div>
      </div>
      <div class="terminal-input-line">
        <span class="terminal-prompt">wp-debug&gt;</span>
        <input
          ref="commandInput"
          type="text"
          class="terminal-input"
          v-model="currentCommand"
          @keydown.enter="executeCommand"
          @keydown.up="navigateHistory(-1)"
          @keydown.down="navigateHistory(1)"
          @keydown.tab.prevent="autocompleteCommand"
          placeholder="Type a command or 'help' for available commands!"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue';
import { useToast } from 'primevue/usetoast';
import { $post } from '../request';

const toast = useToast();
const terminalWindow = ref(null);
const commandInput = ref(null);
const outputLines = ref([
  { text: 'WordPress Debug Terminal v1.2.0', type: 'info' },
  { text: 'Type <strong>help</strong> to see available commands', type: 'info' },
  { text: 'Type <strong>wp</strong> to see WP-CLI style commands', type: 'info' },
  { text: '', type: 'info' }
]);
const currentCommand = ref('');
const commandHistory = ref([]);
const historyPosition = ref(-1);
const isExecuting = ref(false);

// Available commands for autocomplete
const availableCommands = [
  'help',
  'clear',
  'wp',
  'wp:core',
  'wp:core:version',
  'wp:core:check',
  'wp:plugin',
  'wp:plugin:list',
  'wp:plugin:info',
  'wp:theme',
  'wp:theme:list',
  'wp:log',
  'wp:log:tail',
  'wp:log:search',
  'wp:log:stats',
  'wp:hook',
  'wp:hook:list',
  'wp:cron',
  'wp:cron:list',
  'wp:option',
  'wp:option:list',
  'wp:db',
  'wp:db:tables',
  'wp:db:size',
  'wp:db:prefix',
  'wp:db:query',
  'wp:db:optimize',
  'wp:db:columns',
  'php',
  'php:info',
  'php:memory',
  'ls',
  'cat',
  'date',
  'pwd'
];

// Command descriptions for help
const commandDescriptions = {
  'help': 'Show available commands',
  'clear': 'Clear the terminal screen',
  'wp': 'WordPress CLI commands',
  'wp core': 'WordPress core commands',
  'wp core version': 'Show WordPress version information',
  'wp core check': 'Check WordPress core files and configuration',
  'wp plugin': 'WordPress plugin commands',
  'wp plugin list': 'List all installed plugins',
  'wp plugin info [slug]': 'Show detailed information about a plugin',
  'wp theme': 'WordPress theme commands',
  'wp theme list': 'List all installed themes',
  'wp log': 'WordPress log commands',
  'wp log tail [lines=10]': 'Show the last N lines of the debug log',
  'wp log search [term]': 'Search the debug log for a specific term',
  'wp log stats': 'Show error statistics from the debug log',
  'wp hook': 'WordPress hook commands',
  'wp hook list [hook]': 'List actions/filters attached to a specific hook',
  'wp cron': 'WordPress cron commands',
  'wp cron list': 'List scheduled cron jobs',
  'wp option': 'WordPress option commands',
  'wp option list': 'List autoloaded options',
  'wp db': 'WordPress database commands',
  'wp db tables': 'List database tables with row counts',
  'wp db size': 'Show database size information',
  'wp db prefix': 'Show the database table prefix',
  'wp db query [sql]': 'Execute a SQL query (SELECT only)',
  'wp db optimize': 'Optimize database tables',
  'wp db columns [table]': 'Show columns in a database table',
  'php': 'PHP commands',
  'php info': 'Show PHP configuration information',
  'php memory': 'Show memory usage statistics',
  'ls': 'List directory contents',
  'cat [file]': 'Display file contents',
  'date': 'Show current date and time',
  'pwd': 'Show current directory'
};

onMounted(() => {
  // Focus the input when component is mounted
  commandInput.value.focus();
});

function formatOutput(text) {
  // Convert URLs to clickable links
  return text.replace(/(https?:\/\/[^\s]+)/g, '<a href="$1" target="_blank">$1</a>');
}

function scrollToBottom() {
  nextTick(() => {
    if (terminalWindow.value) {
      terminalWindow.value.scrollTop = terminalWindow.value.scrollHeight;
    }
  });
}

function addOutput(text, type = 'normal') {
    console.log('text',text)
    console.log('text',text)
  outputLines.value.push({ text, type });
  scrollToBottom();
}

function clearTerminal() {
  outputLines.value = [
    { text: 'Terminal cleared', type: 'info' },
    { text: '', type: 'info' }
  ];
}

function copyOutput() {
  const text = outputLines.value.map(line => line.text.replace(/<[^>]*>/g, '')).join('\n');
  navigator.clipboard.writeText(text).then(() => {
    toast.add({ severity: 'success', summary: 'Copied', detail: 'Terminal output copied to clipboard', life: 3000 });
  });
}

function navigateHistory(direction) {
  if (commandHistory.value.length === 0) return;

  historyPosition.value += direction;

  if (historyPosition.value < 0) {
    historyPosition.value = -1;
    currentCommand.value = '';
    return;
  }

  if (historyPosition.value >= commandHistory.value.length) {
    historyPosition.value = commandHistory.value.length - 1;
  }

  currentCommand.value = commandHistory.value[historyPosition.value];
}

function autocompleteCommand() {
  if (!currentCommand.value) return;

  const matchingCommands = availableCommands.filter(cmd =>
    cmd.startsWith(currentCommand.value.split(' ')[0])
  );

  if (matchingCommands.length === 1) {
    // If there's only one match, autocomplete it
    currentCommand.value = matchingCommands[0];
  } else if (matchingCommands.length > 1) {
    // If there are multiple matches, show them
    addOutput('Available commands:', 'info');
    matchingCommands.forEach(cmd => {
      addOutput(`  ${cmd}`, 'info');
    });
  }
}

async function executeCommand() {
  if (!currentCommand.value.trim() || isExecuting.value) return;

  const command = currentCommand.value.trim();

  // Add command to history
  commandHistory.value.unshift(command);
  if (commandHistory.value.length > 50) {
    commandHistory.value.pop();
  }
  historyPosition.value = -1;

  // Echo the command
  addOutput(`<span class="terminal-prompt">wp-debug&gt;</span> ${command}`, 'command');

  // Clear the input
  currentCommand.value = '';

  // Handle built-in commands
  if (command === 'clear') {
    clearTerminal();
    return;
  }

  if (command === 'help') {
    addOutput('<strong>Available Commands:</strong>', 'info');
    addOutput('', 'info');

    // Group commands by category
    const categories = {
      'WordPress Core': Object.entries(commandDescriptions).filter(([cmd]) => cmd.startsWith('wp core')),
      'WordPress Plugins': Object.entries(commandDescriptions).filter(([cmd]) => cmd.startsWith('wp plugin')),
      'WordPress Themes': Object.entries(commandDescriptions).filter(([cmd]) => cmd.startsWith('wp theme')),
      'WordPress Logs': Object.entries(commandDescriptions).filter(([cmd]) => cmd.startsWith('wp log')),
      'WordPress Hooks': Object.entries(commandDescriptions).filter(([cmd]) => cmd.startsWith('wp hook')),
      'WordPress Cron': Object.entries(commandDescriptions).filter(([cmd]) => cmd.startsWith('wp cron')),
      'WordPress Options': Object.entries(commandDescriptions).filter(([cmd]) => cmd.startsWith('wp option')),
      'WordPress Database': Object.entries(commandDescriptions).filter(([cmd]) => cmd.startsWith('wp db')),
      'PHP': Object.entries(commandDescriptions).filter(([cmd]) => cmd.startsWith('php')),
      'Shell': Object.entries(commandDescriptions).filter(([cmd]) => !cmd.startsWith('wp') && !cmd.startsWith('php') && cmd !== 'help' && cmd !== 'clear'),
      'General': Object.entries(commandDescriptions).filter(([cmd]) => cmd === 'help' || cmd === 'clear' || cmd === 'wp')
    };

    // Display commands by category
    Object.entries(categories).forEach(([category, commands]) => {
      if (commands.length > 0) {
        addOutput(`<strong>${category}:</strong>`, 'info');
        commands.forEach(([cmd, desc]) => {
          addOutput(`  <strong>${cmd}</strong>: ${desc}`, 'info');
        });
        addOutput('', 'info');
      }
    });

    addOutput('Use <strong>Tab</strong> key for command auto-completion', 'info');
    addOutput('Commands can be entered in the format <strong>wp command subcommand</strong> or <strong>wp:command:subcommand</strong>', 'info');
    return;
  }

  // Execute the command on the server
  isExecuting.value = true;
  try {
    // Special handling for SQL queries to preserve quotes
    let processedCommand = command;
    if (command.toLowerCase().includes('select') &&
        (command.toLowerCase().includes('from') || command.toLowerCase().includes('where'))) {
      // This is likely an SQL query, preserve the quotes
      processedCommand = command.replace(/'/g, "'").replace(/"/g, '"');
    }

    const args = {
      route: 'terminal_command',
      command: processedCommand,
      nonce: window.dlct_wpdebuglog.nonce // Explicitly include nonce for security
    };

    const { data, error } = await $post(args);

    if (error) {
      addOutput(`Error: ${error}`, 'error');
    } else if (data && data.value) {
      const response = data.value.data;

      if (response.success) {
        if (Array.isArray(response.output)) {
          response.output.forEach(line => {
            addOutput(line, response.type || 'normal');
          });
        } else {
          addOutput(response.output, response.type || 'normal');
        }
      } else {
        addOutput(`Error: ${response.message || 'Unknown error'}`, 'error');
      }
    }
  } catch (err) {
    addOutput(`Error: ${err.message || 'Unknown error'}`, 'error');
  } finally {
    isExecuting.value = false;
    addOutput('', 'info'); // Add an empty line for better readability
  }
}
</script>

<style scoped>
.terminal-container {
  display: flex;
  flex-direction: column;
  height: 500px;
  border-radius: 8px;
  overflow: hidden;
  background-color: #1e1e1e;
  font-family: 'Courier New', monospace;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  margin-bottom: 20px;
}

.terminal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background-color: #333;
  padding: 8px 16px;
  border-bottom: 1px solid #444;
}

.terminal-title {
  color: #fff;
  font-weight: bold;
}

.terminal-controls {
  display: flex;
  gap: 8px;
}

.terminal-window {
  flex: 1;
  padding: 16px;
  overflow-y: auto;
  color: #f0f0f0;
}

.terminal-output {
  margin-bottom: 16px;
}

.terminal-line {
  line-height: 1.5;
  white-space: pre-wrap;
  word-break: break-word;
}

.terminal-error {
  color: #ff6b6b;
}

.terminal-success {
  color: #69db7c;
}

.terminal-info {
  color: #74c0fc;
}

.terminal-input-line {
  display: flex;
  align-items: center;
}

.terminal-prompt {
  color: #69db7c;
  margin-right: 8px;
  font-weight: bold;
}

.terminal-input {
  flex: 1;
  background-color: transparent;
  border: none;
  color: #f0f0f0;
  font-family: 'Courier New', monospace;
  font-size: 1em;
  outline: none;
  caret-color: #f0f0f0;
}

a {
  color: #4dabf7;
  text-decoration: underline;
}
</style>
