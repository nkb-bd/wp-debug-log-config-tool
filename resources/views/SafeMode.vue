<template>
    <div class="safe-mode-page">
        <Toast position="bottom-right" group="br"/>

        <div v-if="isLoadingPage" class="loading-container">
            <ProgressSpinner strokeWidth="5" aria-label="Loading"/>
        </div>

        <div v-else class="safe-mode-content">
            <div class="safe-mode-summary">
                <div class="safe-mode-heading">
                    <span class="safe-mode-icon"><i class="pi pi-shield"></i></span>
                    <div>
                        <h2>WordPress Safe Mode</h2>
                        <p>Temporarily disable unselected plugins and restore the previous state when Safe Mode is turned off.</p>
                    </div>
                </div>
                <span :class="['safe-mode-badge', state.isSafeMode ? 'is-active' : 'is-inactive']">
                    {{ state.isSafeMode ? 'Active' : 'Inactive' }}
                </span>
            </div>

            <div class="safe-mode-steps" aria-label="Safe Mode workflow">
                <span><strong>1</strong> Select plugins</span>
                <span><strong>2</strong> Enable Safe Mode</span>
                <span><strong>3</strong> Troubleshoot</span>
                <span><strong>4</strong> Restore</span>
            </div>

            <div class="safe-mode-controls">
                <div class="safe-mode-toggle">
                    <div class="toggle-label">
                        <label for="safe-mode-switch">Enable Safe Mode</label>
                        <small>Original plugin configuration is restored when this is turned off.</small>
                    </div>
                    <InputSwitch inputId="safe-mode-switch" v-model="state.isSafeMode" class="safe-mode-switch"/>
                </div>

                <div v-if="state.isSafeMode" class="plugin-selection">
                    <div class="selection-header">
                        <div>
                            <h3>Plugins to Keep Active</h3>
                            <p>Everything not selected will be temporarily deactivated.</p>
                        </div>
                        <div class="selection-actions">
                            <Button icon="pi pi-times" label="None" severity="secondary" text @click="resetSelection" />
                            <Button icon="pi pi-check" label="All" severity="secondary" text @click="selectAll" />
                        </div>
                    </div>

                    <div class="plugin-list-container">
                        <Listbox
                            filter
                            v-model="selectedPlugin"
                            multiple
                            :options="pluginsList"
                            optionLabel="name"
                            class="plugin-listbox"
                            listStyle="max-height:300px"
                        />

                        <div class="plugin-status">
                            <span class="status-pill is-active">
                                <i class="pi pi-check-circle"></i>
                                {{ selectedPlugin.length }} remain active
                            </span>
                            <span class="status-pill is-inactive">
                                <i class="pi pi-times-circle"></i>
                                {{ pluginsList.length - selectedPlugin.length }} deactivate
                            </span>
                        </div>
                    </div>
                </div>

                <div class="action-buttons">
                    <Button
                        :loading="update.isLoading"
                        :label="state.isSafeMode ? 'Apply Safe Mode' : 'Save Safe Mode'"
                        :severity="state.isSafeMode ? 'warning' : 'primary'"
                        icon="pi pi-shield"
                        class="p-button-sm"
                        @click="updateSetting()"
                    />
                </div>
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
    const isLoadingPage = ref('false');
    const modifiedActivatedplugins = ref([]);
    const modifiedDeActivatedplugins = ref([]);

    const selectedPlugin = ref([
        { name: "Debug Log Helper", value: 'debug-log-config-tool/plugin.php' },
    ]);
    const pluginsList = ref([
    ]);

    function resetSelection(){
        selectedPlugin.value = [];
    }
    function selectAll(){
        selectedPlugin.value = pluginsList.value;
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
                safe_mode: state.isSafeMode,
                selected_plugins: JSON.stringify(selectedPlugin.value)
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
        } catch (err) {

            state.error = err;
        } finally {
            update.isLoading = false;
        }
    }

    async function fetchSettings() {
        try {
            state.isLoading = true;
            isLoadingPage.value = true;
            const args = {
                route: 'get_safe_mode'
            };
            const {data, error: fetchError} = await $get(args);
            if (data) {
                if (data.value.data.selected_active_plugins_list.length){
                    selectedPlugin.value = data.value.data.selected_active_plugins_list;
                }
                pluginsList.value = data.value.data.all_plugins;
                state.isSafeMode = data.value.data.safe_mode_status;
            } else if (fetchError) {
                console.log('fetchError',fetchError);
                state.error = fetchError;
            }
        } catch (err) {
            console.log('err', err);
            state.error = err;
        } finally {
            state.isLoading = false;
            isLoadingPage.value = false;

        }
    }

    const showTopLeft = (message, success) => {
        toast.add({severity: success ? 'success' : 'warn', summary: '', detail: message, group: 'br', life: 1200});
    };
    onMounted(() => {
        fetchSettings();

    });


