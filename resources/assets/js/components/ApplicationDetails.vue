<template>
    <div v-if="application" class="my-2">
        <p class="mb-2">
            <strong>{{ trans('store.registration.domain') }}:</strong>
            {{ application.domain }}
        </p>
        <p class="mb-2">
            <strong>{{ trans('store.registration.callback_url') }}:</strong>
            {{ application.callbackUrl }}
        </p>
        <p class="mb-2">
            <strong>{{ trans('store.registration.redirect_url') }}:</strong>
            {{ application.redirectUrl }}
        </p>
        <p><strong>{{ trans('store.registration.description') }}:</strong></p>
        <p class="mb-2">{{ application.description }}</p>
        <p><strong>{{ trans('store.registration.schema') }}:</strong></p>
        <GraphQLSchema :schema="application.schema" />
        <slot v-bind="application" />
    </div>
    <p v-else-if="error" class="my-2 text-error">
        {{ error }}
    </p>
    <p v-else class="my-2">
        {{ trans('store.loading') }} <code>{{ schemaUrl }}</code>...
    </p>
</template>

<script lang="ts">
import Vue from 'vue';

import ApplicationsApi from '@/api/Applications';

import Application from '@/models/Application';

import GraphQLSchema from './GraphQLSchema.vue';

interface Data {
    application: Application | null;
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
            application: null,
            error: null,
        };
    },
    created() {
        ApplicationsApi.parseSchema(this.schemaUrl)
            .then(schema => {
                this.application = Application.fromJson({
                    description: this.description,
                    domain: this.domain,
                    callback_url: this.callbackUrl,
                    redirect_url: this.redirectUrl,
                    schema,
                });
            })
            .catch(error => {
                this.error = error.message;
            });
    },
});
</script>
