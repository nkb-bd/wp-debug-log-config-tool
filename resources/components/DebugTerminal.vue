<template>
    <div class="debug-terminal" @click="focusInput">
        <div class="terminal-header">
            <h3>WordPress Debug Terminal</h3>
            <div class="terminal-actions">
                <Button icon="pi pi-copy" class="p-button-sm" @click="copyTerminalContent" v-tooltip.top="'Copy output'" />
                <Button icon="pi pi-trash" class="p-button-sm" @click="clearTerminal" v-tooltip.top="'Clear terminal'" />
            </div>
        </div>

        <div class="terminal-window" ref="terminalWindow">
            <div v-for="(line, index) in terminalOutput" :key="index"
                 class="terminal-line"
                 :class="{ 'command-line': line.type === 'command', 'error-line': line.type === 'error', 'success-line': line.type === 'success' }">
                <span v-if="line.type === 'command'" class="prompt">wp-debug></span>
                <span>{{ stripTerminalMarkup(line.content) }}</span>
            </div>
        </div>

        <div class="terminal-input-container">
            <span class="prompt">wp-debug></span>
            <input
                    ref="commandInput"
                    type="text"
                    v-model="currentCommand"
                    @keyup.enter="executeCommand"
                    @keyup.up="navigateHistory(-1)"
                    @keyup.down="navigateHistory(1)"
                    @keyup.tab.prevent="autocompleteCommand"
                    placeholder="Type a command (or 'help')"
                    class="terminal-input"
            />
        </div>
    </div>
</template>

