# 智慧房地產系統

![智慧房地產系統](https://placehold.co/800x400/4299E1/E0F2F7?text=智慧房地產系統)

**智慧房地產系統** 是一個基於數據驅動的現代化平台，旨在透過人工智慧技術革新傳統房地產產業。本系統整合了建案管理、房價預測和自動化文案生成功能，適用於 B2C（個人用戶）和 B2B（企業客戶）場景。採用微服務架構，包含前端（Vue.js）、後端（Laravel）、AI 服務（FastAPI）和資料庫（MySQL），所有服務均透過 Docker 容器化運行。

## 功能特性

- **房價預測**：根據房屋面積、地址等資訊，利用機器學習模型（XGBoost）進行房價預測。
- **自動文案生成**：透過 AI（例如 OpenAI API 或模擬邏輯）生成吸引人的建案行銷文案。
- **建案管理**：提供建案列表瀏覽、詳細資訊檢視、支援分頁和 Google Maps 地圖整合。
- **模組化架構**：基於微服務設計，透過 Docker Compose 管理容器化服務。
- **安全性**：實現 API 金鑰驗證、CORS 配置和環境變數管理。
- **可擴展性**：支援 SaaS 模式，易於添加新功能或整合第三方服務。

## 技術棧

- **前端**：Vue.js 3、Vite、Tailwind CSS、Axios、Vue Router
- **後端**：Laravel 11（PHP 8.3）、GuzzleHttp
- **AI 服務**：FastAPI（Python 3.10）、Uvicorn、Pandas、Scikit-learn、XGBoost、OpenAI
- **資料庫**：MySQL 8.0
- **容器化**：Docker、Docker Compose
- **其他**：Nginx、Supervisor

## 系統架構

本系統採用模組化微服務架構，所有組件運行於 Docker 容器中，確保環境一致性和可擴展性。以下是系統互動流程圖：

```mermaid
graph TD
    A[使用者 (瀏覽器)] -- GET / (Web 介面) --> B(前端服務 Vue.js/Nginx)
    B -- GET /api/properties --> C(後端服務 Laravel)
    C -- SELECT FROM properties --> D[MySQL 資料庫]
    B -- POST /predict/price --> E(AI 服務 FastAPI)
    C -- POST /properties/{id}/generate-content --> E
    E -- 呼叫外部 API --> F[外部 AI 服務 (e.g., OpenAI)]
    E -- 返回 JSON --> C
    C -- 返回 JSON --> B
    B -- 渲染頁面 --> A

    subgraph 容器化環境 (Docker Compose)
        B
        C
        D
        E
    end
```

## 關鍵代碼範例

以下是帶有詳細註解的關鍵代碼片段，展示系統的核心功能。

### 前端：建案列表組件（Vue.js）

此代碼位於 `frontend/src/pages/HomePage.vue`，展示如何從後端獲取建案列表並實現分頁功能。

```vue
<template>
  <div class="homepage">
    <h1 class="text-4xl font-extrabold text-gray-900 mb-8 text-center">建案列表</h1>

    <!-- 顯示載入中狀態 -->
    <div v-if="isLoading" class="text-center text-gray-600 py-10">
      <p class="text-xl font-semibold">正在載入建案資料...</p>
      <div class="mt-4 animate-pulse">
        <div class="h-4 bg-gray-200 rounded w-1/4 mx-auto"></div>
      </div>
    </div>

    <!-- 顯示錯誤訊息 -->
    <div v-if="error" class="error-alert mb-8">
      <strong class="font-bold">載入建案失敗:</strong>
      <span class="block sm:inline">{{ error }}</span>
    </div>

    <!-- 顯示建案卡片列表 -->
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

    <!-- 分頁控制 -->
    <div v-if="totalPages > 1" class="pagination-controls flex flex-col sm:flex-row justify-between items-center mt-12 space-y-4 sm:space-y-0">
      <div class="flex items-center space-x-2">
        <button
          @click="changePage(currentPage - 1)"
          :disabled="currentPage <= 1"
          class="btn-pagination"
        >
          上一頁
        </button>
        <button
          v-for="page in totalPages"
          :key="page"
          @click="changePage(page)"
          :class="['btn-pagination', { 'active': page === currentPage }]"
        >
          {{ page }}
        </button>
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

// 狀態管理
const properties = ref([]); // 儲存建案列表
const isLoading = ref(true); // 控制載入狀態
const error = ref(null); // 儲存錯誤訊息
const currentPage = ref(1); // 當前頁碼
const totalPages = ref(1); // 總頁數
const totalRecords = ref(0); // 總記錄數
const perPage = ref(10); // 每頁記錄數

// 從後端獲取建案資料
const fetchProperties = async (page) => {
  isLoading.value = true;
  error.value = null;
  try {
    // 發送 GET 請求到後端 API，包含分頁參數
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

// 切換頁面
const changePage = (page) => {
  if (page > 0 && page <= totalPages.value) {
    fetchProperties(page);
  }
};

// 格式化價格顯示
const formatPrice = (price) => {
  return parseFloat(price).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

// 組件掛載時載入第一頁資料
onMounted(() => {
  fetchProperties(currentPage.value);
});
</script>
```

### 後端：建案控制器（Laravel）

此代碼位於 `backend-laravel/app/Http/Controllers/PropertyController.php`，展示如何處理建案列表和詳情 API。

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Property;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PropertyController extends Controller
{
    /**
     * 獲取建案列表，支援分頁
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            // 從查詢參數獲取頁碼和每頁數量
            $page = $request->query('page', 1);
            $limit = $request->query('limit', 10);

            // 使用 Laravel 的分頁功能查詢建案
            $properties = Property::paginate($limit, ['*'], 'page', $page);
            
            // 返回 JSON 格式的響應，包含建案數據和分頁元數據
            return response()->json([
                'data' => $properties->items(),
                'meta' => [
                    'current_page' => $properties->currentPage(),
                    'total' => $properties->total(),
                    'per_page' => (int) $properties->perPage(),
                    'last_page' => $properties->lastPage(),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get property list: ' . $e->getMessage());
            return response()->json(['error' => '無法取得建案資料，請稍後再試。'], 500);
        }
    }

    /**
     * 獲取單一建案詳情
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            // 根據 ID 查找建案，若不存在則拋出 404
            $property = Property::findOrFail($id);
            return response()->json($property);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => '找不到指定的建案。'], 404);
        } catch (\Exception $e) {
            Log::error("Failed to get property ID:{$id} details: " . $e->getMessage());
            return response()->json(['error' => '無法取得建案詳情，請稍後再試。'], 500);
        }
    }
}
```

### AI 服務：房價預測（FastAPI）

此代碼位於 `ai-services-fastapi/routers/predict.py`，展示房價預測邏輯（模擬或使用 XGBoost 模型）。

```python
from fastapi import APIRouter, HTTPException
from pydantic import BaseModel
import pandas as pd
import joblib
import os
import logging

# 配置日誌記錄
logger = logging.getLogger(__name__)

# 定義請求數據模型
class PredictionRequest(BaseModel):
    area: float  # 房屋面積（坪）
    address: str  # 地址或區域
    num_rooms: int = 3  # 房間數（預設值）
    num_bathrooms: int = 2  # 浴室數（預設值）
    age: int = 10  # 建築年齡（預設值）
    location_factor: float = 1.0  # 地理位置影響因子（預設值）
    is_near_mrt: int = 0  # 是否靠近捷運（預設值）

router = APIRouter(
    prefix="/predict",
    tags=["房價預測"]
)

# 載入預訓練模型
MODEL_PATH = os.path.join(os.path.dirname(__file__), '../models/model_xgb.pkl')
MODEL = None
try:
    if os.path.exists(MODEL_PATH):
        MODEL = joblib.load(MODEL_PATH)
        logger.info(f"✅ 成功載入預測模型: {MODEL_PATH}")
    else:
        logger.warning(f"❌ 模型檔案不存在於 {MODEL_PATH}，將使用模擬預測。")
except Exception as e:
    logger.error(f"❌ 載入模型失敗: {e}，將使用模擬預測。")

@router.post("/price", summary="預測房價")
def predict_price(data: PredictionRequest):
    """
    根據面積和地址預測房價
    - area: 房屋面積（坪）
    - address: 房屋地址或區域
    """
    if data.area <= 0:
        logger.error(f"無效的面積輸入: {data.area}")
        raise HTTPException(status_code=400, detail="面積必須是大於零的數值。")
    
    predicted_price_in_ten_thousand = 0.0

    if MODEL:
        try:
            # 根據地址模擬地理位置因子
            location_factor = 1.0
            is_near_mrt = 0
            if '大安區' in data.address or '信義區' in data.address:
                location_factor = 1.5
                is_near_mrt = 1
            elif '中山區' in data.address:
                location_factor = 1.2
            
            # 準備模型輸入數據
            input_data = pd.DataFrame([[data.area, data.num_rooms, data.num_bathrooms, data.age, location_factor, is_near_mrt]],
                                    columns=['area', 'num_rooms', 'num_bathrooms', 'age', 'location_factor', 'is_near_mrt'])
            
            # 使用模型進行預測
            predicted_price_in_ten_thousand = MODEL.predict(input_data)[0]
            logger.info(f"成功使用模型預測價格：{predicted_price_in_ten_thousand} 萬")
        except Exception as e:
            logger.warning(f"⚠️ 模型預測失敗: {e}，退回模擬邏輯。")
            # 模擬預測邏輯
            base_price_per_ping = 150 if '大安區' in data.address or '信義區' in data.address else 80
            predicted_price_in_ten_thousand = data.area * base_price_per_ping
    else:
        # 若無模型，使用模擬邏輯
        base_price_per_ping = 80
        if '大安區' in data.address or '信義區' in data.address:
            base_price_per_ping = 150
        elif '中山區' in data.address or '松山區' in data.address:
            base_price_per_ping = 120
        elif '文山區' in data.address or '北投區' in data.address:
            base_price_per_ping = 70
        
        predicted_price_in_ten_thousand = data.area * base_price_per_ping
        logger.info(f"使用模擬邏輯預測價格：{predicted_price_in_ten_thousand} 萬")

    # 返回四捨五入後的價格（單位：萬台幣）
    return {"predicted_price": round(float(predicted_price_in_ten_thousand), 2)}
```

## 快速開始

### 前置條件

- **Docker Desktop**：包含 Docker Engine 和 Docker Compose。[下載 Docker Desktop](https://docs.docker.com/get-docker/)。
- 確保 Docker 守護進程正在運行。

### 安裝與部署

1. **複製專案程式碼**：
   ```bash
   git clone https://github.com/BpsEason/smart-realestate-system.git
   cd smart-realestate-system
   ```

2. **配置環境變數**：
   - 複製 `.env.example` 為 `.env`：
     ```bash
     cp .env.example .env
     ```
   - 編輯 `.env` 檔案，設置以下關鍵變數：
     - `API_KEY_SECRET`：後端 API 金鑰，用於驗證前端或外部請求。
     - `AI_SERVICE_INTERNAL_API_KEY`：AI 服務內部金鑰，用於後端與 AI 服務通信。
     - `OPENAI_API_KEY`：OpenAI API 金鑰（用於文案生成，若無則使用模擬邏輯）。
     - `VITE_GOOGLE_MAPS_API_KEY`：Google Maps API 金鑰（用於地圖顯示）。

3. **建構並啟動 Docker 容器**：
   ```bash
   docker-compose up --build -d
   ```

4. **執行 Laravel 資料庫遷移與填充**：
   ```bash
   docker-compose exec backend php artisan migrate --seed
   ```

5. **訪問應用程式**：
   - 前端：`http://localhost:3000`
   - 後端 API：`http://localhost:8000/api`
   - AI 服務 API：`http://localhost:8001`

## API 文件

詳細的 REST API 規格請參考 [docs/api-design.md](docs/api-design.md)。主要端點包括：

- **建案列表**：`GET /api/properties`（支援分頁）
- **建案詳情**：`GET /api/properties/{id}`
- **生成文案**：`POST /api/properties/{id}/generate-content`
- **房價預測**：`POST /predict/price`（AI 服務）
- **健康檢查**：`GET /api/health`

所有後端 API 請求需包含 `X-API-KEY` 標頭，AI 服務 API 需包含 `X-API-KEY`（內部金鑰）。

## 專案結構

```
smart-realestate-system/
├── frontend/                     # Vue.js 前端
│   ├── src/
│   │   ├── components/          # Vue 組件
│   │   ├── pages/               # 頁面（首頁、建案詳情、房價預測、關於）
│   │   ├── assets/              # 靜態資源
│   │   ├── router/              # Vue Router 配置
│   │   ├── services/            # API 服務
│   │   └── style.css            # Tailwind CSS 樣式
├── backend-laravel/              # Laravel 後端
│   ├── app/
│   │   ├── Models/              # Eloquent 模型
│   │   ├── Http/Controllers/    # API 控制器
│   │   └── Http/Middleware/     # 自定義中間件
│   ├── database/
│   │   ├── migrations/          # 資料庫遷移
│   │   └── seeders/             # 資料庫填充
│   ├── docker/                  # Nginx 和 Supervisor 配置
│   └── routes/api.php           # API 路由
├── ai-services-fastapi/          # FastAPI AI 服務
│   ├── routers/                 # API 路由（預測、文案生成）
│   ├── models/                  # 機器學習模型
│   ├── services/                # 業務邏輯
│   ├── docs/                    # 訓練腳本等文件
│   └── main.py                  # FastAPI 主應用
├── database/                     # MySQL 資料庫
│   ├── schema.sql               # 資料庫結構
│   └── Dockerfile               # MySQL Dockerfile
├── docs/                         # 文件
│   ├── architecture.md          # 系統架構
│   ├── api-design.md            # API 規格
│   ├── setup.md                 # 部署指南
│   └── examples/                # 示例數據
├── docker-compose.yml            # Docker Compose 配置
└── .env.example                  # 環境變數範例
```

## 問題排除

- **服務啟動失敗**：
  - 檢查 Docker 日誌：`docker-compose logs <service-name>`。
  - 確保埠（3000、8000、8001、3306）未被佔用。
- **資料庫連線錯誤**：
  - 確認 `.env` 中的 `DB_HOST=db` 和其他資料庫設置正確。
  - 檢查 `db` 服務是否為「healthy」：`docker-compose ps`。
- **API 請求失敗**：
  - 檢查 `X-API-KEY` 是否正確設置。
  - 確認 CORS 配置（`ai-services-fastapi/main.py`）是否允許前端域名。
- **AI 服務錯誤**：
  - 檢查 `OPENAI_API_KEY` 是否有效。
  - 確認模型檔案（`ai-services-fastapi/models/model_xgb.pkl`）是否存在。

詳細問題排除請參考 [docs/setup.md](docs/setup.md)。

## 未來擴展

- **SaaS 模式**：支援訂閱計費（基礎版、專業版、企業版）、API 使用量計費及增值服務（如客製化報告）。
- **新功能**：新增用戶認證（Laravel Sanctum）、高級分析儀表板、更多 AI 模型（如圖像生成）。
- **效能優化**：快取（Redis）、負載均衡、水平擴展。
- **安全性增強**：添加 OAuth2、速率限制、進階日誌記錄。

## 貢獻

歡迎提交問題報告或功能建議！請遵循以下步驟：

1. Fork 專案並建立您的分支（`git checkout -b feature/your-feature`）。
2. 提交變更（`git commit -m "Add your feature"`）。
3. 推送到分支（`git push origin feature/your-feature`）。
4. 建立 Pull Request。

## 聯繫方式

如有問題或合作意向，請聯繫：  
📧 [contact@smart-realestate.com](mailto:contact@smart-realestate.com)

## 授權

本專案採用 MIT 授權。詳情請見 [LICENSE](LICENSE) 文件。

---

*最後更新：2025-06-27*
