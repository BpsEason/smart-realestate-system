from fastapi import FastAPI, Request, HTTPException
from fastapi.middleware.cors import CORSMiddleware
from .routers import predict, generate
from dotenv import load_dotenv
import os
import logging

# 配置日誌記錄
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

# Load environment variables from the .env file
load_dotenv()

app = FastAPI(
    title="智慧房地產 AI 服務",
    description="提供房產文案生成與價格預測的 API 服務。",
    version="1.0.0",
)

# 從環境變數獲取內部 API 金鑰
INTERNAL_API_KEY = os.getenv("AI_SERVICE_INTERNAL_API_KEY")

# 中間件用於 API 金鑰驗證
@app.middleware("http")
async def verify_api_key(request: Request, call_next):
    # 健康檢查路徑不需要驗證
    if request.url.path == "/" or request.url.path == "/docs" or request.url.path.startswith("/openapi.json"):
        return await call_next(request)
    
    # 對於其他所有路徑，檢查 X-API-KEY 標頭
    api_key = request.headers.get("X-API-KEY")
    if not INTERNAL_API_KEY:
        logger.warning("AI_SERVICE_INTERNAL_API_KEY is not set in .env. Skipping API key validation.")
        return await call_next(request) # 在開發環境中，如果未設定金鑰則跳過驗證

    if api_key != INTERNAL_API_KEY:
        logger.warning(f"Unauthorized access attempt from {request.client.host} with invalid API key.")
        raise HTTPException(status_code=401, detail="無效或缺少的 API 金鑰。")
    
    logger.info(f"API Key validated successfully for request from {request.client.host} to {request.url.path}")
    response = await call_next(request)
    return response

# Add CORS middleware to allow cross-origin requests
# Note: In a production environment, you should set allow_origins to your frontend domain, e.g., ["http://your-frontend.com"]
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"], # Allow all origins (for development)
    allow_credentials=True,
    allow_methods=["*"], # Allow all HTTP methods
    allow_headers=["*"], # Allow all HTTP headers
)

# Include router modules
app.include_router(predict.router)
app.include_router(generate.router)

@app.get("/", summary="Health check endpoint")
def read_root():
    """
    This endpoint is used to check if the AI service is running correctly.
    """
    return {"message": "AI 服務正在成功運行！"}
