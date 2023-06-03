<template>
    <div>
        <Toast style="margin-top: 20px;" position="bottom-right" group="tr" />
        <div v-if="state.isLoading" class=" flex text-center justify-content-center">
            <ProgressSpinner style="max-width:100%;text-align: center;
                                    display: block;width: 50px; height: 50px" strokeWidth="5" fill="var(--surface-ground)"
                             aria-label="Loading"/>
        </div>
        <div v-else>
            <div v-if="state.logs && Object.entries(state.logs).length == 0" >
                <p style="margin: 20px auto;padding-bottom:20px;text-align: center">Logs Empty</p>
            </div>
            <DataTable paginator :rows="20" :rowsPerPageOptions="[ 20,30, 50]" v-if="state.logs && Object.entries(state.logs).length > 0" :value="state.logs"  >
                <Column field="details" header="Log">
                    <template #body="slotProps">
                       <span v-html="formattedText(slotProps.data.details )"></span>
                    </template>
                </Column>
                <Column field="time" header="Time"></Column>
                <Column field="date" header="Date"></Column>
            </DataTable>
        </div>
        <p>{{ state.error }}</p>
    </div>
</template>

<script setup>
    import {ref, watch, toRef, reactive, onMounted, toRaw, h} from 'vue';
    import {useFetch, $post, $get} from "../request";
    import { useToast } from "primevue/usetoast";


    const toast = useToast()
    const props = defineProps(['trigger'])
    const searchString = ref(['PHP Fatal error', 'PHP Warning error']);

    const display = (props) => {
        return h(props);
    };
    watch(() => props.trigger, (trigger) => {
        if (trigger == 'refresh'){
            fetchLogs()
        } else if (trigger == 'delete'){
            deleteLogs()
        }
    });

    const state = reactive({
        response: null,
        logs: null,
        error: null,
        isLoading: false,
    });

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
                if(str == 'PHP Fatal error'){
                    cssClass = 'highlight-error-fatal';
                }else if (str == 'PHP Warning error'){
                    cssClass = 'highlight-error-warning';
                }
            const regex = new RegExp(str, 'gi');
            formatted =  `<span class="${cssClass}">${text}</span>`;
            // formatted = formatted.replace(regex, `<span class="${cssClass}">$&</span>`);
        }
        });

        return formatted;
    };

    const showTopLeft = () => {
        toast.add({ severity: 'success', summary: '', detail: 'Log Cleared', group: 'br', life: 3000 });
    };
    onMounted(() => {
        fetchLogs();

    });
</script>
