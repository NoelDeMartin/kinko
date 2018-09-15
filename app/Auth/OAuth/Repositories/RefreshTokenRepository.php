<?php

namespace Kinko\Auth\OAuth\Repositories;

use Kinko\Models\RefreshToken;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use Kinko\Auth\OAuth\Entities\RefreshToken as RefreshTokenEntity;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;

class RefreshTokenRepository implements RefreshTokenRepositoryInterface
{
    public function getNewRefreshToken()
    {
        return new RefreshTokenEntity;
    }

    public function persistNewRefreshToken(RefreshTokenEntityInterface $refreshTokenEntity)
    {
        RefreshToken::create([
            'id' => $refreshTokenEntity->getIdentifier(),
            'access_token_id' => $refreshTokenEntity->getAccessToken()->getIdentifier(),
            'revoked' => false,
            'expires_at' => $refreshTokenEntity->getExpiryDateTime(),
        ]);
    }

    public function revokeRefreshToken($tokenId)
    {
        RefreshToken::where('id', $tokenId)->update(['revoked' => true]);
    }

    public function isRefreshTokenRevoked($tokenId)
    {
        return RefreshToken::where('id', $tokenId)->where('revoked', true)->count() > 0;
    }
}
