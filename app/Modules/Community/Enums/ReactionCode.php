<?php
namespace App\Modules\Community\Enums;
/** الخيارات الثابتة للمجال؛ لا تتغير بتعديل التسميات المترجمة. */
enum ReactionCode: string
{
    case Like = 'like';
    case Love = 'love';
    case Support = 'support';
    case Thanks = 'thanks';
}
