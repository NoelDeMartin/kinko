<template>
    <div class="my-2">
        <p class="mb-2">
            <strong>{{ trans('store.registration.name') }}:</strong>
            {{ client.name }}
        </p>
        <p class="mb-2">
            <strong>{{ trans('store.registration.homepage_url') }}:</strong>
            <a :href="client.homepageUrl" target="_blank">
                {{ client.homepageUrl }}
            </a>
        </p>
        <p><strong>{{ trans('store.registration.description') }}:</strong></p>
        <p class="mb-2">{{ client.description }}</p>
        <p><strong>{{ trans('store.registration.schema') }}:</strong></p>
        <GraphQLSchema :schema="client.schema" />
    </div>
</template>

<script lang="ts">
import Vue from 'vue';

import Client from '@/models/Client';

import GraphQLSchema from './GraphQLSchema.vue';

export default Vue.extend({
    components: {
        GraphQLSchema,
    },
    props: {
        clientJson: {
            type: Object,
            required: true,
        },
    },
    computed: {
        client(): Client {
            return new Client(this.clientJson);
        },
    },
});
</script>
