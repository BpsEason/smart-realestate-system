<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str; // 導入 Str 類

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('api_keys', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('client_name')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 動態生成 API 金鑰，若 .env 中未定義則使用隨機字串
        $defaultApiKey = env('API_KEY_SECRET', Str::random(60));

        // 插入預設 API 金鑰供初始設定
        DB::table('api_keys')->insert([
            'key' => $defaultApiKey,
            'client_name' => 'Default Client',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Revert the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_keys');
    }
};
