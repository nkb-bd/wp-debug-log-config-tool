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

                                    <MultiSelect @change="filteredEntries" v-if="state.logs && Object.entries(state.logs).length" v-model="selectedErrorTypes" display="chip" :options="state.error_types"  placeholder="Error types"
                                                 :maxSelectedLabels="3" class="w-full md:w-20rem" />
                                    <span class="p-input-icon-left">
                          <i class="pi pi-search" />
                          <InputText  v-if="state.logs && Object.entries(state.logs).length" @change="filteredEntries" size="small"  v-model="searchText" placeholder="Search" />
                        </span>
                                    <Button   v-if="state.logs && Object.entries(state.logs).length" @click="deleteLogs('debug')" size="small"
                                              icon="pi pi-trash" severity="danger"/>
                                    <Button @click="fetchLogs()" size="small" icon="pi pi-refresh" label="Refresh" severity="info"/>
                                </div>
                            </template>
                            <Column field="details" header="Log">
                                <template #body="slotProps">
                                    <div v-html="slotProps.data.details"></div>
                                </template>
                            </Column>
                            <Column sortable field="time" header="Time"></Column>
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
                                <Button v-if="state.query_logs && Object.entries(state.query_logs).length"
                                            @click="deleteLogs('query')" size="small"   icon="pi pi-trash" severity="danger">
                                </Button>
                                <Button @click="fetchLogs()" size="small" icon="pi pi-refresh" label="Refresh" severity="info"/>
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
    </div>
</template>

<script setup>
    import {ref, watch, toRef, reactive, onMounted, toRaw, h, computed} from 'vue';
    import {useFetch, $post, $get} from "../request";
    import { useToast } from "primevue/usetoast";


    const toast = useToast()
    const props = defineProps(['trigger'])
    const searchString = ref(['Fatal error', 'Warning','Deprecated','Notice','Parse']);
    const trigger = computed(() => props)

    const selectedErrorTypes = ref([]);
    const searchText = ref('');


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
        activeIndex : 0
    });

    function isNotEmptyLog(){
        return filteredEntries && filteredEntries.length >= 0
    }
    const isNotEmpty = computed(isNotEmptyLog);
    function fetchLogs() {

        return new Promise(async (resolve) => {
            try {
                state.isLoading = true;
                state.error = false;
                const args = {
                    route: 'get_log'
                };
                const {data, error: fetchError} = await $get(args);
                if (data && data.value) {
                    if (!data.value.data.success){
                        state.error =data.value.data.message;
                        return;
                    }

                    state.is_save_query_on = data.value.data.is_save_query_on;
                    state.log_path = data.value.data.log_path;
                    state.logs = data.value.data.logs;
                    state.query_logs = data.value.data.query_logs;
                    state.error_types = data.value.data.error_types;
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

        return logs.filter(entry => {
            if (entry.details){
                const matchesErrorType = selectedErrorTypes.value.length === 0 || selectedErrorTypes.value.includes(entry.error_type);
                const matchesSearchText = entry.details.toLowerCase().includes(searchText.value.toLowerCase());

                return matchesErrorType && matchesSearchText;
            }

        });
    };

    const filteredEntries = computed(filterEntries);

    const showTopLeft = () => {
        toast.add({ severity: 'success', summary: '', detail: 'Log Cleared', group: 'br', life: 3000 });
    };
    onMounted(() => {
        fetchLogs();

    });
</script>
