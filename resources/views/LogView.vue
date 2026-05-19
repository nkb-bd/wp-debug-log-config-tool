<template>
    <section class="dlct-log-console">
        <Toast position="bottom-right" group="tr" />

        <div class="dlct-log-card">
            <TabView v-model:activeIndex="state.activeIndex">
                <TabPanel header="Live Debug Logs">
                    <div class="dlct-log-toolbar">
                        <div class="dlct-log-stats">
                            <span class="dlct-stat-pill">
                                <strong>{{ filteredEntries.length }}</strong>
                                <span>{{ filteredEntries.length === 1 ? 'entry' : 'entries' }}</span>
                            </span>
                            <span v-if="state.fileSize" class="dlct-stat-pill dlct-stat-muted">
                                <i class="pi pi-file"></i>
                                <span>{{ formatFileSize(state.fileSize) }}</span>
                            </span>
                            <Button
                                v-if="state.log_path"
                                @click="showLogFileDialog"
                                class="p-button-sm"
                                icon="pi pi-folder-open"
                                label="Log file"
                                severity="secondary"
                                outlined
                            />
                            <Button
                                v-if="state.log_path"
                                @click="openLogFileViewer"
                                class="p-button-sm"
                                icon="pi pi-eye"
                                label="View File"
                                severity="info"
                                outlined
                            />
                        </div>

                        <div class="dlct-log-controls">
                            <MultiSelect
                                v-if="state.logs && Object.entries(state.logs).length"
                                v-model="selectedErrorTypes"
                                display="chip"
                                :options="state.error_types"
                                placeholder="Error types"
                                :maxSelectedLabels="2"
                                class="error-type-filter"
                            />
                            <span v-if="state.logs && Object.entries(state.logs).length" class="p-input-icon-left dlct-search-field">
                                <i class="pi pi-search" />
                                <InputText size="small" v-model="searchText" placeholder="Search logs" />
                            </span>
                            <div class="auto-refresh-controls">
                                <span class="auto-refresh-label">Live</span>
                                <InputSwitch v-model="autoRefreshEnabled" @change="toggleAutoRefresh" />
                                <Dropdown
                                    v-if="autoRefreshEnabled"
                                    v-model="autoRefreshInterval"
                                    :options="refreshIntervals"
                                    optionLabel="label"
                                    optionValue="value"
                                    placeholder="Interval"
                                    class="refresh-interval-dropdown"
                                />
                            </div>
                            <Button
                                v-if="state.logs && Object.entries(state.logs).length"
                                @click="deleteLogs('debug')"
                                class="p-button-sm"
                                icon="pi pi-trash"
                                severity="danger"
                                text
                            />
                            <Button @click="fetchLogs()" class="p-button-sm" icon="pi pi-refresh" severity="info" />
                            <Button v-if="!isProductionEnv" @click="generateTestLogs()" class="p-button-sm" icon="pi pi-bolt" label="Generate" severity="secondary" />
                        </div>
                    </div>

                    <div v-if="state.isLoading" class="dlct-loading">
                        <ProgressSpinner strokeWidth="4" fill="var(--surface-ground)" aria-label="Loading"/>
                    </div>

                    <DataTable
                        v-else
                        class="log-table dlct-timeline-table"
                        :rowClass="getRowClass"
                        :paginator="filteredEntries.length > 20"
                        :rows="20"
                        :rowsPerPageOptions="[20, 30, 50]"
                        :value="filteredEntries"
                    >
                        <Column field="details" header="Log event">
                            <template #body="slotProps">
                                <div class="dlct-log-event">
                                    <span class="dlct-timeline-dot"></span>
                                    <div class="dlct-log-body">
                                        <div class="dlct-log-main">
                                            <div class="dlct-log-meta">
                                                <span class="dlct-log-time" v-tooltip.top="formatExactTime(slotProps.data.raw_time)">
                                                    {{ slotProps.data.time }}
                                                </span>
                                                <span :class="['dlct-severity', getSeverityClass(slotProps.data)]">
                                                    {{ getSeverityLabel(slotProps.data) }}
                                                </span>
                                                <span v-if="slotProps.data.plugin_name" class="dlct-source-chip">
                                                    {{ slotProps.data.plugin_name }}
                                                </span>
                                                <Badge v-if="slotProps.data.occurrenceCount > 1" :value="slotProps.data.occurrenceCount" severity="warning" class="log-count-badge" />
                                            </div>
                                            <div :class="['dlct-log-message', {'is-collapsed': isLongLog(slotProps.data) && !isLogExpanded(slotProps.data)}]">
                                                <p class="dlct-log-summary">{{ getLogSummary(slotProps.data) }}</p>
                                                <ol v-if="getLogFrames(slotProps.data).length" class="dlct-stack-list">
                                                    <li v-for="frame in getLogFrames(slotProps.data)" :key="`${getLogKey(slotProps.data)}-${frame.index}`">
                                                        <span class="dlct-stack-index">#{{ frame.index }}</span>
                                                        <span class="dlct-stack-location">{{ frame.location }}</span>
                                                        <span v-if="frame.call" class="dlct-stack-call">{{ frame.call }}</span>
                                                    </li>
                                                </ol>
                                                <ol v-else-if="getLogTraceItems(slotProps.data).length" class="dlct-stack-list dlct-call-chain-list">
                                                    <li v-for="trace in getLogTraceItems(slotProps.data)" :key="`${getLogKey(slotProps.data)}-trace-${trace.index}`">
                                                        <span class="dlct-stack-index">#{{ trace.index }}</span>
                                                        <span class="dlct-stack-location">{{ trace.call }}</span>
                                                    </li>
                                                </ol>
                                                <pre v-else>{{ getReadableDetails(slotProps.data) }}</pre>
                                            </div>
                                        </div>
                                        <div class="dlct-log-actions">
                                            <Button
                                                v-if="isLongLog(slotProps.data)"
                                                @click="toggleLogExpanded(slotProps.data)"
                                                class="p-button-sm"
                                                :icon="isLogExpanded(slotProps.data) ? 'pi pi-angle-up' : 'pi pi-angle-down'"
                                                :label="isLogExpanded(slotProps.data) ? 'Collapse' : 'Expand'"
                                                severity="secondary"
                                                text
                                            />
                                            <Button
                                                v-if="hasTrace(slotProps.data)"
                                                @click="showStackTrace(slotProps.data)"
                                                class="p-button-sm"
                                                icon="pi pi-list"
                                                label="Trace"
                                                severity="secondary"
                                                text
                                            />
                                            <Button
                                                @click="copyLogAsJson(slotProps.data)"
                                                class="p-button-sm"
                                                icon="pi pi-copy"
                                                label="Copy JSON"
                                                severity="secondary"
                                                text
                                            />
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </Column>
                        <Column sortable field="date" header="Date" class="dlct-date-column"></Column>
                    </DataTable>

                    <div v-if="!state.isLoading && filteredEntries.length === 0" class="dlct-empty-state">
                        No logs found.
                    </div>
                </TabPanel>

                <TabPanel header="Query Log">
                    <div class="dlct-log-toolbar">
                        <div class="dlct-log-stats">
                            <span class="dlct-stat-pill">
                                <strong>{{ state.query_logs ? state.query_logs.length : 0 }}</strong>
                                <span>queries</span>
                            </span>
                            <span class="dlct-stat-pill dlct-stat-muted">Last 50</span>
                        </div>
                        <div class="dlct-log-controls">
                            <div class="auto-refresh-controls">
                                <span class="auto-refresh-label">Live</span>
                                <InputSwitch v-model="autoRefreshEnabled" @change="toggleAutoRefresh" />
                            </div>
                            <Button
                                v-if="state.query_logs && Object.entries(state.query_logs).length"
                                @click="deleteLogs('query')"
                                class="p-button-sm"
                                icon="pi pi-trash"
                                severity="danger"
                                text
                            />
                            <Button @click="fetchLogs()" class="p-button-sm" icon="pi pi-refresh" label="Refresh" severity="info"/>
                        </div>
                    </div>

                    <div v-if="state.isLoading" class="dlct-loading">
                        <ProgressSpinner strokeWidth="4" fill="var(--surface-ground)" aria-label="Loading"/>
                    </div>

                    <Accordion :activeIndex="0" v-else-if="state.is_save_query_on">
                        <AccordionTab v-for="(query,index) in state.query_logs" :key="index">
                            <template #header>
                                <div class="query-log-list">
                                    <code>{{ query.sql }}</code>
                                    <span class="index-number">#{{ index + 1 }}</span>
                                </div>
                            </template>
                            <div class="query-log-detail">
                                <div><b>Caller</b> {{ query.caller }}</div>
                                <div><b>Execution Time</b> {{ query.execution_time }}</div>
                                <div>
                                    <b>Stack Trace</b>
                                    <ul class="query-trace">
                                        <li v-for="(caller, i) in query.stack" :key="i">
                                            {{ caller }}
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </AccordionTab>
                    </Accordion>

                    <div v-else class="message">Enable <b>SAVEQUERIES</b> from settings to view database query logs.</div>
                </TabPanel>
            </TabView>
        </div>

        <div v-if="state.error" class="dlct-error-msg">{{ state.error }}</div>

        <Dialog v-model:visible="stackTraceDialogVisible" :draggable="false" modal header="Error Stack Trace" :style="{width: '80vw'}" :maximizable="false">
            <div class="error-details" v-if="selectedError">
                <div class="error-message">
                    <h3>Error Message</h3>
                    <pre>{{ selectedError.details }}</pre>
                </div>
                <div class="error-location" v-if="selectedError.file_location || selectedError.line_number">
                    <h3>Location</h3>
                    <div>
                        <strong>File:</strong> {{ selectedError.file_location }}<br>
                        <strong>Line:</strong> {{ selectedError.line_number }}
                    </div>
                </div>
                <StackTraceViewer :stackTrace="parsedStackTrace" />
                <div class="dlct-dialog-actions">
                    <Button @click="copyLogAsJson(selectedError)" icon="pi pi-copy" label="Copy as JSON" />
                </div>
            </div>
        </Dialog>

        <Dialog v-model:visible="logFileDialogVisible" :draggable="false" modal header="Debug Log File" :style="{width: '640px'}" :maximizable="false">
            <div class="dlct-file-dialog">
                <div class="dlct-file-summary">
                    <span class="dlct-file-icon"><i class="pi pi-file"></i></span>
                    <div>
                        <strong>{{ logFileName }}</strong>
                        <span>{{ formatFileSize(state.fileSize) }}</span>
                    </div>
                </div>

                <div class="dlct-file-field">
                    <label>Directory</label>
                    <div class="dlct-file-value">
                        <code>{{ logDirectory }}</code>
                        <Button @click="copyText(logDirectory, 'Directory copied')" icon="pi pi-copy" severity="secondary" text />
                    </div>
                </div>

                <div class="dlct-file-field">
                    <label>Full path</label>
                    <div class="dlct-file-value">
                        <code>{{ state.log_path }}</code>
                        <Button @click="copyText(state.log_path, 'Log path copied')" icon="pi pi-copy" severity="secondary" text />
                    </div>
                </div>

                <div class="dlct-file-actions">
                    <Button @click="openLogFileViewer" icon="pi pi-eye" label="View File" severity="info" />
                    <Button @click="openLogDirectory" icon="pi pi-external-link" label="Open Directory" severity="info" />
                    <Button @click="copyText(state.log_path, 'Log path copied')" icon="pi pi-copy" label="Copy Path" severity="secondary" outlined />
                </div>
            </div>
        </Dialog>

        <Dialog v-model:visible="logFileViewerVisible" :draggable="false" modal header="Debug Log File Content" :style="{width: '88vw'}" :maximizable="true">
            <div class="dlct-file-viewer">
                <div class="dlct-file-viewer-toolbar">
                    <div>
                        <strong>{{ logFileName }}</strong>
                        <span v-if="rawFile.truncated">
                            Showing latest {{ formatFileSize(rawFile.maxBytes) }} of {{ formatFileSize(rawFile.fileSize) }}
                        </span>
                        <span v-else>
                            {{ formatFileSize(rawFile.fileSize) }}
                        </span>
                    </div>
                    <Button @click="copyText(rawFile.content, 'File content copied')" icon="pi pi-copy" label="Copy Content" severity="secondary" outlined />
                </div>
                <div v-if="rawFile.isLoading" class="dlct-loading">
                    <ProgressSpinner strokeWidth="4" fill="var(--surface-ground)" aria-label="Loading"/>
                </div>
                <pre v-else class="dlct-raw-log-content">{{ rawFile.content || 'No file content found.' }}</pre>
            </div>
        </Dialog>
    </section>
