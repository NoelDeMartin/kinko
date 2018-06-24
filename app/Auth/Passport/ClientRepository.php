<?php

namespace Kinko\Auth\Passport;

use Kinko\Models\Passport\Client;
use Laravel\Passport\ClientRepository as BaseClientRepository;

class ClientRepository extends BaseClientRepository
{
    public function find($id)
    {
        return Client::find($id);
    }
}
