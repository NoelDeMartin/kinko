import Vue from 'vue';

import '@babel/polyfill';

import '@/filters';

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
