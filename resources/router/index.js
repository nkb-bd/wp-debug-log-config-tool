import { createRouter, createWebHashHistory } from 'vue-router'
import LogView from '../views/LogView'
import AboutView from '../views/AboutView.vue'

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
    }
  ]
})

export default router
