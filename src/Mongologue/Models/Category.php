<?php
/**
 * File containing the Category Model Class
 *
 * @category Mongologue
 * @package  Models
 * @author   @tkannippoyil <tkannippoyil@suyati.com>
 * @author   @kpnunni <krishnanunni@suyati.com>
 * @license  none http://github.com/suyati/mongologue
 * @link     http://github.com/suyati/mongologue
 */
namespace Mongologue\Models;

use \Mongologue\Interfaces\Model;
use \Mongologue\Exceptions\Category as Exceptions;
use \Mongologue\Exception;

/**
 * File containing the Category Model Class
 *
 * @category Mongologue
 * @package  Models
 * @author   @tkannippoyil <tkannippoyil@suyati.com>
 * @author   @kpnunni <krishnanunni@suyati.com>
 * @license  none http://github.com/suyati/mongologue
 * @link     http://github.com/suyati/mongologue
 */
class Category extends Model
{
    protected $id;
    protected $name;

    private $_necessaryAttributes = array("name");

    /**
     * Constructor of the Class
     * 
     * @param array $category Category Data
     */
    public function __construct($category)
    {
        parent::__construct($category);
    }

    /**
     * Set an Id to the Category
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
     * Get the necessary Attributes of Model
     * 
     * @return array necessary attributes
     */
    public function necessaryAttributes()
    {
        return $this->_necessaryAttributes;
    }
}