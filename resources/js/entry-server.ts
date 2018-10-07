import '@/bootstrap';

import app from '@/app';

import { createRenderer } from 'vue-server-renderer';

const path = process.argv[2];

global.Laravel = JSON.parse(process.argv[3]);

app.$router.push(path);
app.$router.onReady(() => {
    createRenderer().renderToString(app).then(html => {
        process.stdout.write(html);
    }).catch(err => {
        console.error(err);
    });
});
