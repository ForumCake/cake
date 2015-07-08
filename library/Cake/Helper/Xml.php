<?php
namespace Cake;

class Helper_Xml
{

    public static function appendXml(\SimpleXMLElement $element1, \SimpleXMLElement $element2)
    {
        // TODO check for empty $element1 and skip if necessary
        
        $xml = $element1->addChild($element2->getName());
        
        if (strlen(trim((string) $element2)) == 0) {
            // do nothing
        } else {
            $node = dom_import_simplexml($xml);
            $document = $node->ownerDocument;
            $node->appendChild($document->createCDATASection($element2));
        }
        
        foreach ($element2->children() as $child) {
            self::appendXml($xml, $child);
        }
        
        foreach ($element2->attributes() as $n => $v) {
            $xml->addAttribute($n, $v);
        }
    }
}