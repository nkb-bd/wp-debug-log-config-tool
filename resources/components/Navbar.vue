<template>
    <div class=" relative z-2">
        <Menubar :model="items">
            <template #item="{ item, props, hasSubmenu }">
                <router-link v-if="item.to" v-slot="{ href, navigate }" :to="item.to" custom>
                    <a v-ripple :href="href" v-bind="props.action" @click="navigate">
                        <span :class="item.icon" />
                        <span class="ml-4">{{ item.label }}</span>
                    </a>
                </router-link>

            </template>
            <template #end>

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
            to: '/settings'
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
