<template>
    <div class="pt-2">
        <table v-for="(definition, index) of schema.definitions" :key="index" class="graphql-type">
            <thead>
                <tr>
                    <th colspan="2">{{ definition.name.value }}</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="field of definition.fields" :key="field.name.value">
                    <td>{{ field.name.value }}</td>
                    <td v-if="field.type.kind === 'NonNullType'">
                        {{ field.type.type.name.value }} <span>required</span>
                    </td>
                    <td v-else>
                        {{ field.type.name.value }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script lang="ts">
import Vue from 'vue';

export default Vue.extend({
    props: {
        schema: {
            type: Object,
            required: true,
        },
    },
});
</script>

<style lang="scss">

    @tailwind utilities;

    .graphql-type {
        border-collapse: collapse;
        @apply .bg-grey-light;

        th {
            @apply .bg-grey-dark .text-white;
        }

        td, th {
            @apply .p-2 .border .border-grey;
        }

        td:last-child {
            @apply .text-blue-dark;

            span {
                @apply .text-xs .text-white .p-1 .rounded .bg-blue-light .float-right .ml-2;
            }

        }

    }

</style>
