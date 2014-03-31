<?php
/**
 * File Conatining the COllections Class
 *
 * @category Mongologue
 * @package  Core
 * @author   @kpnunni <krishnanunni@suyati.com>
 * @license  none http://github.com/suyati/mongologue
 * @link     http://github.com/suyati/mongologue
 */
namespace Mongologue\Core;

use \Mongologue\Interfaces\Collection;
use \Mongologue\Exception;

/**
 * Class Conatining a Group of Collections
 *
 * @category Mongologue
 * @package  Core
 * @author   @kpnunni <krishnanunni@suyati.com>
 * @license  none http://github.com/suyati/mongologue
 * @link     http://github.com/suyati/mongologue
 */
class Collections
{
    private $_collections = array();

    /**
     * Register a Collection
     * 
     * @param string     $type       Identifier for the Collection
     * @param Collection $collection Collection Object
     * 
     * @return void
     */
    public function registerCollection($type, Collection $collection)
    {
        $this->_collections[$type] = $collection;    
    }

    /**
     * Get a Collection for an Identifier
     * 
     * @param string $type Identifier for the Collection
     *
     * @throws Exception If A Collection is not registered to the identifier
     * @return Collection Collection Object for the Identifier
     */
    public function getCollectionFor($type)
    {
        if(isset($this->_collections[$type]))
            return $this->_collections[$type];

        throw new Exception("Cannot Find Collection for $type; Call registerCollection");
        
    }
}