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
     * @return mixed
     */
    public function summon();
    /**
     * Spawns a new child process from the current one
     * 
     * After forking the processes the method should retrun the PID of the 
     * created child process either in the parent and the child process.
     *
     * @return integer  PID of the created child process
     */
    public function spawn();
    /**
     * Finishes the run of the daemon process
     *
     * @return boolean
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