import { createRouter, createWebHistory } from 'vue-router';
import HomePage from '../pages/HomePage.vue';
import PropertyDetail from '../pages/PropertyDetail.vue';
import PricePredict from '../pages/PricePredict.vue';
import AboutPage from '../pages/AboutPage.vue';

const routes = [
  { path: '/', name: 'Home', component: HomePage },
  { path: '/properties/:id', name: 'PropertyDetail', component: PropertyDetail },
  { path: '/predict', name: 'PricePredict', component: PricePredict },
  { path: '/about', name: 'About', component: AboutPage },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

export default router;
