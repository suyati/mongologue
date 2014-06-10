<?php
/**
 * File containing the Group Model Class
 *
 * @category Mongologue
 * @package  Models
 * @author   @kpnunni <krishnanunni@suyati.com>
 * @license  none http://github.com/suyati/mongologue
 * @link     http://github.com/suyati/mongologue
 */
namespace Mongologue\Models;

use \Mongologue\Interfaces\Model;
use \Mongologue\Exceptions\Group as Exceptions;

/**
 * Class Managing Group Properties
 *
 * @category Mongologue
 * @package  Models
 * @author   @kpnunni <krishnanunni@suyati.com>
 * @license  none http://github.com/suyati/mongologue
 * @link     http://github.com/suyati/mongologue
 */
class Group extends Model
{
    protected $id;
    protected $name;
    protected $members = array();
    protected $followers = array();
    protected $parent;
    protected $type;

    /**
     * Additional Data container
     * @var array
     */
    protected $data = array();

    private $_necessaryAttributes = array("name");

    /**
     * Constructor of the Class
     * 
     * @param array $group Group Data
     */
    public function __construct($group)
    {
        parent::__construct($group);
    }

    /**
     * Add a Member to the group
     * 
     * @param string $userId Id of the user
     *
     * @todo implement Exception
     * @return void
     */
    public function addMember($userId)
    {
        if (in_array($userId, $this->members)) {
            throw new Exception("User with ID $userId Already a Member of the Group");
        }

        $this->members[] = $userId;
    }

    /**
     * Add a Follower to the group
     * 
     * @param string $userId Id of the user
     *
     * @todo implement Exception
     * @return void
     */
    public function addFollower($userId)
    {
        if (in_array($userId, $this->followers)) {
            throw new \Exception("User with ID $userId Already a Follower of the Group");
        }

        $this->followers[] = $userId;
    }

    /**
     * 
     * remove a Follower from the group
     * 
     * @param string $userId Id of the user
     *
     * @todo implement Exception
     * @return void
     */
    public function removeFollower($userId)
    {
        if (!in_array($userId, $this->followers)) {
            throw new \Exception("User with ID $userId Already a Follower of the Group");
        }

        $this->followers = array_values(array_diff($this->followers, array($userId)));
    }

    /**
     * Remove a Member from Group
     * 
     * @param string $userId ID of the Member User
     * 
     * @throws MemberNotFoundException if the userId is not already part 
     *                                 of the group
     * @return void
     */
    public function removeMember($userId)
    {
        if (!in_array($userId, $this->members)) {
            throw new Exceptions\MemberNotFoundException("User $userId is not a member of the Group");
        }

        $this->members = array_values(array_diff($this->members, array($userId)));
    }

    /**
     * Set an Id to the Group
     * 
     * @param string $id Id to be set
     *
     * @return void
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Get the Necessary Attributes for the Model
     * 
     * @return array List of necessary Attributes of the Model
     */
    public function necessaryAttributes()
    {
        return $this->_necessaryAttributes;
    }
}
