<?php

namespace Kinko\Exceptions;

use Exception;

class OAuthError extends Exception
{
    protected $error;

    public function __construct($error, $message = null)
    {
        parent::__construct($message);

        $this->error = $error;
    }

    public function render($request)
    {
        $data = ['error' => $this->error];

        if (!is_null($this->message)) {
            $data['error_description'] = $this->message;
        }

        return response()
                ->json($data)
                ->setStatusCode(400);
    }
}
