# REST API 規格定義

本文件詳細說明了「智慧化不動產資料整合與價值模型系統」提供的 RESTful API 端點。

## 基礎 URL:

* **後端 API**: `http://localhost:8000/api`
* **AI 服務 API**: `http://localhost:8001` (直接呼叫，或透過後端轉發)

## 身份驗證:

所有後端 API 都需要通過 `X-API-KEY` 標頭提供 API 金鑰。請在 `.env` 檔案中設定 `API_KEY_SECRET`，並確保該金鑰已存在於資料庫的 `api_keys` 表中且為啟用狀態。

AI 服務 API (直接呼叫時) 也需要 `X-API-KEY` 標頭進行驗證，其金鑰來自 `ai-services-fastapi/.env` 中的 `AI_SERVICE_INTERNAL_API_KEY`。

---

### **1. 建案 (Properties) API**

#### **1.1 取得所有建案列表 (支援分頁)**

* **端點**: `GET /api/properties`
* **描述**: 獲取系統中所有不動產建案的列表，支援分頁。
* **請求參數**:
    * `page` (整數, 可選): 指定返回的頁碼，預設為 1。
    * `limit` (整數, 可選): 每頁返回的建案數量，預設為 10。
* **Curl 範例**:
    ```bash
    curl -X GET "http://localhost:8000/api/properties?page=1&limit=5" \
    -H "X-API-KEY: your_super_secret_api_key_here"
    ```
* **成功回應 (200 OK)**:
    ```json
    {
      "data": [
        {
          "id": 1,
          "address": "台北市大安區新生南路一段",
          "area": "35.50",
          "price": "3500.00",
          "description": "捷運站旁，生活機能極佳，學區房。採光良好，社區管理完善。",
          "image_url": "[https://placehold.co/800x600?text=建案一](https://placehold.co/800x600?text=建案一)",
          "latitude": "25.0339600",
          "longitude": "121.5644700",
          "created_at": "2024-05-20T10:00:00.000000Z",
          "updated_at": "2024-05-20T10:00:00.000000Z"
        },
        // ... more property objects (based on the limit parameter)
      ],
      "meta": {
        "current_page": 1,
        "total": 50,
        "per_page": 10,
        "last_page": 5
      }
    }
    ```
* **錯誤回應 (401 Unauthorized)**:
    ```json
    {
      "error": "無效或停用的 API 金鑰。"
    }
    ```
* **錯誤回應 (500 Internal Server Error)**:
    ```json
    {
      "error": "無法取得建案資料，請稍後再試。"
    }
    ```

#### **1.2 取得單一建案詳情**

* **端點**: `GET /api/properties/{id}`
* **描述**: 根據建案 ID 獲取其詳細資訊。
* **路徑參數**:
    * `id` (整數, 必填): 建案的唯一識別碼。
* **Curl 範例**:
    ```bash
    curl -X GET "http://localhost:8000/api/properties/1" \
    -H "X-API-KEY: your_super_secret_api_key_here"
    ```
* **成功回應 (200 OK)**:
    ```json
    {
      "id": 1,
      "address": "台北市大安區新生南路一段",
      "area": "35.50",
      "price": "3500.00",
      "description": "捷運站旁，生活機能極佳，學區房。採光良好，社區管理完善。",
      "image_url": "[https://placehold.co/800x600?text=建案一](https://placehold.co/800x600?text=建案一)",
      "latitude": "25.0339600",
      "longitude": "121.5644700",
      "created_at": "2024-05-20T10:00:00.000000Z",
      "updated_at": "2024-05-20T10:00:00.000000Z"
    }
    ```
* **錯誤回應 (404 Not Found)**:
    ```json
    {
      "error": "找不到指定的建案。"
    }
    ```

#### **1.3 為建案生成行銷內容**

* **端點**: `POST /api/properties/{id}/generate-content`
* **描述**: 觸發 AI 服務為指定建案自動生成行銷文案。
* **路徑參數**:
    * `id` (整數, 必填): 建案的唯一識別碼。
* **請求體**: 空 JSON 物件 `{}` 或包含 `prompt` 字段。
    ```json
    {
      "prompt": "請強調該建案的交通便利性。"
    }
    ```
* **Curl 範例**:
    ```bash
    curl -X POST "http://localhost:8000/api/properties/1/generate-content" \
    -H "X-API-KEY: your_super_secret_api_key_here" \
    -H "Content-Type: application/json" \
    -d '{ "prompt": "請強調該建案的交通便利性。" }'
    ```
