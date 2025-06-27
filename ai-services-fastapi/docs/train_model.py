# train_model.py
import pandas as pd
import numpy as np
from sklearn.model_selection import train_test_split
from xgboost import XGBRegressor
from sklearn.metrics import mean_squared_error, r2_score
import joblib
import os

def train_and_save_model():
    """
    此函數示範如何訓練 XGBoost 模型並儲存。
    在實際應用中，您將從資料庫或資料湖載入真實的、已清洗的資料。
    """
    print("正在準備訓練資料...")

    # 模擬生成一些不動產資料
    # 在實際情況中，這些資料將從資料庫或其他來源載入
    data = {
        'area': np.random.uniform(20, 100, 1000), # 面積 (坪)
        'num_rooms': np.random.randint(1, 5, 1000), # 房間數
        'num_bathrooms': np.random.randint(1, 3, 1000), # 浴室數
        'age': np.random.randint(1, 30, 1000), # 建築年齡 (年)
        'location_factor': np.random.uniform(0.5, 2.0, 1000), # 地理位置影響因素
        'is_near_mrt': np.random.choice([0, 1], 1000, p=[0.7, 0.3]) # 是否靠近捷運站？
    }
    df = pd.DataFrame(data)

    # 計算模擬價格 (單位：萬台幣)
    # 價格 = (面積 * 每坪基礎價格 + 房間數 * 10 + 浴室數 * 5 - 年齡 * 2) * 地理位置因素
    df['price'] = (
        df['area'] * 80 +
        df['num_rooms'] * 10 +
        df['num_bathrooms'] * 5 -
        df['age'] * 2
    ) * df['location_factor'] + df['is_near_mrt'] * 50 # 靠近捷運的額外價格

    # 確保價格為正值
    df['price'] = df['price'].apply(lambda x: max(100, x))

    # 定義特徵 (X) 和目標 (y)
    X = df[['area', 'num_rooms', 'num_bathrooms', 'age', 'location_factor', 'is_near_mrt']]
    y = df['price']

    # 分割訓練集和測試集
    X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42)

    print(f"訓練資料點數量: {len(X_train)}")
    print(f"測試資料點數量: {len(X_test)}")

    # 初始化並訓練 XGBoost 回歸模型
    print("正在訓練 XGBoost 模型...")
    model = XGBRegressor(
        objective='reg:squarederror', # 回歸任務的目標函數
        n_estimators=100,             # 樹的數量
        learning_rate=0.1,            # 學習率
        max_depth=5,                  # 最大樹深度
        random_state=42
    )
    model.fit(X_train, y_train)
    print("模型訓練完成。")

    # 在測試集上進行預測並評估模型
    y_pred = model.predict(X_test)
    mse = mean_squared_error(y_test, y_pred)
    r2 = r2_score(y_test, y_pred)

    print(f"\n模型評估結果:")
    print(f"均方誤差 (MSE): {mse:.2f}")
    print(f"R 平方 (R2): {r2:.2f}")

    # 儲存訓練好的模型
    model_dir = os.path.join(os.path.dirname(__file__), '../models')
    os.makedirs(model_dir, exist_ok=True) # 確保 models 目錄存在

    model_filename = os.path.join(model_dir, 'model_xgb.pkl')
    joblib.dump(model, model_filename)
    print(f"模型已儲存至: {model_filename}")

    print("\n您現在可以在 ai-services-fastapi/routers/predict.py 中載入此模型以進行實際預測。")

if __name__ == "__main__":
    train_and_save_model()
