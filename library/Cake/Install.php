<?php
namespace Cake;

class Install
{

    /**
     * Standard approach to caching other model objects for the lifetime of the
     * model.
     *
     * @var array
     */
    protected $_modelCache = array();

    /**
     * Gets the specified model object from the cache.
     * If it does not exist, it will be instantiated.
     *
     * @param string $class Name of the class to load
     *
     * @return XenForo_Model
     */
    public function getModelFromCache($class)
    {
        if (!isset($this->_modelCache[$class])) {
            $this->_modelCache[$class] = \XenForo_Model::create($class);
        }

        return $this->_modelCache[$class];
    }
}