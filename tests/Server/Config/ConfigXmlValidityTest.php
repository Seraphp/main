<?php
/**
 * File documentation
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @filesource
 */

/**
 * Class documentation
 */
class ConfigXmlValidityTest extends PHPUnit_Framework_TestCase
{

    private $_dom = null;

    function setUp()
    {
        $this->_dom = new DOMDocument();
    }

    function testXmlIsValid()
    {
        $bLoaded = $this->_dom->load(
            'tests/Server/Config/seraphp_test_config.xml'
        );
        if (!$bLoaded) {
            $this->fail('Error at loading XML');
        }
    }
    function tearDown()
    {
        unset($this->_dom);
    }
}