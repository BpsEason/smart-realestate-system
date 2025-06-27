import { createApp } from 'vue';
import App from './App.vue';
import router from './router';
import './style.css';
import axios from 'axios';

const app = createApp(App);

// 動態設置後端 API URL
const apiBaseUrl = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api';
axios.defaults.baseURL = apiBaseUrl;
console.log(`API Base URL is set to: ${apiBaseUrl}`);

app.use(router);
app.mount('#app');
