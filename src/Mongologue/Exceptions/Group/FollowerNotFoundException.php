<?php
/**
 * File Containing Exception for Posts duplicate
 *
 * @category Mongologue
 * @package  Exceptions
 * @author   @kpnunni <krishnanunni@suyati.com>
 * @author   @naveenbos <nmohanan@suyati.com>
 * @license  NONE http://suyati.com
 * @link     http://suyati.com
 */
namespace Mongologue\Exceptions\Group;

/**
 * Exception for Posts duplicate
 *
 * @category Mongologue
 * @package  Exceptions
 * @author   @kpnunni <krishnanunni@suyati.com>
 * @author   @naveenbos <nmohanan@suyati.com>
 * @license  NONE http://suyati.com
 * @link     http://suyati.com
 */
class FollowerNotFoundException extends \Exception
{
	/**
     * Constructor for custom Exception
     * 
     * @param string    $message  Message for the Exception
     * @param integer   $code     Exception Code
     * @param Exception $previous Prevoious exception if any
     */
    public function __construct($message, $code=315, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
?>