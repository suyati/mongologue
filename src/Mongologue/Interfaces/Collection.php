<?php
/**
 * File Containing the Collection Interface
 *
 * @category Mongologue
 * @package  Interface
 * @author   @kpnunni <krishnanunni@suyati.com>
 * @license  none http://suyati.com
 * @link     http://github.com/xait/docxwriter
 */
namespace Mongologue\Interfaces;

use \Mongologue\Core\Collections;

/**
 * Interface for Collections
 *
 * @category Mongologue
 * @package  Interface
 * @author   @kpnunni <krishnanunni@suyati.com>
 * @license  none http://suyati.com
 * @link     http://github.com/xait/docxwriter
 */
interface Collection
{
    /**
     * Constructor function
     *
     * @param MongoColleciton $mongoCollection Mongo Collection Object
     * @param Collections     $collections     Group of Collecitons
     */
    public function __construct(\MongoCollection $mongoCollection, Collections $collections);

    /**
     * Execute a Callable command on the colleciton
     * 
     * @param mixed $callable Callable command
     * @param array $params   Parameters for the Command
     * 
     * @return mixed Result of execution
     */
    public function execute($callable, array $params);
}