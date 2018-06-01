<template>
    <div class="flex flex-col items-center justify-center">
        <h2>Hello, {{ username }}</h2>
        <router-link
            v-for="collection of collections"
            :key="collection.name"
            :to="'/collection/' + collection.name"
            class="bg-blue hover:bg-blue-dark text-lg text-white font-bold py-2 px-4 mt-2 rounded-full no-underline"
        >
            {{ collection.name }}
        </router-link>
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
        if (!Laravel.serverSide) {
            Collections.index().then(collections => {
                this.collections = collections;
            });
        }
    },
};
</script>