<script setup>
    import { ref, onMounted, nextTick, unref } from 'vue';
    import { useToast } from 'primevue/usetoast';
    import { $post } from '../request';

    const toast = useToast();
    const terminalWindow = ref(null);
    const commandInput = ref(null);
    const terminalOutput = ref([
        { content: 'WordPress Debug Terminal v2.0.0', type: 'info' },
        { content: 'Type help to see available commands', type: 'info' },
        { content: 'Type wp to see WP-CLI style commands', type: 'info' },
        { content: '', type: 'info' }
    ]);
    const currentCommand = ref('');
    const commandHistory = ref([]);
    const historyIndex = ref(-1);

    // Available commands for autocomplete and help
    const availableCommands = {
        // General commands
        'help': 'Show available commands',
        'clear': 'Clear the terminal',
        'wp': 'WordPress CLI commands',

        // WordPress Core commands
        'wp core': 'WordPress core commands',
        'wp core version': 'Show WordPress version information',
        'wp core check': 'Check WordPress core files and configuration',

        // WordPress Plugin commands
        'wp plugin': 'WordPress plugin commands',
        'wp plugin list': 'List all installed plugins',
        'wp plugin info [slug]': 'Show detailed information about a plugin',

        // WordPress Theme commands
        'wp theme': 'WordPress theme commands',
        'wp theme list': 'List all installed themes',

        // WordPress Log commands
        'wp log': 'WordPress log commands',
        'wp log tail [lines=10]': 'Show the last N lines of debug log',
        'wp log search [term]': 'Search the debug log for a specific term',
        'wp log stats': 'Show error statistics from the debug log',

        // WordPress Hook commands
        'wp hook': 'WordPress hook commands',
        'wp hook list [hook]': 'List actions/filters attached to a specific hook',

        // WordPress Cron commands
        'wp cron': 'WordPress cron commands',
        'wp cron list': 'List scheduled cron jobs',

        // WordPress Option commands
        'wp option': 'WordPress option commands',
        'wp option list': 'List autoloaded options',

        // WordPress Database commands
        'wp db': 'WordPress database commands',
        'wp db tables': 'List database tables with row counts',
        'wp db size': 'Show database size information',
        'wp db prefix': 'Show the database table prefix',
        'wp db query [sql]': 'Execute a SQL query (SELECT only)',
        'wp db optimize': 'Optimize database tables',
        'wp db columns [table]': 'Show columns in a database table',

        // PHP commands
        'php': 'PHP commands',
        'php info': 'Show PHP configuration information',
        'php memory': 'Show memory usage statistics',

        // Shell commands
        'ls': 'List directory contents',
        'cat [file]': 'Display file contents',
        'date': 'Show current date and time',
        'pwd': 'Show current directory'
    };

    onMounted(() => {
        // Focus the input when component is mounted
        focusInput();

        // Add a small delay to ensure focus works even after page transitions
        setTimeout(focusInput, 100);
    });

    function stripTerminalMarkup(content) {
        return String(content || '').replace(/<[^>]*>/g, '');
    }

    // Function to focus the input field
    function focusInput() {
        if (commandInput.value) {
            commandInput.value.focus();
        }
    }

    // Function to safely render HTML content
    function sanitizeHtml(html) {
        if (!html) return '';

        // If it's not a string, convert it to a string
        if (typeof html !== 'string') {
            try {
                html = String(html);
            } catch (e) {
                console.error('Error converting to string:', e);
                return '';
            }
        }

        // Handle escaped HTML entities (like \/ and \")
        html = html.replace(/\\\//g, '/').replace(/\\"/g, '"');

        // Handle Unicode escape sequences like \u2022 (bullet point)
        html = html.replace(/\\u([0-9a-fA-F]{4})/g, (match, hex) => {
            return String.fromCharCode(parseInt(hex, 16));
        });

        try {
            // Simple sanitization - this is not comprehensive but helps with basic formatting
            // For production, consider using a dedicated library like DOMPurify

            // Replace potentially dangerous tags
            let sanitized = html
                .replace(/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi, '')
                .replace(/<iframe\b[^<]*(?:(?!<\/iframe>)<[^<]*)*<\/iframe>/gi, '')
                .replace(/<object\b[^<]*(?:(?!<\/object>)<[^<]*)*<\/object>/gi, '')
                .replace(/<embed\b[^<]*(?:(?!<\/embed>)<[^<]*)*<\/embed>/gi, '');

            console.log('Sanitized HTML:', sanitized); // Debug log
            return sanitized;
        } catch (e) {
            console.error('Error sanitizing HTML:', e);
            return html.replace(/</g, '&lt;').replace(/>/g, '&gt;');
        }
    }

    function scrollToBottom() {
        nextTick(() => {
            if (terminalWindow.value) {
                terminalWindow.value.scrollTop = terminalWindow.value.scrollHeight;
            }
        });
    }

    function addOutput(content, type = 'output') {
        // Handle different content types
        let processedContent;

        // If content is undefined or null, use empty string
        if (content === undefined || content === null) {
            processedContent = '';
            console.warn('Undefined or null content passed to addOutput');
        }
        // If content is an object, stringify it
        else if (typeof content === 'object') {
            try {
                processedContent = JSON.stringify(content, null, 2);
            } catch (e) {
                processedContent = '[Object]';
                console.error('Failed to stringify object:', e, content);
            }
        }
        // If content is a string, process it
        else if (typeof content === 'string') {
            // Handle escaped HTML entities and Unicode characters
            processedContent = content
                .replace(/\\\//g, '/') // Replace \/ with /
                .replace(/\\"/g, '"') // Replace \" with "
                .replace(/\\u([0-9a-fA-F]{4})/g, (match, hex) => {
                    return String.fromCharCode(parseInt(hex, 16)); // Convert Unicode escape sequences
                });
        }
        // For any other type, convert to string
        else {
            processedContent = String(content);
        }

        // Add the processed content to the terminal output
        terminalOutput.value.push({ content: processedContent, type });
        scrollToBottom();

        // Debug log
        console.log(`Terminal output (${type}):`, processedContent);
    }

    function clearTerminal() {
        terminalOutput.value = [
            { content: 'Terminal cleared', type: 'info' },
            { content: '', type: 'info' }
        ];
    }

    function copyTerminalContent() {
        // Strip HTML tags for plain text copy
        const plainText = terminalOutput.value
            .map(line => {
                let text = line.content.replace(/<[^>]*>/g, '');
                return line.type === 'command' ? `wp-debug> ${text}` : text;
            })
            .join('\n');

        navigator.clipboard.writeText(plainText)
            .then(() => {
                toast.add({
                    severity: 'success',
                    summary: 'Copied',
                    detail: 'Terminal content copied to clipboard',
                    life: 3000
                });
            })
            .catch(err => {
                toast.add({
                    severity: 'error',
                    summary: 'Error',
                    detail: 'Failed to copy content',
                    life: 3000
                });
            });
    }

    function navigateHistory(direction) {
        if (commandHistory.value.length === 0) return;

        historyIndex.value += direction;

        if (historyIndex.value < 0) {
            historyIndex.value = -1;
            currentCommand.value = '';
            return;
        }

        if (historyIndex.value >= commandHistory.value.length) {
            historyIndex.value = commandHistory.value.length - 1;
        }

        currentCommand.value = commandHistory.value[historyIndex.value];
    }

    function autocompleteCommand() {
        if (!currentCommand.value) return;

        const input = currentCommand.value;

        // Handle WP-CLI style commands with colon syntax
        if (input.includes(':')) {
            const parts = input.split(':');
            const prefix = parts.slice(0, -1).join(':');
            const lastPart = parts[parts.length - 1];

            // Find commands that match the prefix
            const matchingCommands = Object.keys(availableCommands).filter(cmd => {
                const cmdParts = cmd.split(' ');
                const cmdPrefix = cmdParts.join(':');
                return cmdPrefix.startsWith(input);
            });

            if (matchingCommands.length === 1) {
                // If there's only one match, autocomplete it (convert spaces to colons)
                currentCommand.value = matchingCommands[0].replace(/ /g, ':');
            } else if (matchingCommands.length > 1) {
                // If there are multiple matches, show them
                addOutput('Possible commands:', 'info');
                matchingCommands.forEach(cmd => {
                    addOutput(`  ${cmd.replace(/ /g, ':')}`, 'info');
                });
            }
            return;
        }

        // Handle space-separated commands
        const cmdParts = input.split(' ');

        // Find commands that match the current input
        const matches = Object.keys(availableCommands).filter(cmd => {
            const cmdParts = cmd.split(' ');
            const inputParts = input.split(' ');

            // Check if all parts of the input match the beginning of the command parts
            if (inputParts.length > cmdParts.length) return false;

            for (let i = 0; i < inputParts.length; i++) {
                if (i === inputParts.length - 1) {
                    // The last part of the input should be a prefix of the command part
                    if (!cmdParts[i].startsWith(inputParts[i])) return false;
                } else {
                    // All other parts should match exactly
                    if (cmdParts[i] !== inputParts[i]) return false;
                }
            }

            return true;
        });

        if (matches.length === 1) {
            // If there's only one match, autocomplete it
            currentCommand.value = matches[0];
        } else if (matches.length > 1) {
            // If there are multiple matches, show them
            addOutput('Possible commands:', 'info');
            matches.forEach(cmd => {
                addOutput(`  ${cmd}`, 'info');
            });
        }
    }

    async function executeCommand() {
        const command = currentCommand.value.trim();
        if (!command) return;

        // Add to history
        commandHistory.value.unshift(command);
        if (commandHistory.value.length > 30) {
            commandHistory.value.pop();
        }
        historyIndex.value = -1;

        // Show the command
        addOutput(command, 'command');

        // Clear input
        currentCommand.value = '';

        // Handle client-side commands
        if (command === 'clear') {
            clearTerminal();
            return;
        }

        if (command === 'help') {
            addOutput('<strong>Available Commands:</strong>', 'info');
            addOutput('', 'info');

            // Group commands by category
            const categories = {
                'General': Object.entries(availableCommands).filter(([cmd]) => cmd === 'help' || cmd === 'clear' || cmd === 'wp'),
                'WordPress Core': Object.entries(availableCommands).filter(([cmd]) => cmd.startsWith('wp core')),
                'WordPress Plugins': Object.entries(availableCommands).filter(([cmd]) => cmd.startsWith('wp plugin')),
                'WordPress Themes': Object.entries(availableCommands).filter(([cmd]) => cmd.startsWith('wp theme')),
                'WordPress Logs': Object.entries(availableCommands).filter(([cmd]) => cmd.startsWith('wp log')),
                'WordPress Hooks': Object.entries(availableCommands).filter(([cmd]) => cmd.startsWith('wp hook')),
                'WordPress Cron': Object.entries(availableCommands).filter(([cmd]) => cmd.startsWith('wp cron')),
                'WordPress Options': Object.entries(availableCommands).filter(([cmd]) => cmd.startsWith('wp option')),
                'WordPress Database': Object.entries(availableCommands).filter(([cmd]) => cmd.startsWith('wp db')),
                'PHP': Object.entries(availableCommands).filter(([cmd]) => cmd.startsWith('php')),
                'Shell': Object.entries(availableCommands).filter(([cmd]) => !cmd.startsWith('wp') && !cmd.startsWith('php') && cmd !== 'help' && cmd !== 'clear')
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

        // Send command to server
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

            // Send the request
            const response = await $post(args);
            console.log('Terminal response:', response); // Debug log

            // Use Vue's official unref utility to unwrap reactive objects
            const responseData = unref(response.data);
            const responseError = unref(response.error);

            // Check if we have an error in the response
            if (responseError) {
                let errorMessage = 'Unknown error';

                if (typeof responseError === 'string') {
                    errorMessage = responseError;
                } else if (responseError instanceof Error) {
                    errorMessage = responseError.message;
                } else if (typeof responseError === 'object' && responseError !== null) {
                    try {
                        errorMessage = JSON.stringify(responseError);
                    } catch (e) {
                        errorMessage = 'Error object could not be stringified';
                    }
                }

                addOutput(`Error: ${errorMessage}`, 'error');
                return;
            }

            // Check if we have data
            if (!responseData) {
                addOutput('Error: No data received from server', 'error');
                return;
            }

            // Process the response data
            // Check if the response contains nested data structure
            if (responseData.data && (responseData.success === false || responseData.success === true)) {
                // Format appropriate to your API structure
                if (responseData.success) {
                    // Handle success case
                    const output = responseData.data.output || responseData.data.message || 'Command executed successfully';
                    const type = responseData.data.type || 'success';

                    if (Array.isArray(output)) {
                        output.forEach(item => addOutput(item, type));
                    } else {
                        addOutput(output, type);
                    }
                } else {
                    // Handle error case from data
                    const message = responseData.data.message || 'Command execution failed';
                    addOutput(`Error: ${message}`, 'error');
                }
            }
            // Direct success/failure response
            else if (responseData.success === false || responseData.success === true) {
                if (responseData.success) {
                    // Success case
                    if (Array.isArray(responseData.output)) {
                        responseData.output.forEach(line => {
                            if (line === null || line === undefined) {
                                addOutput('', responseData.type || 'output');
                            } else {
                                addOutput(line, responseData.type || 'output');
                            }
                        });
                    } else if (responseData.output !== undefined) {
                        addOutput(responseData.output, responseData.type || 'output');
                    } else if (responseData.message) {
                        addOutput(responseData.message, 'success');
                    } else {
                        addOutput('Command executed successfully (no output)', 'success');
                    }
                } else {
                    // Error case
                    const message = responseData.message || 'Command execution failed';
                    addOutput(`Error: ${message}`, 'error');
                }
            }
            // Fallback for unexpected response structure
            else {
                try {
                    const outputText = typeof responseData === 'object'
                        ? JSON.stringify(responseData, null, 2)
                        : String(responseData);

                    addOutput(outputText, 'output');
                } catch (e) {
                    addOutput('Received response in unknown format', 'error');
                    console.error('Response format error:', e, responseData);
                }
            }
        } catch (err) {
            // Handle request errors
            let errorMessage = 'Unknown error occurred';

            if (typeof err === 'string') {
                errorMessage = err;
            } else if (err instanceof Error) {
                errorMessage = err.message || 'Error without message';
            } else if (typeof err === 'object' && err !== null) {
                try {
                    errorMessage = JSON.stringify(err);
                } catch (e) {
                    errorMessage = 'Error object could not be stringified';
                }
            }

            addOutput(`Error: ${errorMessage}`, 'error');
            console.error('Terminal command error:', err);
        }

        // Add empty line for readability
        addOutput('', 'info');

        // Focus back on input
        focusInput();
    }
</script>

<style scoped>
    .debug-terminal {
        display: flex;
        flex-direction: column;
        height: min(520px, calc(100vh - 250px));
        min-height: 360px;
        border-radius: 6px;
        overflow: hidden;
        background-color: #1e1e1e;
        color: #f0f0f0;
        font-family: 'Courier New', monospace;
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.14);
    }

    .terminal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background-color: #2d2d2d;
        padding: 8px 12px;
        border-bottom: 1px solid #444;
    }

    .terminal-header h3 {
        margin: 0;
        color: #e2e2e2;
        font-size: 0.92rem;
    }

    .terminal-actions {
        display: flex;
        gap: 8px;
    }

    .terminal-window {
        flex: 1;
        padding: 12px;
        overflow-y: auto;
        line-height: 1.4;
    }

    .terminal-line {
        margin-bottom: 2px;
        white-space: pre-wrap;
        word-break: break-word;
    }

    .command-line {
        color: #64b5f6;
        font-weight: bold;
    }

    .error-line {
        color: #ff5252;
    }

    .success-line {
        color: #69f0ae;
    }

    .terminal-input-container {
        display: flex;
        align-items: center;
        padding: 8px 12px;
        background-color: #252525;
        border-top: 1px solid #444;
    }

    .prompt {
        color: #64b5f6;
        margin-right: 8px;
        font-weight: bold;
    }

    .terminal-input {
        flex: 1;
        background-color: transparent;
        border: none;
        color: #f0f0f0;
        font-family: 'Courier New', monospace;
        font-size: 0.95em;
        outline: none;
    }

    /* Style links in terminal output */
    :deep(a) {
        color: #64b5f6;
        text-decoration: underline;
    }
</style>
