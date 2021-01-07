<?php

namespace App\Resolvers;

use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Support\Facades\Date;
use Coderello\SocialGrant\Resolvers\SocialUserResolverInterface;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use League\OAuth2\Server\Exception\OAuthServerException;

class SocialUserResolver implements SocialUserResolverInterface
{
    public function resolveUserByProviderCredentials(string $provider, string $accessToken): ?Authenticatable
    {
        $this->validateProviderName($provider);

        try {
            $providerUser = Socialite::driver($provider)->userFromToken($accessToken);
        } catch (\Throwable $exception) {
            // Send actual error message in development
            if (config('app.debug')) {
                throw $exception;
            }

            return null;
        }

        DB::beginTransaction();
        $user = $this->findOrCreateUser($provider, $providerUser);
        DB::commit();

        return $user;
    }

    /**
     * Make sure api client has provided a valid provider name.
     *
     * @param string $provider
     *
     * @throws OAuthServerException
     */
    protected function validateProviderName(string $provider)
    {
        if (!in_array(strtolower($provider), $this->getValidProviders(), true)) {
            throw new OAuthServerException(
                sprintf('%s provider is not supported.', ucfirst($provider)),
                Response::HTTP_BAD_REQUEST,
                'unsupported_social_provider',
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    protected function getValidProviders(): array
    {
        return ['github'];
    }

    /**
     * Create a user if does not exist.
     *
     * @param string $providerName
     * @param \Laravel\Socialite\Two\User $providerUser
     *
     * @return Authenticatable
     * @throws OAuthServerException
     */
    protected function findOrCreateUser(string $providerName, $providerUser): Authenticatable
    {
        /**
         * @var SocialAccount $socialAccount
         */
        $socialAccount = SocialAccount::query()->firstOrNew([
            'provider_user_id' => $providerUser->getId(),
            'provider' => $providerName,
        ]);

        /*
         * So, we found an social account and we are sure that
         * a user already exists against this account.
         */
        if ($socialAccount->exists) {
            return $socialAccount->user;
        }

        /*
         * We requires user email from social provider only during sign-up.
         * We can allow user to login even if provider did'nt send any email.
         */
        $this->ensureHasEmailAddress($providerName, $providerUser);

        /**
         * Lets try to find the user by email address send by provider.
         *
         * @var User $user
         */
        $user = User::query()->firstOrNew([
            'email' => $providerUser->getEmail(),
        ]);

        /*
         * User not found so lets persist it.
         */
        if (!$user->exists) {
            $user->fill([
                'name' => $providerUser->getName(),
                'password' => Hash::make(Str::random(30)),
                'email_verified_at' => Date::now(),
            ]);
            $user->save();

            event(new \Illuminate\Auth\Events\Registered($user));
        }

        /*
         * Associate the social account with this user.
         */
        $socialAccount->user()->associate($user);
        $socialAccount->save();

        return $user;
    }

    /**
     * Make sure provider sent an email address in response.
     *
     * @param string $providerName
     * @param \Laravel\Socialite\Two\User $providerUser
     *
     * @return void
     * @throws OAuthServerException
     */
    protected function ensureHasEmailAddress(string $providerName, $providerUser)
    {
        if (empty($providerUser->getEmail())) {
            throw new OAuthServerException(
                sprintf('Could not retrieve email address from %s.', Str::title($providerName)),
                Response::HTTP_BAD_REQUEST,
                'inaccessible_email_address',
                Response::HTTP_BAD_REQUEST
            );
        }
    }
}
