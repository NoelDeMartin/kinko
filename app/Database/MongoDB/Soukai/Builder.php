<?php

namespace Kinko\Database\MongoDB\Soukai;

use Kinko\Support\Facades\MongoDB;
use Kinko\Database\Soukai\NonRelationalBuilder;

class Builder extends NonRelationalBuilder
{
    public function find($id, $columns = ['*'])
    {
        return parent::find(MongoDB::key($id), $columns);
    }
}
