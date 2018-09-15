<?php

namespace Kinko\Auth\OAuth\Repositories\Concerns;

trait FormatsScopesForStorage
{
    public function formatScopesForStorage(array $scopes)
    {
        return json_encode($this->scopesToArray($scopes));
    }

    public function scopesToArray(array $scopes)
    {
        return array_map(function ($scope) {
            return $scope->getIdentifier();
        }, $scopes);
    }
}
