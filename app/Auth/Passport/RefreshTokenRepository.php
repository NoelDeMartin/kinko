<?php

namespace Kinko\Auth\Passport;

use Kinko\Models\Passport\RefreshToken;
use Laravel\Passport\Events\RefreshTokenCreated;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use Laravel\Passport\Bridge\RefreshTokenRepository as BaseRefreshTokenRepository;

class RefreshTokenRepository extends BaseRefreshTokenRepository
{
    public function persistNewRefreshToken(RefreshTokenEntityInterface $refreshTokenEntity)
    {
        $id = $refreshTokenEntity->getIdentifier();
        $accessTokenId = $refreshTokenEntity->getAccessToken()->getIdentifier();

        RefreshToken::create([
            'id' => $id,
            'access_token_id' => $accessTokenId,
            'revoked' => false,
            'expires_at' => $refreshTokenEntity->getExpiryDateTime(),
        ]);

        $this->events->fire(new RefreshTokenCreated($id, $accessTokenId));
    }

    public function revokeRefreshToken($tokenId)
    {
        RefreshToken::where('id', $tokenId)->update(['revoked' => true]);
    }

    public function isRefreshTotruekenRevoked($tokenId)
    {
        $refreshToken = RefreshToken::where('id', $tokenId)->first();

        if (is_null($refreshToken) || $refreshToken->revoked) {
            return true;
        }

        return $this->tokens->isAccessTokenRevoked(
            $refreshToken->access_token_id
        );
    }
}
