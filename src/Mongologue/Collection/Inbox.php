<?php
/**
 * File containing the Inbox Collection Class
 *
 * @category Mongologue
 * @package  Collection
 * @author   @kpnunni <krishnanunni@suyati.com>
 * @license  none http://github.com/suyati/mongologue
 * @link     http://github.com/suyati/mongologue
 */
namespace Mongologue\Collection;

use \Mongologue\Interfaces\Collection;
use \Mongologue\Core\Collections;
use \Mongologue\Models;
use \Mongologue\Models\Message;

/**
 * Class Managing the Inbox Collection
 *
 * @category Mongologue
 * @package  Collection
 * @author   @kpnunni <krishnanunni@suyati.com>
 * @license  none http://github.com/suyati/mongologue
 * @link     http://github.com/suyati/mongologue
 */
class Inbox implements Collection
{
    private $_collection;
    private $_collections;

    /**
     * Constructor function
     *
     * @param MongoColleciton $mongoCollection Mongo Collection Object
     * @param Collections     $collections     Group of Collecitons
     */
    public function __construct(\MongoCollection $mongoCollection, Collections $collections)
    {
        $this->_collections = $collections;
        $this->_collection = $mongoCollection;
    }

    /**
     * Clean Inbox of a User
     * 
     * @param mixed $to    Id of the User who is the recipient
     * @param mixed $from  Id of the User who is the owner
     * @param mixed $group Id of the Group who is a recipient
     * 
     * @return void
     */
    public function clean($to, $from, $group = null)
    {
        if (!is_null($from)) {
            $followers = $this->_collections->getCollectionFor("users")->followers($from);
            if (!in_array($to, $followers)) {
                $this->remove($to, $from);
            }
        }
        
        if (!is_null($group)) {

            $subscriptions = $this->_collections->getCollectionFor("users")->subscriptions($to);
            $group = $this->_collections->getCollectionFor("groups")->modelFromId($group);
            $members = $group->members;

            $toRemove = array();
            foreach ($members as $id) {
                if (!in_array($id, $subscriptions)) {
                    $toRemove[] = $id;
                }
            }

            foreach ($toRemove as $from) {
                $this->remove($to, $from);
            }
        }
    }

    public function refresh($to, $from, $group = null)
    {
        $subscriptions = $this->_collections->getCollectionFor("users")->subscriptions($to);

        if (!is_null($from)) {
            if (!in_array($from, $subscriptions)) {
                $posts = $this->_collections->getCollectionFor("posts")->search(array("userId"=>$from, "parent" => null));
                $user = $this->_collections->getCollectionFor("users")->modelFromId($from);
                $parentGroups = $this->_collections->getCollectionFor("users")->parentGroups($user);
                
                foreach ($posts as $post) {
                    if ($post->category) {
                        $category = $this->_collections->getCollectionFor("category")->modelFromId($post->category);
                    } else {
                        $category = null;
                    }
                    
                    $message = Message::create($post, $user, $category, $parentGroups);
                    $message->setRecipient($to);
                    $this->_collection->insert($message->document());
                }
            }
        }

        if (!is_null($group)) {
            $group = $this->_collections->getCollectionFor("groups")->modelFromId($group);
            $members = $group->members;

            $toAdd = array();
            foreach ($members as $id) {
                if (!in_array($id, $subscriptions)) {
                    $toAdd[] = $id;
                }
            }

            foreach ($toAdd as $from) {
                if (!in_array($from, $subscriptions)) {
                    $posts = $this->_collections->getCollectionFor("posts")->search(array("userId"=>$from, "parent" => null));
                    $user = $this->_collections->getCollectionFor("users")->modelFromId($from);
                    $parentGroups = $this->_collections->getCollectionFor("users")->parentGroups($user);
                
                    foreach ($posts as $post) {
                        if ($post->category) {
                            $category = $this->_collections->getCollectionFor("category")->modelFromId($post->category);
                        } else {
                            $category = null;
                        }
                        
                        $message = Message::create($post, $user, $category, $parentGroups);
                        $message->setRecipient($to);
                        $this->_collection->insert($message->document());
                    }
                }
            }

        }
    }

    /**
     * Write Messages to Recipient Inbox
     * 
     * @param Models\Post $post Post Models
     * 
     * @return boolean True if Success
     */
    public function write(Models\Post $post)
    {
        $user = $this->_collections->getCollectionFor("users")->modelFromId($post->userId);
        $parentGroups = $this->_collections->getCollectionFor("users")->parentGroups($user);

        $recipients = $this->_collections->getCollectionFor("users")->followers($user->id);
        
        if ($post->category) {
            $category = $this->_collections->getCollectionFor("category")->modelFromId($post->category);
        } else {
            $category = null;
        }
        
        foreach ($recipients as $recipient) {
            $toUser = $this->_collections->getCollectionFor("users")->modelFromId($recipient);
            if (!in_array($user->id, $toUser->blocking)) {
                $message = Message::create($post, $user, $category, $parentGroups);
                $message->setRecipient($recipient);
                $this->_collection->insert($message->document());
            }
        }

        return true;
    }

    /**
     * Remove Messages from Inbox
     * 
     * @param mixed $to   Id of recipient
     * @param mixed $from Id of sender
     * 
     * @return void
     */
    public function remove($to, $from)
    {
        $query = array("to"=>$to, "from"=>$from);
        $this->_collection->remove($query);
    }


    /**
     * Get the Post Feed For a User
     * 
     * @param string  $userId ID of the User
     * @param integer $limit  Pagination Limit
     * @param integer $since  Since Which ID
     * 
     * @return array List of Posts in Feed
     */
    public function feed($userId, $limit = null, $since = null, $upto = null)
    {
        $query = array("to" => $userId);
        if ($since) {
            $query["post"] = array('$lt' => $since);
        } elseif ($upto) {
            $query["post"] = array('$gt' => $upto);
        }

        $cursor = $this->_collection->find($query);
        $cursor = $cursor->sort(array("post"=>-1));

        if ($limit) {
            $cursor->limit((int)$limit);
        }

        $response = array();

        foreach ($cursor as $post) {
            $response[] = $post;
        }

        return $response;
    }

    /**
     * Get a Inbox Model using a Query
     * 
     * @param array $query Query for the model
     *
     * @return Models\Inbox Model of the matching  query
     */
    public function find(array $query)
    {
        $feed = $this->_collection->findOne($query);

        if ($feed) {
            return $feed;
        } else {
            throw new \Exception("No feed Matching Query");
        }
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
