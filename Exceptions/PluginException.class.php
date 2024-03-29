<?php
/**
 * Holds  PluginException implementation
 *
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @copyright Copyright (c) 2008, Peter Nagy
 * @package Exceptions
 * @filesource
 */
namespace Seraphp\Exceptions;
require_once 'NestedException.class.php';
/**
 * Exception class for policy plugin related issues
 *
 * @package Exceptions
 */
class PluginException extends NestedException
{
}