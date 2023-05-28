<template>
    <div>

        <div v-if="state.isLoading" class=" flex text-center justify-content-center">
            <ProgressSpinner style="max-width:100%;text-align: center;
                                    display: block;width: 50px; height: 50px" strokeWidth="5" fill="var(--surface-ground)"
                             aria-label="Custom ProgressSpinner"/>
        </div>
        <div v-else>

            <DataTable v-if="state.logs" :value="state.logs" tableStyle="min-width: 50rem">
                <Column field="line" header="Code"></Column>
                <Column field="time" header="Name"></Column>
                <Column field="details" header="Category"></Column>
            </DataTable>
        </div>
        <p>{{ state.error }}</p>
    </div>
</template>

<script setup>
    import {ref, toRef, reactive, onMounted, toRaw, toRefs} from 'vue';
    import {useFetch, $post, $get} from "../request";


    const products = ref();
    const state = reactive({
        response: null,
        logs: null,
        error: null,
        isLoading: false,
    });

    function fetchData() {
        return new Promise(async (resolve) => {
            try {
                state.isLoading = true;
                const args = {
                    route: 'get_config'
                };
                const {data, error: fetchError} = await $post(args);
                if (data) {
                    state.response = data;
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
    onMounted(() => {
        fetchData();
    });
</script>
