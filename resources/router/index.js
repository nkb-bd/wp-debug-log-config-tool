import { createRouter, createWebHashHistory } from 'vue-router'
import LogView from '../views/LogView'
import  SettingsView from '../views/SettingsView'
import NotificationView from '../views/NotificationView.vue'
import SupportView from "../views/supportView";

const router = createRouter({
  history: createWebHashHistory(),
  routes: [
    {
      path: '/',
      name: 'logs',
      component: LogView
    },
    {
      path: '/settings',
      name: 'settings',
      component: SettingsView
    },
    {
      path: '/notification',
      name: 'notification',
      component: NotificationView
    },
    {
      path: '/support',
      name: 'support',
      component: SupportView
    }
  ]
})

export default router
