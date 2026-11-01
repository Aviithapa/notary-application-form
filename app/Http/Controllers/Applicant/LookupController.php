<?php

namespace App\Http\Controllers\Applicant;

use App\Http\Controllers\Controller;
use App\Models\District;
use App\Models\Municipality;
use App\Support\NepaliDate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Small endpoints used by the applicant form: dependent address selects, AD/BS conversion,
 * and viewing uploaded files (which are only served to their owner or an admin).
 */
class LookupController extends Controller
{
    public function municipalities(District $district): JsonResponse
    {
        $nepali = app()->getLocale() === 'np';

        return response()->json(
            $district->municipalities()->orderBy('name_en')->get(['id', 'name_en', 'name_np'])
                ->map(fn ($m) => ['id' => $m->id, 'name' => $nepali ? $m->name_np : $m->name_en])
        );
    }

    public function wards(Municipality $municipality): JsonResponse
    {
        return response()->json(
            $municipality->wards()->orderBy('ward_number')->get(['id', 'ward_number'])
                ->map(fn ($w) => ['id' => $w->id, 'name' => (string) $w->ward_number])
        );
    }

    public function convertDate(Request $request): JsonResponse
    {
        if ($request->filled('ad')) {
            $ad = NepaliDate::normalize($request->query('ad'));
            $bs = NepaliDate::adToBs($ad);
        } else {
            $bs = NepaliDate::normalize($request->query('bs'));
            $ad = NepaliDate::bsToAd($bs);
        }

        if ($ad === null || $bs === null) {
            return response()->json(['message' => __('Invalid date')], 422);
        }

        return response()->json(['ad' => $ad, 'bs' => $bs]);
    }

    public function media(Request $request, Media $media)
    {
        $user = $request->user();
        $isAdmin = in_array($user->mainRole()?->name, ['admin', 'super_admin'], true);

        abort_unless($isAdmin || (int) $media->model?->user_id === $user->id, 403);

        return $media->toInlineResponse($request);
    }
}
