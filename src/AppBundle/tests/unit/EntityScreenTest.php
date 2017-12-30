<?php
namespace AppBundle;

use AppBundle\Entity\Screen;

class EntityScreenTest extends \PHPUnit_Framework_TestCase
{
    /** @var  Screen $testSubject */
    protected $testSubject;

    protected function setUp()
    {
        $this->testSubject = new Screen();
    }

    protected function tearDown()
    {
    }

    public function testGuid()
    {
        $exp = 'test123';
        $this->testSubject->setGuid($exp);
        $this->assertEquals($exp, $this->testSubject->getGuid());
    }
    public function testAdminC()
    {
        $exp = 'test123';
        $this->testSubject->setAdminC($exp);
        $this->assertEquals($exp, $this->testSubject->getAdminC());
    }
    public function testConnectCode()
    {
        $exp = 'test123';
        $this->testSubject->setConnectCode($exp);
        $this->assertEquals($exp, $this->testSubject->getConnectCode());
    }
    public function testLocation()
    {
        $exp = 'test123';
        $this->testSubject->setLocation($exp);
        $this->assertEquals($exp, $this->testSubject->getLocation());
    }
    public function testName()
    {
        $exp = 'test123';
        $this->testSubject->setName($exp);
        $this->assertEquals($exp, $this->testSubject->getName());
    }
    public function testNotes()
    {
        $exp = 'test123';
        $this->testSubject->setNotes($exp);
        $this->assertEquals($exp, $this->testSubject->getNotes());
    }
    public function testLastConnect()
    {
        $exp = new \DateTime('2017-02-03 11:12:13');
        $this->testSubject->setLastConnect($exp);
        $this->assertEquals($exp, $this->testSubject->getLastConnect());
    }
    public function testFirstConnect()
    {
        $exp = new \DateTime('2017-02-01 14:13:12');
        $this->testSubject->setFirstConnect($exp);
        $this->assertEquals($exp, $this->testSubject->getFirstConnect());
    }
}
