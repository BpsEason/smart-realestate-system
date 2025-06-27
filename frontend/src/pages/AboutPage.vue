<template>
  <div class="about-page text-center max-w-4xl mx-auto">
    <h1 class="text-5xl font-extrabold text-gray-900 mb-8">關於我們</h1>
    <p class="text-xl text-gray-700 mb-6 leading-relaxed">
      我們致力於利用數據和人工智慧技術，革新傳統房地產產業。
    </p>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-10 mt-12 text-left">
      <div class="bg-white p-8 rounded-lg shadow-lg">
        <h2 class="text-3xl font-bold text-blue-700 mb-4">我們的使命</h2>
        <p class="text-gray-600 leading-relaxed">
          透過提供精準的房價預測、市場趨勢分析和自動化文案生成等服務，幫助房地產專業人士和潛在買家做出更明智的決策。我們相信透明的數據能夠創造更公平、高效的房地產市場。
        </p>
      </div>
      <div class="bg-white p-8 rounded-lg shadow-lg">
        <h2 class="text-3xl font-bold text-blue-700 mb-4">技術棧</h2>
        <ul class="list-disc list-inside text-gray-600 space-y-2">
          <li><strong>前端:</strong> Vue.js, Vite, Tailwind CSS</li>
          <li><strong>後端:</strong> Laravel (PHP)</li>
          <li><strong>AI 服務:</strong> FastAPI (Python)</li>
          <li><strong>資料庫:</strong> MySQL</li>
          <li><strong>容器化:</strong> Docker, Docker Compose</li>
        </ul>
      </div>
    </div>
    <div class="mt-16">
      <h2 class="text-3xl font-bold text-gray-900 mb-6">聯絡我們</h2>
      <p class="text-gray-700">
        若您有任何問題或合作意向，請隨時透過 <a href="mailto:contact@smart-realestate.com" class="text-blue-600 hover:underline">contact@smart-realestate.com</a> 與我們聯繫。
      </p>
    </div>
  </div>
</template>

<script setup>
/* No script needed for this static page */
</script>

<style scoped>
/* No scoped styles needed, using Tailwind CSS */
</style>
