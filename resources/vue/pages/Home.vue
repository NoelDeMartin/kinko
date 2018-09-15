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
                v-for="application of applications"
                :key="application.id"
                :to="'/app/' + application.domain"
                class="bg-blue hover:bg-blue-dark text-lg text-white font-bold py-2 px-4 mt-2 rounded-full no-underline"
            >
                {{ application.name }}
            </router-link>
            <p v-if="applications.length === 0">
                No applications.
            </p>
        </template>
    </div>
</template>

<script lang="ts">
import Vue from 'vue';

import ApplicationsApi from '@/api/Applications';
import CollectionsApi from '@/api/Collections';

import Application from '@/models/Application';
import Collection from '@/models/Collection';

interface Data {
    loading: boolean;
    collections: Collection[];
    applications: Application[];
}

export default Vue.extend({
    data(): Data {
        return {
            loading: true,
            collections: [],
            applications: [],
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
                ApplicationsApi.index().then(applications => {
                    this.applications = applications;
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
