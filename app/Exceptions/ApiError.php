<?php

namespace Kinko\Exceptions;

use Exception;

class ApiError extends Exception
{
    protected $status;

    public function __construct($message = '', $status = 400)
    {
        parent::__construct($message);

        $this->status = $status;
    }

    public function render($request)
    {
        return response()
                ->json(['message' => $this->message])
                ->setStatusCode($this->status);
    }
}
