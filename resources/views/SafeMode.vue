<template>
    <div class="safe-mode-page">
        <Toast position="bottom-right" group="br"/>

        <div v-if="isLoadingPage" class="loading-container">
            <ProgressSpinner strokeWidth="5" aria-label="Loading"/>
        </div>

        <div v-else>
            <Card>
                <template #title>
                    <div class="card-title">
                        <i class="pi pi-shield" style="font-size: 1.2rem; margin-right: 0.5rem;"></i>
                        WordPress Safe Mode
                    </div>
                </template>

                <template #subtitle>
                    Troubleshoot your site by selectively disabling plugins
                </template>

                <template #content>
                    <div class="safe-mode-content">
                        <!-- Visual explanation -->
                        <div class="safe-mode-explainer">
                            <div class="explainer-step">
                                <div class="step-number">1</div>
                                <div class="step-content">
                                    <h3>Select Plugins</h3>
                                    <p>Choose which plugins should remain active during Safe Mode</p>
                                    <div class="step-visual">
                                        <div class="plugin-icons">
                                            <i class="pi pi-check-circle active-plugin"></i>
                                            <i class="pi pi-check-circle active-plugin"></i>
                                            <i class="pi pi-times-circle inactive-plugin"></i>
                                            <i class="pi pi-times-circle inactive-plugin"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="explainer-step">
                                <div class="step-number">2</div>
                                <div class="step-content">
                                    <h3>Enable Safe Mode</h3>
                                    <p>Activate Safe Mode to temporarily disable unselected plugins</p>
                                    <div class="step-visual">
                                        <div class="toggle-visual">
                                            <div class="toggle-track" :class="{'active': state.isSafeMode}">
                                                <div class="toggle-thumb"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="explainer-step">
                                <div class="step-number">3</div>
                                <div class="step-content">
                                    <h3>Troubleshoot</h3>
                                    <p>Debug your site with only essential plugins active</p>
                                    <div class="step-visual">
                                        <i class="pi pi-search troubleshoot-icon"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="explainer-step">
                                <div class="step-number">4</div>
                                <div class="step-content">
                                    <h3>Disable Safe Mode</h3>
                                    <p>Turn off Safe Mode to restore all previously active plugins</p>
                                    <div class="step-visual">
                                        <div class="plugin-icons">
                                            <i class="pi pi-check-circle active-plugin"></i>
                                            <i class="pi pi-check-circle active-plugin"></i>
                                            <i class="pi pi-check-circle active-plugin"></i>
                                            <i class="pi pi-check-circle active-plugin"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Safe Mode Controls -->
                        <div class="safe-mode-controls">
                            <Message severity="info">
                                <span>
                                    Safe Mode temporarily disables selected plugins to help identify conflicts.
                                    Your original plugin configuration will be restored when Safe Mode is turned off.
                                </span>
                            </Message>

                            <div class="safe-mode-toggle">
                                <div class="toggle-label">
                                    <label for="safe-mode-switch">Enable Safe Mode</label>
                                    <small v-if="state.isSafeMode" class="status-text active">Active</small>
                                    <small v-else class="status-text">Inactive</small>
                                </div>
                                <InputSwitch inputId="safe-mode-switch" v-model="state.isSafeMode" class="safe-mode-switch"/>
                            </div>

                            <div v-if="state.isSafeMode" class="plugin-selection">
                                <div class="selection-header">
                                    <h3>Active Plugins in Safe Mode</h3>
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
                                        listStyle="max-height:350px"
                                    />

                                    <div class="plugin-count">
                                        <Badge :value="selectedPlugin.length" severity="info"></Badge>
                                        <span>{{ selectedPlugin.length }} plugin{{ selectedPlugin.length !== 1 ? 's' : '' }} selected</span>
                                    </div>
                                </div>

                                <div class="plugin-status">
                                    <div class="status-item">
                                        <i class="pi pi-check-circle active-plugin"></i>
                                        <span>{{ selectedPlugin.length }} plugins will remain active</span>
                                    </div>
                                    <div class="status-item">
                                        <i class="pi pi-times-circle inactive-plugin"></i>
                                        <span>{{ pluginsList.length - selectedPlugin.length }} plugins will be deactivated</span>
                                    </div>
                                </div>
                            </div>

                            <div class="action-buttons">
                                <Button
                                    :loading="update.isLoading"
                                    :label="state.isSafeMode ? 'Apply Safe Mode Settings' : 'Update Settings'"
                                    :severity="state.isSafeMode ? 'warning' : 'primary'"
                                    icon="pi pi-shield"
                                    class="p-button-sm"
                                    @click="updateSetting()"
                                />
                            </div>
                        </div>
                    </div>
                </template>
            </Card>
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
                safe_mode: this.state.isSafeMode,
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
            update.isLoading = false;
        } catch (err) {

            state.error = err;
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
            console.log('err',fetchError);

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
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.loading-container {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 300px;
}

.card-title {
    display: flex;
    align-items: center;
}

/* Explainer section */
.safe-mode-content {
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

.safe-mode-explainer {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 1rem;
}

.explainer-step {
    display: flex;
    gap: 1rem;
    padding: 1rem;
    background-color: #f8f9fa;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.step-number {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 30px;
    height: 30px;
    background-color: #3b82f6;
    color: white;
    border-radius: 50%;
    font-weight: bold;
    flex-shrink: 0;
}

.step-content {
    flex: 1;
}

.step-content h3 {
    margin-top: 0;
    margin-bottom: 0.5rem;
    font-size: 1.1rem;
    color: #1e293b;
}

.step-content p {
    margin-top: 0;
    margin-bottom: 1rem;
    color: #64748b;
    font-size: 0.9rem;
}

.step-visual {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 60px;
    background-color: #fff;
    border-radius: 6px;
    border: 1px solid #e2e8f0;
}

.plugin-icons {
    display: flex;
    gap: 0.5rem;
}

.active-plugin {
    color: #22c55e;
    font-size: 1.2rem;
}

.inactive-plugin {
    color: #ef4444;
    font-size: 1.2rem;
}

.troubleshoot-icon {
    color: #3b82f6;
    font-size: 1.5rem;
}

.toggle-visual {
    position: relative;
    width: 50px;
    height: 24px;
}

.toggle-track {
    position: absolute;
    width: 100%;
    height: 100%;
    background-color: #e2e8f0;
    border-radius: 12px;
    transition: background-color 0.3s;
}

.toggle-track.active {
    background-color: #3b82f6;
}

.toggle-thumb {
    position: absolute;
    top: 2px;
    left: 2px;
    width: 20px;
    height: 20px;
    background-color: white;
    border-radius: 50%;
    transition: transform 0.3s;
}

.toggle-track.active .toggle-thumb {
    transform: translateX(26px);
}

/* Controls section */
.safe-mode-controls {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.safe-mode-toggle {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    background-color: #f8fafc;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
}

.toggle-label {
    display: flex;
    flex-direction: column;
}

.toggle-label label {
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.status-text {
    color: #64748b;
}

.status-text.active {
    color: #3b82f6;
    font-weight: 500;
}

.plugin-selection {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    padding: 1.5rem;
    background-color: #fff;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.selection-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.selection-header h3 {
    margin: 0;
    font-size: 1.1rem;
    color: #1e293b;
}

.selection-actions {
    display: flex;
    gap: 0.5rem;
}

.plugin-list-container {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.plugin-listbox {
    width: 100%;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
}

.plugin-count {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.9rem;
    color: #64748b;
}

.plugin-status {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    margin-top: 0.5rem;
}

.status-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.9rem;
    color: #64748b;
}

.action-buttons {
    display: flex;
    justify-content: flex-end;
    margin-top: 1rem;
}

@media (max-width: 768px) {
    .safe-mode-explainer {
        grid-template-columns: 1fr;
    }

    .safe-mode-toggle {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
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
