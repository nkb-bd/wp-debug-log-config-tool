<template>
    <div>
        <Toast style="margin-top: 20px;" position="bottom-right" group="br" />
        <div class="flex flex-column gap-2 dlct-form">
            <div class=" flex justify-content-center dlct-form-item">
                <label for="email">Notification Email</label>
                <InputText id="email" v-model="state.email" aria-describedby="email" />
                <Button size="medium" @click="updatetNotificationEmail" label="Update" />

            </div>

            <div class=" flex justify-content-center dlct-form-item">
                <p> A notification email will be sent with when a deubg</p>
            </div>


            <div class=" flex justify-content-center dlct-form-item">
                <p> Turn on/off daily schedule mail if there is any log created</p>
                <InputSwitch @change="updatetNotificationEmail" v-model="state.status" />
            </div>

        </div>
    </div>
</template>

<script setup>
    import {ref, reactive, onMounted, computed} from 'vue';
    import {useFetch, $post, $get} from "../request";
    import { useToast } from "primevue/usetoast";


    const toast = useToast()
    const searchString = ref(['error', 'Hello']);

    const state = reactive({
        email: null,
        status: null,
        error: null,
        isLoading: false,
    });

    async function fetchNotificationEmail(){
        try {
            state.isLoading = true;
            const args = {
                route: 'get_notification_email'
            };
            const {data, error: fetchError} = await $get(args);
            if (data) {
                state.email = data.value.data.email;
                state.status = data.value.data.status;
            } else if (fetchError) {
                state.error = fetchError;
            }
        } catch (err) {
            state.error = err;
        } finally {
            state.isLoading = false;
        }

    }

     async function updatetNotificationEmail(){
        try {
            state.isLoading = true;
            const args = {
                route: 'update_notification_email',
                status: state.status,
                email: state.email
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
            state.isLoading = false;
        }

    }

    const showTopLeft = (message, type) => {
        toast.add({ severity: type == true ?'success' :'warn', summary: '', detail:message, group: 'br', life: 3000 });
    };
    onMounted(() => {
        fetchNotificationEmail();
    });
</script>
