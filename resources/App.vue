<script setup>
    import {RouterView} from 'vue-router'
    import Navbar from "./components/Navbar";
    import {ref} from 'vue'

    const trigger = ref(null)
    const refresh = ref(null)

    function callback(type) {
        trigger.value = type;
        refresh.value = !refresh.value;
    }

</script>

<template>
    <div class="dlct-wrapper">
        <Navbar @triggerEventDlc="callback"/>
        <RouterView :key="refresh" :trigger="trigger"/>
    </div>
</template>
