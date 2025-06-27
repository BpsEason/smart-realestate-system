<template>
  <div class="property-detail">
    <div v-if="isLoading" class="text-center text-gray-600 py-10">
      <p class="text-xl font-semibold">正在載入建案詳情...</p>
    </div>

    <div v-if="error" class="error-alert mb-8">
      <strong class="font-bold">載入詳情失敗:</strong>
      <span class="block sm:inline">{{ error }}</span>
    </div>

    <div v-if="property && !isLoading && !error" class="bg-white rounded-lg shadow-xl p-8">
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
        <div>
          <img
            :src="property.image_url"
            :alt="property.address"
            class="rounded-lg w-full h-auto object-cover shadow-md mb-6"
            onerror="this.onerror=null;this.src='https://placehold.co/800x600/E0F2F7/4299E1?text=無圖片';"
          />
          <h1 class="text-4xl font-extrabold text-gray-900 mb-4">{{ property.address }}</h1>
          <div class="flex items-baseline mb-6">
            <span class="text-5xl font-extrabold text-blue-600">NT$ {{ formatPrice(property.price) }} 萬</span>
            <span class="text-gray-500 ml-3">/ 總價</span>
          </div>
          <div class="text-gray-700 space-y-4 text-lg">
            <p><strong>面積:</strong> {{ property.area }} 坪</p>
            <p class="leading-relaxed">{{ property.description }}</p>
            <div class="mt-8">
              <h3 class="text-2xl font-bold text-gray-800 mb-4">生成建案文案 (Powered by AI)</h3>
              <textarea v-model="contentPrompt" rows="4" class="w-full p-3 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" placeholder="請輸入文案生成提示詞，例如：'寫一篇充滿活力的建案介紹，強調生活機能。'"></textarea>
              <button @click="generateContent" :disabled="isGenerating" class="btn btn-primary mt-4 w-full sm:w-auto">
                <span v-if="!isGenerating">生成文案</span>
                <span v-else>正在生成...</span>
              </button>
              <div v-if="generatedContent" class="mt-6 p-6 bg-blue-50 rounded-md border border-blue-200">
                <p class="whitespace-pre-wrap text-gray-800">{{ generatedContent }}</p>
              </div>
              <div v-if="generationError" class="error-alert mt-4">
                <strong class="font-bold">文案生成失敗:</strong>
                <span class="block sm:inline">{{ generationError }}</span>
              </div>
            </div>
          </div>
        </div>
        <div class="relative min-h-[400px] lg:min-h-full">
          <iframe
            :src="googleMapsUrl"
            width="100%"
            height="100%"
            style="border:0;"
            allowfullscreen=""
            loading="lazy"
            referrerpolicy="no-referrer-when-downgrade"
            class="rounded-lg shadow-md absolute inset-0"
          ></iframe>
        </div>
      </div>
    </div>

    <div v-if="!property && !isLoading && !error" class="text-center text-gray-500 py-10">
      <p class="text-xl">找不到該建案的詳情。</p>
      <router-link to="/" class="mt-6 inline-block text-blue-600 hover:underline">返回首頁</router-link>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';

const route = useRoute();
const property = ref(null);
const isLoading = ref(true);
const error = ref(null);

const generatedContent = ref('');
const contentPrompt = ref('');
const isGenerating = ref(false);
const generationError = ref(null);

const fetchPropertyDetail = async () => {
  isLoading.value = true;
  error.value = null;
  const propertyId = route.params.id;
  try {
    const response = await axios.get(`/properties/${propertyId}`);
    property.value = response.data;
  } catch (err) {
    console.error('Failed to fetch property detail:', err);
    error.value = err.response?.data?.message || '無法從伺服器載入資料。';
  } finally {
    isLoading.value = false;
  }
};

const generateContent = async () => {
  if (!contentPrompt.value.trim()) {
    alert('請輸入文案生成提示詞！');
    return;
  }
  isGenerating.value = true;
  generationError.value = null;
  generatedContent.value = '';
  try {
    const aiServiceBaseUrl = import.meta.env.VITE_AI_API_BASE_URL || 'http://localhost:8001';
    const response = await axios.post(
      `${aiServiceBaseUrl}/generate/content`,
      {
        prompt: contentPrompt.value,
        property_info: {
          address: property.value.address,
          area: property.value.area,
          price: property.value.price,
          description: property.value.description
        }
      },
      {
        headers: {
          'X-API-KEY': import.meta.env.VITE_AI_API_KEY || 'default_ai_key_frontend' // 從 .env.example 取得或使用預設值
        }
      }
    );
    generatedContent.value = response.data.generated_content; // 修正為 generated_content
  } catch (err) {
    console.error('Failed to generate content:', err);
    generationError.value = err.response?.data?.detail || '文案生成失敗，請稍後再試。';
  } finally {
    isGenerating.value = false;
  }
};

const formatPrice = (price) => {
  return parseFloat(price).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

const googleMapsUrl = computed(() => {
  if (!property.value) return '';
  const lat = property.value.latitude;
  const lon = property.value.longitude;
  const zoom = 15;
  const apiKey = import.meta.env.VITE_GOOGLE_MAPS_API_KEY || 'YOUR_GOOGLE_MAPS_API_KEY'; // 從 .env.example 取得或使用預設值
  return `https://www.google.com/maps/embed/v1/place?key=${apiKey}&q=${lat},${lon}&zoom=${zoom}`;
});

onMounted(() => {
  fetchPropertyDetail();
});
</script>

<style scoped>
/* No scoped styles needed, using Tailwind CSS */
</style>
