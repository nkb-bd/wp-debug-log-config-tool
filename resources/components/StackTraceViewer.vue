<template>
    <div class="stack-trace-viewer">
        <div v-if="!stackTrace || stackTrace.length === 0" class="no-stack-trace">
            No stack trace available
        </div>
        <div v-else class="stack-trace-container">
            <div class="stack-trace-header">
                <h3>Stack Trace</h3>
                <Button @click="copyToClipboard" size="small" icon="pi pi-copy" label="Copy" severity="secondary"/>
            </div>
            <div class="stack-trace-content">
                <ul class="stack-frames">
                    <li v-for="(frame, index) in parsedStackTrace" :key="index"
                        :class="{ 'highlight-frame': frame.isHighlighted }"
                        @click="toggleFrameDetails(index)">
                        <div class="frame-header">
                            <span class="frame-number">{{ index + 1 }}</span>
                            <span class="frame-file">{{ frame.file }}</span>
                            <span class="frame-line">:{{ frame.line }}</span>
                            <span class="frame-function">{{ frame.function }}</span>
                        </div>
                        <div v-if="expandedFrames[index]" class="frame-details">
                            <div v-if="frame.args && frame.args.length > 0" class="frame-args">
                                <div class="args-header">Arguments:</div>
                                <div v-for="(arg, argIndex) in frame.args" :key="argIndex" class="arg-item">
                                    <span class="arg-index">{{ argIndex + 1 }}:</span>
                                    <span class="arg-value">{{ arg }}</span>
                                </div>
                            </div>
                            <div v-if="frame.context && frame.context.length > 0" class="frame-context">
                                <div class="context-header">Code Context:</div>
                                <pre class="context-code">{{ frame.context.join('\n') }}</pre>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useToast } from 'primevue/usetoast';

const props = defineProps({
    stackTrace: {
        type: Array,
        default: () => []
    }
});

const toast = useToast();
const expandedFrames = ref({});

// Parse the stack trace into a more structured format
const parsedStackTrace = computed(() => {
    if (!props.stackTrace || props.stackTrace.length === 0) {
        return [];
    }

    return props.stackTrace.map((frame, index) => {
        // Parse the frame string to extract file, line, function, etc.
        const parsedFrame = parseStackFrame(frame);

        // Highlight WordPress core frames differently
        parsedFrame.isHighlighted = isWordPressCore(parsedFrame.file);

        return parsedFrame;
    });
});

// Parse a stack frame string into components
function parseStackFrame(frameString) {
    console.log('Parsing frame:', frameString);

    // Default structure if parsing fails
    const defaultFrame = {
        file: 'Unknown',
        line: 0,
        function: frameString,
        args: [],
        context: []
    };

    try {
        // Handle variable dump lines
        if (frameString.startsWith('$') || frameString.startsWith('Variable dump')) {
            defaultFrame.file = 'Variable dump';
            defaultFrame.function = frameString;
            defaultFrame.isHighlighted = true;
            return defaultFrame;
        }

        // Handle the "thrown in" line differently
        if (frameString.includes('thrown in')) {
            const thrownMatch = frameString.match(/thrown in ([^\s]+) on line (\d+)/);
            if (thrownMatch && thrownMatch.length >= 3) {
                defaultFrame.file = thrownMatch[1];
                defaultFrame.line = parseInt(thrownMatch[2], 10);
                defaultFrame.function = 'Exception thrown';
                defaultFrame.isHighlighted = true;
                return defaultFrame;
            }
        }

        // Handle standard stack trace format
        // Example: "#0 /path/to/file.php(123): SomeClass->someMethod(arg1, arg2)"
        const frameNumberMatch = frameString.match(/^#(\d+)\s+/);
        if (frameNumberMatch) {
            // Remove the frame number prefix for further parsing
            const withoutNumber = frameString.replace(/^#\d+\s+/, '');

            // Try to parse common stack trace formats
            // Example: "/path/to/file.php(123): SomeClass->someMethod(arg1, arg2)"
            const fileMatch = withoutNumber.match(/^(.*?)\((\d+)\):/);
            const functionMatch = withoutNumber.match(/\): (.*?)(\(.*\))?$/);

            if (fileMatch && fileMatch.length >= 3) {
                defaultFrame.file = fileMatch[1];
                defaultFrame.line = parseInt(fileMatch[2], 10);
            }

            if (functionMatch && functionMatch.length >= 2) {
                defaultFrame.function = functionMatch[1];

                // Try to extract arguments if present
                if (functionMatch[2]) {
                    const argsString = functionMatch[2].replace(/^\(|\)$/g, '');
                    defaultFrame.args = argsString.split(',').map(arg => arg.trim());
                }
            }

            // If we couldn't parse the function, use the whole string after the file part
            if (!functionMatch && withoutNumber.includes(':')) {
                const parts = withoutNumber.split(':');
                if (parts.length >= 2) {
                    defaultFrame.function = parts.slice(1).join(':').trim();
                }
            }
        } else {
            // Try to handle other formats
            // Example: "/path/to/file.php(123): SomeClass->someMethod(arg1, arg2)"
            const fileMatch = frameString.match(/^(.*?)\((\d+)\):/);
            if (fileMatch && fileMatch.length >= 3) {
                defaultFrame.file = fileMatch[1];
                defaultFrame.line = parseInt(fileMatch[2], 10);

                const functionMatch = frameString.match(/\): (.*?)(\(.*\))?$/);
                if (functionMatch && functionMatch.length >= 2) {
                    defaultFrame.function = functionMatch[1];

                    // Try to extract arguments if present
                    if (functionMatch[2]) {
                        const argsString = functionMatch[2].replace(/^\(|\)$/g, '');
                        defaultFrame.args = argsString.split(',').map(arg => arg.trim());
                    }
                }
            } else if (frameString.includes('=')) {
                // This might be a variable dump line
                const parts = frameString.split('=');
                if (parts.length >= 2) {
                    defaultFrame.file = 'Variable';
                    defaultFrame.function = frameString;
                    defaultFrame.isHighlighted = true;
                }
            } else {
                // If all else fails, just use the raw string
                defaultFrame.function = frameString;
            }
        }
    } catch (e) {
        console.error('Error parsing stack frame:', e);
    }

    console.log('Parsed frame:', defaultFrame);
    return defaultFrame;
}

