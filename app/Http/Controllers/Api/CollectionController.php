<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MediaResource;
use App\Models\Media;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CollectionController extends Controller
{
    public function index(User $user): AnonymousResourceCollection
    {
        return MediaResource::collection(
            $user->media()->orderBy('title')->get()
        );
    }

    public function store(User $user, Media $media): JsonResponse|MediaResource
    {
        $result = $user->media()->syncWithoutDetaching([
            $media->getKey() => ['progress' => 0],
        ]);

        if ($result['attached'] === []) {
            return response()->json([
                'message' => 'Media sudah ada di koleksi pengguna.',
            ], 409);
        }

        $collectionItem = $user->media()
            ->whereKey($media->getKey())
            ->firstOrFail();

        return (new MediaResource($collectionItem))
            ->response()
            ->setStatusCode(201);
    }

    public function destroy(User $user, Media $media): JsonResponse
    {
        if ($user->media()->detach($media->getKey()) === 0) {
            return response()->json([
                'message' => 'Media tidak ditemukan di koleksi pengguna.',
            ], 404);
        }

        return response()->json(null, 204);
    }
}
