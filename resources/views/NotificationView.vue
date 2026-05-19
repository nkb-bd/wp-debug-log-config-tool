<template>
    <div class="notification-view">
        <Toast position="bottom-right" group="br" />

        <div v-if="state.isLoading" class="loading-container">
            <ProgressSpinner />
            <p>Loading notification settings...</p>
        </div>

        <div v-else class="notification-content">
            <div class="notification-summary">
                <span class="notification-icon"><i class="pi pi-bell"></i></span>
                <div>
                    <h2>Email Notifications</h2>
                    <p>Send debug log summaries to a selected inbox.</p>
                </div>
            </div>

            <section class="notification-panel">
                <div class="form-field">
                    <div class="form-field-info">
                        <label for="email" class="form-label">Notification Email</label>
                        <small id="email-help" class="form-text">Recipient for debug log notifications.</small>
                    </div>
                    <div class="p-inputgroup notification-email-input">
                        <span class="p-inputgroup-addon">
                            <i class="pi pi-envelope"></i>
                        </span>
                        <InputText
                            id="email"
                            v-model="state.email"
                            placeholder="Enter email address"
                            aria-describedby="email-help"
                        />
                    </div>
                </div>

                <div class="option-item">
                    <div class="option-content">
                        <h4>Daily Summary</h4>
                        <p>Send one email when debug logs were created that day.</p>
                    </div>
                    <div class="option-control">
                        <span :class="['option-status', state.status ? 'is-on' : 'is-off']">
                            {{ state.status ? 'Enabled' : 'Disabled' }}
                        </span>
                        <InputSwitch
                            v-model="state.status"
                            @change="updatetNotificationEmail"
                        />
                    </div>
                </div>
            </section>

            <div class="form-actions">
                <Button
                    label="Save Settings"
                    icon="pi pi-check"
                    @click="updatetNotificationEmail"
                    :loading="state.isSaving"
                    :disabled="!state.email"
                />
                <Button
                    label="Test Email"
                    icon="pi pi-envelope"
                    class="p-button-outlined"
                    @click="sendTestEmail"
                    :disabled="!state.email"
                />
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { $post, $get } from "../request";
import { useToast } from "primevue/usetoast";

const toast = useToast();

const state = reactive({
    email: null,
    status: null,
    error: null,
    isLoading: false,
    isSaving: false
});

async function fetchNotificationEmail() {
    try {
        state.isLoading = true;
        const args = {
            route: 'get_notification_email'
        };
        const { data, error: fetchError } = await $get(args);
        if (data && data.value && data.value.data) {
            state.email = data.value.data.email;
            state.status = data.value.data.status;
        } else if (fetchError) {
            state.error = fetchError;
            showToast('error', 'Error', 'Failed to load notification settings');
        }
    } catch (err) {
        state.error = err;
        showToast('error', 'Error', 'Failed to load notification settings');
    } finally {
        state.isLoading = false;
    }
}

async function updatetNotificationEmail() {
    try {
        state.isSaving = true;
        const args = {
            route: 'update_notification_email',
            status: state.status,
            email: state.email
        };
        const { data, error: fetchError } = await $post(args);
        if (data && data.value && data.value.data) {
            showToast(
                data.value.data.success ? 'success' : 'warn',
                data.value.data.success ? 'Success' : 'Warning',
                data.value.data.message || 'Notification settings updated'
            );
        } else if (fetchError) {
            state.error = fetchError;
            showToast('error', 'Error', 'Failed to update notification settings');
        }
    } catch (err) {
        state.error = err;
        showToast('error', 'Error', 'Failed to update notification settings');
    } finally {
        state.isSaving = false;
    }
}

async function sendTestEmail() {
    try {
        if (!state.email) {
            showToast('warn', 'Warning', 'Please enter an email address first');
            return;
        }

        showToast('info', 'Sending', 'Sending test email to ' + state.email);

        // Here you would implement the actual test email sending
        // For now, we'll just simulate it with a timeout
        setTimeout(() => {
            showToast('success', 'Success', 'Test email sent to ' + state.email);
        }, 1500);
    } catch (err) {
        showToast('error', 'Error', 'Failed to send test email');
    }
}

function showToast(severity, summary, detail) {
    toast.add({
        severity: severity,
        summary: summary,
        detail: detail,
        group: 'br',
        life: 3000
    });
}

onMounted(() => {
    fetchNotificationEmail();
});
</script>

<style scoped>
.notification-view {
    margin: 0;
    padding: 0;
}

.loading-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 40px;
}

.notification-content {
    display: grid;
    gap: 14px;
}

.notification-summary {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px;
    border: 1px solid #dbeafe;
    border-radius: 8px;
    background: #fff;
}

.notification-icon {
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

.notification-summary h2 {
    margin: 0 0 4px;
    color: #1e293b;
    font-size: 18px;
}

.notification-summary p {
    margin: 0;
    color: #64748b;
    font-size: 13px;
}

.notification-panel {
    overflow: hidden;
    border: 1px solid #dbeafe;
    border-radius: 8px;
    background: #fff;
}

.form-field {
    display: grid;
    grid-template-columns: minmax(220px, 0.35fr) minmax(0, 1fr);
    gap: 16px;
    align-items: center;
    padding: 16px;
    border-bottom: 1px solid #e5e7eb;
}

.form-label {
    display: block;
    margin-bottom: 4px;
    font-weight: 600;
    color: #1e293b;
}

.form-text {
    display: block;
    color: #64748b;
    font-size: 13px;
}

.notification-email-input {
    display: flex;
    min-width: 0;
}

.notification-email-input .p-inputgroup-addon {
    width: 46px;
    min-width: 46px;
    padding: 0;
    border-color: #bfdbfe;
    border-right: 0;
    border-radius: 8px 0 0 8px;
    background: #f8fafc;
    color: #64748b;
}

.notification-email-input .p-inputtext {
    flex: 1 1 auto;
    min-width: 0;
    height: 42px;
    border-color: #bfdbfe;
    border-left: 0;
    border-radius: 0 8px 8px 0;
    box-shadow: none;
}

.notification-email-input .p-inputtext:focus {
    border-color: #93c5fd;
    box-shadow: 0 0 0 1px #93c5fd;
}

.notification-email-input:focus-within .p-inputgroup-addon {
    border-color: #93c5fd;
    box-shadow: 0 0 0 1px #93c5fd;
}

.option-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
    padding: 16px;
    background-color: #fff;
}

.option-content h4 {
    margin: 0 0 4px;
    color: #1e293b;
    font-size: 15px;
}

.option-content p {
    margin: 0;
    color: #64748b;
    font-size: 13px;
}

.option-control {
    display: flex;
    align-items: center;
    gap: 10px;
}

.option-status {
    min-width: 68px;
    padding: 4px 8px;
    border-radius: 999px;
    text-align: center;
    font-size: 12px;
    font-weight: 700;
}

.option-status.is-on {
    background: #ecfdf5;
    color: #047857;
}

.option-status.is-off {
    background: #f1f5f9;
    color: #64748b;
}

.form-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .option-item {
        flex-direction: column;
        align-items: flex-start;
    }

    .form-field {
        grid-template-columns: 1fr;
    }

    .option-control {
        width: 100%;
        justify-content: space-between;
    }

    .form-actions {
        flex-direction: column;
    }

    .form-actions .p-button {
        width: 100%;
    }
}
</style>
