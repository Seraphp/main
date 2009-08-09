<?php
/**
 * Contains definition of Daemon Interface
 *
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
interface Daemon
{
    /**
     * Creates a deamon process from the implementing class
     *
     * @return void
     */
    public function summon();
    /**
     * Spawns a new child process from the current one
     *
     * @return void
     */
    public function spawn();
    /**
     * Finishes the run of the daemon process
     *
     * @return void
     */
    public function expell();
    /**
     * Set the maximum amount of allowed child processes running on the same
     * time
     *
     * @param $num
     * @return integer
     */
    public function setMaxSpawns($num);
    /**
     * Returns with the maximum amount of allowed child processes
     *  running on the same time
     *
     * @return integer
     */
    public function getMaxSpawns();
}