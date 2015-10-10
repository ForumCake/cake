<?php
namespace Cake;

abstract class ControllerHelper_Abstract extends \XenForo_ControllerHelper_Abstract
{

    use Trait_Core;
    
    /**
     * Standard approach to caching model objects for the lifetime of the controller.
     *
     * @var array
     */
    protected $_modelCache = array();

    /**
     * Gets the specified model object from the cache.
     * If it does not exist,
     * it will be instantiated.
     *
     * @param string $class
     *            Name of the class to load
     *            
     * @return XenForo_Model
     */
    public function getModelFromCache($class)
    {
        if ((strpos($class, '\\') === false && strpos($class, '_') === false) || (strlen($class) > 5 && substr($class, 0, 6) == 'Model_')) {
            $class = $this->_addNamespaceToClass($class);
        }
        
        if (! isset($this->_modelCache[$class])) {
            $this->_modelCache[$class] = \XenForo_Model::create($class);
        }
        
        return $this->_modelCache[$class];
    }
}