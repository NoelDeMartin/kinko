import Vue from 'vue';

declare module 'vue/types/vue' {

    interface Vue {
        trans(key: string): string;
    }

}