</template>

<script setup>
    import {ref, watch, toRef, reactive, onMounted, onBeforeUnmount, toRaw, h, computed} from 'vue';
    import {useFetch, $post, $get} from "../request";
    import { useToast } from "primevue/usetoast";
    import StackTraceViewer from "../components/StackTraceViewer.vue";


    const toast = useToast()
    const props = defineProps(['trigger'])
    const searchString = ref(['Fatal error', 'Warning','Deprecated','Notice','Parse']);

    // Check if we're in production environment to hide test features
    let isProductionEnv = process.env.NODE_ENV === 'production';

    // For local development, you can override this with a URL parameter
    if (typeof window !== 'undefined' && window.location.search.includes('force_production=true')) {
        isProductionEnv = true;
    }
    const trigger = computed(() => props)

    const selectedErrorTypes = ref([]);
    const searchText = ref('');
    const expandedLogs = ref({});

    // Auto-refresh settings
    const autoRefreshEnabled = ref(false);
    const autoRefreshInterval = ref(30);
    const refreshIntervals = ref([
        { label: '5 seconds', value: 5 },
        { label: '10 seconds', value: 10 },
        { label: '30 seconds', value: 30 },
        { label: '1 minute', value: 60 }
    ]);
    let refreshTimer = null;


    const display = (props) => {
        return h(props);
    };
    watch(trigger, (newValue, oldValue) => {
        if (newValue.trigger === 'refresh') {
            // fetchLogs()
        } else if (newValue.trigger === 'delete') {
            deleteLogs();
        }
    });


    const state = reactive({
        response: null,
        logs: null,
        query_logs: null,
        error: null,
        log_path: null,
        isLoading: false,
        search: '',
        error_types: {},
        is_save_query_on : false,
        activeIndex : 0,
        lastModified: 0,
        fileSize: 0
    });

    const rawFile = reactive({
        content: '',
        isLoading: false,
        fileSize: 0,
        maxBytes: 0,
        truncated: false,
        error: null
    });

    // Stack trace dialog state
    const stackTraceDialogVisible = ref(false);
    const logFileDialogVisible = ref(false);
    const logFileViewerVisible = ref(false);
    const selectedError = ref(null);
    const parsedStackTrace = ref([]);

    const logDirectory = computed(() => {
        if (!state.log_path) {
            return '';
        }

        const normalizedPath = String(state.log_path).replace(/\\/g, '/');
        const separatorIndex = normalizedPath.lastIndexOf('/');

        return separatorIndex >= 0 ? normalizedPath.slice(0, separatorIndex) : normalizedPath;
    });

    const logFileName = computed(() => {
        if (!state.log_path) {
            return 'debug.log';
        }

        const normalizedPath = String(state.log_path).replace(/\\/g, '/');
        const separatorIndex = normalizedPath.lastIndexOf('/');

        return separatorIndex >= 0 ? normalizedPath.slice(separatorIndex + 1) : normalizedPath;
    });

    function showStackTrace(errorData) {
        selectedError.value = errorData;

        // Use stack trace from server if available
        if (errorData.stack_trace && Array.isArray(errorData.stack_trace) && errorData.stack_trace.length > 0) {
            parsedStackTrace.value = errorData.stack_trace;
        } else {
            // Fallback to client-side parsing if server didn't provide stack trace
            const stackTraceLines = [];

            if (errorData.details) {
                const textContent = String(errorData.details || '');

                // Check for custom backtrace format
                if (textContent.includes('Backtrace:')) {
                    const parts = textContent.split('Backtrace:');
                    if (parts.length > 1) {
                        const traceText = parts[1].trim();
                        const lines = traceText.split('\n');
                        for (const line of lines) {
                            const trimmedLine = line.trim();
                            if (trimmedLine.startsWith('#') || trimmedLine.includes('Variable dump')) {
                                stackTraceLines.push(trimmedLine);
                            }
                        }

                        // If we found Variable dump, add those lines too
                        if (traceText.includes('Variable dump:')) {
                            const varDumpParts = traceText.split('Variable dump:');
                            if (varDumpParts.length > 1) {
                                const varDumpText = varDumpParts[1].trim();
                                const varDumpLines = varDumpText.split('\n');
                                for (const line of varDumpLines) {
                                    const trimmedLine = line.trim();
                                    if (trimmedLine.length > 0) {
                                        stackTraceLines.push(trimmedLine);
                                    }
                                }
                            }
                        }
                    }
                }
                // Try to extract stack trace from PHP error message
                else if (textContent.includes('Stack trace:')) {
                    const parts = textContent.split('Stack trace:');
                    if (parts.length > 1) {
                        const traceText = parts[1].trim();
                        const lines = traceText.split('\n');
                        for (const line of lines) {
                            const trimmedLine = line.trim();
                            if (trimmedLine.startsWith('#') || trimmedLine.includes('thrown in')) {
                                stackTraceLines.push(trimmedLine);
                            }
                        }
                    }
                }

                // If still no stack trace found, try to extract from the formatted error message
                if (stackTraceLines.length === 0) {
                    // Look for indented lines that might be a stack trace
                    const stackTraceRegex = /#\d+\s+[^\n]+/g;
                    const matches = textContent.match(stackTraceRegex);
                    if (matches) {
                        for (const line of matches) {
                            stackTraceLines.push(line.trim());
                        }
                    }
                }

                // If still no stack trace found, at least add the file and line info
                if (stackTraceLines.length === 0 && errorData.file_location) {
                    stackTraceLines.push(`${errorData.file_location}(${errorData.line_number}): ${errorData.error_type}`);
                }

                // If we still couldn't find any stack trace information, add a fallback message
                if (stackTraceLines.length === 0) {
                    // Add a message explaining why no stack trace is available
                    stackTraceLines.push("No stack trace information was found in the error log.");
                    stackTraceLines.push("This may be because:");
                    stackTraceLines.push("- The error didn't generate a stack trace");
                    stackTraceLines.push("- The error logging level doesn't include stack traces");
                    stackTraceLines.push("- The error format is not recognized by the parser");

                    // If we have error details, add them as context
                    if (errorData.error_type) {
                        stackTraceLines.push(`\nError Type: ${errorData.error_type}`);
                    }
                }
            }

            parsedStackTrace.value = stackTraceLines;
        }

        stackTraceDialogVisible.value = true;
    }

    function isNotEmptyLog(){
        return filteredEntries && filteredEntries.length >= 0
    }
    const isNotEmpty = computed(isNotEmptyLog);

    function hasTrace(log) {
        return Boolean(
            (log.stack_trace && Array.isArray(log.stack_trace) && log.stack_trace.length > 0) ||
            (log.details && (log.details.includes('Stack trace:') || log.details.includes('Backtrace:') || log.details.includes('backtrace')))
        );
    }

    function getSeverityLabel(log) {
        return log.error_type || 'info';
    }

    function getSeverityClass(log) {
        const label = String(getSeverityLabel(log)).toLowerCase();

        if (label.includes('fatal') || label.includes('parse')) {
            return 'is-critical';
        }

        if (label.includes('warning')) {
            return 'is-warning';
        }

        if (label.includes('deprecated') || label.includes('notice')) {
            return 'is-muted';
        }

        return 'is-info';
    }

    function getLogKey(log) {
        return [
            log.date || '',
            log.time || '',
            log.error_type || '',
            log.file_location || '',
            log.line_number || '',
            String(log.details || '').slice(0, 120)
        ].join('|');
    }

    function isLongLog(log) {
        const frames = getLogFrames(log);
        const traceItems = getLogTraceItems(log);

        if (frames.length > 2) {
            return true;
        }

        if (traceItems.length > 5) {
            return true;
        }

        if (log && log.stack_trace && Array.isArray(log.stack_trace) && log.stack_trace.length > 2) {
            return true;
        }

        return false;
    }

    function isLogExpanded(log) {
        return Boolean(expandedLogs.value[getLogKey(log)]);
    }

    function toggleLogExpanded(log) {
        const key = getLogKey(log);
        expandedLogs.value = {
            ...expandedLogs.value,
            [key]: !expandedLogs.value[key]
        };
    }

    function getLogSummary(log) {
        const details = getReadableDetails(log).trim();

        if (!details) {
            return '';
        }

        const stackIndex = details.search(/\n#\d+\s/);

        if (stackIndex >= 0) {
            return details.slice(0, stackIndex).replace(/\n+Stack trace:\s*$/i, '').trim();
        }

        const madeByIndex = details.indexOf(' made by ');

        if (madeByIndex >= 0) {
            return details.slice(0, madeByIndex).trim();
        }

        return details;
    }

    function getLogFrames(log) {
        const details = getReadableDetails(log);
        const lines = details.split('\n');
        const frames = [];
        let current = null;

        lines.forEach((line) => {
            const trimmed = line.trim();

            if (!trimmed) {
                return;
            }

            if (/^#\d+\s/.test(trimmed)) {
                if (current) {
                    frames.push(parseStackFrame(current));
                }

                current = trimmed;
                return;
            }

            if (current) {
                current += ' ' + trimmed;
            }
        });

        if (current) {
            frames.push(parseStackFrame(current));
        }

        return frames;
    }

    function parseStackFrame(frame) {
        const match = frame.match(/^#(\d+)\s+(.+?)(?::\s*(.*))?$/);

        if (!match) {
            return {
                index: '?',
                location: frame,
                call: ''
            };
        }

        return {
            index: match[1],
            location: match[2] || '',
            call: match[3] || ''
        };
    }

    function getLogTraceItems(log) {
        const details = getReadableDetails(log);
        const madeByIndex = details.indexOf(' made by ');

        if (madeByIndex < 0) {
            return [];
        }

        const traceText = details.slice(madeByIndex + ' made by '.length);

        return traceText
            .split(/,\s+/)
            .map((call) => call.trim())
            .filter(Boolean)
            .map((call, index) => ({
                index,
                call
            }));
    }

    function getReadableDetails(log) {
        const details = String(log && log.details ? log.details : '');

        return decodeLogText(details)
            .replace(/<a\b[^>]*>(.*?)<\/a>/gi, '$1')
            .replace(/<\/?(strong|em|code|span)\b[^>]*>/gi, '')
            .replace(/<br\s*\/?>/gi, '\n')
            .trim();
    }

    function decodeLogText(value) {
        if (typeof document === 'undefined') {
            return value;
        }

        const textarea = document.createElement('textarea');
        textarea.innerHTML = value;

        return textarea.value;
    }

    async function copyLogAsJson(log) {
        if (!log) {
            return;
        }

        const payload = JSON.stringify(toRaw(log), null, 2);

        await copyText(payload, 'Log copied as JSON');
    }

    async function copyText(value, detail = 'Copied') {
        if (!value) {
            return;
        }

        try {
            await navigator.clipboard.writeText(value);
            toast.add({ severity: 'success', summary: 'Copied', detail, group: 'tr', life: 1800 });
        } catch (err) {
            toast.add({ severity: 'warn', summary: 'Copy failed', detail: 'Could not write to clipboard', group: 'tr', life: 2500 });
        }
    }

    function showLogFileDialog() {
        logFileDialogVisible.value = true;
    }

    async function openLogFileViewer() {
        logFileViewerVisible.value = true;
        rawFile.isLoading = true;
        rawFile.error = null;

        try {
            const {data, error: fetchError} = await $get({
                route: 'get_log_file_content'
            });

            if (data && data.value && data.value.success && data.value.data) {
                rawFile.content = data.value.data.content || '';
                rawFile.fileSize = data.value.data.file_size || 0;
                rawFile.maxBytes = data.value.data.max_bytes || 0;
                rawFile.truncated = Boolean(data.value.data.truncated);
            } else {
                const message = data && data.value && data.value.data && data.value.data.message
                    ? data.value.data.message
                    : 'Could not load log file content';
                rawFile.error = message;
                rawFile.content = message;
            }

            if (fetchError && fetchError.value) {
                rawFile.error = fetchError.value;
                rawFile.content = String(fetchError.value);
            }
        } catch (err) {
            rawFile.error = err;
            rawFile.content = String(err);
        } finally {
            rawFile.isLoading = false;
        }
    }

    function openLogDirectory() {
        if (!logDirectory.value) {
            return;
        }

        const fileUrl = 'file://' + logDirectory.value.split('/').map(segment => encodeURIComponent(segment)).join('/');
        window.open(fileUrl, '_blank', 'noopener');
        toast.add({
            severity: 'info',
            summary: 'Opening directory',
            detail: 'If the browser blocks local file links, copy the directory path instead.',
            group: 'tr',
            life: 3000
        });
    }

    function fetchLogs(isAutoRefresh = false) {
        return new Promise(async (resolve) => {
            try {
                // Only show loading indicator for manual refreshes
                if (!isAutoRefresh) {
                    state.isLoading = true;
                }
                state.error = false;

                // For auto-refresh, only fetch new logs since last update
                const args = {
                    route: 'get_log'
                };

                if (isAutoRefresh && state.lastModified && state.fileSize) {
                    args.last_modified = state.lastModified;
                    args.last_size = state.fileSize;
                }

                const {data, error: fetchError} = await $get(args);
                if (data && data.value) {
                    if (!data.value.data.success){
                        state.error = data.value.data.message;
                        return;
                    }

                    // If no changes, don't update the UI
                    if (data.value.data.no_changes) {
                        state.fileSize = data.value.data.file_size;
                        state.lastModified = data.value.data.last_modified;
                        return;
                    }

                    state.is_save_query_on = data.value.data.is_save_query_on;
                    state.log_path = data.value.data.log_path;
                    state.fileSize = data.value.data.file_size;
                    state.lastModified = data.value.data.last_modified;

                    // For auto-refresh, append new logs to existing ones
                    if (isAutoRefresh && state.logs && data.value.data.logs) {
                        // Merge new logs with existing logs, avoiding duplicates
                        const newLogs = data.value.data.logs;
                        if (newLogs && newLogs.length > 0) {
                            // Create a map of existing logs by a unique identifier
                            const existingLogsMap = new Map();
                            state.logs.forEach(log => {
                                // Create a unique key using date, time, and details
                                const key = `${log.date}-${log.time}-${log.details}`;
                                existingLogsMap.set(key, true);
                            });

                            // Filter out duplicates from new logs
                            const uniqueNewLogs = newLogs.filter(log => {
                                const key = `${log.date}-${log.time}-${log.details}`;
                                return !existingLogsMap.has(key);
                            });

                            if (uniqueNewLogs.length > 0) {
                                state.logs = [...uniqueNewLogs, ...state.logs];
                                // Update notification if new logs arrived
                                toast.add({ severity: 'info', summary: 'New Logs', detail: `${uniqueNewLogs.length} new log entries`, group: 'br', life: 3000 });
                            }
                        }
                    } else {
                        // For manual refresh, replace all logs
                        state.logs = data.value.data.logs;
                    }

                    state.query_logs = data.value.data.query_logs;

                    // Always update error types
                    if (data.value.data.error_types) {
                        state.error_types = data.value.data.error_types;
                    }
                } else if (fetchError) {
                    state.error = fetchError;
                }
            } catch (err) {
                state.error = err;
            } finally {
                state.isLoading = false;
                resolve();
            }
        });
    }
    async function deleteLogs(type='debug'){
        try {
            state.isLoading = true;
            state.error = false;
            let route = '';
            if (type == 'debug'){
                route  =   'clear_debug_logs'
            }else{
                route  =   'clear_query_logs'
            }
            const args = {
                route: route
            };
            const {data, error: fetchError} = await $post(args);
            if (data) {
                if (type == 'debug'){
                    state.logs = null;
                    state.activeIndex = 0;
                }else{
                    state.query_logs = null;
                    state.activeIndex = 1;

                }

            } else if (fetchError) {
                state.error = fetchError;
            }
        } catch (err) {
            state.error = err;
        } finally {
            state.isLoading = false;
        }

    }
    function formattedText(text) {
        let formatted = text;

        searchString.value.forEach(str => {
        if (str && formatted.includes(str)) {
                // Inject <span> tag around the search string
                var cssClass = 'highlight-error'
                if(str == 'Fatal error'){
                    cssClass = 'highlight-error-fatal';
                }else if (str == 'Warning'){
                    cssClass = 'highlight-error-warning';
                }else if (str == 'Deprecated'){
                    cssClass = 'highlight-error-deprecated';
                }else if (str == 'Notice'){
                    cssClass = 'highlight-error-info';
                }else if (str == 'Parse'){
                    cssClass = 'highlight-error-parse';
                }
            const regex = new RegExp(str, 'gi');
            formatted =  `<div class="${cssClass}">${text}</div>`;
        }
        });

        return formatted;
    };

    function getRowClass(text) {
        let formatted = text.details;
        let cssClass = '';
        searchString.value.forEach(str => {
            if (str && formatted.includes(str)) {
                // Inject <span> tag around the search string
                 cssClass = 'highlight-error'
                if(str == 'Fatal error'){
                    cssClass = 'highlight-error-fatal';
                }else if (str == 'Warning'){
                    cssClass = 'highlight-error-warning';
                }else if (str == 'Deprecated'){
                    cssClass = 'highlight-error-deprecated';
                }else if (str == 'Notice'){
                    cssClass = 'highlight-error-info';
                }else if (str == 'Parse'){
                    cssClass = 'highlight-error-parse';
                }

            }
        });

        return cssClass;
    };


    const filterEntries = () => {
        const logs = state.logs;

        if (!logs) {
            return [];
        }

        // First filter the logs based on error type and search text
        const filteredLogs = logs.filter(entry => {
            if (entry.details){
                const matchesErrorType = selectedErrorTypes.value.length === 0 || selectedErrorTypes.value.includes(entry.error_type);
                const matchesSearchText = entry.details.toLowerCase().includes(searchText.value.toLowerCase());

                return matchesErrorType && matchesSearchText;
            }
        });

        // Group identical logs and add occurrence count
        const groupedLogs = [];
        const logMap = new Map();

        filteredLogs.forEach(log => {
            // Create a unique key for each log based on its content
            const logKey = `${log.details}-${log.error_type}-${log.file_location}-${log.line_number}`;

            if (logMap.has(logKey)) {
                // Increment count for duplicate logs
                const existingLog = logMap.get(logKey);
                existingLog.occurrenceCount = (existingLog.occurrenceCount || 1) + 1;
            } else {
                // Add new log with count of 1
                log.occurrenceCount = 1;
                logMap.set(logKey, log);
                groupedLogs.push(log);
            }
        });

        return groupedLogs;
    };

    const filteredEntries = computed(filterEntries);

    const showTopLeft = () => {
        toast.add({ severity: 'success', summary: '', detail: 'Log Cleared', group: 'br', life: 3000 });
    };
    function toggleAutoRefresh() {
        clearInterval(refreshTimer);
        if (autoRefreshEnabled.value) {
            refreshTimer = setInterval(() => {
                fetchLogs(true); // Pass true to indicate this is an auto-refresh
            }, autoRefreshInterval.value * 1000);
        }
    }

    // Watch for changes in the refresh interval
    watch(autoRefreshInterval, () => {
        if (autoRefreshEnabled.value) {
            toggleAutoRefresh();
        }
    });

    onMounted(() => {
        fetchLogs();
        // Try to load saved preferences
        const savedAutoRefresh = localStorage.getItem('dlct_auto_refresh_enabled');
        const savedInterval = localStorage.getItem('dlct_auto_refresh_interval');

        if (savedAutoRefresh !== null) {
            autoRefreshEnabled.value = savedAutoRefresh === 'true';
        }

        if (savedInterval !== null) {
            autoRefreshInterval.value = parseInt(savedInterval, 10);
        }

        // Initialize auto-refresh if enabled
        if (autoRefreshEnabled.value) {
            toggleAutoRefresh();
        }
    });

    // Save preferences when they change
    watch(autoRefreshEnabled, (newValue) => {
        localStorage.setItem('dlct_auto_refresh_enabled', newValue);
    });

    watch(autoRefreshInterval, (newValue) => {
        localStorage.setItem('dlct_auto_refresh_interval', newValue);
    });

    // Clean up timer when component is destroyed
    onBeforeUnmount(() => {
        if (refreshTimer) {
            clearInterval(refreshTimer);
        }
    });

    // Format file size to human-readable format
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';

        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));

        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    // Format timestamp to exact date and time for tooltip
    function formatExactTime(timestamp) {
        if (!timestamp) return '';

        try {
            const date = new Date(timestamp * 1000);
            return date.toLocaleString(undefined, {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
        } catch (e) {
            console.error('Error formatting timestamp:', e);
            return 'Invalid date';
        }
    }

    // Function to generate test logs for development and demonstration only
    // This is hidden in production environments
    async function generateTestLogs() {
        try {
            state.isLoading = true;
            const args = {
                route: 'generate_test_logs'
            };
            const {data, error: fetchError} = await $post(args);
            if (data && data.value) {
                toast.add({
                    severity: 'success',
                    summary: 'Test Logs Generated',
                    detail: 'Sample logs of different types have been added to the log file',
                    group: 'br',
                    life: 3000
                });
                // Refresh logs to show the new test logs
                await fetchLogs();
            } else if (fetchError) {
                toast.add({
                    severity: 'error',
                    summary: 'Error',
                    detail: 'Failed to generate test logs',
                    group: 'br',
                    life: 3000
                });
            }
        } catch (err) {
            toast.add({
                severity: 'error',
                summary: 'Error',
                detail: err.message || 'An error occurred',
                group: 'br',
                life: 3000
            });
        } finally {
            state.isLoading = false;
        }
    }
</script>
