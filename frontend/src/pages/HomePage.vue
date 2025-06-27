<template>
  <div class="homepage">
    <h1 class="text-4xl font-extrabold text-gray-900 mb-8 text-center">建案列表</h1>

    <div v-if="isLoading" class="text-center text-gray-600 py-10">
      <p class="text-xl font-semibold">正在載入建案資料...</p>
      <div class="mt-4 animate-pulse">
        <div class="h-4 bg-gray-200 rounded w-1/4 mx-auto"></div>
      </div>
    </div>

    <div v-if="error" class="error-alert mb-8">
      <strong class="font-bold">載入建案失敗:</strong>
      <span class="block sm:inline">{{ error }}</span>
    </div>

    <div v-if="!isLoading && !error && properties.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
      <div v-for="property in properties" :key="property.id" class="card bg-white rounded-lg shadow-lg overflow-hidden transition-transform transform hover:scale-105 duration-300">
        <router-link :to="`/properties/${property.id}`">
          <img
            :src="property.image_url"
            :alt="property.address"
            class="w-full h-48 object-cover bg-gray-100"
            onerror="this.onerror=null;this.src='https://placehold.co/800x600/E0F2F7/4299E1?text=無圖片';"
          />
          <div class="p-6">
            <h2 class="text-2xl font-bold text-gray-900 truncate">{{ property.address }}</h2>
            <p class="text-gray-600 mt-2">面積: {{ property.area }} 坪</p>
            <p class="text-3xl font-bold text-blue-600 mt-2">NT$ {{ formatPrice(property.price) }} 萬</p>
          </div>
        </router-link>
      </div>
    </div>

    <div v-if="!isLoading && !error && properties.length === 0" class="text-center text-gray-500 py-10">
      <p class="text-xl">目前沒有可用的建案資料。</p>
    </div>

    <div v-if="totalPages > 1" class="pagination-controls flex flex-col sm:flex-row justify-between items-center mt-12 space-y-4 sm:space-y-0">
      <div class="flex items-center space-x-2">
        <button
          @click="changePage(currentPage - 1)"
          :disabled="currentPage <= 1"
          class="btn-pagination"
        >
          上一頁
        </button>
        <div class="flex space-x-1">
          <button
            v-for="page in totalPages"
            :key="page"
            @click="changePage(page)"
            :class="['btn-pagination', { 'active': page === currentPage }]"
          >
            {{ page }}
          </button>
        </div>
        <button
          @click="changePage(currentPage + 1)"
          :disabled="currentPage >= totalPages"
          class="btn-pagination"
        >
          下一頁
        </button>
      </div>
      <span class="text-lg font-medium text-gray-700">頁數 {{ currentPage }} / {{ totalPages }} (共 {{ totalRecords }} 筆記錄)</span>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const properties = ref([]);
const isLoading = ref(true);
const error = ref(null);
const currentPage = ref(1);
const totalPages = ref(1);
const totalRecords = ref(0);
const perPage = ref(10); // 假定每頁10筆

const fetchProperties = async (page) => {
  isLoading.value = true;
  error.value = null;
  try {
    const response = await axios.get(`/properties?page=${page}&per_page=${perPage.value}`);
    properties.value = response.data.data;
    const meta = response.data.meta;
    currentPage.value = meta.current_page;
    totalPages.value = meta.last_page;
    totalRecords.value = meta.total;
  } catch (err) {
    console.error('Failed to fetch properties:', err);
    error.value = err.message || '無法從伺服器載入資料。';
  } finally {
    isLoading.value = false;
  }
};

const changePage = (page) => {
  if (page > 0 && page <= totalPages.value) {
    fetchProperties(page);
  }
};

const formatPrice = (price) => {
  return parseFloat(price).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

onMounted(() => {
  fetchProperties(currentPage.value);
});
</script>

<style scoped>
/* Scoped styles can be added here if needed, but Tailwind CSS is preferred for utility classes. */
</style>
