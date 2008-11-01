<?php
/**
 * File documentation
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @filesource
 */
//namespace Phaser\Policy;
/**
 * Specification interface
 * 
 * Has a required method "isSatisfiedBy" which a data source as parameter.
 * 
 * @package Phaser
 * @subpackage Policy
 */
interface Specification {
  /**
   * @param array|Iterator $src
   * @return boolean
   */
  public function isSatisfiedBy($src);
}
?>
