<?php
/**
 * Holds LogException implementation
 *
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @copyright Copyright (c) 2009, Peter Nagy
 * @package Exceptions
 * @filesource
 */
namespace Seraphp\Exceptions;
require_once 'NestedException.class.php';
/**
 * LogException class
 *
 * @package Exceptions
 */
class LogException extends NestedException
{
}