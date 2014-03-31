<?php
/**
 * File Containing the Groups Collection
 *
 * @category Mongologue
 * @package  Collection
 * @author   @kpnunni <krishnanunni@suyati.com>
 * @license  none http://suyati.com
 * @link     http://github.com/xait/docxwriter
 */
namespace Mongologue\Collection;

use \Mongologue\Interfaces\Collection;
use \Mongologue\Models;
use \Mongologue\Core\Collections;

/**
 * Class Managing Collection of Groups
 *
 * @category Mongologue
 * @package  Collection
 * @author   @kpnunni <krishnanunni@suyati.com>
 * @license  none http://suyati.com
 * @link     http://github.com/xait/docxwriter
 */
class Group implements Collection
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
     * Update document of a Group
     * 
     * @param Models\Group $group Model of a group
     * 
     * @return void
     */
    public function update(Models\Group $group)
    {

        $this->_collection->update(
            array("id" => $group->id()),
            $group->document()
        );
    }

    /**
     * Get a Group Model from a Group Id
     * 
     * @param string $id Id of the Group
     *
     * @throws GroupNotFoundException when Invalid id is provided
     * @return Models\Group Model of the Group
     */
    public function modelFromId($id)
    {
        $group = $this->_collection->findOne(array("id" => $id));
        if($group)
            return new Models\Group($group);
        else
            throw new Exceptions\Group\GroupNotFoundException("Group with ID $id not found");
    }

    /**
     * Register a New Group
     * 
     * @param Models\Group $group Model of the Group to be Registered
     *
     * @throws DuplicateGroupException when an existing group id is provided
     * @return bool True if Success
     */
    public function register(Models\Group $group)
    {
        try
        {
            $temp = $this->modelFromId($group->id());
        }
        catch(Exceptions\Group\GroupNotFoundException $e)
        {
            $this->_collection->insert($group->document());
            return true;
        }

        throw new Exceptions\Group\DuplicateGroupException("Group with this ID already registered");
    }

    /**
     * Get All Groups
     * 
     * @return array List of All Groups
     */
    public function all()
    {
        $groups = array();
        $cursor = $this->_collection->find();

        foreach ($$cursor as $document) {
            $groups[] = new Models\Group($document);
        }

        return $groups;
    }

    /**
     * Follow a Group
     * 
     * @param string $groupId    Id of the Group to be followed
     * @param string $followerId Id of the Follower
     * 
     * @return void
     */
    public function follow($groupId, $followerId)
    {
        $group = $this->modelFromId($groupId);
        $follower = $this->_collections->getCollectionFor("user")->modelFromId($followerId);

        $follower->followGroup($groupId);
        $group->addFollower($followerId);

        $this->update($group);
        $this->_collections->getCollectionFor("user")->update($follower);
    }

    /**
     * Unfollow a Group
     * 
     * @param string $groupId    Id of the Group to be followed
     * @param string $followerId Id of the Follower
     * 
     * @return void
     */
    public function unfollow($groupId, $followerId)
    {
        $group = $this->modelFromId($groupId);
        $follower = $this->_collections->getCollectionFor("user")->modelFromId($followerId);

        $follower->unfollowGroup($groupId);
        $group->removeFollower($followerId);

        $this->update($group);
        $this->_collections->getCollectionFor("user")->update($follower);
    }

    /**
     * Join a Group
     * 
     * @param string $groupId  Id of the Group
     * @param string $joineeId Id of the Joinee User
     * 
     * @return void
     */
    public function join($groupId, $joineeId)
    {
        $group = $this->modelFromId($groupId);
        $joinee = $this->_collections->getCollectionFor("user")->modelFromId($joineeId);

        $joinee->joinGroup($groupId);
        $group->addMember($joineeId);

        $this->update($group);
        $this->_collections->getCollectionFor("user")->update($joinee);
    }

    /**
     * Leave a group
     * 
     * @param string $groupId  Id of the Group
     * @param string $memberId Id of the Memeber User
     * 
     * @return void
     */
    public function leave($groupId, $memberId)
    {
        $group = $this->modelFromId($groupId);
        $member = $this->_collections->getCollectionFor("user")->modelFromId($memberId);

        $joinee->leaveGroup($groupId);
        $group->removeMember($memberId);

        $this->update($group);
        $this->_collections->getCollectionFor("user")->update($member);
    }

    /**
     * Get a Group Model using a Query
     * 
     * @param array $query Query for the model
     *
     * @throws GroupNotFoundException if a Group which matches criteria cannot be found
     * @return Models\Group Model of the matching group
     */
    public function modelFromQuery(array $query)
    {
        $group = $this->_collection->findOne($query);

        if($group)
            return new Models\Group($group);
        else
            throw new Exceptions\Group\GroupNotFoundException("No Group Matching Query");
            
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
