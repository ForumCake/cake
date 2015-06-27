<?php
namespace Cake;

class Helper_String
{

    public static function pascalCaseToCamelCase($pascalCase)
    {
        $parts = explode('_', $pascalCase);
        foreach ($parts as &$part) {
            $part = lcfirst($part);
        }
        
        return implode('_', $parts);
    }

    public static function camelCaseToSnakeCase($camelCase)
    {
        preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $camelCase, $matches);
        $snakeCase = $matches[0];
        foreach ($snakeCase as &$match) {
            $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
        }
        return implode('_', $snakeCase);
    }

    public static function snakeCaseToCamelCase($snakeCase, $lcFirst = false)
    {
        $snakeCase = str_replace(' ', '', ucwords(str_replace('_', ' ', $snakeCase)));
        
        $snakeCase = lcfirst($snakeCase);
        
        return $snakeCase;
    }
}