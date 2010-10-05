<?php
/**
 * Holds  SocketException implementation
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
 * SocketException class
 *
 * @package Exceptions
 */
class SocketException extends NestedException
{
}