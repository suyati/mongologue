<?php
/**
 * File Containing Exception for Group not Found
 *
 * @category Mongologue
 * @package  Exceptions
 * @author   @kpnunni <krishnanunni@suyati.com>
 * @author   @naveenbos <nmohanan@suyati.com>
 * @license  NONE http://github.com/suyati/mongologue
 * @link     http://github.com/suyati/mongologue
 */
namespace Mongologue\Exceptions\Group;

/**
 * Exception for Group not Found
 *
 * @category Mongologue
 * @package  Exceptions
 * @author   @kpnunni <krishnanunni@suyati.com>
 * @author   @naveenbos <nmohanan@suyati.com>
 * @license  NONE http://github.com/suyati/mongologue
 * @link     http://github.com/suyati/mongologue
 */
class GroupNotFoundException extends \Exception
{
	/**
     * Constructor for custom Exception
     * 
     * @param string    $message  Message for the Exception
     * @param integer   $code     Exception Code
     * @param Exception $previous Prevoious exception if any
     */
    public function __construct($message, $code=308, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
?>