<?php

namespace App\Http\Controllers;

use App\Models\Purchase;

class DownloadController extends Controller
{
    public function __invoke(Purchase $purchase)
    {
        abort_if($purchase->user_id !== auth()->id(), 403);

        return response()->download(
            storage_path('app/'.$purchase->product->file_path),
            $purchase->product->title.'.zip'
        );
    }
}
