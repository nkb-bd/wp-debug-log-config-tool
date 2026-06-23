
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
            <Column field="value" header="Status"  style="width: 40%" >
                <template #body="slotProps">
                    <ProgressSpinner v-if="update.isLoading && update.updating_key == slotProps.data.name" style=" margin-bottom: 5px;margin-right: 10px;text-align: center;width: 20px; height: 20px" strokeWidth="4" fill="var(--surface-ground)" aria-label="Loading"/>
                     <InputSwitch v-if="slotProps.data.name != 'WP_DEBUG_LOG'" :disabled="update.isLoading" @change="updateSettingFromSwitch(slotProps.index, slotProps.data.name, slotProps.data.value)" size="small"  v-model="state.settings[slotProps.index].value " />
                     <InputText readonly style="width:100%;"  v-if="slotProps.data.name == 'WP_DEBUG_LOG'" :disabled="update.isLoading" @change="updateSettingFromSwitch(slotProps.index, slotProps.data.name, slotProps.data.value)" size="small"  v-model="state.settings[slotProps.index].value " />
                </template>
            </Column>
        </DataTable>

        <div class="dlct-emergency-card">
            <div class="dlct-emergency-head">
                <h3>Emergency Log Viewer</h3>
                <span :class="['dlct-emergency-badge', emergency.enabled ? 'on' : 'off']">
                    {{ emergency.enabled ? 'Enabled' : 'Disabled' }}
                </span>
            </div>
            <p class="dlct-emergency-desc">
                A password-protected page that lets you read the debug log even when your site is down
                with a fatal error and wp-admin is unreachable. Set a username and password, then bookmark
                the URL shown below before you need it.
            </p>
            <div class="dlct-emergency-form">
                <InputText v-model="emergency.auth_user" placeholder="Username" :disabled="emergency.isLoading" />
                <InputText type="password" v-model="emergency.auth_pass" placeholder="Password (min 8 characters)" :disabled="emergency.isLoading" />
                <Button :label="emergency.enabled ? 'Update' : 'Enable'" :loading="emergency.isLoading" @click="saveEmergency" />
                <Button v-if="emergency.enabled" label="Disable" severity="danger" outlined :loading="emergency.isLoading" @click="disableEmergency" />
            </div>
            <div v-if="emergency.enabled && emergency.url" class="dlct-emergency-url">
                <InputText readonly :value="emergency.url" @focus="$event.target.select()" />
                <Button icon="pi pi-copy" label="Copy" outlined @click="copyEmergencyUrl" />
            </div>
            <p v-if="emergency.enabled" class="dlct-emergency-warn">
                Anyone with this URL and the credentials can read your debug log. Keep them private and rotate them after use.
            </p>
        </div>
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
    const emergency = reactive({
        enabled: false,
        auth_user: '',
        auth_pass: '',
        url: '',
        isLoading: false,
    });

    async function fetchEmergency() {
        try {
            const { data } = await $get({ route: 'get_emergency_viewer' });
            if (data) {
                const d = data.value.data;
                emergency.enabled = !!d.enabled;
                emergency.auth_user = d.auth_user || '';
                emergency.url = d.url || '';
            }
        } catch (err) {
            state.error = err;
        }
    }

    async function saveEmergency() {
        if ((emergency.auth_user || '').length < 3 || (emergency.auth_pass || '').length < 8) {
            showTopLeft('Username needs 3+ characters and password 8+.', false);
            return;
        }
        try {
            emergency.isLoading = true;
            const { data } = await $post({
                route: 'update_emergency_viewer',
                auth_user: emergency.auth_user,
                auth_pass: emergency.auth_pass,
            });
            if (data) {
                const d = data.value.data;
                emergency.enabled = !!d.enabled;
                emergency.url = d.url || emergency.url;
                emergency.auth_pass = '';
                showTopLeft(d.message || 'Saved', d.success);
            }
        } catch (err) {
            showTopLeft('Could not save emergency viewer settings.', false);
        } finally {
            emergency.isLoading = false;
        }
    }

    async function disableEmergency() {
        try {
            emergency.isLoading = true;
            const { data } = await $post({ route: 'disable_emergency_viewer' });
            if (data) {
                const d = data.value.data;
                emergency.enabled = false;
                emergency.auth_pass = '';
                showTopLeft(d.message || 'Disabled', d.success);
            }
        } catch (err) {
            showTopLeft('Could not disable emergency viewer.', false);
        } finally {
            emergency.isLoading = false;
        }
    }

    function copyEmergencyUrl() {
        if (navigator.clipboard && emergency.url) {
            navigator.clipboard.writeText(emergency.url);
            showTopLeft('URL copied to clipboard.', true);
        }
    }

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
        toast.add({ severity: success ? 'success' : 'warn', summary: '', detail: message, group: 'br', life: 3500 });
    };
    onMounted(() => {
        fetchSettings();
        fetchEmergency();
        jQuery(document).on('dlct:debug_status_changed', (event, data) => {
            fetchSettings();
        });
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
     .dlct-emergency-card {
        margin-top: 24px;
        padding: 20px;
        border: 1px solid var(--surface-border, #dee2e6);
        border-radius: 10px;
        background: var(--surface-card, #fff);
     }
     .dlct-emergency-head {
        display: flex;
        align-items: center;
        gap: 12px;
     }
     .dlct-emergency-head h3 { margin: 0; font-size: 16px; }
     .dlct-emergency-badge {
        font-size: 11px;
        font-weight: 600;
        padding: 2px 10px;
        border-radius: 999px;
     }
     .dlct-emergency-badge.on { background: #def7ec; color: #03543f; }
     .dlct-emergency-badge.off { background: #f3f4f6; color: #6b7280; }
     .dlct-emergency-desc { color: var(--text-color-secondary, #6b7280); margin: 10px 0 16px; max-width: 760px; }
     .dlct-emergency-form { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; }
     .dlct-emergency-form .p-inputtext { min-width: 220px; }
     .dlct-emergency-url { display: flex; gap: 10px; margin-top: 14px; align-items: center; }
     .dlct-emergency-url .p-inputtext { flex: 1; font-family: ui-monospace, Menlo, monospace; }
     .dlct-emergency-warn { color: #9a3412; background: #fff7ed; border: 1px solid #fed7aa; padding: 8px 12px; border-radius: 8px; margin-top: 14px; font-size: 13px; }
</style>
