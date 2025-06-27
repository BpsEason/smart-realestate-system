<template>
  <div class="price-predict-page">
    <h1 class="text-4xl font-extrabold text-gray-900 mb-8 text-center">房價預測</h1>
    <div class="max-w-3xl mx-auto bg-white p-8 rounded-lg shadow-xl">
      <p class="text-gray-600 mb-6 text-center">請輸入房屋相關資訊，以獲得價格預測。</p>
      <form @submit.prevent="predictPrice" class="space-y-6">
        <div>
          <label for="address" class="block text-sm font-medium text-gray-700">房屋地址 (請盡量詳細)</label>
          <input type="text" id="address" v-model="predictForm.address" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-3 focus:ring-blue-500 focus:border-blue-500">
        </div>
        <div>
          <label for="area" class="block text-sm font-medium text-gray-700">房屋面積 (坪)</label>
          <input
            type="number"
            id="area"
            v-model.number="predictForm.area"
            required
            min="1"
            max="500"
            step="0.1"
            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-3 focus:ring-blue-500 focus:border-blue-500"
          >
        </div>
        <div class="flex justify-center">
          <button type="submit" :disabled="isPredicting" class="btn btn-primary w-full sm:w-auto px-10 py-3 text-lg">
            <span v-if="!isPredicting">預測價格</span>
            <span v-else>正在預測...</span>
          </button>
        </div>
      </form>

      <div v-if="predictedPrice !== null" class="mt-12 text-center">
        <h2 class="text-3xl font-bold text-gray-800 mb-4">預測結果</h2>
        <div class="bg-blue-100 p-8 rounded-lg shadow-inner">
          <p class="text-5xl font-extrabold text-blue-700">NT$ {{ formatPrice(predictedPrice) }} 萬</p>
          <p class="text-gray-600 mt-2">此為根據輸入資訊的預測總價。</p>
        </div>
      </div>

      <div v-if="predictionError" class="error-alert mt-8">
        <strong class="font-bold">預測失敗:</strong>
        <span class="block sm:inline">{{ predictionError }}</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';

const predictForm = ref({
  address: '',
  area: null,
});
const predictedPrice = ref(null);
const isPredicting = ref(false);
const predictionError = ref(null);

const predictPrice = async () => {
  isPredicting.value = true;
  predictionError.value = null;
  predictedPrice.value = null;
  try {
    const aiServiceBaseUrl = import.meta.env.VITE_AI_API_BASE_URL || 'http://localhost:8001';
    const response = await axios.post(
      `${aiServiceBaseUrl}/predict/price`,
      predictForm.value,
      {
        headers: {
          'X-API-KEY': import.meta.env.VITE_AI_API_KEY || 'default_ai_key_frontend' // 從 .env.example 取得或使用預設值
        }
      }
    );
    predictedPrice.value = response.data.predicted_price;
  } catch (err) {
    console.error('Failed to predict price:', err);
    predictionError.value = err.response?.data?.detail || '價格預測服務暫時不可用，請稍後再試。';
  } finally {
    isPredicting.value = false;
  }
};

const formatPrice = (price) => {
  return parseFloat(price).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};
</script>

<style scoped>
/* No scoped styles needed, using Tailwind CSS */
</style>
