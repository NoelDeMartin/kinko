import Vue from 'vue';

import ClientDetails from './components/ClientDetails.vue';

Vue.component('client-details', ClientDetails);

Vue.mixin(Vue.extend({
    methods: {
        trans(key: string) {
            const keys = key.split('.');
            let text: string | Lang = Laravel.lang || {};

            while (keys.length > 0) {
                text = text[<string> keys.shift()];
                if (!text) return key;
            }

            return text;
        },
    },
}));

const app = new Vue();

app.$mount('#app');
