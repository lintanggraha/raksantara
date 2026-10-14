import './bootstrap';
import { createApp } from 'vue';
import ExampleComponent from './components/ExampleComponent.vue';

const appRoot = document.getElementById('app');

if (appRoot) {
    const app = createApp({});
    app.component('example-component', ExampleComponent);
    app.mount('#app');
}
