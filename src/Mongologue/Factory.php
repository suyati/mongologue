<?php
/**
 * File Containing the Factory Class
 *
 * @category Mongologue
 * @package  Mongologue
 * @author   @kpnunni <krishnanunni@suyati.com>
 * @license  none http://github.com/suyati/mongologue
 * @link     http://github.com/suyati/mongologue
 */
namespace Mongologue;

use \Mongologue\Config;
use \Mongologue\Collection;
use \Mongologue\Core\Collections;

/**
 * Factory Class for Mongologue
 *
 * @category Mongologue
 * @package  Mongologue
 * @author   @kpnunni <krishnanunni@suyati.com>
 * @license  none http://github.com/suyati/mongologue
 * @link     http://github.com/suyati/mongologue
 */
class Factory
{
    /**
     * Create an Instance of Mongologue and Build all dependencies
     * 
     * @param MongoClient $mongoClient Mongo Client Instance
     * @param string      $dbName      Name of the DB
     * 
     * @return Mongologue Instance of Mongologue App
     */
    public function createMongologue(\MongoClient $mongoClient, $dbName)
    {
        $db           = $mongoClient->$dbName;

        $collections  = new Collections();

        $users        = new Collection\User($db->createCollection("users"), $collections);
        $groups       = new Collection\Group($db->createCollection("groups"), $collections);
        $posts        = new Collection\Post($db->createCollection("posts"), $collections);
        $inbox        = new Collection\Inbox($db->createCollection("inbox"), $collections);
        $category     = new Collection\Category($db->createCollection("category"), $collections);
        $notification = new Collection\Notification($db->createCollection("notification"), $collections);
        
        $counters     = new Collection\Counter($db->createCollection("counters"), $collections);
        
        $posts->setGridFS($db->getGridFS());

        $collections->registerCollection("users", $users);
        $collections->registerCollection("groups", $groups);
        $collections->registerCollection("posts", $posts);
        $collections->registerCollection("inbox", $inbox);
        $collections->registerCollection("category", $category);
        $collections->registerCollection("notification", $notification);

        $collections->registerCollection("counters", $counters);

        return new \Mongologue\Core\Mongologue(
            $users,
            $groups,
            $posts,
            $inbox,
            $category,
            $notification
        );
    }
}
