export default [
    {
        path: '/',
        name: 'home',
        component: () => import('@/pages/Home.vue'),
    },
    {
        path: '/collection/:name',
        name: 'collection',
        component: () => import('@/pages/Collection.vue'),
    },
];
