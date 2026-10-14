<?php
namespace App\Modules\Catalog\Enums;
/** طريقة إتاحة البند داخل الباقة، وليست حالة تنفيذ خدمة. */
enum FeatureAvailability: string
{
    case Included = 'included';
    case Excluded = 'excluded';
    case AddOn = 'addon';
}
