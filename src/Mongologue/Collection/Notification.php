<?php
/**
 * File Containing the Notifications Collection
 *
 * @category Mongologue
 * @package  Collection
 * @author   @kpnunni <krishnanunni@suyati.com>
 * @author   @tkannippoyil <tkannippoyil@suyati.com>
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
 * @author   @tkannippoyil <tkannippoyil@suyati.com>
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
        $this->_collection  = $mongoCollection;
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
        $notification->setId(
            $this->_collections->getCollectionFor("counters")->nextId("notifications")
        );
        $this->_collection->insert($notification->document());
        return true;
    }

    /**
     * Update document of a Notification
     * 
     * @param Models\Notification $notification Model of a notification
     * 
     * @return void
     */
    public function update(Models\Notification $notification)
    {
        $this->_collection->update(
            array("id" => $notification->id),
            $notification->document()
        );
        return true;
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
    public function get($userId, $limit = null, $since = null, $upto = null)
    {
        $query = array("notifierId" => $userId);
        if ($since) {
            $query["notification"] = array('$lt' => $since);
        } elseif ($upto) {
            $query["notification"] = array('$gt' => $upto);
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
     * Get the unread Notifications For a User
     * 
     * @param string  $userId ID of the User
     * @param integer $limit  Pagination Limit
     * @param integer $since  Since Which ID
     * 
     * @return array List of Notifications
     */
    public function getUnread($userId, $limit = null, $since = null, $upto = null)
    {
        $query = array("notifierId" => $userId, "read" => false);
        if ($since) {
            $query["notification"] = array('$lt' => $since);
        } elseif ($upto) {
            $query["notification"] = array('$gt' => $upto);
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
     * Remove notification
     * 
     * @param string $notifierId Id of the notifier
     * @param string $userId Id of the notifiee
     * 
     * @return void
     */
    public function remove($notifierId, $userId)
    {
        $query = array(
                       "notifierId" => $notifierId,
                       "userId"     => $userId
                );
        $this->_collection->remove($query);
        return true;
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
