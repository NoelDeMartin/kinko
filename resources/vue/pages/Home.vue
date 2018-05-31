<template>
    <div class="w-screen h-screen flex flex-col items-center justify-center bg-grey-lighter">
        <h1 class="flex items-center justify-center">
            <img src="https://png.icons8.com/color/80/000000/safe.png"> 金庫
        </h1>
        <h2>Hello, {{ username }}</h2>
        <ul>
            <li
                v-for="collection of collections"
                :key="collection.name"
            >
                {{ collection.name }}
            </li>
        </ul>
    </div>
</template>

<script>
import Collections from '@/api/Collections';

export default {
    data() {
        return {
            collections: [],
        };
    },
    computed: {
        username() {
            return Laravel.user
                ? Laravel.user.first_name + ' ' + Laravel.user.last_name
                : 'Guest';
        },
    },
    created() {
        Collections.index().then(collections => {
            this.collections = collections;
        });
    },
};
</script>
