<?php
/**
 * Contains definition of Daemon Interface
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @copyright Copyright (c) 2008, Peter Nagy
 * @package Server
 * @filesource
 */
/**
 * Daemon Interface definition for creating processes running on their
 * own (as a daemon in UNIX terminology).
 *
 * @package Server
 *
 */
interface Daemon{
    public function summon();
    public function spawn();
    public function expell();
    public function setMaxSpawns($num);
    public function getMaxSpawns();
}
?>