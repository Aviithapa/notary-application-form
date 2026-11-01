<?php

namespace App\Http\Controllers\Applicant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Applicant\PersonalDetailsRequest;
use App\Http\Requests\Applicant\QualificationsRequest;
use App\Http\Requests\Applicant\TrainingsRequest;
use App\Models\ApplicantProfile;
use App\Models\ApplicantQualification;
use App\Models\ApplicantTraining;
use App\Models\District;
use App\Models\Municipality;
use App\Models\User;
use App\Models\Ward;
use App\Support\NepaliDate;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

/**
 * Three-step applicant form: personal details -> qualifications -> trainings.
 * Each step saves on its own, so the applicant can stop and come back later.
 */
class ApplicationController extends Controller
{
    public function personal()
    {
        $user = $this->user();
        $profile = $user->applicantProfile ?? new ApplicantProfile(['email' => $user->email]);

        $districtId = old('district_id', $profile->district_id);
        $municipalityId = old('municipality_id', $profile->municipality_id);
        $corrDistrictId = old('correspondence_district_id', $profile->correspondence_district_id);
        $corrMunicipalityId = old('correspondence_municipality_id', $profile->correspondence_municipality_id);

        return view('admin.pages.applicant.personal', [
            'step' => 'personal',
            'progress' => $user->applicationProgress(),
            'profile' => $profile,
            'genders' => PersonalDetailsRequest::GENDERS,
            'districts' => District::orderBy('name_en')->get(['id', 'name_en', 'name_np']),
            // Pre-filled so the selects render the saved choice without waiting for JavaScript
            'municipalities' => $this->municipalitiesOf($districtId),
            'wards' => $this->wardsOf($municipalityId),
            'corrMunicipalities' => $this->municipalitiesOf($corrDistrictId),
            'corrWards' => $this->wardsOf($corrMunicipalityId),
        ]);
    }

    public function savePersonal(PersonalDetailsRequest $request)
    {
        $profile = ApplicantProfile::updateOrCreate(
            ['user_id' => $this->user()->id],
            $request->profileData(),
        );

        foreach ([ApplicantProfile::PHOTO, ApplicantProfile::CITIZENSHIP_FRONT, ApplicantProfile::CITIZENSHIP_BACK] as $collection) {
            if ($request->hasFile($collection)) {
                // Single-file collections, so a new upload replaces the old one
                $profile->addMediaFromRequest($collection)->toMediaCollection($collection);
            }
        }

        return redirect()->route('applicant.qualifications')
            ->with('success', __('Personal details saved.'));
    }

    public function qualifications()
    {
        $user = $this->user();
        $saved = $user->qualifications()->with('media')->get();

        return view('admin.pages.applicant.qualifications', [
            'step' => 'qualifications',
            'progress' => $user->applicationProgress(),
            'rows' => $this->rows('qualifications', $saved, ['university_name', 'passed_year', 'level', 'division', 'percentage']),
            'saved' => $saved->keyBy('id'),
        ]);
    }

    public function saveQualifications(QualificationsRequest $request)
    {
        $user = $this->user();
        $fields = ['university_name', 'passed_year', 'level', 'division', 'percentage'];

        $this->syncRows($user->qualifications(), $request, 'qualifications', $fields,
            function (ApplicantQualification $qualification, array $row, int|string $index) use ($request) {
                $qualification->media()
                    ->where('collection_name', ApplicantQualification::DOCUMENTS)
                    ->whereIn('id', $row['remove_documents'] ?? [])
                    ->get()
                    ->each->delete();

                foreach ($request->file("qualifications.$index.documents", []) as $file) {
                    $qualification->addMedia($file)->toMediaCollection(ApplicantQualification::DOCUMENTS);
                }
            },
            fn (int|string $index) => $request->hasFile("qualifications.$index.documents"),
        );

        return redirect()->route('applicant.trainings')
            ->with('success', __('Qualifications saved.'));
    }

    public function trainings()
    {
        $user = $this->user();
        $saved = $user->trainings()->with('media')->get();

        return view('admin.pages.applicant.trainings', [
            'step' => 'trainings',
            'progress' => $user->applicationProgress(),
            'rows' => $this->rows('trainings', $saved, ['training_name', 'certificate_number', 'organization', 'issue_date_ad', 'issue_date_bs']),
            'saved' => $saved->keyBy('id'),
        ]);
    }

    public function saveTrainings(TrainingsRequest $request)
    {
        $user = $this->user();
        $fields = ['training_name', 'certificate_number', 'organization', 'issue_date_ad', 'issue_date_bs'];

        $this->syncRows($user->trainings(), $request, 'trainings', $fields,
            function (ApplicantTraining $training, array $row, int|string $index) use ($request) {
                if ($request->hasFile("trainings.$index.certificate")) {
                    $training->addMediaFromRequest("trainings.$index.certificate")
                        ->toMediaCollection(ApplicantTraining::CERTIFICATE);
                } elseif (! empty($row['remove_certificate'])) {
                    $training->clearMediaCollection(ApplicantTraining::CERTIFICATE);
                }
            },
            fn (int|string $index) => $request->hasFile("trainings.$index.certificate"),
            function (array $data) {
                [$data['issue_date_ad'], $data['issue_date_bs']] =
                    NepaliDate::reconcile($data['issue_date_ad'] ?? null, $data['issue_date_bs'] ?? null);

                return $data;
            },
        );

        return redirect()->route('dashboard')
            ->with('success', __('Your application details have been saved.'));
    }

    private function user(): User
    {
        /** @var User */
        return Auth::user();
    }


    private function municipalitiesOf($districtId): Collection
    {
        return $districtId
            ? Municipality::where('district_id', $districtId)->orderBy('name_en')->get(['id', 'name_en', 'name_np'])
            : collect();
    }

    private function wardsOf($municipalityId): Collection
    {
        return $municipalityId
            ? Ward::where('municipality_id', $municipalityId)->orderBy('ward_number')->get(['id', 'ward_number'])
            : collect();
    }

    /**
     * Rows for a repeatable section: the submitted input after a failed validation,
     * otherwise the saved records, otherwise one empty row to start with.
     */
    private function rows(string $key, Collection $saved, array $fields): array
    {
        if (is_array(old($key))) {
            return old($key);
        }

        $rows = $saved->map(fn ($model) => ['id' => $model->id] + $model->only($fields))->all();

        return $rows ?: [array_fill_keys(['id', ...$fields], null)];
    }

    /**
     * Saves the submitted rows of a repeatable section: updates rows that carry one of the
     * user's ids, creates new ones, and deletes saved rows that were removed from the form.
     * Blank new rows (no text, no file) are skipped.
     */
    private function syncRows(
        HasMany $relation,
        FormRequest $request,
        string $key,
        array $fields,
        callable $afterSave,
        callable $hasUpload,
        ?callable $prepare = null,
    ): void {
        $existing = $relation->get()->keyBy('id');
        $kept = [];

        foreach ((array) $request->validated($key, []) as $index => $row) {
            $data = Arr::only($row, $fields);
            if ($prepare) {
                $data = $prepare($data);
            }

            $model = isset($row['id']) ? $existing->get((int) $row['id']) : null;

            if (! $model) {
                if (! array_filter($data, 'filled') && ! $hasUpload($index)) {
                    continue;
                }
                $model = $relation->make();
            }

            $model->fill($data)->save();
            $afterSave($model, $row, $index);
            $kept[] = $model->id;
        }

        // Deleting the model also deletes its uploaded files
        $existing->except($kept)->each->delete();
    }
}
