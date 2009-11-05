<?php
/**
 * File documentation
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
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
        $bLoaded = $this->dom->load('tests/Server/Config/seraphp_test_config.xml');
        if (!$bLoaded) { $this->fail('Error at loading XML'); }
    }

}