<template>
    <div class="w-screen h-screen flex flex-col items-center justify-center bg-grey-lighter">
        <h1 class="flex items-center justify-center">
            <img
                src="https://png.icons8.com/office/80/000000/add-database.png"
                class="mr-2"
            >
            {{ collectionName | title_case }}
        </h1>
    </div>
</template>

<script>
export default {
    computed: {
        collectionName() {
            return this.$route.params.name;
        },
    },
};
</script>
