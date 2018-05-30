<?php

namespace Kinko\Database;

abstract class NonRelationalGrammar
{
    // TODO this may not be necessary for mongo...
    /**
     * Get the format for database stored dates.
     *
     * @return string
     */
    public function getDateFormat()
    {
        return 'Y-m-d H:i:s';
    }
}
