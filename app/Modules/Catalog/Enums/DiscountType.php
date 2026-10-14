<?php
namespace App\Modules\Catalog\Enums;
/** الخيارات الثابتة للتخفيض؛ الخدمات والباقات نفسها سجلات بمعرفات. */
enum DiscountType: string
{
    case None = 'none';
    case Fixed = 'fixed';
    case Percentage = 'percentage';
}
