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
        return isset($parts['host']) && $parts['host'] === $this->request->input($this->field);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return ":attribute must have the same domain as {$this->field}.";
    }
}
