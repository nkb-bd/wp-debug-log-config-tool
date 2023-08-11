<template>
    <div class=" relative z-2">
        <Menubar exact :model="items">
            <template #start>
            </template>

            <template v-if="path == '/'" #end>
                <Button @click="$emit('triggerEventDlc','delete')" size="small" style="margin-right: 10px;"
                        icon="pi pi-trash" severity="danger"/>
                <Button @click="$emit('triggerEventDlc','refresh')" size="small" icon="pi pi-refresh" severity="info"/>
            </template>

        </Menubar>
    </div>
</template>

<script setup>
    import {ref,watch} from "vue";
    import {useRoute} from 'vue-router'
    import {computed} from 'vue'

    const route=useRoute();

    const path = computed(() =>route.path)
    const items = ref([
        {
            label: 'Logs',
            icon: 'pi pi-fw pi-file',
            to: "/"
        },
        {
            label: 'Settings',
            icon: 'pi pi-fw pi-cog',
            to: "/settings"
        },
        {
            label: 'Notification',
            icon: 'pi pi-fw pi-bell',
            to: "/notification"
        },
        {
            label: 'Support',
            to: "/support"
        },

    ]);
</script>
