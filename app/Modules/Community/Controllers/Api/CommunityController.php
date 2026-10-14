<?php

namespace App\Modules\Community\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Community\Services\ApiCommunityService;
use App\Modules\Identity\Models\User;
use App\Modules\Community\Requests\ApiCommunityAnnouncementsRequest;
use App\Modules\Community\Requests\ApiCommunityCommunityPostsRequest;
use App\Modules\Community\Requests\ApiCommunityCreateCommunityPostRequest;
use App\Modules\Community\Requests\ApiCommunityCommentRequest;
use App\Modules\Community\Requests\ApiCommunityReactRequest;
use App\Modules\Community\Requests\ApiCommunityInitiativesRequest;
use App\Modules\Community\Requests\ApiCommunityJoinInitiativeRequest;

/** متحكم HTTP لمجال Community؛ يفوض جميع العمليات إلى الخدمة. */
class CommunityController extends Controller
{
    /**
     * حقن خدمة المجال؛ لا توجد استعلامات أو قواعد عمل داخل المتحكم.
     * @param ApiCommunityService $service
     * @return void
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function __construct(private readonly ApiCommunityService $service)
    {
    }

    /**
     * تمرير الطلب المتحقق منه وإرجاع استجابة HTTP فقط.
     * @param ApiCommunityAnnouncementsRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function announcements(ApiCommunityAnnouncementsRequest $request): \Illuminate\Http\JsonResponse
    {
        return response()->json($this->service->announcements($request->validated(), $request->user()), 200);
    }

    /**
     * تمرير الطلب المتحقق منه وإرجاع استجابة HTTP فقط.
     * @param ApiCommunityCommunityPostsRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function communityPosts(ApiCommunityCommunityPostsRequest $request): \Illuminate\Http\JsonResponse
    {
        return response()->json($this->service->communityPosts($request->validated(), $request->user()), 200);
    }

    /**
     * تمرير الطلب المتحقق منه وإرجاع استجابة HTTP فقط.
     * @param ApiCommunityCreateCommunityPostRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function createCommunityPost(ApiCommunityCreateCommunityPostRequest $request): \Illuminate\Http\JsonResponse
    {
        return response()->json($this->service->createCommunityPost($request->validated(), $request->user()), 201);
    }

    /**
     * تمرير الطلب المتحقق منه وإرجاع استجابة HTTP فقط.
     * @param ApiCommunityCommentRequest $request
     * @param int $postId
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function comment(ApiCommunityCommentRequest $request, int $postId): \Illuminate\Http\JsonResponse
    {
        return response()->json($this->service->comment($request->validated(), $request->user(), $postId), 201);
    }

    /**
     * تمرير الطلب المتحقق منه وإرجاع استجابة HTTP فقط.
     * @param ApiCommunityReactRequest $request
     * @param int $postId
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function react(ApiCommunityReactRequest $request, int $postId): \Illuminate\Http\JsonResponse
    {
        return response()->json($this->service->react($request->validated(), $request->user(), $postId), 200);
    }

    /**
     * تمرير الطلب المتحقق منه وإرجاع استجابة HTTP فقط.
     * @param ApiCommunityInitiativesRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function initiatives(ApiCommunityInitiativesRequest $request): \Illuminate\Http\JsonResponse
    {
        return response()->json($this->service->initiatives($request->validated(), $request->user()), 200);
    }

    /**
     * تمرير الطلب المتحقق منه وإرجاع استجابة HTTP فقط.
     * @param ApiCommunityJoinInitiativeRequest $request
     * @param int $initiativeId
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function joinInitiative(ApiCommunityJoinInitiativeRequest $request, int $initiativeId): \Illuminate\Http\JsonResponse
    {
        return response()->json($this->service->joinInitiative($request->validated(), $request->user(), $initiativeId), 200);
    }
}
