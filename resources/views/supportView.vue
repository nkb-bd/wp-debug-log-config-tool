<template>
    <div class="support-page">
        <Card>
            <template #title>
                <div class="card-title">
                    <i class="pi pi-info-circle mr-2"></i>
                    About Debug Log Config Tool
                </div>
            </template>
            <template #subtitle>
                Powerful debugging tools for WordPress developers
            </template>
            <template #content>
                <div class="support-content">
                    <div class="plugin-info">
                        <p>
                            Debug Log Config Tool is a WordPress plugin designed to simplify debugging and troubleshooting WordPress sites.
                            It provides powerful tools for viewing, analyzing, and managing debug logs with an intuitive interface.
                        </p>
                    </div>

                    <Divider align="center">
                        <span class="divider-text">Key Features</span>
                    </Divider>

                    <div class="feature-grid">
                        <div class="feature-card">
                            <div class="feature-icon-container">
                                <i class="pi pi-terminal feature-icon"></i>
                            </div>
                            <div class="feature-content">
                                <h4>Debug Terminal</h4>
                                <p>WP-CLI style command-line interface with WordPress and shell commands</p>
                            </div>
                        </div>

                        <div class="feature-card">
                            <div class="feature-icon-container">
                                <i class="pi pi-refresh feature-icon"></i>
                            </div>
                            <div class="feature-content">
                                <h4>Auto-Refresh</h4>
                                <p>Real-time log monitoring with configurable intervals</p>
                            </div>
                        </div>

                        <div class="feature-card">
                            <div class="feature-icon-container">
                                <i class="pi pi-list feature-icon"></i>
                            </div>
                            <div class="feature-content">
                                <h4>Stack Trace Viewer</h4>
                                <p>Visual analysis of PHP error stack traces</p>
                            </div>
                        </div>

                        <div class="feature-card">
                            <div class="feature-icon-container">
                                <i class="pi pi-shield feature-icon"></i>
                            </div>
                            <div class="feature-content">
                                <h4>Safe Mode</h4>
                                <p>Safely disable problematic plugins when debugging</p>
                            </div>
                        </div>

                        <div class="feature-card">
                            <div class="feature-icon-container">
                                <i class="pi pi-database feature-icon"></i>
                            </div>
                            <div class="feature-content">
                                <h4>Database Tools</h4>
                                <p>Query and analyze your WordPress database</p>
                            </div>
                        </div>

                        <div class="feature-card">
                            <div class="feature-icon-container">
                                <i class="pi pi-bell feature-icon"></i>
                            </div>
                            <div class="feature-content">
                                <h4>Notifications</h4>
                                <p>Get email alerts when errors occur</p>
                            </div>
                        </div>
                    </div>

                    <Divider align="center">
                        <span class="divider-text">Resources</span>
                    </Divider>

                    <div class="support-links">
                        <Button icon="pi pi-github" label="GitHub" class="p-button-outlined p-button-rounded" @click="openLink('https://github.com/nkb-bd/debug-log-config-tool')" />
                        <Button icon="pi pi-question-circle" label="Documentation" class="p-button-outlined p-button-rounded" @click="openLink('https://github.com/nkb-bd/debug-log-config-tool#readme')" />
                        <Button icon="pi pi-exclamation-circle" label="Report Issue" class="p-button-outlined p-button-rounded" @click="openLink('https://github.com/nkb-bd/debug-log-config-tool/issues')" />
                        <Button icon="pi pi-user" label="Developer Profile" class="p-button-outlined p-button-rounded p-button-success" @click="openLink('https://github.com/nkb-bd')" />
                    </div>

                    <Divider align="center">
                        <span class="divider-text">Developer</span>
                    </Divider>

                    <div class="developer-info">
                        <div class="developer-card">
                            <div class="developer-avatar">
                                <i class="pi pi-user-plus"></i>
                            </div>
                            <div class="developer-details">
                                <h3>Nazmul Hossain</h3>
                                <p>WordPress Developer & Plugin Author</p>
                                <div class="developer-social">
                                    <Button icon="pi pi-github" class="p-button-rounded p-button-text" @click="openLink('https://github.com/nkb-bd')" />
                                    <Button icon="pi pi-twitter" class="p-button-rounded p-button-text p-button-info" @click="openLink('https://twitter.com/nkb_bd')" />
                                    <Button icon="pi pi-linkedin" class="p-button-rounded p-button-text p-button-help" @click="openLink('https://www.linkedin.com/in/nkb-bd/')" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="version-info">
                        <Chip label="Version: 1.2.0" icon="pi pi-tag" />
                        <Chip label="License: GPL v2 or later" icon="pi pi-check-circle" />
                    </div>
                </div>
            </template>
        </Card>
    </div>
</template>

<script setup>
import { useToast } from 'primevue/usetoast';

const toast = useToast();

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
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.support-content {
    line-height: 1.6;
}

.card-title {
    display: flex;
    align-items: center;
    font-size: 1.25rem;
}

.plugin-info {
    margin-bottom: 1.5rem;
    font-size: 1.1rem;
    color: #4b5563;
}

.divider-text {
    background-color: white;
    padding: 0 1rem;
    font-weight: 600;
    color: #4b5563;
}

.feature-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.5rem;
    margin: 2rem 0;
}

.feature-card {
    display: flex;
    align-items: flex-start;
    padding: 1.25rem;
    border-radius: 8px;
    background-color: #f8fafc;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    transition: transform 0.2s, box-shadow 0.2s;
}

.feature-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.feature-icon-container {
    display: flex;
    justify-content: center;
    align-items: center;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background-color: #e0f2fe;
    margin-right: 1rem;
}

.feature-icon {
    font-size: 1.5rem;
    color: #0284c7;
}

.feature-content {
    flex: 1;
}

.feature-content h4 {
    margin: 0 0 0.5rem 0;
    color: #0f172a;
    font-size: 1.1rem;
}

.feature-content p {
    margin: 0;
    color: #64748b;
    font-size: 0.95rem;
}

.support-links {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    justify-content: center;
    margin: 2rem 0;
}

.developer-info {
    display: flex;
    justify-content: center;
    margin: 2rem 0;
}

.developer-card {
    display: flex;
    align-items: center;
    padding: 1.5rem;
    background-color: #f8fafc;
    border-radius: 12px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    max-width: 500px;
    width: 100%;
}

.developer-avatar {
    display: flex;
    justify-content: center;
    align-items: center;
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background-color: #e0f2fe;
    margin-right: 1.5rem;
}

.developer-avatar i {
    font-size: 2.5rem;
    color: #0284c7;
}

.developer-details {
    flex: 1;
}

.developer-details h3 {
    margin: 0 0 0.5rem 0;
    color: #0f172a;
    font-size: 1.25rem;
}

.developer-details p {
    margin: 0 0 1rem 0;
    color: #64748b;
}

.developer-social {
    display: flex;
    gap: 0.5rem;
}

.version-info {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    justify-content: center;
    margin-top: 2rem;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .feature-grid {
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
