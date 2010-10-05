<?php
/**
 * Contains Spedificatin interface definition
 *
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id:Specification.interface.php 234 2008-11-01 15:35:32Z peter $
 * @copyright Copyright (c) 2008, Peter Nagy
 * @package Policy
 * @filesource
 */
/***/
namespace Seraphp\Policy;
/**
 * Specification interface
 *
 * Has a required method "isSatisfiedBy" which a data source as parameter.
 *
 * @package Policy
 */
interface Specification
{
  /**
   * @param array|Iterator $src
   * @return boolean
   */
  public function isSatisfiedBy($src);
}