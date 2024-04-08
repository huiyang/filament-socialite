<?php

namespace DutchCodingCompany\FilamentSocialite\Traits;

use Closure;
use DutchCodingCompany\FilamentSocialite\FilamentSocialitePlugin;
use DutchCodingCompany\FilamentSocialite\Models\Contracts\FilamentSocialiteUser as FilamentSocialiteUserContract;
use Filament\Facades\Filament;
use Laravel\Socialite\Contracts\User as SocialiteUserContract;

trait Callbacks
{
    /**
     * @phpstan-var ?\Closure(string $provider, \Laravel\Socialite\Contracts\User $oauthUser, self $socialite): \Illuminate\Contracts\Auth\Authenticatable
     */
    protected ?Closure $createUserCallback = null;

    /**
     * @phpstan-var ?\Closure(string $provider, \DutchCodingCompany\FilamentSocialite\Models\Contracts\FilamentSocialiteUser $socialiteUser, \DutchCodingCompany\FilamentSocialite\FilamentSocialitePlugin $plugin): \Illuminate\Http\RedirectResponse
     */
    protected ?Closure $loginRedirectCallback = null;

    /**
     * @phpstan-var ?\Closure(string $provider, \Laravel\Socialite\Contracts\User $oauthUser, \DutchCodingCompany\FilamentSocialite\FilamentSocialitePlugin $plugin): ?(\Illuminate\Contracts\Auth\Authenticatable)
     */
    protected ?Closure $userResolver = null;

    /**
     * @param ?\Closure(string $provider, \Laravel\Socialite\Contracts\User $oauthUser, \DutchCodingCompany\FilamentSocialite\FilamentSocialitePlugin $plugin): \Illuminate\Contracts\Auth\Authenticatable $callback
     */
    public function createUserCallback(Closure $callback = null): static
    {
        $this->createUserCallback = $callback;

        return $this;
    }

    /**
     * @return \Closure(string $provider, \Laravel\Socialite\Contracts\User $oauthUser, \DutchCodingCompany\FilamentSocialite\FilamentSocialitePlugin $plugin): \Illuminate\Contracts\Auth\Authenticatable
     */
    public function getCreateUserCallback(): Closure
    {
        return $this->createUserCallback ?? function (
            string $provider,
            SocialiteUserContract $oauthUser,
            FilamentSocialitePlugin $plugin,
        ) {
            /**
             * @var \Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model&\Illuminate\Contracts\Auth\Authenticatable> $query
             */
            $query = (new $this->userModelClass())->query();

            return $query->create([
                'name' => $oauthUser->getName(),
                'email' => $oauthUser->getEmail(),
            ]);
        };
    }

    /**
     * @param \Closure(string $provider, \DutchCodingCompany\FilamentSocialite\Models\Contracts\FilamentSocialiteUser $socialiteUser, \DutchCodingCompany\FilamentSocialite\FilamentSocialitePlugin $plugin): \Illuminate\Http\RedirectResponse $callback
     */
    public function loginRedirectCallback(Closure $callback): static
    {
        $this->loginRedirectCallback = $callback;

        return $this;
    }

    /**
     * @return \Closure(string $provider, \DutchCodingCompany\FilamentSocialite\Models\Contracts\FilamentSocialiteUser $socialiteUser, \DutchCodingCompany\FilamentSocialite\FilamentSocialitePlugin $plugin): \Illuminate\Http\RedirectResponse
     */
    // @TODO redirectAfterLoginUsing
    public function getLoginRedirectCallback(): Closure
    {
        return $this->loginRedirectCallback ?? function (string $provider, FilamentSocialiteUserContract $socialiteUser, FilamentSocialitePlugin $plugin) {
            if (($panel = $this->getPanel())->hasTenancy()) {
                $tenant = Filament::getUserDefaultTenant($socialiteUser->getUser());

                if (is_null($tenant) && $tenantRegistrationUrl = $panel->getTenantRegistrationUrl()) {
                    return redirect()->intended($tenantRegistrationUrl);
                }

                return redirect()->intended(
                    $panel->getUrl($tenant)
                );
            }

            return redirect()->intended(
                $this->getPanel()->getUrl()
            );
        };
    }

    /**
     * @param \Closure(string $provider, \Laravel\Socialite\Contracts\User $oauthUser, \DutchCodingCompany\FilamentSocialite\FilamentSocialitePlugin $plugin): ?(\Illuminate\Contracts\Auth\Authenticatable) $callback
     */
    public function userResolver(Closure $callback = null): static
    {
        $this->userResolver = $callback;

        return $this;
    }

    /**
     * @return \Closure(string $provider, \Laravel\Socialite\Contracts\User $oauthUser, \DutchCodingCompany\FilamentSocialite\FilamentSocialitePlugin $plugin): ?(\Illuminate\Contracts\Auth\Authenticatable)
     */
    public function getUserResolver(): Closure
    {
        return $this->userResolver ?? function (string $provider, SocialiteUserContract $oauthUser, FilamentSocialitePlugin $plugin) {
            /** @var \Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model&\Illuminate\Contracts\Auth\Authenticatable> $model */
            $model = (new $this->userModelClass());

            return $model->where(
                'email',
                $oauthUser->getEmail()
            )->first();
        };
    }
}
