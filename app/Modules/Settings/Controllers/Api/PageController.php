<?php

namespace App\Modules\Settings\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Settings\Services\ApiPageService;
use Illuminate\Http\JsonResponse;

/**
 * متحكم استرجاع الصفحات الثابتة (الشروط والأحكام وسياسة الخصوصية) لتطبيق الموبايل والعملاء.
 */
class PageController extends Controller
{
    public function __construct(private readonly ApiPageService $service)
    {
    }

    /**
     * إرجاع الشروط والأحكام.
     */
    public function terms(): JsonResponse
    {
        return response()->json($this->service->getPage('terms'), 200);
    }

    /**
     * إرجاع سياسة الخصوصية.
     */
    public function privacy(): JsonResponse
    {
        return response()->json($this->service->getPage('privacy'), 200);
    }

    /**
     * إرجاع أي صفحة بناءً على الـ slug.
     */
    public function show(string $slug): JsonResponse
    {
        return response()->json($this->service->getPage($slug), 200);
    }
}

