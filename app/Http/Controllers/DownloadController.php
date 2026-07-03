<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DownloadController extends Controller
{
    public function __invoke(Purchase $purchase): StreamedResponse
    {
        abort_if($purchase->user_id !== auth()->id(), 403);

        $filePath = $purchase->product->file_path;

        abort_unless(Storage::disk('local')->exists($filePath), 404, 'File not found.');

        $originalName = $purchase->product->title.'.'.pathinfo($filePath, PATHINFO_EXTENSION);

        return Storage::disk('local')->download($filePath, $originalName);
    }
}
