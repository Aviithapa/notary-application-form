<?php

namespace Tests\Feature;

use App\Enums\RoleEnum;
use App\Enums\UserStatusEnum;
use App\Mail\OtpMail;
use App\Models\Role;
use App\Models\User;
use App\Support\EmailOtp;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class RegistrationAndPasswordResetTest extends TestCase
{
    use DatabaseTransactions;

    private const EMAIL = 'applicant.test@example.com';

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        $this->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class);
    }

    private function queuedOtp(string $purpose): string
    {
        $otp = null;
        Mail::assertQueued(OtpMail::class, function (OtpMail $mail) use ($purpose, &$otp) {
            if ($mail->hasTo(self::EMAIL) && $mail->purpose === $purpose) {
                $otp = $mail->otp;
            }

            return $otp !== null;
        });

        return $otp;
    }

    public function test_registration_queues_otp_and_verifying_logs_in_as_applicant(): void
    {
        $this->post(route('register.store'), [
            'email' => self::EMAIL,
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ])->assertRedirect(route('register.verify'));

        $user = User::where('email', self::EMAIL)->firstOrFail();
        $this->assertSame(UserStatusEnum::INACTIVE->value, $user->status);
        $this->assertSame(RoleEnum::APPLICANT, $user->mainRole()->name);
        $this->assertNull($user->reference);
        $this->assertGuest();

        $otp = $this->queuedOtp(EmailOtp::PURPOSE_REGISTER);
        $wrong = $otp === '111111' ? '222222' : '111111';

        $this->post(route('register.verify.submit'), ['otp' => $wrong])->assertSessionHasErrors('otp');
        $this->assertGuest();

        $this->post(route('register.verify.submit'), ['otp' => $otp])->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
        $this->assertSame(UserStatusEnum::ACTIVE->value, $user->fresh()->status);
        $this->assertNotNull($user->fresh()->email_verified_at);

        $this->get(route('dashboard'))->assertOk()->assertSee(self::EMAIL);
    }

    public function test_registration_validates_password_confirmation_and_unique_email(): void
    {
        $this->post(route('register.store'), [
            'email' => self::EMAIL,
            'password' => 'secret123',
            'password_confirmation' => 'different1',
        ])->assertSessionHasErrors('password');

        Mail::assertNothingQueued();
    }

    public function test_otp_is_invalidated_after_too_many_wrong_attempts(): void
    {
        $this->post(route('register.store'), [
            'email' => self::EMAIL,
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);
        $otp = $this->queuedOtp(EmailOtp::PURPOSE_REGISTER);
        $wrong = $otp === '111111' ? '222222' : '111111';

        for ($i = 0; $i < EmailOtp::MAX_ATTEMPTS; $i++) {
            $this->post(route('register.verify.submit'), ['otp' => $wrong]);
        }

        $this->post(route('register.verify.submit'), ['otp' => $otp])->assertSessionHasErrors('otp');
        $this->assertGuest();
    }

    public function test_unverified_applicant_logging_in_is_sent_to_verification(): void
    {
        $user = User::create([
            'email' => self::EMAIL,
            'password' => Hash::make('secret123'),
            'status' => UserStatusEnum::INACTIVE->value,
        ]);
        $user->roles()->attach(Role::where('name', RoleEnum::APPLICANT)->firstOrFail());

        $this->post(route('login'), ['email' => self::EMAIL, 'password' => 'secret123'])
            ->assertRedirect(route('register.verify'));
        $this->assertGuest();

        $this->post(route('register.verify.submit'), ['otp' => $this->queuedOtp(EmailOtp::PURPOSE_REGISTER)])
            ->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_forgot_password_resets_password_with_otp(): void
    {
        $user = User::create([
            'email' => self::EMAIL,
            'password' => Hash::make('oldpass123'),
            'status' => UserStatusEnum::ACTIVE->value,
        ]);
        $user->roles()->attach(Role::where('name', RoleEnum::APPLICANT)->firstOrFail());

        $this->get(route('password.reset'))->assertRedirect(route('password.request'));

        $this->post(route('password.email'), ['email' => self::EMAIL])->assertRedirect(route('password.verify'));
        $otp = $this->queuedOtp(EmailOtp::PURPOSE_RESET);

        $this->post(route('password.verify.submit'), ['otp' => $otp])->assertRedirect(route('password.reset'));

        $this->post(route('password.store'), [
            'password' => 'newpass123',
            'password_confirmation' => 'newpass123',
        ])->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('newpass123', $user->fresh()->password));

        // the verified session is single use
        $this->get(route('password.reset'))->assertRedirect(route('password.request'));

        $this->post(route('login'), ['email' => self::EMAIL, 'password' => 'newpass123'])
            ->assertRedirect(route('dashboard'));
    }

    public function test_forgot_password_for_unknown_email_sends_nothing_but_looks_the_same(): void
    {
        $this->post(route('password.email'), ['email' => 'nobody@example.com'])
            ->assertRedirect(route('password.verify'));

        Mail::assertNothingQueued();
    }

    public function test_auth_pages_render(): void
    {
        $this->get(route('register'))->assertOk()->assertSee(__('Create an account'));
        $this->get(route('password.request'))->assertOk();
        $this->get('/login')->assertOk()->assertSee(route('register'))->assertSee(route('password.request'));
        $this->get(route('register.verify'))->assertRedirect(route('register'));
    }
}
