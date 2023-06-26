<?php

declare(strict_types=1);

namespace Xgp\App\Libraries;

class Page
{
    /**
     * Removes speacial chars like tabs, new lines and carriage return.
     */
    public static function jsReady(string $template = ''): string
    {
        $output = str_replace(["\r\n", "\r"], "\n", $template);
        $lines = explode("\n", $output);
        $new_lines = [];

        foreach ($lines as $i => $line) {
            if (!empty($line)) {
                $new_lines[] = trim($line);
            }
        }

        return join($new_lines);
    }
}
