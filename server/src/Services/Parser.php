<?php

namespace PHPInternalsDocs\Services;

class Parser
{
    public static function parse($file, $model)
    {
        $class = "PHPInternalsDocs\\Models\\{$model}";
        $content = \file_get_contents($file);
        $previousHeading = '';
        $o = new $class();
        $ro = new \ReflectionObject($o);

        \preg_match_all('~\[\[\[.+?\]\]\]~', $content, $matches);

        if (!\count($matches[0])) {
            echo "ERROR: No headings found\n";
            die;
        }

        foreach ($matches[0] as $section) {
            $name = \trim($section, '[]');

            if (!$ro->hasProperty($name)) {
                echo "ERROR: A property with name '{$name}' does not exist on {$model}\n";
                die;
            }

            $parts = \explode($section, $content);

            if ($previousHeading) {
                $o->$previousHeading = \trim($parts[0]);
            }

            $content = $parts[1];
            $previousHeading = $name;
        }

        $o->$previousHeading = \trim($content);
        $o->format();

        return $o;
    }
}
