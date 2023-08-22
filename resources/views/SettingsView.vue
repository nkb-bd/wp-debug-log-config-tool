
<template>
    <div >

        <Toast style="margin-top: 20px;" label="Sticky" position="bottom-right" group="br" />

        <div v-if="state.isLoading" class=" flex text-center justify-content-center">
            <ProgressSpinner style="max-width:100%;text-align: center;
                                    display: block;width: 50px; height: 50px" strokeWidth="5" fill="var(--surface-ground)"
                             aria-label="Loading"/>
        </div>
        <DataTable v-else @row-dblclick="updateSettingFromRow" :value="state.settings" >
            <Column field="name" header="Key">
                <template #body="slotProps">
                   <b> {{slotProps.data.name}}</b>
                </template>
            </Column>
            <Column field="info" header="Info"></Column>
            <Column field="value" header="Status"  style="width: 15%" >
                <template #body="slotProps">
                    <ProgressSpinner v-if="update.isLoading && update.updating_key == slotProps.data.name" style=" margin-bottom: 5px;margin-right: 10px;text-align: center;width: 20px; height: 20px" strokeWidth="4" fill="var(--surface-ground)" aria-label="Loading"/>
                     <InputSwitch :disabled="update.isLoading" @change="updateSettingFromSwitch(slotProps.index, slotProps.data.name, slotProps.data.value)" size="small"  v-model="state.settings[slotProps.index].value " />
                </template>
            </Column>
        </DataTable>
    </div>
</template>

<script setup>
    import {onMounted, reactive, watch} from "vue";
    import {$get, $post} from "../request";
    import {useToast} from "primevue/usetoast";
    const toast = useToast()


    const state = reactive({
        settings: null,
        error: null,
        isLoading: false,
    });
    const update = reactive({
        isLoading: false,
        updating_key: null
    });

    function updateSettingFromRow(e){
        const  key = state.settings[e.index].name
        const  value = state.settings[e.index].value = !state.settings[e.index].value
        updateSetting(key,value)
    }

    function updateSettingFromSwitch(index,key, value){
        updateSetting(key, value)
    }

    async function updateSetting(settingKey, settingsValue){
        try {
            update.isLoading = true;
            update.updating_key = settingKey;
            const args = {
                route: 'update_settings',
                setting_key: settingKey,
                setting_value: settingsValue,
            };
            const {data, error: fetchError} = await $post(args);

            if (data) {
                showTopLeft(data.value.data.message, data.value.data.success)

            } else if (fetchError) {
                state.error = fetchError;
            }
        } catch (err) {
            state.error = err;
        } finally {
            update.updating_key = null;
            update.isLoading = false;
        }
    }
    async function fetchSettings() {
        try {
            state.isLoading = true;
            const args = {
                route: 'get_settings'
            };
            const {data, error: fetchError} = await $get(args);
            if (data) {
                state.response = data;
                state.settings = data.value.data.settings;
            } else if (fetchError) {
                state.error = fetchError;
            }
        } catch (err) {
            state.error = err;
        } finally {
            state.isLoading = false;
        }
    }
    const showTopLeft = (message,success) => {
        toast.add({ severity: success ? 'success' : 'warn', summary: '', detail: message, group: 'br', life: 1200 });
    };
    onMounted(() => {
        fetchSettings();
    });



</script>
<style type="text/css">

     svg.p-icon.null.p-toast-message-icon {
        width: 16px!important;
        height: 16px!important;
     }
     .p-toast-message-content{
        align-items: center!important;
     }
     .p-column-header-content {
        justify-content: left;
     }
</style>
