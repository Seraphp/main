<?php
/**
 * File documentation
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id:Specification.interface.php 234 2008-11-01 15:35:32Z peter $
 * @filesource
 */
//namespace Phaser\Policy;
/**
 * Specification interface
 * 
 * Has a required method "isSatisfiedBy" which a data source as parameter.
 * 
 * @package Policy
 */
interface Specification {
  /**
   * @param array|Iterator $src
   * @return boolean
   */
  public function isSatisfiedBy($src);
}
?>
