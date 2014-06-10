<?php
/**
 * File Containing the Configuration Class
 *
 * @category Mongologue
 * @package  Core
 * @author   @kpnunni <krishnanunni@suyati.com>
 * @license  NONE http://github.com/suyati/mongologue
 * @version  0.1.1
 * @link     http://github.com/suyati/mongologue
 */
namespace Mongologue;

/**
 * Class Having the Configuration Constants
 *
 * @category Mongologue
 * @package  Core
 * @author   @kpnunni <krishnanunni@suyati.com>
 * @license  NONE http://github.com/suyati/mongologue
 * @version  0.1.1
 * @link     http://github.com/suyati/mongologue
 */
class Config
{
    const DB_NAME                 = "twitterMongo";
    const USER_COLLECTION         = "users";
    const POST_COLLECTION         = "posts";
    const GROUP_COLLECTION        = "groups";
    const COMMENTS_COLLECTION     = "comments";
    const CATEGORY_COLLECTION     = "category";
    const INBOX_COLLECTION        = "inbox";
    const PREMADEPOST_COLLECTION  = "premadepost";
    const NOTIFICATION_COLLECTION = "notification";
}
