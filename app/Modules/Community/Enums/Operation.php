<?php
namespace App\Modules\Community\Enums;
/** أسماء عمليات المجال المستخدمة في الأحداث. */
enum Operation: string
{
    case AdminResidentContentStoreAnnouncement = 'AdminResidentContentService.storeAnnouncement';
    case AdminResidentContentStoreInitiative = 'AdminResidentContentService.storeInitiative';
    case ApiCommunityCreateCommunityPost = 'ApiCommunityService.createCommunityPost';
    case ApiCommunityComment = 'ApiCommunityService.comment';
    case ApiCommunityReact = 'ApiCommunityService.react';
    case ApiCommunityJoinInitiative = 'ApiCommunityService.joinInitiative';
}
