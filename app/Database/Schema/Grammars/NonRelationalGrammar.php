<?php

namespace Kinko\Database\Schema\Grammars;

use Kinko\Database\NonRelationalGrammar as BaseGrammar;

abstract class NonRelationalGrammar extends BaseGrammar
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
