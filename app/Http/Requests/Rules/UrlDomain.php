<?php

namespace Kinko\Http\Requests\Rules;

use Illuminate\Contracts\Validation\Rule;

class UrlDomain implements Rule
{
    protected $request;

    protected $field;

    public function __construct($request, $field)
    {
        $this->request = $request;
        $this->field = $field;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $parts = parse_url($value);

        if (!isset($parts['host'])) {
            return false;
        }

        $urls = $this->request->input($this->field);

        if (!is_array($urls)) {
            $urls = [$urls];
        }

        foreach ($urls as $url) {
            $urlParts = parse_url($url);

            if (!isset($urlParts['host']) || $parts['host'] !== $urlParts['host']) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return "The :attribute field must have the same domain as {$this->field}.";
    }
}
