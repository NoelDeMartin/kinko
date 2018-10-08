<template>
    <div class="w-screen h-screen flex flex-col items-center justify-center bg-grey-lighter">
        <header class="fixed pin flex items-center justify-center h-16 bg-overlay">
            <router-link
                to="/"
                class="flex items-center justify-center no-underline hover:bg-overlay p-2 rounded"
            >
                <img src="https://png.icons8.com/color/40/000000/safe.png">
                <h1 class="text-blue-darker text-lg">{{ trans('kinko.title') }}</h1>
            </router-link>
        </header>
        <router-view :key="$route.fullPath"/>
    </div>
</template>

<script>
export default {
};
</script>
