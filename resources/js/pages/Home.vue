<template>
    <div class="flex flex-col items-center justify-center">
        <h2>Hello, {{ username }}</h2>

        <p v-if="loading">
            Loading...
        </p>

        <template v-else>
            <router-link
                v-for="collection of collections"
                :key="collection.name"
                :to="'/collection/' + collection.name"
                class="bg-blue hover:bg-blue-dark text-lg text-white font-bold py-2 px-4 mt-2 rounded-full no-underline"
            >
                {{ collection.name }}
            </router-link>
            <p v-if="collections.length === 0">
                No collections.
            </p>

            <router-link
                v-for="client of clients"
                :key="client.id"
                :to="'/client/' + client.id"
                :class="{
                    'bg-blue hover:bg-blue-dark ': client.validated,
                    'bg-grey hover:bg-grey-dark ': !client.validated,
                }"
                class="text-lg text-white font-bold py-2 px-4 mt-2 rounded-full no-underline"
            >
                {{ client.name }}
            </router-link>
            <p v-if="clients.length === 0">
                No clients.
            </p>
        </template>
    </div>
</template>

<script lang="ts">
import Vue from 'vue';

import ClientsApi from '@/api/Clients';
import CollectionsApi from '@/api/Collections';

import Client from '@/models/Client';
import Collection from '@/models/Collection';

interface Data {
    loading: boolean;
    collections: Collection[];
    clients: Client[];
}

export default Vue.extend({
    data(): Data {
        return {
            loading: true,
            collections: [],
            clients: [],
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
            Promise.all([
                ClientsApi.index().then(clients => {
                    this.clients = clients;
                }),
                CollectionsApi.index().then(collections => {
                    this.collections = collections;
                }),
            ]).then(() => {
                this.loading = false;
            });
        }
    },
});
</script>
