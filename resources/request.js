import { ref } from 'vue';

export async function useFetch(args = {}, url = '', method) {
    const data = ref(null);
    const error = ref(null);

    try {
        let param = {
            method: method,
        };
        if ( method !== 'GET') {
            param.body = args
        }
        const response = await fetch(url, param);
        const jsonData = await response.json();
        data.value = jsonData;
    } catch (err) {
        error.value = err;
    }
    return { data, error };
}

export async function $get(args, url = '') {
    args.action = 'dlct_logs_admin';
    args.nonce = window.dlct_wpdebuglog.nonce;
    const queryString = new URLSearchParams(args).toString();
    if (!url) {
        url = window.dlct_wpdebuglog.ajax_url + '?' + queryString;
    }

    return await useFetch('', url, 'GET');
}

export async function $post(args, url = '') {
    const requestData = new FormData();
    requestData.append('action', 'dlct_logs_admin');
    requestData.append('nonce', window.dlct_wpdebuglog.nonce);
    Object.keys(args).forEach(function (key) {
        requestData.append(key, args[key]);
    });
    if (url == '') {
        url = window.dlct_wpdebuglog.ajax_url;
    }

    return await useFetch(requestData, url, 'POST');
}
