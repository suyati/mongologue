<?php
/**
 * File Containing the Inbox Class
 *
 * @category Mongologue
 * @package  Core
 * @author   @kpnunni <krishnanunni@suyati.com>
 * @license  NONE http://suyati.com
 * @version  0.1.1
 * @link     http://suyati.com
 */
namespace Mongologue;

/**
 * Class Managing Inboxes
 *
 * @category Mongologue
 * @package  Core
 * @author   @kpnunni <krishnanunni@suyati.com>
 * @license  NONE http://suyati.com
 * @version  0.1.1
 * @link     http://suyati.com
 */
class Inbox
{
    /**
     * Write Post to Inbox
     * 
     * @param Post            $post   Post
     * @param MongoCollection $inbox  Inbox Collection
     * @param MongoCollection $posts  Post Collection
     * @param MongoCollection $users  Users Collection
     * @param MongoCollection $groups Groups Collection
     * 
     * @return boolean True if Success
     */
    public static function writeToInbox(Post $post, \MongoCollection $inbox, \MongoCollection $posts, \MongoCollection $users, \MongoCollection $groups)
    {
        $recipients = $post->getRecipients();

        foreach ($recipients as $recipient) {
            $postDocument = $post->document();
            $postDocument["recipient"] = $recipient;
            $inbox->insert($postDocument);
        }

        return true;
    }

    /**
     * Get the Messages for a User
     * 
     * @param string          $userId Id of User
     * @param MongoCollection $inbox  Inbox Collection
     * @param integer         $limit  Limit for the results
     * @param integer         $since  Since which id
     * 
     * @return array List of Posts
     */
    public static function getMessages($userId, \MongoCollection $inbox, $limit=null, $since=null)
    {
        if ($since) {
            $cursor = $inbox->find(array("recipient"=>$userId, "id"=>array('$gt'=>$since)));
        } else {
            $cursor = $inbox->find(array("recipient"=>$userId));
        }

        $cursor = $cursor->sort(array("id"=>-1));

        if ($limit) {
            $cursor->limit((int)$limit);
        }

        return iterator_to_array($cursor);
    }
}
?>