<?php

namespace Tests\Feature;

use App\Enums\RoleEnum;
use App\Models\ApplicantProfile;
use App\Models\ApplicantQualification;
use App\Models\ApplicantTraining;
use App\Models\Municipality;
use App\Models\Role;
use App\Models\User;
use App\Models\Ward;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ApplicantApplicationTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake(config('media-library.disk_name'));
    }

    private function applicant(): User
    {
        $user = User::create([
            'email' => 'applicant' . uniqid() . '@example.com',
            'password' => bcrypt('secret123'),
            'status' => 'active',
        ]);
        $user->roles()->attach(Role::where('name', RoleEnum::APPLICANT)->value('id'));

        return $user;
    }

    /** A ward with its municipality and district, from the seeded location data. */
    private function location(): Ward
    {
        return Ward::with('municipality')->firstOrFail();
    }

    public function test_step_pages_render(): void
    {
        $this->actingAs($this->applicant());

        $this->get(route('applicant.personal'))->assertOk()->assertSee(__('Date of birth'));
        $this->get(route('applicant.qualifications'))->assertOk()->assertSee(__('Add qualification'));
        $this->get(route('applicant.trainings'))->assertOk()->assertSee(__('Add training'));
        $this->get(route('dashboard'))->assertOk()->assertSee(__('Start application'));
    }

    public function test_non_applicants_are_sent_to_the_dashboard(): void
    {
        $user = User::create(['email' => 'norole' . uniqid() . '@example.com', 'password' => bcrypt('x'), 'status' => 'active']);

        $this->actingAs($user)->get(route('applicant.personal'))->assertRedirect(route('dashboard'));
    }

    public function test_personal_details_save_with_bs_birth_date_and_files(): void
    {
        $user = $this->applicant();
        $ward = $this->location();

        $this->actingAs($user)->post(route('applicant.personal.save'), [
            'name' => 'Ram Bahadur',
            'name_np' => 'राम बहादुर',
            'district_id' => $ward->municipality->district_id,
            'municipality_id' => $ward->municipality_id,
            'ward_id' => $ward->id,
            'tole' => 'Baneshwor',
            'dob_bs' => '२०४७-०२-०१',
            'gender' => 'male',
            'photo' => UploadedFile::fake()->image('photo.jpg'),
            'citizenship_front' => UploadedFile::fake()->create('front.pdf', 100, 'application/pdf'),
        ])->assertSessionHasNoErrors()->assertRedirect(route('applicant.qualifications'));

        $profile = $user->applicantProfile()->firstOrFail();
        $this->assertSame('2047-02-01', $profile->dob_bs);
        $this->assertSame('1990-05-15', $profile->dob_ad);
        $this->assertSame($ward->id, $profile->ward_id);
        $this->assertNull($profile->citizenship_number);
        $this->assertNotNull($profile->getFirstMedia(ApplicantProfile::PHOTO));
        $this->assertNotNull($profile->getFirstMedia(ApplicantProfile::CITIZENSHIP_FRONT));
        $this->assertNull($profile->getFirstMedia(ApplicantProfile::CITIZENSHIP_BACK));

        // Everything is optional: an empty submit still saves
        $this->post(route('applicant.personal.save'), [])->assertSessionHasNoErrors();
    }

    public function test_personal_details_reject_mismatched_location_and_dates(): void
    {
        $ward = $this->location();
        $otherMunicipality = Municipality::where('district_id', '!=', $ward->municipality->district_id)->firstOrFail();

        $this->actingAs($this->applicant())->post(route('applicant.personal.save'), [
            'district_id' => $ward->municipality->district_id,
            'municipality_id' => $otherMunicipality->id,
            'dob_ad' => '1990-05-15',
            'dob_bs' => '2047-02-05',
        ])->assertSessionHasErrors(['municipality_id', 'dob_bs']);

        $this->post(route('applicant.personal.save'), ['dob_bs' => '2047-01-32'])
            ->assertSessionHasErrors('dob_bs');
    }

    public function test_qualifications_are_created_updated_and_removed(): void
    {
        $user = $this->applicant();
        $this->actingAs($user);

        $this->post(route('applicant.qualifications.save'), [
            'qualifications' => [
                0 => ['university_name' => 'Tribhuvan University', 'level' => 'LLB', 'passed_year' => '2070', 'division' => 'First', 'percentage' => '65',
                    'documents' => [UploadedFile::fake()->create('a.pdf', 50, 'application/pdf'), UploadedFile::fake()->image('b.jpg')]],
                1 => ['university_name' => 'NEB', 'level' => '+2'],
                2 => ['university_name' => '', 'level' => ''], // blank row is skipped
            ],
        ])->assertSessionHasNoErrors()->assertRedirect(route('applicant.trainings'));

        $this->assertSame(2, $user->qualifications()->count());
        $llb = $user->qualifications()->where('level', 'LLB')->firstOrFail();
        $documents = $llb->getMedia(ApplicantQualification::DOCUMENTS);
        $this->assertCount(2, $documents);

        // Keep LLB (drop one document, add one), drop the +2 row
        $this->post(route('applicant.qualifications.save'), [
            'qualifications' => [
                0 => ['id' => $llb->id, 'university_name' => 'TU', 'level' => 'LLB',
                    'remove_documents' => [$documents[0]->id],
                    'documents' => [UploadedFile::fake()->create('c.pdf', 50, 'application/pdf')]],
            ],
        ])->assertSessionHasNoErrors();

        $this->assertSame(1, $user->qualifications()->count());
        $this->assertSame('TU', $llb->fresh()->university_name);
        $this->assertCount(2, $llb->fresh()->getMedia(ApplicantQualification::DOCUMENTS));
    }

    public function test_another_users_qualification_id_cannot_be_edited(): void
    {
        $other = $this->applicant();
        $theirs = $other->qualifications()->create(['university_name' => 'Theirs']);

        $this->actingAs($this->applicant())->post(route('applicant.qualifications.save'), [
            'qualifications' => [['id' => $theirs->id, 'university_name' => 'Hijacked']],
        ]);

        $this->assertSame('Theirs', $theirs->fresh()->university_name);
    }

    public function test_trainings_save_with_ad_date_and_certificate(): void
    {
        $user = $this->applicant();

        $this->actingAs($user)->post(route('applicant.trainings.save'), [
            'trainings' => [
                ['training_name' => 'Notary orientation', 'organization' => 'NPC', 'certificate_number' => 'C-101',
                    'issue_date_ad' => '2024-04-13', 'certificate' => UploadedFile::fake()->image('cert.png')],
            ],
        ])->assertSessionHasNoErrors()->assertRedirect(route('dashboard'));

        $training = $user->trainings()->firstOrFail();
        $this->assertSame('2081-01-01', $training->issue_date_bs);
        $this->assertNotNull($training->getFirstMedia(ApplicantTraining::CERTIFICATE));

        $this->post(route('applicant.trainings.save'), [
            'trainings' => [['id' => $training->id, 'training_name' => 'Notary orientation', 'remove_certificate' => '1']],
        ])->assertSessionHasNoErrors();
        $this->assertNull($training->fresh()->getFirstMedia(ApplicantTraining::CERTIFICATE));
    }

    public function test_uploaded_files_are_only_served_to_their_owner(): void
    {
        $owner = $this->applicant();
        $this->actingAs($owner)->post(route('applicant.personal.save'), [
            'photo' => UploadedFile::fake()->image('photo.jpg'),
        ]);
        $media = $owner->applicantProfile->getFirstMedia(ApplicantProfile::PHOTO);

        $this->get(route('lookup.media', $media))->assertOk();
        $this->actingAs($this->applicant())->get(route('lookup.media', $media))->assertForbidden();
    }

    public function test_lookup_endpoints(): void
    {
        $ward = $this->location();
        $this->actingAs($this->applicant());

        $this->getJson(route('lookup.municipalities', $ward->municipality->district_id))
            ->assertOk()->assertJsonFragment(['id' => $ward->municipality_id]);
        $this->getJson(route('lookup.wards', $ward->municipality_id))
            ->assertOk()->assertJsonFragment(['id' => $ward->id, 'name' => (string) $ward->ward_number]);

        $this->getJson(route('lookup.date.convert', ['ad' => '1990-05-15']))->assertExactJson(['ad' => '1990-05-15', 'bs' => '2047-02-01']);
        $this->getJson(route('lookup.date.convert', ['bs' => '2047-02-01']))->assertExactJson(['ad' => '1990-05-15', 'bs' => '2047-02-01']);
        $this->getJson(route('lookup.date.convert', ['bs' => '2047-01-32']))->assertStatus(422);
    }
}
