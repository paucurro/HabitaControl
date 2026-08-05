<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Fortify\Events\TwoFactorAuthenticationChallenged;
use Laravel\Socialite\AbstractUser;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Throwable;

class SocialAuthenticationController extends Controller
{
    /** @var list<string> */
    private const PROVIDERS = ['google', 'apple'];

    public function redirect(string $provider): RedirectResponse
    {
        abort_unless(in_array($provider, self::PROVIDERS, true), 404);

        return Socialite::driver($provider)->redirect();
    }

    public function callback(Request $request, string $provider): RedirectResponse
    {
        abort_unless(in_array($provider, self::PROVIDERS, true), 404);

        try {
            $socialiteUser = Socialite::driver($provider)->user();
            $user = $this->resolveUser($provider, $socialiteUser);
        } catch (Throwable $exception) {
            report($exception);

            return redirect()->route('login')->withErrors([
                'social' => 'No se ha podido completar el acceso con '.ucfirst($provider).'. Inténtalo de nuevo.',
            ]);
        }

        if ($user->hasEnabledTwoFactorAuthentication()) {
            $request->session()->put([
                'login.id' => $user->getKey(),
                'login.remember' => false,
            ]);

            TwoFactorAuthenticationChallenged::dispatch($user);

            return redirect()->route('two-factor.login');
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    private function resolveUser(string $provider, SocialiteUser $socialiteUser): User
    {
        $providerUserId = trim((string) $socialiteUser->getId());
        $email = Str::lower(trim((string) $socialiteUser->getEmail()));

        if ($providerUserId === '' || $email === '' || ! $this->hasVerifiedEmail($socialiteUser)) {
            throw new \UnexpectedValueException('The social account did not provide a verified email address.');
        }

        return DB::transaction(function () use ($provider, $providerUserId, $email, $socialiteUser): User {
            $socialAccount = SocialAccount::query()
                ->with('user')
                ->where('provider', $provider)
                ->where('provider_user_id', $providerUserId)
                ->first();

            if ($socialAccount !== null) {
                return $socialAccount->user;
            }

            $user = User::query()->where('email', $email)->first();

            if ($user === null) {
                $user = User::query()->create([
                    'name' => $this->socialName($socialiteUser, $email),
                    'email' => $email,
                    'password' => Str::password(40),
                ]);
                $user->forceFill(['email_verified_at' => now()])->save();
            } elseif ($user->email_verified_at === null) {
                $user->forceFill(['email_verified_at' => now()])->save();
            }

            $user->socialAccounts()->create([
                'provider' => $provider,
                'provider_user_id' => $providerUserId,
            ]);

            return $user;
        });
    }

    private function hasVerifiedEmail(SocialiteUser $socialiteUser): bool
    {
        if (! $socialiteUser instanceof AbstractUser) {
            return false;
        }

        $rawUser = $socialiteUser->getRaw();
        $verified = $rawUser['verified_email'] ?? $rawUser['email_verified'] ?? false;

        return filter_var($verified, FILTER_VALIDATE_BOOL);
    }

    private function socialName(SocialiteUser $socialiteUser, string $email): string
    {
        $name = trim((string) $socialiteUser->getName());

        return $name !== '' ? $name : Str::before($email, '@');
    }
}
