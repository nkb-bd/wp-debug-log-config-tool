
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
import Row from 'primevue/row';
import Toolbar from 'primevue/toolbar';
import ProgressSpinner from 'primevue/progressspinner';
import Toast from 'primevue/toast';
import InputSwitch from 'primevue/inputswitch';
import ToastService from 'primevue/toastservice';
import InputText from 'primevue/inputtext';
import Listbox from 'primevue/listbox';
import SelectButton from 'primevue/selectbutton';
import MultiSelect from "primevue/multiselect";
import Accordion from 'primevue/accordion';
import AccordionTab from 'primevue/accordiontab';
import TabView from 'primevue/tabview';
import TabPanel from 'primevue/tabpanel';

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
        application.component('ProgressSpinner', ProgressSpinner);
        application.component('Toast', Toast);
        application.component('InputSwitch', InputSwitch);
        application.component('InputText', InputText);
        application.component('Listbox', Listbox);
        application.component('SelectButton', SelectButton);
        application.component('MultiSelect', MultiSelect);
        application.component('Accordion', Accordion);
        application.component('AccordionTab', AccordionTab);
        application.component('TabView', TabView);
        application.component('TabPanel', TabPanel);


        application.use(ToastService);
        application.use(router).mount(appEl);
    }
}

