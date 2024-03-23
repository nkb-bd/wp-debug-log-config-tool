<template>
    <div>
        <Toast style="margin-top: 20px;" position="bottom-right" group="tr" />
        <div v-if="state.isLoading" class=" flex text-center justify-content-center">
            <ProgressSpinner style="max-width:100%;text-align: center;
                                    display: block;width: 50px; height: 50px" strokeWidth="5" fill="var(--surface-ground)"
                             aria-label="Loading"/>
        </div>
        <div v-else>

            <DataTable class="log-table" :rowClass="getRowClass"  :paginator ='filteredEntries && Object.entries(filteredEntries).length > 20' :rows="20" :rowsPerPageOptions="[ 20,30, 50]"  :value="filteredEntries"  >
                <template #header>
                    <div class="table-header">

                        <MultiSelect @change="filteredEntries" v-if="state.error_types.length" v-model="selectedErrorTypes" display="chip" :options="state.error_types"  placeholder="Error types"
                                     :maxSelectedLabels="3" class="w-full md:w-20rem" />
                        <span class="p-input-icon-left">
                          <i class="pi pi-search" />
                          <InputText  v-if="!isNotEmptyLog" @change="filteredEntries" size="small"  v-model="searchText" placeholder="Search" />
                        </span>
                        <Button   v-if="isNotEmptyLog" @click="deleteLogs()" size="small" style="margin-right: 10px;"
                                icon="pi pi-trash" severity="danger"/>
                        <Button @click="fetchLogs()" size="small" icon="pi pi-refresh" severity="info"/>
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

            </DataTable>
            <div v-if="filteredEntries && filteredEntries.length === 0" >
                <p style="margin: 20px auto;padding-bottom:20px;text-align: center">  No result found !</p>
            </div>
        </div>
        <p>{{ state.error }}</p>
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
        console.log('x',newValue);
        if (newValue.trigger === 'refresh') {
            // fetchLogs()
        } else if (newValue.trigger === 'delete') {
            deleteLogs();
        }
    });


    const state = reactive({
        response: null,
        logs: null,
        error: null,
        isLoading: false,
        search: '',
        error_types: {}
    });

    function isNotEmptyLog(){
        return state.logs && Object.entries(state.logs).length > 0;
    }
    function fetchLogs() {
        return new Promise(async (resolve) => {
            try {
                state.isLoading = true;
                const args = {
                    route: 'get_log'
                };
                const {data, error: fetchError} = await $get(args);
                if (data && data.value) {
                    state.logs = data.value.data.logs;
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
     async function deleteLogs(){
        try {
            state.isLoading = true;
            const args = {
                route: 'clear_logs'
            };
            const {data, error: fetchError} = await $post(args);
            if (data) {
                state.logs = data.value.data.logs;
            } else if (fetchError) {
                state.error = fetchError;
            }
        } catch (err) {
            state.error = err;
        } finally {
            state.isLoading = false;
            showTopLeft()
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
