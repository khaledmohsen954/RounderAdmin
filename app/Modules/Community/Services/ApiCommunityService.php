<?php

namespace App\Modules\Community\Services;
use App\Core\Exceptions\DomainViolation;
use App\Core\Enums\FailureReason;

use App\Modules\Community\Models\Announcement;

use App\Modules\Community\Models\CommunityComment;
use App\Modules\Community\Models\CommunityPost;
use App\Modules\Community\Models\CommunityReaction;

use App\Modules\Community\Models\Initiative;
use App\Modules\Community\Models\InitiativeParticipation;
use App\Modules\Properties\Models\ResidentHome;

use Illuminate\Support\Str;

use App\Modules\Identity\Models\User;
use Illuminate\Support\Facades\DB;
use App\Modules\Community\Events\CommunityChanged;
use App\Modules\Community\Enums\Operation;

/** قواعد عمل Community؛ تستقبل بيانات متحققاً منها دون الاعتماد على HTTP. */
class ApiCommunityService
{

    /**
     * قراءة بيانات المجال مع الحفاظ على قيود الوصول.
     * @param array $input
     * @param ?User $actor
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function announcements(array $input, ?User $actor): mixed
    {
        $homeIds = $this->homeIds($input, $actor);
        DomainViolation::rejectIf($homeIds->isEmpty(), FailureReason::Forbidden, 'Link a home first.');

        $locale = app()->getLocale();
        $rows = Announcement::query()
            ->leftJoin('announcement_translations', function ($join) use ($locale) {
                $join->on('announcements.id', '=', 'announcement_translations.announcement_id')
                    ->where('announcement_translations.locale', '=', $locale);
            })
            ->where('announcements.is_published', true)
            ->where(function ($query) use ($homeIds) {
                $query->whereNull('announcements.home_id')->orWhereIn('announcements.home_id', $homeIds);
            })
            ->where(function ($query) {
                $query->whereNull('announcements.expires_at')->orWhere('announcements.expires_at', '>', now());
            })
            ->select('announcements.id', 'announcements.reference', 'announcements.published_at',
                'announcement_translations.title', 'announcement_translations.body')
            ->orderByDesc('announcements.published_at')->paginate(20);

        return ['data' => $rows];
    }

    /**
     * قراءة بيانات المجال مع الحفاظ على قيود الوصول.
     * @param array $input
     * @param ?User $actor
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function communityPosts(array $input, ?User $actor): mixed
    {
        $homeIds = $this->homeIds($input, $actor);
        DomainViolation::rejectIf($homeIds->isEmpty(), FailureReason::Forbidden, 'Link a home first.');

        return ['data' => CommunityPost::query()
            ->whereIn('home_id', $homeIds)->where('is_hidden', false)
            ->orderByDesc('created_at')->paginate(20)];
    }

    /**
     * تنفيذ التغيير داخل معاملة؛ الحدث لا يخرج إلا بعد نجاح الحفظ.
     * @param array $input
     * @param ?User $actor
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function createCommunityPost(array $input, ?User $actor): mixed
    {
        return DB::transaction(function () use ($input, $actor) {
            $result = $this->performCreateCommunityPost($input, $actor);
            DB::afterCommit(fn () => event(new CommunityChanged(Operation::ApiCommunityCreateCommunityPost, $actor?->id)));
            return $result;
        });
    }

    /**
     * قواعد العملية؛ المعاملة والتبليغ مسؤولية المدخل العام للخدمة.
     * @param array $input
     * @param ?User $actor
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    private function performCreateCommunityPost(array $input, ?User $actor): mixed
    {
        $data = $input;

        $membership = $this->membership($input, $actor);
        DomainViolation::unless($membership, FailureReason::NotFound, 'Resident home not found.');
        

        $id = CommunityPost::query()->create([
            'home_id' => $membership->home_id,
            'resident_home_id' => $membership->id,
            'created_by' => $actor->id,
            'reference' => 'CP-' . Str::upper(Str::random(12)),
            'body' => $data['body'],
            'is_hidden' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ])->getKey();

        return ['data' => CommunityPost::query()->find($id)];
    }

    /**
     * تنفيذ التغيير داخل معاملة؛ الحدث لا يخرج إلا بعد نجاح الحفظ.
     * @param array $input
     * @param ?User $actor
     * @param int $postId
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function comment(array $input, ?User $actor, int $postId): mixed
    {
        return DB::transaction(function () use ($input, $actor, $postId) {
            $result = $this->performComment($input, $actor, $postId);
            DB::afterCommit(fn () => event(new CommunityChanged(Operation::ApiCommunityComment, $actor?->id)));
            return $result;
        });
    }

    /**
     * قواعد العملية؛ المعاملة والتبليغ مسؤولية المدخل العام للخدمة.
     * @param array $input
     * @param ?User $actor
     * @param int $postId
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    private function performComment(array $input, ?User $actor, int $postId): mixed
    {
        $data = $input;

        $homeIds = $this->homeIds($input, $actor);
        DomainViolation::rejectIf($homeIds->isEmpty(), FailureReason::Forbidden, 'Link a home first.');
        DomainViolation::unless(CommunityPost::query()->where('id', $postId)->whereIn('home_id', $homeIds)->where('is_hidden', false)->exists(), FailureReason::NotFound);

        
        $id = CommunityComment::query()->create([
            'community_post_id' => $postId,
            'created_by' => $actor->id,
            'parent_id' => $data['parent_id'] ?? null,
            'body' => $data['body'],
            'created_at' => now(),
            'updated_at' => now(),
        ])->getKey();

        return ['data' => CommunityComment::query()->find($id)];
    }

    /**
     * تنفيذ التغيير داخل معاملة؛ الحدث لا يخرج إلا بعد نجاح الحفظ.
     * @param array $input
     * @param ?User $actor
     * @param int $postId
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function react(array $input, ?User $actor, int $postId): mixed
    {
        return DB::transaction(function () use ($input, $actor, $postId) {
            $result = $this->performReact($input, $actor, $postId);
            DB::afterCommit(fn () => event(new CommunityChanged(Operation::ApiCommunityReact, $actor?->id)));
            return $result;
        });
    }

    /**
     * قواعد العملية؛ المعاملة والتبليغ مسؤولية المدخل العام للخدمة.
     * @param array $input
     * @param ?User $actor
     * @param int $postId
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    private function performReact(array $input, ?User $actor, int $postId): mixed
    {
        $data = $input;

        $homeIds = $this->homeIds($input, $actor);
        DomainViolation::rejectIf($homeIds->isEmpty(), FailureReason::Forbidden, 'Link a home first.');
        DomainViolation::unless(CommunityPost::query()->where('id', $postId)->whereIn('home_id', $homeIds)->where('is_hidden', false)->exists(), FailureReason::NotFound);

        
        CommunityReaction::query()->updateOrCreate(
            ['community_post_id' => $postId, 'user_id' => $actor->id],
            ['reaction_code' => $data['reaction_code'], 'updated_at' => now(), 'created_at' => now()]
        );

        return ['message' => 'Reaction saved.'];
    }

    /**
     * قراءة بيانات المجال مع الحفاظ على قيود الوصول.
     * @param array $input
     * @param ?User $actor
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function initiatives(array $input, ?User $actor): mixed
    {
        $homeIds = $this->homeIds($input, $actor);
        DomainViolation::rejectIf($homeIds->isEmpty(), FailureReason::Forbidden, 'Link a home first.');

        return ['data' => Initiative::query()
            ->whereIn('home_id', $homeIds)->where('is_published', true)
            ->where('ends_at', '>=', now())->orderBy('starts_at')->paginate(20)];
    }

    /**
     * تنفيذ التغيير داخل معاملة؛ الحدث لا يخرج إلا بعد نجاح الحفظ.
     * @param array $input
     * @param ?User $actor
     * @param int $initiativeId
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function joinInitiative(array $input, ?User $actor, int $initiativeId): mixed
    {
        return DB::transaction(function () use ($input, $actor, $initiativeId) {
            $result = $this->performJoinInitiative($input, $actor, $initiativeId);
            DB::afterCommit(fn () => event(new CommunityChanged(Operation::ApiCommunityJoinInitiative, $actor?->id)));
            return $result;
        });
    }

    /**
     * قواعد العملية؛ المعاملة والتبليغ مسؤولية المدخل العام للخدمة.
     * @param array $input
     * @param ?User $actor
     * @param int $initiativeId
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    private function performJoinInitiative(array $input, ?User $actor, int $initiativeId): mixed
    {
        $data = $input;

        $membership = $this->membership($input, $actor);
        DomainViolation::unless($membership, FailureReason::NotFound, 'Resident home not found.');
        DomainViolation::unless(Initiative::query()->where('id', $initiativeId)
            ->where('home_id', $membership->home_id)->where('is_published', true)
            ->where('ends_at', '>=', now())->exists(), FailureReason::NotFound);

        
        InitiativeParticipation::query()->updateOrCreate(
            ['initiative_id' => $initiativeId, 'resident_home_id' => $membership->id],
            ['user_id' => $actor->id, 'pledged_amount' => $data['pledged_amount'] ?? 0,
                'joined_at' => now(), 'updated_at' => now(), 'created_at' => now()]
        );

        return ['message' => 'Participation saved.'];
    }

    /**
     * مساعد داخلي يحافظ على قواعد الوصول والعلاقات.
     * @param array $input
     * @param ?User $actor
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    private function membership(array $input, ?User $actor): mixed
    {
        $query = ResidentHome::query()->where('user_id', $actor->id);
        if ((isset($input['resident_home_id']) && $input['resident_home_id'] !== '')) {
            $query->where('id', (int) ($input['resident_home_id'] ?? 0));
        } else {
            $query->orderByDesc('is_primary')->orderBy('id');
        }

        return $query->first();
    }

    /**
     * مساعد داخلي يحافظ على قواعد الوصول والعلاقات.
     * @param array $input
     * @param ?User $actor
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    private function homeIds(array $input, ?User $actor): mixed
    {
        return ResidentHome::query()->where('user_id', $actor->id)
            ->pluck('home_id')->unique()->values();
    }
}