</script>

<style scoped>
.safe-mode-page {
    margin: 0;
    padding: 0;
}

.loading-container {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 300px;
}

.safe-mode-content {
    display: grid;
    gap: 14px;
}

.safe-mode-summary {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 16px;
    border: 1px solid #dbeafe;
    border-radius: 8px;
    background: #fff;
}

.safe-mode-heading {
    display: flex;
    align-items: center;
    gap: 12px;
    min-width: 0;
}

.safe-mode-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 auto;
    width: 38px;
    height: 38px;
    border-radius: 8px;
    background: #eff6ff;
    color: #2563eb;
}

.safe-mode-heading h2 {
    margin: 0 0 4px;
    color: #1e293b;
    font-size: 18px;
}

.safe-mode-heading p {
    margin: 0;
    color: #64748b;
    font-size: 13px;
}

.safe-mode-badge,
.status-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 5px 9px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 700;
    white-space: nowrap;
}

.safe-mode-badge.is-active,
.status-pill.is-active {
    background: #ecfdf5;
    color: #047857;
}

.safe-mode-badge.is-inactive {
    background: #f1f5f9;
    color: #64748b;
}

.status-pill.is-inactive {
    background: #fff0f0;
    color: #c92525;
}

.safe-mode-steps {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.safe-mode-steps span {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    min-height: 32px;
    padding: 0 10px;
    border: 1px solid #dbeafe;
    border-radius: 8px;
    background: #fff;
    color: #475569;
    font-size: 13px;
    font-weight: 600;
}

.safe-mode-steps strong {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 20px;
    height: 20px;
    border-radius: 999px;
    background: #2563eb;
    color: #fff;
    font-size: 12px;
}

.safe-mode-controls {
    display: grid;
    gap: 14px;
}

.safe-mode-toggle {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
    padding: 16px;
    background-color: #fff;
    border-radius: 8px;
    border: 1px solid #dbeafe;
}

.toggle-label {
    display: flex;
    flex-direction: column;
}

.toggle-label label {
    font-weight: 600;
    margin-bottom: 4px;
    color: #1e293b;
}

.plugin-selection {
    display: flex;
    flex-direction: column;
    gap: 12px;
    padding: 16px;
    background-color: #fff;
    border-radius: 8px;
    border: 1px solid #dbeafe;
}

.selection-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.selection-header h3 {
    margin: 0 0 4px;
    font-size: 15px;
    color: #1e293b;
}

.selection-header p {
    margin: 0;
    color: #64748b;
    font-size: 13px;
}

.selection-actions {
    display: flex;
    gap: 0.5rem;
}

.plugin-list-container {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.plugin-listbox {
    width: 100%;
    border: 1px solid #dbeafe;
    border-radius: 6px;
}

.plugin-status {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.action-buttons {
    display: flex;
    justify-content: flex-end;
}

@media (max-width: 768px) {
    .safe-mode-summary,
    .safe-mode-toggle {
        flex-direction: column;
        align-items: flex-start;
    }

    .selection-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }

    .action-buttons {
        justify-content: center;
    }
}
</style>
