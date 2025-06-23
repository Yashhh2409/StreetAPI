<?php

if (!function_exists('addon_published_status')) {
    function addon_published_status($module_name)
    {
        $is_published = 0;

        try {
            $path = __DIR__ . "/../Modules/{$module_name}/Addon/info.php";

            if (!file_exists($path)) {
                return 0;
            }

            $full_data = include($path);

            if (isset($full_data['is_published']) && $full_data['is_published'] == 1) {
                $is_published = 1;
            }
        } catch (Exception $e) {
            return 0;
        }

        return $is_published;
    }
}
