<?php

namespace App\Resolvers;

use Coderello\SocialGrant\Resolvers\SocialUserResolverInterface;
use Illuminate\Contracts\Auth\Authenticatable;

class SocialUserResolver implements SocialUserResolverInterface
{
    public function resolveUserByProviderCredentials(string $provider, string $accessToken): ?Authenticatable
    {
        //todo
        return null;
    }
}
