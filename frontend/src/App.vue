<template>
  <div id="app" class="min-h-screen flex flex-col">
    <header class="bg-white shadow-sm sticky top-0 z-10">
      <div class="container py-4 flex justify-between items-center">
        <router-link to="/" class="text-2xl font-bold text-blue-700">智慧房產</router-link>
        <nav>
          <router-link to="/" class="text-gray-600 hover:text-blue-700 mx-3 font-medium">首頁</router-link>
          <router-link to="/predict" class="text-gray-600 hover:text-blue-700 mx-3 font-medium">房價預測</router-link>
          <router-link to="/about" class="text-gray-600 hover:text-blue-700 mx-3 font-medium">關於我們</router-link>
        </nav>
      </div>
    </header>

    <main class="flex-grow py-8">
      <div class="container">
        <router-view></router-view>
      </div>
    </main>

    <footer class="bg-gray-800 text-white py-6">
      <div class="container text-center text-sm">
        © 2024 智慧房產系統. All rights reserved.
      </div>
    </footer>
  </div>
</template>

<script setup>
</script>

<style scoped>
/* No scoped styles needed, using Tailwind CSS */
</style>
