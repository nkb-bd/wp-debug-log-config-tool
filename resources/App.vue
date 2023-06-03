<script setup>
    import {RouterView} from 'vue-router'
    import Navbar from "./components/Navbar";
    import {ref} from 'vue'

    const trigger = ref(null)

    function callback(type) {
        trigger.value = type;
    }

</script>

<template>
    <div class="dlct-wrapper">
        <Navbar @triggerEventDlc="callback"/>
        <RouterView :trigger="trigger"/>
    </div>
</template>
