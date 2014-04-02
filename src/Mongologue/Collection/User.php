<?php
/**
 * File Containing the User Collection Class
 *
 * @category Mongologue
 * @package  Collection
 * @author   @kpnunni <krishnanunni@suyati.com>
 * @license  none http://github.com/suyati/mongologue
 * @link     http://github.com/suyati/mongologue
 */
namespace Mongologue\Collection;

use \Mongologue\Exceptions as Exceptions;
use \Mongologue\Interfaces\Collection;
use \Mongologue\Models;
use \Mongologue\Core\Collections;

/**
 * Class Managing the User Collection
 *
 * @category Mongologue
 * @package  Collection
 * @author   @kpnunni <krishnanunni@suyati.com>
 * @license  none http://github.com/suyati/mongologue
 * @link     http://github.com/suyati/mongologue
 */
class User implements Collection
{
    private $_collection;
    private $_collections;

    /**
     * Constructor function
     *
     * @param MongoColleciton $mongoCollection Mongo Collection Object
     * @param Collections     $collections     User of Collecitons
     */
    public function __construct(\MongoCollection $mongoCollection, Collections $collections)
    {
        $this->_collections = $collections; 
        $this->_collection = $mongoCollection;
    }

    /**
     * Update a User Document
     * 
     * @param Models\User $user A User Model
     *
     * @todo Handle Exception Cases
     * 
     * @return void
     */
    public function update(Models\User $user)
    {
        $this->_collection->update(
            array("id" => $user->id),
            $user->document()
        );
    }

    /**
     * Get a User Model from a User Id
     * 
     * @param string $id Id of the User
     *
     * @throws UserNotFoundException when Invalid id is provided
     * @return Models\User Model for the User
     */
    public function modelFromId($id)
    {
        $user = $this->_collection->findOne(array("id"=> $id));
        if($user)
            return new Models\User($user);
        else
            throw new Exceptions\User\UserNotFoundException("User with ID $id Not Found");
    }

    /**
     * Get a User Model using a Query
     * 
     * @param array $query Query for the model
     *
     * @throws UserNotFoundException if a User which matches criteria cannot be found
     * @return Models\User Model of the matching user
     */
    public function modelFromQuery(array $query)
    {
        $user = $this->_collection->findOne($query);

        if($user)
            return new Models\User($user);
        else
            throw new Exceptions\User\UserNotFoundException("No User Matching Query");
            
    }

    /**
     * Register a New User
     * 
     * @param Models\User $user Model of the User to be Registered
     *
     * @throws DuplicateUserException when an existing user id is provided
     * @return bool True if Success
     */
    public function register(Models\User $user)
    {
        try
        {
            $temp = $this->modelFromId($user->id);
        }
        catch(Exceptions\User\UserNotFoundException $e)
        {
            $this->_collection->insert($user->document());
            return true;
        }

        throw new Exceptions\User\DuplicateUserException("User with this ID already registered");
    }

    /**
     * Get All Users in the System
     * 
     * @return array List of Users in the System
     */
    public function all()
    {
        $users = array();
        $cursor = $this->_collection->find();

        foreach ($cursor as $document) {
            $users[] = new Models\User($document);
        }

        return $users;
    }

    /**
     * Find a User
     * 
     * @param mixed $param Parameter to Find. Pass an Id or a query
     * 
     * @return array document for the user
     */
    public function find($param)
    {
        if(is_array($param))
            return $this->modelFromQuery($param)->document();
        else
            return $this->modelFromId($param)->document();
    }

    /**
     * Follow a User
     * 
     * @param string $followeeId Id of the Followee
     * @param string $followerId Id of the Follower
     *
     * @return void
     */
    public function follow($followeeId, $followerId)
    {
        $followee = $this->modelFromId($followeeId);
        $follower = $this->modelFromId($followerId);

        $follower->follow($followeeId);
        $followee->addFollower($followerId);

        $this->update($follower);
        $this->update($followee);
    }

    /**
     * Unfollow a User
     * 
     * @param string $followeeId Id of the followee
     * @param string $followerId Id of the follower
     * 
     * @return void
     */
    public function unfollow($followeeId, $followerId)
    {
        $followee = $this->modelFromId($followeeId);
        $follower = $this->modelFromId($followerId);

        $follower->unfollow($followeeId);
        $followee->removeFollower($followerId);

        $this->update($follower);
        $this->update($followee);
    }

    /**
     * Block a User
     * 
     * @param string $blockeeId Id of the Blockee
     * @param string $blockerId Id of the Blocker
     * 
     * @return void
     */
    public function block($blockeeId, $blockerId)
    {
        $blocker = $this->modelFromId($blockerId);

        $blocker->block($blockeeId);

        $this->update($blocker);
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