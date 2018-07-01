<template>
    <p v-if="error" class="my-2 text-error">
        {{ error }}
    </p>
    <div v-else class="my-2">
        <p class="mb-2">
            <strong>{{ trans('store.registration.domain') }}:</strong>
            {{ domain }}
        </p>
        <p class="mb-2">
            <strong>{{ trans('store.registration.callback_url') }}:</strong>
            {{ callbackUrl }}
        </p>
        <p class="mb-2">
            <strong>{{ trans('store.registration.redirect_url') }}:</strong>
            {{ redirectUrl }}
        </p>
        <p><strong>{{ trans('store.registration.description') }}:</strong></p>
        <p class="mb-2">{{ description }}</p>
        <p><strong>{{ trans('store.registration.schema') }}:</strong></p>
        <template v-if="schema">
            <GraphQLSchema :schema="schema" />
            <slot :schema="schema" />
        </template>
        <p v-else class="my-2">
            {{ trans('store.loading') }} <code>{{ schemaUrl }}</code>...
        </p>
    </div>
</template>

<script lang="ts">
import Vue from 'vue';

import ApplicationsApi from '@/api/Applications';

import { Schema } from '@/models/Application';

import GraphQLSchema from './GraphQLSchema.vue';

interface Data {
    schema: Schema | null;
    error: string | null;
}

export default Vue.extend({
    components: {
        GraphQLSchema,
    },
    props: {
        description: {
            type: String,
            required: true,
        },
        domain: {
            type: String,
            required: true,
        },
        callbackUrl: {
            type: String,
            required: true,
        },
        redirectUrl: {
            type: String,
            required: true,
        },
        schemaUrl: {
            type: String,
            required: true,
        },
    },
    data(): Data {
        return {
            schema: null,
            error: null,
        };
    },
    created() {
        ApplicationsApi.parseSchema(this.schemaUrl)
            .then(schema => {
                this.schema = schema;
            })
            .catch(error => {
                this.error = error.message;
            });
    },
});
</script>
