
//style
//core
// import "primevue/resources/themes/fluent-light/theme.css";
import "primevue/resources/themes/tailwind-light/theme.css";

import "primevue/resources/primevue.min.css";
import './assets/main.scss';


//theme

import { createApp } from 'vue'
import App from './App.vue'
import router from './router'

import PrimeVue from 'primevue/config';
import Menubar from 'primevue/menubar';
import Button from "primevue/button";
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import ColumnGroup from 'primevue/columngroup';   // optional
import SplitButton from 'primevue/splitbutton';   // optional
import Row from 'primevue/row';
import Toolbar from 'primevue/toolbar';
import ProgressSpinner from 'primevue/progressspinner';



window.onload = function () {
    const appEl = document.querySelector('#main-app');
    if (appEl) {
       const application = createApp(App)
        application.use(PrimeVue)
        application.component('Button', Button);
        application.component('Menubar', Menubar);
        application.component('DataTable', DataTable);
        application.component('Column', Column);
        application.component('ColumnGroup', ColumnGroup);
        application.component('Row', Row);
        application.component('Toolbar', Toolbar);
        application.component('SplitButton', SplitButton);
        application.component('ProgressSpinner', ProgressSpinner);
        application.use(router).mount(appEl);
    }
}

