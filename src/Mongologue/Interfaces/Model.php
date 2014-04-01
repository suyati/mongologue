<?php
/**
 * File Containing the Model Interface
 *
 * @category Mongologue
 * @package  Interfaces
 * @author   @kpnunni <krishnanunni@suyati.com>
 * @license  none http://github.com/suyati/mongologue
 * @link     http://github.com/suyati/mongologue
 */
namespace Mongologue\Interfaces;

use \Mongologue\Exception;

 /**
 * File Containing the Model Interface
 *
 * @category Mongologue
 * @package  Interfaces
 * @author   @kpnunni <krishnanunni@suyati.com>
 * @license  none http://github.com/suyati/mongologue
 * @link     http://github.com/suyati/mongologue
 */
class Model
{

    /**
     * Constructor of Class
     * 
     * @param array $data Data Passed to the Model
     */
    public function __construct(array $data)
    {
        $attributes = $this->_getAttributes();

        foreach ($attributes as $attribute) {
            if (isset($data[$attribute])) {
                $this->$attribute = $data[$attribute];
            } else {
                if(in_array($attribute, $this->necessaryAttributes()))
                    throw new Exception("All Required Data not provided");
            }
        }       
    }

    /**
     * Get a Document for the Model
     * 
     * @return array Document for the Model
     */
    public function document()
    {
        $document = array();

        foreach (array_keys(get_class_vars(get_class($this))) as $key) {
            $document[$key] = $this->$key;
        }

        return $document;
    }

    /**
     * Magic Function to get all Attributes
     * 
     * @param string $attribute identifier for attribute
     * 
     * @return mixed Value of attribute
     */
    public function __get($attribute)
    {
        return $this->$attribute;
    }

    /**
     * Get All Attributes of the Class
     * 
     * @return array list of Attributes
     */
    private function _getAttributes()
    {
        return array_keys(get_class_vars(get_class($this)));
    }
}