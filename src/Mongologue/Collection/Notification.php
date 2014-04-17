<?php
/**
 * File Containing the Notifications Collection
 *
 * @category Mongologue
 * @package  Collection
 * @author   @kpnunni <krishnanunni@suyati.com>
 * @license  none http://github.com/suyati/mongologue
 * @link     http://github.com/suyati/mongologue
 */
namespace Mongologue\Collection;

use \Mongologue\Interfaces\Collection;
use \Mongologue\Models;
use \Mongologue\Core\Collections;
use \Mongologue\Exceptions;

/**
 * Class Managing Collection of Notifications
 *
 * @category Mongologue
 * @package  Collection
 * @author   @kpnunni <krishnanunni@suyati.com>
 * @license  none http://github.com/suyati/mongologue
 * @link     http://github.com/suyati/mongologue
 */
class Notification implements Collection
{
    private $_collection;
    private $_collections;

    /**
     * Constructor function
     *
     * @param MongoColleciton $mongoCollection Mongo Collection Object
     * @param Collections     $collections     Notification of Collecitons
     */
    public function __construct(\MongoCollection $mongoCollection, Collections $collections)
    {
        $this->_collections = $collections;
        $this->_collection = $mongoCollection;
    }

    /**
     * Register a New Notification
     * 
     * @param Models\Notification $notification Model of the Notification to be Registered
     *
     * @return bool True if Success
     */
    public function create(Models\Notification $notification)
    {
        $this->_collection->insert($notification->document());
    }

    /**
     * Get the Notifications For a User
     * 
     * @param string  $userId ID of the User
     * @param integer $limit  Pagination Limit
     * @param integer $since  Since Which ID
     * 
     * @return array List of Notifications
     */
    public function get($userId, $limit = null, $since = null)
    {
        $query = array("notifierId" => $userId);
        if ($since) {
            $query["notification"] = array('$gt' => $since);
        }

        $cursor = $this->_collection->find($query);
        $cursor = $cursor->sort(array("notification"=>-1));

        if ($limit) {
            $cursor->limit((int)$limit);
        }

        $response = array();

        foreach ($cursor as $notification) {
            $response[] = $notification;
        }

        return $response;
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
}
