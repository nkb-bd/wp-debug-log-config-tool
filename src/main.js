import Vue from 'vue';
import Router from 'vue-router';
import App from './App';
import {routes} from "./routes";

Vue.use(Router);
Vue.mixin({
    methods: {
        $get(url, data) {
            if (!data) {
                data = {};
            }
            if (!url) {
                url = window.dlct_wpdebuglog.ajax_url;
            }
            data.action = window.dlct_wpdebuglog.action;
            data.nonce = window.dlct_wpdebuglog.nonce;
            jQuery.ajaxSetup({
                success: function (res) {
                    if (res.success == false) {
                        alert(res.data.message)
                    }
                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    console.log(errorThrown)
                }
            });
            return jQuery.get(url, data);
        },
        $post(url, data) {
            if (!data) {
                data = {};
            }
            if (!url) {
                url = window.dlct_wpdebuglog.ajax_url+'?time='+(new Date()).getTime();
            }
            data.action = window.dlct_wpdebuglog.action;
            data.nonce = window.dlct_wpdebuglog.nonce;
            jQuery.ajaxSetup({
                success: function (res) {
                    if (res.success == false) {
                        alert(res.data.message)
                    }
                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    console.log(errorThrown)
                }
            });
            return jQuery.post(url, data);
        }
    }
});
const router = new Router({
    routes: routes,
    linkActiveClass: 'wpdd-active'
});
window.onload = function () {
    const app = new Vue({
        el: '#wpdebugapp',
        render: h => h(App),
        router: router
    });
}
