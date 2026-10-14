<?php
namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/** تخزين الصور بأسماء عشوائية؛ المسارات لا تؤخذ من مدخلات المستخدم. */
class CatalogImageStorage
{
    /**
     * @param UploadedFile $image
     * @return string
     * @throws RuntimeException
     */
    public function store(UploadedFile $image): string
    {
        $path = $image->store('catalog', 'public');
        if (!$path) {
            throw new RuntimeException('Unable to store catalog image.');
        }
        return $path;
    }

    /**
     * الحذف مخصص لملفات هذا المجال فقط؛ لا نقبل مسارات خارج مجلد الكتالوج.
     * @param string|null $path
     * @return void
     */
    public function delete(?string $path): void
    {
        if ($path && str_starts_with($path, 'catalog/')) {
            Storage::disk('public')->delete($path);
        }
    }
}
