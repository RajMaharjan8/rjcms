<?php

namespace Rjcodes\Rjcms\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Rjcodes\Rjcms\Actions\StoreMedia;
use Rjcodes\Rjcms\Http\Controllers\Controller;

/**
 * Handles inline image uploads from the rich-text editor, storing them in the
 * media library and returning the URL the editor embeds.
 */
class EditorController extends Controller
{
    /**
     * Store an uploaded editor image and return its URL (CKEditor format).
     */
    public function upload(Request $request, StoreMedia $storeMedia): JsonResponse
    {
        $request->validate([
            'upload' => ['required', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
        ]);

        $media = $storeMedia($request->file('upload'));

        return response()->json(['url' => $media->url]);
    }
}
