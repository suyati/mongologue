<?php
/**
 * File Containing Exception for User not Found
 *
 * @category Mongologue
 * @package  Exceptions
 * @author   @kpnunni <krishnanunni@suyati.com>
 * @license  NONE http://suyati.com
 * @link     http://suyati.com
 */
namespace Mongologue\Exceptions\User;

/**
 * Exception for User not Found
 *
 * @category Mongologue
 * @package  Exceptions
 * @author   @kpnunni <krishnanunni@suyati.com>
 * @license  NONE http://suyati.com
 * @link     http://suyati.com
 */
class DuplicateUserException extends \Exception
{
	/**
     * Constructor for custom Exception
     * 
     * @param string    $message  Message for the Exception
     * @param integer   $code     Exception Code
     * @param Exception $previous Prevoious exception if any
     */
    public function __construct($message, $code=302, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
?>