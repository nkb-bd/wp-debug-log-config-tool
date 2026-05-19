<template>
    <div class="support-page">
        <div class="support-content">
            <section class="overview-panel">
                <div class="overview-heading">
                    <span class="overview-icon"><i class="pi pi-info-circle"></i></span>
                    <div>
                        <h2>About Debug Log Config Tool</h2>
                        <p>Focused debugging tools for WordPress developers.</p>
                    </div>
                </div>
                <p class="plugin-info">
                    View logs, inspect traces, run guarded terminal commands, and isolate plugin conflicts from one admin screen.
                </p>
            </section>

            <section class="feature-panel">
                <h3>Key Features</h3>
                <div class="feature-grid">
                    <div v-for="feature in features" :key="feature.title" class="feature-row">
                        <span class="feature-icon-container">
                            <i :class="feature.icon"></i>
                        </span>
                        <div class="feature-content">
                            <h4>{{ feature.title }}</h4>
                            <p>{{ feature.copy }}</p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="meta-panel">
                <div class="resource-card">
                    <h3>Resources</h3>
                    <div class="support-links">
                        <Button icon="pi pi-exclamation-circle" label="Report Issue" class="p-button-outlined p-button-sm" @click="openLink('https://github.com/nkb-bd/wp-debug-log-config-tool/issues')" />
                        <Button icon="pi pi-github" label="Developer Profile" class="p-button-outlined p-button-sm" @click="openLink('https://github.com/nkb-bd')" />
                    </div>
                </div>

                <div class="developer-card">
                    <div class="developer-avatar">
                        <i class="pi pi-user"></i>
                    </div>
                    <div class="developer-details">
                        <h3>Lukman Nakib</h3>
                        <p>WordPress Developer & Plugin Author</p>
                        <div class="developer-social">
                            <Button icon="pi pi-github" class="p-button-rounded p-button-text" @click="openLink('https://github.com/nkb-bd')" />
                            <Button icon="pi pi-twitter" class="p-button-rounded p-button-text p-button-info" @click="openLink('https://twitter.com/nkb_bd')" />
                            <Button icon="pi pi-linkedin" class="p-button-rounded p-button-text p-button-help" @click="openLink('https://www.linkedin.com/in/lukman-nakib/')" />
                        </div>
                    </div>
                </div>
            </section>

            <div class="version-info">
                <Chip label="Version: 3.0.1" icon="pi pi-tag" />
                <Chip label="License: GPL v2 or later" icon="pi pi-check-circle" />
            </div>
        </div>
    </div>
</template>

<script setup>
import { useToast } from 'primevue/usetoast';

const toast = useToast();
const features = [
    { title: 'Debug Terminal', icon: 'pi pi-code', copy: 'WP-CLI style commands for WordPress debugging.' },
    { title: 'Live Logs', icon: 'pi pi-refresh', copy: 'Auto-refreshing log view with filters and search.' },
    { title: 'Stack Traces', icon: 'pi pi-list', copy: 'Readable stack trace analysis for PHP errors.' },
    { title: 'Safe Mode', icon: 'pi pi-shield', copy: 'Temporarily isolate plugin conflicts.' },
    { title: 'Database Tools', icon: 'pi pi-database', copy: 'Guarded database inspection commands.' },
    { title: 'Notifications', icon: 'pi pi-bell', copy: 'Daily email summaries for debug activity.' },
];

function openLink(url) {
    window.open(url, '_blank');
    toast.add({
        severity: 'info',
        summary: 'Opening Link',
        detail: 'Opening external link in a new tab',
        life: 3000
    });
}
</script>

<style scoped>
.support-page {
    margin: 0;
    padding: 0;
}

.support-content {
    display: grid;
    gap: 14px;
}

.overview-panel,
.feature-panel,
.resource-card,
.developer-card {
    border: 1px solid #dbeafe;
    border-radius: 8px;
    background: #fff;
}

.overview-panel {
    padding: 16px;
}

.overview-heading {
    display: flex;
    align-items: center;
    gap: 12px;
}

.overview-icon,
.feature-icon-container,
.developer-avatar {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 auto;
    border-radius: 8px;
    background: #eff6ff;
    color: #2563eb;
}

.overview-icon {
    width: 38px;
    height: 38px;
}

.overview-heading h2 {
    margin: 0 0 4px;
    color: #1e293b;
    font-size: 18px;
}

.overview-heading p,
.plugin-info {
    margin: 0;
    color: #64748b;
    font-size: 13px;
}

.plugin-info {
    margin-top: 12px;
}

.feature-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 0;
}

.feature-panel {
    overflow: hidden;
}

.feature-panel h3,
.resource-card h3 {
    margin: 0;
    padding: 12px 14px;
    border-bottom: 1px solid #e5e7eb;
    color: #334155;
    font-size: 15px;
}

.feature-row {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px;
    border-right: 1px solid #e5e7eb;
    border-bottom: 1px solid #e5e7eb;
}

.feature-icon-container {
    width: 34px;
    height: 34px;
}

.feature-icon-container i {
    font-size: 16px;
    line-height: 1;
}

.feature-content h4 {
    margin: 0 0 3px;
    color: #1e293b;
    font-size: 14px;
}

.feature-content p {
    margin: 0;
    color: #64748b;
    font-size: 13px;
}

.meta-panel {
    display: grid;
    grid-template-columns: minmax(0, 1fr) minmax(320px, 0.8fr);
    gap: 14px;
}

.support-links {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    padding: 14px;
}

.developer-card {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px;
}

.developer-avatar {
    width: 38px;
    height: 38px;
}

.developer-details {
    flex: 1;
}

.developer-details h3 {
    margin: 0 0 3px;
    color: #1e293b;
    font-size: 15px;
}

.developer-details p {
    margin: 0 0 8px;
    color: #64748b;
    font-size: 13px;
}

.developer-social {
    display: flex;
    gap: 4px;
}

.version-info {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    justify-content: flex-start;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .feature-grid {
        grid-template-columns: 1fr;
    }

    .meta-panel {
        grid-template-columns: 1fr;
    }

    .support-links {
        flex-direction: column;
        align-items: center;
    }

    .support-links .p-button {
        width: 100%;
        max-width: 300px;
    }
}
</style>
