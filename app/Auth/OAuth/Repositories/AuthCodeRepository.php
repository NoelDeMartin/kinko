<?php

namespace Kinko\Auth\OAuth\Repositories;

use Kinko\Models\AuthCode;
use Kinko\Support\Facades\MongoDB;
use Kinko\Auth\OAuth\Entities\AuthCode as AuthCodeEntity;
use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;
use Kinko\Auth\OAuth\Repositories\Concerns\FormatsScopesForStorage;
use League\OAuth2\Server\Exception\UniqueTokenIdentifierConstraintViolationException;

class AuthCodeRepository implements AuthCodeRepositoryInterface
{
    use FormatsScopesForStorage;

    public function getNewAuthCode()
    {
        return new AuthCodeEntity;
    }

    public function persistNewAuthCode(AuthCodeEntityInterface $authCodeEntity)
    {
        $codeId = $authCodeEntity->getIdentifier();

        if (AuthCode::where('id', $codeId)->count() !== 0) {
            throw new UniqueTokenIdentifierConstraintViolationException();
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
