<?php

namespace Kinko\Auth\OAuth\Repositories;

use Kinko\Models\AccessToken;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use Kinko\Auth\OAuth\Entities\AccessToken as AccessTokenEntity;
use Kinko\Auth\OAuth\Repositories\Concerns\FormatsScopesForStorage;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;

class AccessTokenRepository implements AccessTokenRepositoryInterface
{
    use FormatsScopesForStorage;

    public function getNewToken(ClientEntityInterface $clientEntity, array $scopes, $userIdentifier = null)
    {
        return new AccessTokenEntity($userIdentifier, $scopes);
    }

    public function persistNewAccessToken(AccessTokenEntityInterface $accessTokenEntity)
    {
        AccessToken::create([
            'id' => $accessTokenEntity->getIdentifier(),
            'user_id' => $accessTokenEntity->getUserIdentifier(),
            'client_id' => $accessTokenEntity->getClient()->getIdentifier(),
            'scopes' => $this->scopesToArray($accessTokenEntity->getScopes()),
            'revoked' => false,
            'expires_at' => $accessTokenEntity->getExpiryDateTime(),
        ]);
    }

    public function revokeAccessToken($tokenId)
    {
        AccessToken::where('id', $tokenId)->update(['revoked' => true]);
    }

    public function isAccessTokenRevoked($tokenId)
    {
        return AccessToken::where('id', $tokenId)->where('revoked', true)->count() > 0;
    }
}
