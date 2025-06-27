# 系統設定與部署指南

本指南將引導您如何在本地環境中，使用 Docker Compose 部署「智慧化不動產資料整合與價值模型系統」。本專案僅依賴 Docker 環境運行，無需在本地安裝 Node.js、PHP 或 Python。

## 前置條件

在開始之前，請確保您的系統已安裝以下軟體：

* **Docker Desktop** (包含 Docker Engine 和 Docker Compose)
    * **安裝連結**: [下載 Docker Desktop](https://docs.docker.com/get-docker/) (適用於 Windows, macOS, Linux)
    * 請確保 Docker 守護程式正在運行。

### 注意：CORS 配置
在生產環境中，請修改 `ai-services-fastapi/main.py` 的 CORS 設置，將 `allow_origins=["*"]` 更改為僅允許前端域名（例如 `["http://your-frontend.com"]`），以提高安全性。

## 部署步驟

### **步驟 1: 取得專案程式碼**

首先，您需要將專案程式碼下載到您的本地機器。如果您是從這個腳本生成的，則已經有了。

### **步驟 2: 配置環境變數**

1.  在專案根目錄下找到 `.env.example` 文件。
2.  將其複製並重新命名為 `.env`：
    ```bash
    cp .env.example .env
    ```
3.  編輯 `.env` 文件，根據您的需求修改變數。
    * `API_KEY_SECRET`: 這是一個範例的 API 金鑰，用於後端 API 驗證。請將其設置為一個強大且唯一的秘密字串。
    * `OPENAI_API_KEY`: 如果您希望 AI 服務能夠真正呼叫 OpenAI 的模型來生成內容，請在此處填入您的 OpenAI API 金鑰。如果留空，AI 服務將使用模擬內容。
    * `AI_SERVICE_INTERNAL_API_KEY`: 這是 AI 服務的內部 API 金鑰，用於後端服務向 AI 服務發送請求時的驗證。請確保此金鑰的安全。

### **步驟 3: 建構並啟動 Docker 容器**

在專案根目錄 (即 `docker-compose.yml` 所在的目錄) 中，執行以下命令來建構所有服務的 Docker 映像並啟動容器：

```bash
docker-compose up --build -d
```

* `--build`: 確保所有服務的 Docker 映像都會被重新建構。這在您第一次運行或更改 Dockerfile 時非常重要。
* `-d`: 以分離模式 (detached mode) 運行容器，讓它們在背景運行。

這個過程可能需要一些時間，具體取決於您的網路速度和機器性能，因為它需要下載基礎映像和安裝所有依賴。

### **步驟 4: 執行 Laravel 資料庫遷移與填充 (Migrations & Seeding)**

一旦所有服務都啟動並運行，您需要為 Laravel 後端運行資料庫遷移，以建立所需的資料表並填充初始測試資料。

```bash
docker-compose exec backend php artisan migrate --seed
```

* `docker-compose exec backend`: 在 `backend` 服務容器內部執行命令。
* `php artisan migrate --seed`: Laravel's Artisan command, used to run database migrations and simultaneously run the database seeders.

### **步驟 5: 訪問應用程式**

所有服務都啟動並設定完成後，您可以通過以下 URL 訪問應用程式：

* **前端應用程式**: `http://localhost:3000`
* **後端 API (Laravel)**: `http://localhost:8000/api`
* **AI 服務 API (FastAPI)**: `http://localhost:8001`

## 常用 Docker Compose 命令

* **查看運行中的容器**:
    ```bash
    docker-compose ps
    ```
* **停止所有服務**:
    ```bash
    docker-compose stop
    ```
* **停止並移除所有服務、網路和資料卷 (危險！這將刪除資料庫資料和日誌)**:
    ```bash
    docker-compose down -v
    ```
* **查看服務日誌**:
    ```bash
    docker-compose logs -f [服務名稱] # For example: docker-compose logs -f frontend
    ```
    Use `-f` (follow) to view logs in real-time.

## 問題排除

* **服務啟動失敗**:
  - 檢查 Docker Desktop 是否正在運行。
  - 使用 `docker-compose logs` 命令查看服務日誌，以確定錯誤原因。
  - 確保相關埠沒有被其他應用程式佔用。
* **無法連接到資料庫**:
  - 檢查 `.env` 檔案中的資料庫設定（例如 `DB_HOST=db`）。
  - 使用 `docker-compose ps` 確認資料庫服務（`db`）是否顯示為「healthy」。
* **前端無法訪問後端 API**:
  - 檢查瀏覽器開發者工具中的網路請求是否有 CORS 錯誤。
  - 確認後端服務（`backend`）是否已啟動，並檢查前端服務（`frontend`）的環境變數 `VITE_API_BASE_URL` 是否正確指向後端服務。
* **AI 服務無法生成內容或預測**:
  - 檢查 `.env` 中的 `OPENAI_API_KEY` 是否有效。
  - 檢查 AI 服務日誌（`docker-compose logs ai-service`），查看是否有 API 相關的錯誤訊息或模型載入錯誤。
