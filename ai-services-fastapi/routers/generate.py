from fastapi import APIRouter, HTTPException, Depends
from pydantic import BaseModel
import os
from openai import OpenAI, RateLimitError, AuthenticationError
import logging

# 配置日誌記錄
logger = logging.getLogger(__name__)

# Define the data model for the request body
class GenerationRequest(BaseModel):
    property_data: dict # Dictionary containing property details
    prompt: str = "" # 額外的提示詞，用於細化文案生成

router = APIRouter(
    prefix="/generate",
    tags=["內容生成"] # Tag for the API documentation
)

# 初始化 OpenAI 客戶端
# 確保您的 OPENAI_API_KEY 環境變數已設定
client = OpenAI(api_key=os.getenv("OPENAI_API_KEY"))

@router.post("/content", summary="Automatically generate property marketing content")
async def generate_content(data: GenerationRequest):
    """
    Automatically generates attractive marketing content based on the provided property data.
    
    - **property_data**: A dictionary containing information like property address, area, price, and description.
    - **prompt**: An optional additional prompt to refine the content generation.
    """
    property_info = data.property_data
    
    # 準備一個用於語言模型的提示
    base_prompt = (
        f"請為這個位於 {property_info.get('address', '不詳地點')} 的建案撰寫一段精美的行銷文案。\n"
        f"主要資訊：面積約 {property_info.get('area', '未知')} 坪，開價約 {property_info.get('price', '未知')} 萬。\n"
        f"更多細節：{property_info.get('description', '無特別描述。')}\n"
    )

    if data.prompt:
        base_prompt += f"額外要求: {data.prompt}\n"
    
    base_prompt += f"請強調其優勢，並使用吸引人的語氣，字數控制在 150 字以內。"


    generated_content = ""
    try:
        if not client.api_key:
            logger.error("OpenAI API 金鑰未配置。")
            raise HTTPException(status_code=500, detail="OpenAI API 金鑰未配置。請檢查 .env 檔案。")

        logger.info(f"嘗試生成內容，提示詞開頭：{base_prompt[:100]}...")
        response = client.chat.completions.create(
            model="gpt-3.5-turbo", # 或 "gpt-4" 等
            messages=[
                {"role": "system", "content": "您是一位專業且極具創意的房地產文案寫手。"},
                {"role": "user", "content": base_prompt}
            ],
            max_tokens=300, # 控制生成內容的長度
            temperature=0.7, # 創意程度
        )
        generated_content = response.choices[0].message.content.strip()
        logger.info("內容成功生成。")

    except AuthenticationError as e:
        # 處理無效的 API 金鑰
        logger.error(f"OpenAI API 認證失敗: {e}")
        raise HTTPException(status_code=401, detail=f"OpenAI API 金鑰無效或認證失敗，請檢查您的金鑰配置。詳細錯誤: {e}")
    except RateLimitError as e:
        # 處理配額超限
        logger.error(f"OpenAI API 配額超限: {e}")
        raise HTTPException(status_code=429, detail=f"OpenAI API 配額超限或請求頻率過高，請稍後重試。詳細錯誤: {e}")
    except Exception as e:
        # 捕獲其他潛在錯誤，例如網路問題、無效的模型名稱等
        logger.error(f"呼叫 OpenAI API 失敗: {e}")
        raise HTTPException(status_code=500, detail=f"生成文案時發生未預期錯誤，請檢查 AI 服務日誌。詳細錯誤: {e}")

    return {"generated_content": generated_content}
