<?php
/**
 * File Containing the Counters Collection
 *
 * @category Mongologue
 * @package  Collection
 * @author   @tkannippoyil <tkannippoyil@suyati.com>
 * @license  none http://suyati.com
 * @link     http://github.com/xait/docxwriter
 */
namespace Mongologue\Collection;

use \Mongologue\Interfaces\Collection;
use \Mongologue\Models;
use \Mongologue\Core\Collections;

/**
 * Class Managing Collection of Counters
 *
 * @category Mongologue
 * @package  Collection
 * @author   @tkannippoyil <tkannippoyil@suyati.com>
 * @license  none http://suyati.com
 * @link     http://github.com/xait/docxwriter
 */
class Counter implements Collection
{
    private $_collection;
    private $_collections;

    /**
     * Constructor function
     *
     * @param MongoColleciton $mongoCollection Mongo Collection Object
     * @param Collections     $collections     Counter of Collecitons
     */
    public function __construct(\MongoCollection $mongoCollection, Collections $collections)
    {
        $this->_collections = $collections; 
        $this->_collection = $mongoCollection;
    }

    /**
     * Execute a Command and return the Results
     * 
     * @param string $callable A function of the instance
     * @param array  $params   Parameters to be passed to the instance
     * 
     * @return mixed Result of the Funciton
     */
    public function execute($callable, array $params)
    {
        return call_user_func_array(array($this, $callable), $params);
    }

    /**
     * Get the Next Counter Id
     * 
     * @return integer The Id for the Counter Id next in Sequence
     */
    private function _getNextCounterId()
    {
        $count = $counters->findAndModify(
            array("id"=>"counters"),
            array('$inc'=>array("s"=>1)),
            null,
            array("upsert"=>true, "new"=>true)
        );
        return $count["s"];
    }
}