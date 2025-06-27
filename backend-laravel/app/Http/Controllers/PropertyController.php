<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Property;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class PropertyController extends Controller
{
    /**
     * Display a listing of the properties, with pagination support.
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $page = $request->query('page', 1);
            $limit = $request->query('limit', 10);

            $properties = Property::paginate($limit, ['*'], 'page', $page);
            
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
     * Display the specified property.
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $property = Property::findOrFail($id);
            return response()->json($property);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => '找不到指定的建案。'], 404);
        } catch (\Exception $e) {
            Log::error("Failed to get property ID:{$id} details: " . $e->getMessage());
            return response()->json(['error' => '無法取得建案詳情，請稍後再試。'], 500);
        }
    }

    /**
     * Automatically generate marketing content for the specified property.
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function generateContent($id)
    {
        try {
            $property = Property::findOrFail($id);

            // Ensure the AI service URL is configured
            $aiServiceUrl = env('AI_SERVICE_URL');
            $aiServiceApiKey = env('AI_SERVICE_INTERNAL_API_KEY');
            if (empty($aiServiceUrl)) {
                throw new \Exception('AI 服務 URL 未設定。');
            }

            // Call the FastAPI AI service to generate content
            $response = Http::timeout(60)
                            ->withHeaders([
                                'X-API-KEY' => $aiServiceApiKey, // 添加 AI 服務內部 API 金鑰
                            ])
                            ->post("{$aiServiceUrl}/generate/content", [
                                'property_data' => $property->toArray(),
                            ]);

            // Check the AI service response status
            if ($response->successful()) {
                return response()->json([
                    'property_id' => $property->id,
                    'generated_content' => $response->json()['generated_content'] ?? '內容生成失敗或為空。'
                ]);
            } else {
                $errorMessage = $response->json()['detail'] ?? $response->body();
                Log::error("Failed to generate content from AI service, status code: {$response->status()}，response: {$errorMessage}");
                return response()->json([
                    'error' => '無法從 AI 服務生成內容。',
                    'details' => $errorMessage
                ], $response->status() >= 400 ? $response->status() : 500);
            }
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => '找不到指定的建案。'], 404);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Could not connect to AI service: ' . $e->getMessage());
            return response()->json(['error' => '無法連接到 AI 服務，請檢查服務是否運行。'], 503);
        } catch (\Exception $e) {
            Log::error("Failed to generate content for property ID:{$id}: " . $e->getMessage());
            return response()->json(['error' => '內容生成過程中發生錯誤，請稍後再試。'], 500);
        }
    }

    /**
     * Health check endpoint.
     * Checks the connection status of the database and the AI service.
     * @return \Illuminate\Http\JsonResponse
     */
    public function healthCheck()
    {
        $status = ['api_service' => 'ok'];

        // Check database connection
        try {
            DB::connection()->getPdo();
            $status['database'] = 'ok';
        } catch (\Exception $e) {
            $status['database'] = 'error';
            $status['database_error'] = '資料庫連線失敗: ' . $e->getMessage();
            Log::error('Database connection check failed: ' . $e->getMessage());
        }

        // Check AI service connection by calling a test endpoint
        try {
            $aiServiceUrl = env('AI_SERVICE_URL');
            $aiServiceApiKey = env('AI_SERVICE_INTERNAL_API_KEY'); // 取得 AI 服務內部金鑰
            if (empty($aiServiceUrl)) {
                $status['ai_service'] = 'error';
                $status['ai_service_error'] = 'AI 服務 URL 未設定。';
            } else {
                // 模擬真實 AI 服務輸入進行健康檢查
                $testPayload = [
                    'area' => 50.0,
                    'address' => '台北市健康區',
                    'num_rooms' => 3,
                    'num_bathrooms' => 2,
                    'age' => 10,
                    'location_factor' => 1.0,
                    'is_near_mrt' => 0
                ];
                $response = Http::timeout(5)
                                ->withHeaders([
                                    'X-API-KEY' => $aiServiceApiKey, // 傳遞 AI 服務內部 API 金鑰
                                ])
                                ->post("{$aiServiceUrl}/predict/price", $testPayload);
                
                if ($response->successful()) {
                    $status['ai_service'] = 'ok';
                } else {
                    $status['ai_service'] = 'error';
                    $status['ai_service_error'] = "AI 服務回應錯誤: {$response->status()} - " . $response->body();
                }
            }
        } catch (\Exception $e) {
            $status['ai_service'] = 'error';
            $status['ai_service_error'] = '無法連接到 AI 服務: ' . $e->getMessage();
            Log::error('AI service connection check failed: ' . $e->getMessage());
        }

        $httpStatus = 200;
        if (in_array('error', $status, true)) {
            $httpStatus = 500;
        }

        return response()->json($status, $httpStatus);
    }
}
