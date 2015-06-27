<?php
namespace Cake;

class Helper_Xml
{

    public static function appendXml(\SimpleXMLElement $element1, \SimpleXMLElement $element2)
    {
        if (strlen(trim((string) $element2)) == 0) {
            $xml = $element1->addChild($element2->getName());
        } else {
            $xml = $element1->addChild($element2->getName(), (string) $element2);
        }
        
        foreach ($element2->children() as $child) {
            self::appendXml($xml, $child);
        }
        
        foreach ($element2->attributes() as $n => $v) {
            $xml->addAttribute($n, $v);
        }
    }
}