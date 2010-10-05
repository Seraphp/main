<?php
/**
 * Holds HttpException implementation
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
 * HttpException class
 *
 * @package Exceptions
 */
class HttpException extends NestedException
{
}