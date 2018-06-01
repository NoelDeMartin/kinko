<?php

use Illuminate\Support\Collection;
use Kinko\Database\Soukai\NonRelationalModel;

if (!function_exists('resource')) {
    /**
     * Convert objects into api resources.
     *
     * @param  mixed  $models
     * @return mixed
     */
    function resource($result)
    {
        if (is_array($result) || $result instanceof Collection) {
            if (count($result) > 0 && $result[0] instanceof NonRelationalModel) {
                $resourceClass = $result[0]->getResourceClass();
                return $resourceClass::collection($result);
            } else {
                return $result;
            }
        } elseif ($result instanceof NonRelationalModel) {
            return $result->resource();
        } else {
            return $result;
        }
    }
}

if (!function_exists('app_laravel_data')) {
    /**
     * Prepare app laravel json.
     *
     * @return string
     */
    function app_laravel_data($serverSide = true)
    {
        $data = [ 'serverSide' => $serverSide ];

        if (auth()->check()) {
            $data['user'] = auth()->user()->resource();
        }

        return $data;
    }
}

if (!function_exists('render_vue')) {
    /**
     * Render vue application.
     *
     * @return string
     */
    function render_vue($path)
    {
        return shell_exec(implode(' ', [
            'node',
            realpath(base_path('scripts/vue-ssr/render.js')),
            $path,
            escapeshellarg(json_encode(app_laravel_data())),
            '2>&1'
        ]));
    }
}
