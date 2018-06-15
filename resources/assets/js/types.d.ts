import Vue from 'vue';

declare module 'vue/types/vue' {

    interface Vue {
        trans(key: string): string;
    }

}

declare global {

    type Lang = { [key: string]: string | Lang };

    interface LaravelData {
        lang?: Lang;
    }

}
