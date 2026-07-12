<?php

if (! function_exists('formatBytes')) {
    function formatBytes(string $value)
    {
        if (is_numeric($value)) {
            $bytes = (float) $value;
        } else {
            $unit = strtolower(substr($value, -1));
            $number = (float) $value;

            switch ($unit) {
                case 'g':
                    return $number.'GB';
                case 'm':
                    return $number.'MB';
                case 'k':
                    return $number.'KB';
                default:
                    return $number.'B';
            }
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2).$units[$i];
    }
}
