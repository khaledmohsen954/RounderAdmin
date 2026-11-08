<?php

namespace App\Modules\Providers\Services;

use App\Modules\Providers\Models\ProviderProfile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProviderDocumentService
{
    /** @var array<string, string> */
    public const DOCUMENTS = [
        'company_seal' => 'Company seal image',
        'commercial_registration_document' => 'Commercial registration document',
        'legal_representative_id_document' => 'Legal representative ID',
        'signed_annex_a' => 'Signed Annex A',
        'municipal_license' => 'Municipal license',
    ];

    /**
     * Save uploaded files privately; old files are removed only after the database commit.
     * @param array<string, mixed> $input
     */
    public function save(ProviderProfile $profile, array $input): void
    {
        $newPaths = [];
        $oldPaths = [];

        foreach (self::DOCUMENTS as $field => $label) {
            $upload = $input[$field] ?? null;
            if (!$upload instanceof UploadedFile) {
                continue;
            }

            $oldPath = $profile->getAttribute($field);
            $newPaths[$field] = $upload->store("provider-documents/{$profile->id}", 'local');
            if ($oldPath) {
                $oldPaths[] = $oldPath;
            }
        }

        if ($newPaths === []) {
            return;
        }

        $profile->update($newPaths);
        DB::afterCommit(static function () use ($oldPaths): void {
            if ($oldPaths !== []) {
                Storage::disk('local')->delete($oldPaths);
            }
        });
    }

    public function download(ProviderProfile $profile, string $key): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        abort_unless(array_key_exists($key, self::DOCUMENTS), 404);
        $path = $profile->getAttribute($key);
        abort_unless(is_string($path) && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->download($path, __(self::DOCUMENTS[$key]) . '.' . pathinfo($path, PATHINFO_EXTENSION));
    }
}