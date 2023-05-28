
<template>
    <div class=" relative z-2">
        <Menubar :model="items">
            <template #start>
            </template>

        </Menubar>
    </div>
</template>

<script setup>
    import { ref } from "vue";

    const items = ref([
        {
            label: 'Logs',
            icon: 'pi pi-fw pi-file',
            to: "/"
        },
        {
            label: 'Settings',
            icon: 'pi pi-fw pi-pencil',
            to: "/settings"

        },
        {
            label: 'Notification',
            icon: 'pi pi-fw pi-user',
            to: "/notification"
        },
        {
            label: 'Support',
            icon: 'pi pi-fw pi-calendar',
            to: "/support"
        }
    ]);
</script>