// Check if a file path is part of WordPress core
function isWordPressCore(filePath) {
    return filePath.includes('/wp-includes/') ||
           filePath.includes('/wp-admin/') ||
           filePath.includes('/wp-content/');
}

// Toggle the expanded state of a frame
function toggleFrameDetails(frameIndex) {
    expandedFrames.value = {
        ...expandedFrames.value,
        [frameIndex]: !expandedFrames.value[frameIndex]
    };
}

// Copy the stack trace to clipboard
function copyToClipboard() {
    const text = props.stackTrace.join('\n');
    navigator.clipboard.writeText(text).then(() => {
        toast.add({
            severity: 'success',
            summary: 'Copied',
            detail: 'Stack trace copied to clipboard',
            life: 3000
        });
    }).catch(err => {
        console.error('Failed to copy:', err);
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: 'Failed to copy to clipboard',
            life: 3000
        });
    });
}
</script>

<style scoped>
.stack-trace-viewer {
    margin: 1rem 0;
    border: 1px solid #e5e7eb;
    border-radius: 4px;
    background-color: #f9fafb;
}

.no-stack-trace {
    padding: 1rem;
    color: #6b7280;
    text-align: center;
    font-style: italic;
}

.stack-trace-container {
    display: flex;
    flex-direction: column;
}

.stack-trace-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 1rem;
    background-color: #f3f4f6;
    border-bottom: 1px solid #e5e7eb;
}

.stack-trace-header h3 {
    margin: 0;
    font-size: 1rem;
    font-weight: 600;
    color: #374151;
}

.stack-trace-content {
    padding: 0.5rem;
    max-height: 400px;
    overflow-y: auto;
}

.stack-frames {
    list-style: none;
    margin: 0;
    padding: 0;
}

.stack-frames li {
    padding: 0.5rem;
    border-bottom: 1px solid #e5e7eb;
    cursor: pointer;
}

.stack-frames li:last-child {
    border-bottom: none;
}

.stack-frames li:hover {
    background-color: #f3f4f6;
}

.frame-header {
    display: flex;
    align-items: center;
    font-family: monospace;
    font-size: 0.9rem;
}

.frame-number {
    display: inline-block;
    width: 2rem;
    color: #6b7280;
    text-align: right;
    margin-right: 0.5rem;
}

.frame-file {
    color: #1e40af;
    margin-right: 0.25rem;
}

.frame-line {
    color: #6b7280;
    margin-right: 0.5rem;
}

.frame-function {
    color: #047857;
    font-weight: 600;
}

.highlight-frame {
    background-color: #ecfdf5;
}

.frame-details {
    margin-top: 0.5rem;
    padding: 0.5rem;
    background-color: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 4px;
}

.frame-args, .frame-context {
    margin-top: 0.5rem;
}

.args-header, .context-header {
    font-weight: 600;
    margin-bottom: 0.25rem;
    color: #4b5563;
}

.arg-item {
    display: flex;
    margin-bottom: 0.25rem;
    font-family: monospace;
    font-size: 0.85rem;
}

.arg-index {
    width: 2rem;
    color: #6b7280;
}

.arg-value {
    color: #1e3a8a;
}

.context-code {
    margin: 0;
    padding: 0.5rem;
    background-color: #f3f4f6;
    border-radius: 4px;
    font-size: 0.85rem;
    overflow-x: auto;
}
</style>
