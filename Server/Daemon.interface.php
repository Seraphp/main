<?php
/**
 * Contains definition of Daemon Interface
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @filesource
 */
/**
 * Daemon Interface definition for creating processes running on their 
 * own as daemons.
 * 
 * @package Server
 * 
 */
interface Daemon{
    public function summon();
    public function spawn();
    public function expell();
    public function setMaxSpawns($num);
}
?>