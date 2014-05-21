<?php
/**
 * File Containing the Groups Collection
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
 * Class Managing Collection of Groups
 *
 * @category Mongologue
 * @package  Collection
 * @author   @kpnunni <krishnanunni@suyati.com>
 * @license  none http://github.com/suyati/mongologue
 * @link     http://github.com/suyati/mongologue
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
            array("id" => $group->id),
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
        if ($group) {
            return new Models\Group($group);
        } else {
            throw new Exceptions\Group\GroupNotFoundException("Group with ID $id not found");
        }
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
        $group->setId(
            $this->_collections->getCollectionFor("counters")->nextId("group")
        );
        $this->_collection->insert($group->document());
        return $group->id;
    }

    /**
     * Remove a Group
     * 
     * @param string $groupId id of the Group
     *
     * @return void
     */
    public function remove($groupId)
    {
        try {
            $this->_collection->remove(array("id"=>$groupId));
        } catch (Exceptions\Group\GroupNotFoundException $e) {
            throw new Exception("Group with this ID not found");
        }
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

        foreach ($cursor as $document) {
            $groups[] = new Models\Group($document);
        }

        return $groups;
    }

    /**
     * Find a Group
     * 
     * @param mixed $param Parameter to Find. Pass an Id or a query
     * 
     * @return array document for the group
     */
    public function find($param)
    {
        if (is_array($param)) {
            return $this->modelFromQuery($param)->document();
        } else {
            return $this->modelFromId($param)->document();
        }
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
        $follower = $this->_collections->getCollectionFor("users")->modelFromId($followerId);

        $group->addFollower($followerId);
        $follower->followGroup($groupId);
        
        $this->_collections->getCollectionFor("inbox")->refresh($followerId, null, $groupId);
        $this->update($group);
        $this->_collections->getCollectionFor("users")->update($follower);
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
        $follower = $this->_collections->getCollectionFor("users")->modelFromId($followerId);

        $group->removeFollower($followerId);
        $this->update($group);

        $follower->unfollowGroup($groupId);
        $this->_collections->getCollectionFor("users")->update($follower);

        $this->_collections->getCollectionFor("inbox")->clean($followerId, null, $groupId);
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
        $joinee = $this->_collections->getCollectionFor("users")->modelFromId($joineeId);

        $joinee->joinGroup($groupId);
        $group->addMember($joineeId);
        
        $this->_collections->getCollectionFor("inbox")->refresh($joineeId, null, $groupId);

        $this->update($group);
        $this->_collections->getCollectionFor("users")->update($joinee);
    }


    /**
     * Get the subgroups
     * 
     * @param string $groupId  Id of the parent Group
     * 
     * @return void
     */
    public function subGroups($groupId)
    {
        $groups = array();
        $cursor = $this->_collection->find(array("parent" => $groupId));

        foreach ($cursor as $document) {
            $groups[] = new Models\Group($document);
        }

        return $groups;
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
        $member = $this->_collections->getCollectionFor("users")->modelFromId($memberId);

        $joinee->leaveGroup($groupId);
        $group->removeMember($memberId);

        $this->update($group);
        $this->_collections->getCollectionFor("users")->update($member);
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

        if ($group) {
            return new Models\Group($group);
        } else {
            throw new Exceptions\Group\GroupNotFoundException("No Group Matching Query");
        }
            
    }

    /**
     * Find the Parent Group of a Group
     * 
     * @param mixed   $groupId   Id of the Group
     * @param boolean $recursive Set flag to true to find the parent group recursively
     * @param integer $iteration The iteration in which the parent search is in
     * 
     * @return Group Model of the Parent Group; Returns the Model of the Group itself if it is a parent
     */
    public function parent($groupId, $recursive = false, $iteration = 0)
    {
        $group = $this->modelFromId($groupId);

        if ($recursive) {
            if ($group->parent) {
                return $this->parent($group->parent, true, ++$iteration);
            } elseif ($iteration>0) {
                return $group;
            } else {
                return $group;
            }
        } else {
            if ($group->parent) {
                return $this->modelFromId($group->parent);
            } else {
                return $group;
            }
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
