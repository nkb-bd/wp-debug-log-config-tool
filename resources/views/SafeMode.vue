<template>
    <div>

        <Toast style="margin-top: 20px;" label="Sticky" position="bottom-right" group="br"/>
        <div class="flex flex-column gap-2 dlct-form">


            <div class=" flex justify-content-center dlct-form-item">
                <div class="message" severity="info"><i class="pi pi-fw pi-info-circle"></i> This will
                    deactivate/activate all selected plugins when turned on. After turning off previous active plugins
                    will be restored. Please note that it is in beta version currently.
                </div>
            </div>


            <div class="flex justify-content-center dlct-form-item">
                <div><label for="switch1">Enable SafeMode</label></div>
                <InputSwitch inputId="switch1" v-model="state.isSafeMode"/>
            </div>

            <div v-if="state.isSafeMode == true" class=" flex justify-content-center dlct-form-item">
                <div>
                    <label>Select Plugins that will remain active during safe mode </label>
                    <br>
                    <Button outlined raised="false" severity="secondary"  size="small" @click="resetSelection" label="Reset"/>
                    &nbsp;<Button outlined raised="false" severity="info" size="small" @click="selectAll"  label="Select All"/>
                </div>

                <Listbox listStyle="max-height:350px" filter option-vale="value" v-model="selectedCity" multiple :options="cities" optionLabel="name" class="w-full md:w-14rem" />

            </div>

            <div v-if="state.isSafeMode == true" class="flex justify-content-center dlct-form-item">
                <div></div>
                <div><small>Other plugins will remain deactivated</small>
                </div>

            </div>

            <div class=" flex justify-content-center dlct-form-item">
                <Button :loading="update.isLoading" size="medium" @click="updateSetting()" label="Update"/>
            </div>
        </div>
    </div>
</template>

<script setup>
    import {computed, onMounted, reactive, ref} from "vue";
    import {$get, $post} from "../request";
    import {useToast} from "primevue/usetoast";

    const toast = useToast()
    const selectedPlugins = reactive([
        {
            label: "Debug Log Viewer & Control",
            value: "debug-log-config-tool/plugin.php"
        }
    ]);
    const pluginList = ref([]);

    const selectedCity = ref();
    const cities = ref([
        { name: "Debug Log Viewer & Control", value: 'debug-log-config-tool/plugin.php' },
        { name: 'Paris', value: 'PRS' }
    ]);

    function resetSelection(){
        selectedCity.value = [];
    }
    function selectAll(){
        selectedCity.value = cities.value;
    }

    const state = reactive({
        error: null,
        isLoading: false,
        isSafeMode: false,
    });
    const update = reactive({
        isLoading: false,
        updating_key: null
    });

    const otherPluginState = computed(GetOtherPluginState);

    function GetOtherPluginState() {
        return '';
    }

    async function updateSetting() {
        try {
            update.isLoading = true;
            const args = {
                route: 'update_safe_mode',
                safe_mode: this.state.isSafeMode,
                selected_plugins: JSON.stringify(selectedCity.value)
            };
            const {data, error: fetchError} = await $post(args);
            if (data) {
                setTimeout(function() {
                    // window.location.reload();
                }, 1000);
                showTopLeft(data.value.data.message, data.value.data.success)
            } else if (fetchError) {
                state.error = fetchError;
            }
            update.isLoading = false;
        } catch (err) {

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
                console.log(selectedPlugins.value)
                console.log(data.value.data.selected_active_plugins_list)
                selectedCity.value = data.value.data.selected_active_plugins_list;
                cities.value = data.value.data.all_plugins;
                state.isSafeMode = data.value.data.safe_mode_status;
            } else if (fetchError) {
                console.log('fetchError',fetchError);
                state.error = fetchError;
            }
        } catch (err) {
            console.log('err',fetchError);

            state.error = err;
        } finally {
            console.log('finally');

            state.isLoading = false;
        }
    }

    const showTopLeft = (message, success) => {
        toast.add({severity: success ? 'success' : 'warn', summary: '', detail: message, group: 'br', life: 1200});
    };
    onMounted(() => {
        fetchSettings();

    });


</script>
