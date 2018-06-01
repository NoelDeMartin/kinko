import { createRenderer } from 'vue-server-renderer';

import app from './app';

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
