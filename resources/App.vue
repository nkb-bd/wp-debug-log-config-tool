<template>
    <div class="wpdebugapp_wrap">

        <div>
            <div class=" clearfix tabs">
                    <router-link   v-for="(menu,index) in menuItems" class="tab" :class=" $route.path == menu.path ? 'is-tab-selected' :'' " :to="menu.path"  :key="menu.route">
                        {{menu.title}}
                    </router-link>

            </div>
            <div class="tab-body">

                <router-view  @notification="sendNotification"></router-view>
            </div>
            <notification
                    message="Text"
                    :options.sync="notificationOptions"
                    :show.sync="showNotification"
                    @close="closeNotification"
            >
            </notification>
            <span class="wpdebug-notification-message" v-if="message">

            </span>
        </div>

    </div>
</template>
<script type="text/babel">
    import Logs from "./components/Logs";
    import DebugConstants from "./components/DebugConstants";
    import Notification from "./components/Notification";

    export default {
        name: 'App',
        data() {
            return {
                activeTab: 'logs',
                message: '',
                showNotification: false,
                notificationOptions: {},
                menuItems: []


            };
        },
        components: {Logs, DebugConstants, Notification},
        methods: {
            defaultRoutes() {
                return [
                    {
                        path: '/',
                        title:'Debug Constants'
                    },
                    {
                        path: '/logs',
                        title: 'Debug Logs',
                    },
                    {
                        path: '/email',
                        title: 'Email Notification',
                    },
                    {
                        path: '/support',
                        title: 'Developer Contact'
                    }
                ]
            },
            setMenus() {
                this.menuItems = this.defaultRoutes();
            },
            sendNotification(notification) {
                this.notificationOptions.content = notification.text;
                this.showNotification = true;
            },
            closeNotification() {
                this.showNotification = false;
            },

        },
        mounted() {
            this.setMenus();
        }

    }
</script>
