<?php

namespace App\Modules\Providers\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Providers\Models\ProviderProfile;
use App\Modules\Providers\Services\ProviderDocumentService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProviderDocumentController extends Controller
{
    public function show(ProviderProfile $provider, string $document, ProviderDocumentService $documents): StreamedResponse
    {
        return $documents->download($provider, $document);
    }
}