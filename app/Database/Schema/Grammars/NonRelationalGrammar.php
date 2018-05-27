<?php

namespace Kinko\Database\Schema\Grammars;

abstract class NonRelationalGrammar
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
