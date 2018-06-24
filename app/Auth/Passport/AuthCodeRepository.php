<?php

namespace Kinko\Auth\Passport;

use Kinko\Support\Facades\MongoDB;
use Kinko\Models\Passport\AuthCode;
use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use Laravel\Passport\Bridge\AuthCodeRepository as BaseAuthCodeRepository;
use League\OAuth2\Server\Exception\UniqueTokenIdentifierConstraintViolationException;

class AuthCodeRepository extends BaseAuthCodeRepository
{
    public function persistNewAuthCode(AuthCodeEntityInterface $authCodeEntity)
    {
        $codeId = $authCodeEntity->getIdentifier();

        if (AuthCode::where('id', $codeId)->count() !== 0) {
            throw new UniqueTokenIdentifierConstraintViolationException;
        }

        AuthCode::create([
            'id' => $codeId,
            'user_id' => MongoDB::key($authCodeEntity->getUserIdentifier()),
            'client_id' => MongoDB::key($authCodeEntity->getClient()->getIdentifier()),
            'scopes' => $this->formatScopesForStorage($authCodeEntity->getScopes()),
            'revoked' => false,
            'expires_at' => $authCodeEntity->getExpiryDateTime(),
        ]);
    }

    public function revokeAuthCode($codeId)
    {
        AuthCode::where('id', $codeId)->update(['revoked' => true]);
    }

    public function isAuthCodeRevoked($codeId)
    {
        return AuthCode::where('id', $codeId)->where('revoked', true)->count() > 0;
    }
}
