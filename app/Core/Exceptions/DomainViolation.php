<?php
namespace App\Core\Exceptions;
use App\Core\Enums\FailureReason;
use RuntimeException;
/** رفض قاعدة عمل دون ربط الخدمة بطلب HTTP أو استجابته. */
final class DomainViolation extends RuntimeException
{
    /**
     * @param FailureReason $reason
     * @param string $message
     * @return void
     */
    public function __construct(public readonly FailureReason $reason, string $message = '')
    {
        parent::__construct($message);
    }
    /**
     * @param mixed $condition
     * @param FailureReason $reason
     * @param string $message
     * @return void
     * @throws self
     */
    public static function rejectIf(mixed $condition, FailureReason $reason, string $message = ''): void
    {
        if ($condition) { throw new self($reason, $message); }
    }
    /**
     * @param mixed $condition
     * @param FailureReason $reason
     * @param string $message
     * @return void
     * @throws self
     */
    public static function unless(mixed $condition, FailureReason $reason, string $message = ''): void
    {
        self::rejectIf(!$condition, $reason, $message);
    }
}
