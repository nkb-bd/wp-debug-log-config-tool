<script setup>
    import {RouterLink, RouterView, useRoute} from 'vue-router'
    import {computed, ref} from 'vue'

    const route = useRoute()
    const trigger = ref(null)
    const refresh = ref(null)

    const navItems = [
        {label: 'Overview', icon: 'pi pi-chart-pie', to: '/support'},
        {label: 'Debug Logs', icon: 'pi pi-chart-line', to: '/'},
        {label: 'Settings', icon: 'pi pi-cog', to: '/settings'},
        {label: 'Safe Mode', icon: 'pi pi-shield', to: '/safemode'},
        {label: 'Notifications', icon: 'pi pi-bell', to: '/notification'},
        {label: 'Terminal', icon: 'pi pi-code', to: '/terminal'},
        {label: 'Terminal Settings', icon: 'pi pi-sliders-h', to: '/terminal-settings'},
    ];

    const pageMeta = {
        '/': {
            title: 'Machine Logs and Errors',
            copy: 'Live debug output, query traces, and stack details from this WordPress install.',
            selector: 'All logs'
        },
        '/settings': {
            title: 'Debug Configuration',
            copy: 'WordPress debug constants and runtime logging controls.',
            selector: 'wp-config.php'
        },
        '/safemode': {
            title: 'Safe Mode',
            copy: 'Isolate plugin conflicts without losing the previous active-plugin state.',
            selector: 'Plugins'
        },
        '/notification': {
            title: 'Error Notifications',
            copy: 'Email delivery controls for debug log events.',
            selector: 'Email'
        },
        '/terminal': {
            title: 'Debug Terminal',
            copy: 'Admin-only command surface for WordPress debugging.',
            selector: 'WP CLI'
        },
        '/terminal-settings': {
            title: 'Terminal Settings',
            copy: 'Access controls for terminal and database commands.',
            selector: 'Access'
        },
        '/support': {
            title: 'Diagnostics',
            copy: 'Plugin resources, environment hints, and support links.',
            selector: 'System'
        },
    }

    const currentMeta = computed(() => pageMeta[route.path] || pageMeta['/'])

    function callback(type) {
        trigger.value = type;
        refresh.value = !refresh.value;
    }
</script>

<template>
    <div class="dlct-wrapper">
        <header class="dlct-shell-header">
            <div class="dlct-brand">
                <span class="dlct-brand-icon"><i class="pi pi-bolt"></i></span>
                <span>DebugLog</span>
            </div>

            <nav class="dlct-nav" aria-label="Debug Log Manager">
                <RouterLink
                    v-for="item in navItems"
                    :key="item.to"
                    :to="item.to"
                    class="dlct-nav-item"
                >
                    <span class="dlct-nav-icon"><i :class="item.icon"></i></span>
                    <span>{{ item.label }}</span>
                </RouterLink>
            </nav>
        </header>

        <main class="dlct-main">
            <header class="dlct-page-header">
                <div class="dlct-page-title">
                    <div class="dlct-title-row">
                        <h1>{{ currentMeta.title }}</h1>
                        <span class="dlct-context-select">{{ currentMeta.selector }} <i class="pi pi-chevron-down"></i></span>
                    </div>
                    <p>{{ currentMeta.copy }}</p>
                </div>
            </header>

            <RouterView :key="refresh" :trigger="trigger" @triggerEventDlc="callback"/>
        </main>
    </div>
</template>
