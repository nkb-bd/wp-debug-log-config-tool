import { createRouter, createWebHashHistory } from 'vue-router'
import LogView from '../views/LogView'
import AboutView from '../views/AboutView.vue'
import  SettingsView from '../views/SettingsView'
import NotificationView from '../views/NotificationView.vue'

const router = createRouter({
  history: createWebHashHistory(),
  routes: [
    {
      path: '/',
      name: 'logs',
      component: LogView
    },
    {
      path: '/logs',
      name: 'about',
      component: AboutView
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
    }
  ]
})

export default router
