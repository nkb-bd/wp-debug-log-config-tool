import DebugConstants from "./components/DebugConstants";
import Logs from "./components/Logs";
import EmailNotification from "./components/EmailNotification";
import SupportContact from "./components/SupportContact";



export const routes = [
    {
        path: '/',
        name: 'DebugConstants',
        component: DebugConstants
    },
    {
        path: '/logs',
        name: 'Logs',
        component: Logs
    },
    {
        path:'/email',
        name:'EmailNotification',
        component:EmailNotification
    },
    {
        path:'/support',
        name:'support',
        component: SupportContact
    }

];


