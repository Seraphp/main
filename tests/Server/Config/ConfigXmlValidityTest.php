<?php
/**
 * File documentation
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id: ConfigFactoryTest.php 360 2009-02-23 21:44:39Z peter $
 * @filesource
 */
require_once 'PHPUnit/Framework.php';
/**
 * Class documentation
 */
class ConfigXmlValidityTest extends PHPUnit_Framework_TestCase{

    private $dom = null;

    function setUp()
    {
        $this->dom = new DOMDocument();
    }

    function testXmlIsValid()
    {
        $bLoaded = $dom->load('seraphp_test_config.xml');
        if (!$bLoaded) { $this->fail('Error at loading XML'); }
    }

}
?>