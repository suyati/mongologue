<?php
/**
 * File Contiaing the USer Model Specs Class
 *
 * @category Mongologue
 * @package  Tests
 * @author   @kpnunni <krishnanunni@suyati.com>
 * @license  none http://suyati.com
 * @link     http://github.com/xait/docxwriter
 */
namespace Mongologue\Models;

use \Mongologue\Exception;

/**
 * Test Cases for the User Model
 *
 * @todo implement testcases for follow, group functions
 * 
 * @category Mongologue
 * @package  Tests
 * @author   @kpnunni <krishnanunni@suyati.com>
 * @license  none http://suyati.com
 * @link     http://github.com/xait/docxwriter
 */
class UserModelSpec extends \PHPUnit_Framework_TestCase
{
    /**
     * Check Instantiation with BAD/Insufficient Data
     *
     * @param mixed $userData Bad User Data
     * 
     * @test
     *
     * @dataProvider providerInvalidUserData
     * @expectedException Exception
     * @return void
     */
    public function shouldThrowExceptionIfAllUserDataIsNotProvided($userData)
    {
        $user = new User($userData);
    }

    /**
     * Check if Valid Data is Accepted and Document formation is as expected
     * 
     * @param array $userData         UserData
     * @param array $expectedDocument Expected Document
     *
     * @test
     *
     * @dataProvider providerForValidData
     * @return void
     */
    public function shouldAcceptValidUserDataAndMustReturnProperDocumentArray($userData, $expectedDocument)
    {
        $user = new User($userData);
        $this->assertEquals($expectedDocument, $user->document());
        $this->assertEquals(array(), $user->followers);
    }

    /**
     * Provider for Valid USer Data
     * 
     * @return array Valid USer Data
     */
    public function providerForValidData()
    {
        return array(
            array(
                array(
                    "id" => 21,
                    "handle" => "feynman",
                    "firstName" => "Richard",
                    "lastName" => "FeynMan",
                    "email" => "r@x.com",
                    "pic" => "htpp://kl.com/pic.jpeg"
                ),
                array(
                    "id" => 21,
                    "handle" => "feynman",
                    "firstName" => "Richard",
                    "lastName" => "FeynMan",
                    "email" => "r@x.com",
                    "pic" => "htpp://kl.com/pic.jpeg",
                    "following" => array(),
                    "followers" => array(),
                    "groups" => array(),
                    "blocking" => array(),
                    "blockers" => array(),
                    "postUnfollowing" => array(),
                    "followingGroups" => array(),
                    "likes" => array(),
                    "taggedUsers" => array(),
                    "data" => array()
                )
            ),
            array(
                array(
                    "id" => 22,
                    "handle" => "tim",
                    "firstName" => "Tim",
                    "lastName" => "Henford",
                    "following" => array(21),
                    "pic" => "someurl.com",
                    "email" => "a@b.com",
                    "groups" => array(33),
                    "data" => array("userPages"=>array("A", "B"))
                ),
                array(
                    "id" => 22,
                    "handle" => "tim",
                    "firstName" => "Tim",
                    "lastName" => "Henford",
                    "email" => "a@b.com",
                    "pic" => "someurl.com",
                    "following" => array(21),
                    "followers" => array(),
                    "groups" => array(33),
                    "blocking" => array(),
                    "blockers" => array(),
                    "postUnfollowing" => array(),
                    "followingGroups" => array(),
                    "likes" => array(),
                    "taggedUsers" => array(),
                    "data" => array("userPages"=>array("A", "B"))
                )
            ),
        );
    }

    /**
     * Providef for bad user data
     * 
     * @return array Bad User Data
     */
    public function providerInvalidUserData()
    {
        return array(
            array("hello"),
            array(array("id"=>1,"handle"=>"bokkumo","firstName"=>'MEH')),
            array(array("id"=>1,"firstName"=>'MEH'))
        );
    }
}
