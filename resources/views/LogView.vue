<template>
    <div>
        <Toast style="margin-top: 20px;" position="bottom-right" group="tr" />

        <div>
            <TabView v-model:activeIndex="state.activeIndex">
                    <TabPanel header="Debug Log">
                        <div v-if="state.isLoading" class=" flex text-center justify-content-center">
                            <ProgressSpinner style="max-width:100%;text-align: center;
                                    display: block;width: 50px; height: 50px" strokeWidth="5" fill="var(--surface-ground)"
                                             aria-label="Loading"/>
                        </div>

                        <DataTable v-else class="log-table" :rowClass="getRowClass"  :paginator ='filteredEntries && Object.entries(filteredEntries).length > 20' :rows="20" :rowsPerPageOptions="[ 20,30, 50]"  :value="filteredEntries"  >
                            <template #header>
                                <div class="table-header">
                                    <div class="log-stats">
                                        <div class="log-count" v-if="filteredEntries && filteredEntries.length > 0">
                                            <Badge :value="filteredEntries.length" severity="info"></Badge>
                                            <span class="log-count-text">{{ filteredEntries.length === 1 ? 'Entry' : 'Entries' }}</span>
                                        </div>
                                        <div class="file-size" v-if="state.fileSize">
                                            <i class="pi pi-file"></i>
                                            <span class="file-size-text">{{ formatFileSize(state.fileSize) }}</span>
                                        </div>
                                    </div>

                                    <MultiSelect @change="filteredEntries" v-if="state.logs && Object.entries(state.logs).length" v-model="selectedErrorTypes" display="chip" :options="state.error_types"  placeholder="Error types"
                                                 :maxSelectedLabels="3" class="error-type-filter" />
                                         <span  v-if="state.logs && Object.entries(state.logs).length" class="p-input-icon-left">
                                              <i class="pi pi-search" />
                                              <InputText  @change="filteredEntries" size="small"  v-model="searchText" placeholder="Search" />
                                        </span>
                                    <div class="auto-refresh-controls">
                                        <span class="auto-refresh-label">Auto-refresh:</span>
                                        <InputSwitch v-model="autoRefreshEnabled" @change="toggleAutoRefresh" />
                                        <Dropdown v-if="autoRefreshEnabled" v-model="autoRefreshInterval" :options="refreshIntervals" optionLabel="label" optionValue="value" placeholder="Interval" class="refresh-interval-dropdown" />
                                    </div>
                                    <Button   v-if="state.logs && Object.entries(state.logs).length" @click="deleteLogs('debug')" class="p-button-sm"
                                              icon="pi pi-trash" severity="danger"/>
                                    <Button @click="fetchLogs()" class="p-button-sm" icon="pi pi-refresh" label="" severity="info"/>
                                    <Button v-if="!isProductionEnv" @click="generateTestLogs()" class="p-button-sm" icon="pi pi-bolt" label="Generate Test Logs" severity="secondary"/>
                                </div>
                            </template>
                            <Column field="details" header="Log">
                                <template #body="slotProps">
                                    <div>
                                        <div class="log-entry-header">
                                            <div v-html="slotProps.data.details"></div>
                                            <Badge v-if="slotProps.data.occurrenceCount > 1" :value="slotProps.data.occurrenceCount" severity="warning" class="log-count-badge" />
                                        </div>
                                        <div>
                                            <Button  v-if="slotProps.data.error_type && (slotProps.data.error_type === 'Fatal error' || slotProps.data.error_type === 'Parse error' || slotProps.data.error_type === 'Parse')"
                                                @click="showStackTrace(slotProps.data)" class="p-button-sm mt-2" icon="pi pi-list" label="View Stack Trace" severity="secondary"/>
                                            <Button v-else-if="slotProps.data.details && slotProps.data.details.includes('backtrace')"
                                                @click="showStackTrace(slotProps.data)" class="p-button-sm mt-2" icon="pi pi-list" label="View Backtrace" severity="info"/>
                                        </div>
                                    </div>
                                </template>
                            </Column>
                            <Column sortable field="raw_time" header="Time">
                                <template #body="slotProps">
                                    <span class="time-with-tooltip" v-tooltip.top="formatExactTime(slotProps.data.raw_time)">{{ slotProps.data.time }}</span>
                                </template>
                            </Column>
                            <Column sortable field="plugin_name" header="Plugin Name"></Column>
                            <Column sortable field="date" header="Date"></Column>
                            <template  v-tooltip="'Enter your username'"  v-if="state.log_path" #footer><small>Log Path: {{state.log_path}}. Path randomized for security reasons. </small></template>
                        </DataTable>
                        <div v-if="filteredEntries && filteredEntries.length === 0" >
                            <p style="margin: 20px auto;padding-bottom:20px;text-align: center">  No Log found !</p>
                        </div>
                    </TabPanel>
                    <TabPanel header="Query Log">
                        <div class="query-table">
                            <div class=" table-header ">
                                <div></div>
                                <button disabled>Last 50 query</button>
                                <div class="auto-refresh-controls">
                                    <span class="auto-refresh-label">Auto-refresh:</span>
                                    <InputSwitch v-model="autoRefreshEnabled" @change="toggleAutoRefresh" />
                                </div>
                                <Button v-if="state.query_logs && Object.entries(state.query_logs).length"
                                            @click="deleteLogs('query')" class="p-button-sm"   icon="pi pi-trash" severity="danger">
                                </Button>
                                <Button @click="fetchLogs()" class="p-button-sm" icon="pi pi-refresh" label="Refresh" severity="info"/>
                            </div>
                        </div>
                        <div v-if="state.isLoading" class=" flex text-center justify-content-center">
                            <ProgressSpinner style="max-width:100%;text-align: center;
                                    display: block;width: 50px; height: 50px" strokeWidth="5" fill="var(--surface-ground)"
                                             aria-label="Loading"/>
                        </div>
                        <Accordion :activeIndex="0"  v-else-if="state.is_save_query_on">

                            <AccordionTab v-for="(query,index) in state.query_logs ">
                                <template #header>
                                    <div class="query-log-list">
                                        <div>{{query.sql}}</div>
                                        <div class="index-number">{{index+1}}</div>
                                    </div>
                                                </template>
                                <div class="query-log-list">
                                    <div> <b>Caller</b> {{ query.caller }}</div>
                                    <div><b>Execution Time</b> {{ query.execution_time }}</div>
                                    <!-- Add other properties as needed -->
                                    <div> <b>Stack Trace</b>
                                        <ul class="query-trace">
                                            <li v-for="(caller, i) in query.stack" :key="i">
                                                {{ caller }}
                                            </li>
                                        </ul>
                                    </div>


                                </div>
                            </AccordionTab>

                        </Accordion>
                        <div v-else>
                            <p class="message">Enable <b>SAVEQUERIES</b> from settings page to view database query logs. Please note it will store all queries so always turn it off after debugging in production mode or it will effect performance.</p>
                        </div>
                    </TabPanel>
        </TabView>
        </div>
        <div v-if="state.error" class="dlct-error-msg">{{ state.error }}</div>

        <!-- Stack Trace Dialog -->
        <Dialog v-model:visible="stackTraceDialogVisible" draggable="false" modal header="Error Stack Trace" :style="{width: '80vw'}" :maximizable="false">
            <div class="error-details" v-if="selectedError">
                <div class="error-message">
                    <h3>Error Message</h3>
                    <div v-html="selectedError.details"></div>
                </div>
                <div class="error-location" v-if="selectedError.file_location || selectedError.line_number">
                    <h3>Location</h3>
                    <div>
                        <strong>File:</strong> {{ selectedError.file_location }}<br>
                        <strong>Line:</strong> {{ selectedError.line_number }}
                    </div>
                </div>
                <StackTraceViewer :stackTrace="parsedStackTrace" />
            </div>
        </Dialog>
    </div>
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

    // Stack trace dialog state
    const stackTraceDialogVisible = ref(false);
    const selectedError = ref(null);
    const parsedStackTrace = ref([]);

    function showStackTrace(errorData) {
        selectedError.value = errorData;

        // Use stack trace from server if available
        if (errorData.stack_trace && Array.isArray(errorData.stack_trace) && errorData.stack_trace.length > 0) {
            parsedStackTrace.value = errorData.stack_trace;
        } else {
            // Fallback to client-side parsing if server didn't provide stack trace
            const stackTraceLines = [];

            if (errorData.details) {
                // First, try to extract from HTML content by converting HTML to text
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = errorData.details;
                const textContent = tempDiv.textContent || tempDiv.innerText || '';

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
            }

            parsedStackTrace.value = stackTraceLines;
        }

        stackTraceDialogVisible.value = true;
    }

    function isNotEmptyLog(){
        return filteredEntries && filteredEntries.length >= 0
    }
    const isNotEmpty = computed(isNotEmptyLog);
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
