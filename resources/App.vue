<script setup>
import { RouterLink, RouterView } from 'vue-router'
import HelloWorld from './components/HelloWorld.vue'
import Navbar from "./components/Navbar";
import { ref, onMounted } from 'vue'

onMounted(() => {
    console.log('mounted')

})
</script>

<template>

    <div class="dlct-wrapper">
        <Navbar />
        <Toolbar>
            <template #start>
                <SplitButton label="Save" icon="pi pi-check" :model="items" severity="warning"></SplitButton>
            </template>

            <template #end>
                <Button icon="pi pi-refresh" severity="primary" outlined />
            </template>
        </Toolbar>
        <RouterView />

    </div>

</template>
<style type="text/css">

</style>
