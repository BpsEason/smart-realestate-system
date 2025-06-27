FROM mysql:8.0

# 複製 schema.sql 到 Docker 映像中 MySQL 啟動時會自動執行的目錄
# 注意：此處僅用於提供一個初始模式，實際資料庫遷移和填充應由 Laravel 處理。
# 如果此目錄下有多個 .sql 或 .sh 檔，MySQL 會按照字母順序執行它們。
COPY ./schema.sql /docker-entrypoint-initdb.d/
