<?php

namespace Kinko\Database\Schema\Grammars;

use Illuminate\Database\Schema\Grammars\Grammar;

abstract class NonRelationalGrammar extends Grammar
{
    protected $transactions = false;

    /**
     * Check if this Grammar supports schema changes wrapped in a transaction.
     *
     * @return bool
     */
    public function supportsSchemaTransactions()
    {
        return $this->transactions;
    }
}
