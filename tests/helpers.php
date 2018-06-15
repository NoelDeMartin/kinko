<?php

use Illuminate\Support\Collection;
use Kinko\Database\Soukai\NonRelationalModel;

if (!function_exists('stubs_path')) {
    /**
     * Resolve stub file path.
     *
     * @param  string  $path
     * @return string
     */
    function stubs_path($path)
    {
        return base_path('tests/stubs/' . $path);
    }
}

if (!function_exists('load_stub')) {
    /**
     * Load stub object.
     *
     * @param  string  $path
     * @return string
     */
    function load_stub($path)
    {
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        switch ($extension) {
            case 'json':
                return json_decode(file_get_contents(stubs_path($path)), true);
                break;
        }
    }
}
