from fastapi import APIRouter, HTTPException, Depends
from pydantic import BaseModel
import pandas as pd
import joblib
import os
import logging

# 配置日誌記錄
logger = logging.getLogger(__name__)

# Define the data model for the request body
class PredictionRequest(BaseModel):
    area: float # Area (ping)
    address: str # Address or region
    # 添加其他可能用於模型預測的模擬特徵，以使健康檢查更真實
    num_rooms: int = 3
    num_bathrooms: int = 2
    age: int = 10
    location_factor: float = 1.0
    is_near_mrt: int = 0

router = APIRouter(
    prefix="/predict",
    tags=["房價預測"] # Tag for the API documentation
)

# --- Start of model loading logic ---
# Define the path to the model file
MODEL_PATH = os.path.join(os.path.dirname(__file__), '../models/model_xgb.pkl')
MODEL = None
try:
    # 檢查模型檔案是否存在並載入
    if os.path.exists(MODEL_PATH):
        MODEL = joblib.load(MODEL_PATH)
        logger.info(f"✅ 成功載入預測模型: {MODEL_PATH}")
    else:
        # 如果檔案不存在，則記錄警告並使用模擬預測
        logger.warning(f"❌ 警告: 預測模型檔案不存在於 {MODEL_PATH}。將執行模擬預測。")
except Exception as e:
    logger.error(f"❌ 載入模型失敗，檔案位於 {MODEL_PATH}: {e}。將執行模擬預測。")
# --- End of model loading logic ---

@router.post("/price", summary="Predict property price")
def predict_price(data: PredictionRequest):
    """
    Predicts the property price based on area and address.
    
    - **area**: Property area (ping)
    - **address**: Property address or region
    """
    
    if data.area <= 0:
        logger.error(f"無效的面積輸入: {data.area}")
        raise HTTPException(status_code=400, detail="面積必須是大於零的數值。")
    
    predicted_price_in_ten_thousand = 0.0

    # 使用載入的模型進行預測，如果模型存在
    if MODEL:
        try:
            # 為模型準備輸入資料。
            # 在真實系統中，會有更完善的特徵工程管道。
            # 'location_factor' 和 'is_near_mrt' 是根據地址模擬的。
            location_factor = 1.0
            is_near_mrt = 0
            if '大安區' in data.address or '信義區' in data.address:
                location_factor = 1.5
                is_near_mrt = 1
            elif '中山區' in data.address:
                location_factor = 1.2
            
            input_data = pd.DataFrame([[data.area, data.num_rooms, data.num_bathrooms, data.age, location_factor, is_near_mrt]],
                                      columns=['area', 'num_rooms', 'num_bathrooms', 'age', 'location_factor', 'is_near_mrt'])
            
            predicted_price_in_ten_thousand = MODEL.predict(input_data)[0]
            logger.info(f"成功使用模型預測價格：{predicted_price_in_ten_thousand} 萬 (面積: {data.area}, 地址: {data.address})")
        except Exception as e:
            # 如果預測失敗 (例如，特徵不匹配)，則記錄並退回模擬預測
            logger.warning(f"⚠️ 模型預測失敗，錯誤訊息: {e}。退回模擬邏輯。")
            # 退回模擬邏輯
            base_price_per_ping = 150 if '大安區' in data.address or '信義區' in data.address else 80
            predicted_price_in_ten_thousand = data.area * base_price_per_ping
    else:
        # 如果模型未載入，則使用模擬預測邏輯
        base_price_per_ping = 80
        
        if '大安區' in data.address or '信義區' in data.address:
            base_price_per_ping = 150
        elif '中山區' in data.address or '松山區' in data.address:
            base_price_per_ping = 120
        elif '文山區' in data.address or '北投區' in data.address:
            base_price_per_ping = 70
        
        predicted_price_in_ten_thousand = data.area * base_price_per_ping
        logger.info(f"使用模擬邏輯預測價格：{predicted_price_in_ten_thousand} 萬 (面積: {data.area}, 地址: {data.address})")

    # 返回價格並四捨五入到小數點後兩位 (單位：萬台幣)
    return {"predicted_price": round(float(predicted_price_in_ten_thousand), 2)}