* **成功回應 (200 OK)**:
    ```json
    {
      "property_id": 1,
      "generated_content": "絕佳機會！位於台北市大安區新生南路的典雅居所現正發售。擁有約 35.50 坪的舒適空間，並以約 3500.00 萬的價格呈現。特色：捷運站旁，生活機能極佳，學區房。採光良好，格局方正，交通便利。是您尋找新家或投資的理想選擇。立即預約參觀，親身體驗這個夢幻般的居住空間！"
    }
    ```
* **錯誤回應 (404 Not Found)**:
    ```json
    {
      "error": "找不到指定的建案。"
    }
    ```
* **錯誤回應 (500 Internal Server Error)** (AI service internal error):
    ```json
    {
      "error": "無法從 AI 服務生成內容。",
      "details": "（AI 服務返回的錯誤訊息或堆疊追蹤）"
    }
    ```
* **錯誤回應 (503 Service Unavailable)** (Could not connect to AI service):
    ```json
    {
      "error": "無法連接到 AI 服務，請檢查服務是否運行。"
    }
    ```

### **2. AI 服務 (FastAPI) API**

These endpoints are mainly called internally by the backend service, but can also be accessed directly by trusted clients (e.g., using a dedicated internal API key).

#### **2.1 預測房價**

* **端點**: `POST /predict/price`
* **描述**: 預測不動產的市場價格。
* **請求體**:
    ```json
    {
      "area": 30.5,
      "address": "台北市大安區",
      "num_rooms": 3,
      "num_bathrooms": 2,
      "age": 10,
      "location_factor": 1.2,
      "is_near_mrt": 1
    }
    ```
* **Curl 範例**:
    ```bash
    curl -X POST "http://localhost:8001/predict/price" \
    -H "X-API-KEY: your_ai_service_internal_key" \
    -H "Content-Type: application/json" \
    -d '{ "area": 30.5, "address": "台北市大安區", "num_rooms": 3, "num_bathrooms": 2, "age": 10, "location_factor": 1.2, "is_near_mrt": 1 }'
    ```
* **成功回應 (200 OK)**:
    ```json
    {
      "predicted_price": 4500.00
    }
    ```
* **錯誤回應 (400 Bad Request)**:
    ```json
    {
      "detail": "面積必須是大於零的數值。"
    }
    ```
* **錯誤回應 (401 Unauthorized)**:
    ```json
    {
      "detail": "無效或缺少的 API 金鑰。"
    }
    ```
* **錯誤回應 (500 Internal Server Error)**:
    ```json
    {
      "detail": "預測模型檔案不存在，請確認模型已正確訓練並儲存。"
    }
    ```

#### **2.2 生成行銷內容**

* **端點**: `POST /generate/content`
* **描述**: 根據提供的建案資料生成行銷文案。
* **請求體**:
    ```json
    {
      "property_data": {
        "address": "台北市大安區新生南路一段",
        "area": 35.5,
        "price": 3500,
        "description": "捷運站旁，生活機能極佳，學區房。"
      },
      "prompt": "請強調該建案的投資潛力。"
    }
    ```
* **Curl 範例**:
    ```bash
    curl -X POST "http://localhost:8001/generate/content" \
    -H "X-API-KEY: your_ai_service_internal_key" \
    -H "Content-Type: application/json" \
    -d '{ "property_data": { "address": "台北市大安區新生南路一段", "area": 35.5, "price": 3500, "description": "捷運站旁，生活機能極佳，學區房。" }, "prompt": "請強調該建案的投資潛力。" }'
    ```
* **成功回應 (200 OK)**:
    ```json
    {
      "generated_content": "絕佳機會！位於台北市大安區新生南路的典雅居所現正發售。擁有約 35.5 坪的舒適空間，並以約 3500 萬的價格呈現。特色：捷運站旁，生活機能極佳，學區房。是您尋找新家或投資的理想選擇。立即預約參觀，親身體驗這個夢幻般的居住空間！"
    }
    ```
* **錯誤回應 (401 Unauthorized)** (OpenAI API key invalid or internal API key missing/invalid):
    ```json
    {
      "detail": "OpenAI API 金鑰無效或認證失敗，請檢查您的金鑰配置。"
    }
    ```
* **錯誤回應 (429 Too Many Requests)** (OpenAI rate limit exceeded):
    ```json
    {
      "detail": "OpenAI API 配額超限或請求頻率過高，請稍後重試。"
    }
    ```
* **錯誤回應 (500 Internal Server Error)** (e.g., other OpenAI API errors):
    ```json
    {
      "detail": "生成文案時發生未預期錯誤，請檢查 AI 服務日誌。"
    }
    ```
