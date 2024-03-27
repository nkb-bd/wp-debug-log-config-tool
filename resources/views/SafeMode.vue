
<template>
    <div >

        <Toast style="margin-top: 20px;" label="Sticky" position="bottom-right" group="br" />
        <div class="flex flex-column gap-2 dlct-form">


            <div class=" flex justify-content-center dlct-form-item">
                <div class="message" severity="info"><i class="pi pi-fw pi-info-circle"></i> This will deactivate/activate all selected plugins when turned on. After turning off previous active plugins will be restored as it was before. </div>
            </div>


            <div class=" flex justify-content-center dlct-form-item">
               <div> <label for="switch1">Enable SafeMode {{state.isSafeMode}}</label></div>
                <InputSwitch inputId="switch1" v-model="state.isSafeMode" />
            </div>
            <div class=" flex justify-content-center dlct-form-item">
                <div><label>Action Type</label></div>
                        <SelectButton optionLabel="name" :optionValue="value" v-model="state.action_mode" :options="options" aria-labelledby="basic" />
            </div>
            <div v-if="state.action_mode" class=" flex justify-content-center dlct-form-item">
                <div><label>Select Plugins that will remain <span class="highlight">{{state.action_mode.value}}</span> during safe mode </label></div>

                <MultiSelect v-model="selectedPlugins" :options="pluginList" optionLabel="label" optionGroupLabel="label" optionGroupChildren="items" display="chip" placeholder="Select Plugins" class="w-full md:w-20rem">

                </MultiSelect>
            </div>
            <div class="flex justify-content-center dlct-form-item">
                <div></div>
                <div>                <small>Other plugins will remain {{otherPluginState}}</small>
                </div>

            </div>

            <div class=" flex justify-content-center dlct-form-item">
                    <Button size="medium" @click="updateSetting()"  label="Update" />
            </div>
        </div>
    </div>
</template>

<script setup>
    import {computed, onMounted, reactive, ref} from "vue";
    import {$get, $post} from "../request";
    import {useToast} from "primevue/usetoast";
    const toast = useToast()
    const selectedPlugins = ref([
        {
            "label": "Debug Log Viewer & Control",
            "value": "debug-log-config-tool/plugin.php"
        }
    ]);
    const  pluginList= ref([]);

    const options = reactive([
        {name: 'Active Plugins', value: 'active'},
        {name: 'Deactive Plugins', value: 'deactive'}
    ]);

    const state = reactive({
        settings: null,
        error: null,
        isLoading: false,
        isSafeMode: false,
        action_mode: ''
    });
    const update = reactive({
        isLoading: false,
        updating_key: null
    });

    const otherPluginState = computed(GetOtherPluginState);
    function GetOtherPluginState(){
        return state.action_mode.value == 'active' ? 'Inactive' : 'Active';
    }

    async function updateSetting(){
        try {
            update.isLoading = true;
            const args = {
                route: 'update_safe_mode',
                safe_mode: this.state.isSafeMode,
                action_mode: this.state.action_mode.value,
                selected_plugins: JSON.stringify(selectedPlugins.value.map(item => item.value ))
            };
            const {data, error: fetchError} = await $post(args);
            console.log(data)
            if (data) {
                showTopLeft(data.value.data.message, data.value.data.success)
            } else if (fetchError) {
                state.error = fetchError;
            }

        } catch (err) {
            console.log('xx')

            state.error = err;
        }
    }
    async function fetchSettings() {
        try {
            state.isLoading = true;
            const args = {
                route: 'get_safe_mode'
            };
            const {data, error: fetchError} = await $get(args);
            if (data) {
                pluginList.value = data.value.data.all_plugins;
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
