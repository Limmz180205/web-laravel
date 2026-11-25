<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMediaRequest;
use App\Http\Requests\UpdateMediaRequest;
use App\Http\Resources\MediaResource;
use App\Models\Media;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class MediaController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->validate([
            'search' => ['sometimes', 'string', 'max:255'],
            'type' => ['sometimes', 'in:anime,drama,manga,light_novel'],
            'status' => ['sometimes', 'in:finished,current,upcoming'],
        ]);

        $media = Media::query()
            ->when(
                isset($filters['search']),
                fn (Builder $query) => $query->where('title', 'like', '%'.$filters['search'].'%')
            )
            ->when(
                isset($filters['type']),
                fn (Builder $query) => $query->where('type', $filters['type'])
            )
            ->when(
                isset($filters['status']),
                fn (Builder $query) => $query->where('status', $filters['status'])
            )
            ->orderByDesc('score')
            ->paginate(15)
            ->withQueryString();

        return MediaResource::collection($media);
    }

    public function store(StoreMediaRequest $request): JsonResponse
    {
        $media = Media::create($request->validated());

        return (new MediaResource($media))->response()->setStatusCode(201);
    }

    public function show(Media $media): MediaResource
    {
        return new MediaResource($media->load('users'));
    }

    public function update(UpdateMediaRequest $request, Media $media): MediaResource
    {
        $media->update($request->validated());

        return new MediaResource($media->refresh());
    }

    public function destroy(Media $media): Response
    {
        $media->delete();

        return response()->noContent();
    }
}
