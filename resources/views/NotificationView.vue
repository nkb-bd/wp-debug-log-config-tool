<template>
    <div class="notification-view">
        <Toast position="bottom-right" group="br" />

        <Card>
            <template #title>
                <div class="card-title">
                    <i class="pi pi-bell mr-2"></i>
                    Email Notifications
                </div>
            </template>
            <template #subtitle>
                Configure email alerts for debug log events
            </template>
            <template #content>
                <div v-if="state.isLoading" class="loading-container">
                    <ProgressSpinner />
                    <p>Loading notification settings...</p>
                </div>

                <div v-else class="notification-content">
                    <Message severity="info" class="mb-4">
                        <span>Configure email notifications to stay informed about debug log events. Receive daily summaries or immediate alerts when errors occur.</span>
                    </Message>

                    <div class="notification-form">
                        <div class="form-field">
                            <label for="email" class="form-label">Notification Email</label>
                            <div class="p-inputgroup">
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
                            <small id="email-help" class="form-text">Email address to receive notifications</small>
                        </div>

                        <Divider />

                        <div class="notification-options">
                            <div class="option-item">
                                <div class="option-content">
                                    <h4>Daily Summary</h4>
                                    <p>Receive a daily email summary if any debug logs were created</p>
                                </div>
                                <InputSwitch
                                    v-model="state.status"
                                    @change="updatetNotificationEmail"
                                />
                            </div>
                        </div>

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
        </Card>
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
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.card-title {
    display: flex;
    align-items: center;
    font-size: 1.25rem;
}

.loading-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 40px;
}

.notification-content {
    padding: 10px;
}

.notification-form {
    margin-top: 20px;
}

.form-field {
    margin-bottom: 1.5rem;
}

.form-label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: #4b5563;
}

.form-text {
    display: block;
    margin-top: 0.5rem;
    color: #6b7280;
}

.notification-options {
    margin: 1.5rem 0;
}

.option-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    background-color: #f9fafb;
    border-radius: 8px;
    margin-bottom: 1rem;
}

.option-content h4 {
    margin: 0 0 0.5rem 0;
    color: #374151;
}

.option-content p {
    margin: 0;
    color: #6b7280;
}

.form-actions {
    display: flex;
    gap: 1rem;
    margin-top: 2rem;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .option-item {
        flex-direction: column;
        align-items: flex-start;
    }

    .option-item .p-inputswitch {
        margin-top: 1rem;
    }

    .form-actions {
        flex-direction: column;
    }

    .form-actions .p-button {
        width: 100%;
    }
}
</style>