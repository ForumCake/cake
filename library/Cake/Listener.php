<?php
namespace Cake;

class Listener
{

    public static function loadClass($class, array &$extend)
    {
        $calledClass = get_called_class();
        
        if (strpos($calledClass, '\Listener')) {
            $preClass = substr($calledClass, 0, -9);
            $extend[] = $preClass . '\\Proxy\\' . $class;
        }
    }
}