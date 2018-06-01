import Vue from 'vue';

import '@/filters';

import router from '@/router';

import App from '@/App.vue';

export default new Vue({
    router,
    render: h => h(App),
});
