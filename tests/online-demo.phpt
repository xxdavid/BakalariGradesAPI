<?php

/**
 * Test for online demo at http://www.demo.bakalari.cz/bakaweb
 *
 * @testCase
 */
require_once __DIR__ . '/bootstrap.php';

use Tester\Assert;

class OnlineDemoTest extends Tester\TestCase
{

    private $subjects;
    private $subject;

    public function __construct()
    {
        $bakalari = new BakalariGradesAPI('zak', 'z', 'http://www.demo.bakalari.cz/bakaweb', 'cookie.txt');
        $this->subjects = $bakalari->getGrades();
        $this->subject = reset($this->subjects);
    }

    public function testGradeArray()
    {
        Assert::type('array', $this->subject);
        Assert::type('array', $this->subject[0]);
    }

    public function testGrade()
    {
        Assert::type('string', $this->subject[0]['grade']);
        Assert::true(strlen($this->subject[0]['grade']) > 0);
        Assert::true(strlen($this->subject[0]['grade']) <= 5);
    }

    public function testWeight()
    {
        Assert::type('string', $this->subject[0]['weight']);
        Assert::true(strlen($this->subject[0]['weight']) >= 1);
        Assert::true(strlen($this->subject[0]['weight']) <= 2);
    }

    public function testDate()
    {
        Assert::type('string', $this->subject[0]['date']);
        Assert::true(strlen($this->subject[0]['date']) == 10);
    }

    public function testDescription()
    {
        Assert::type('string', $this->subject[0]['description']);
        Assert::true(strlen($this->subject[0]['description']) > 0);
        Assert::true(strlen($this->subject[0]['description']) <= 30);
    }

}

$testCase = new OnlineDemoTest();
$testCase->run();
