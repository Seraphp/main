<?php
namespace Seraphp\Process;
require_once 'Process/Process.class.php';
require_once 'Process/User.class.php';
require_once 'Process/Group.class.php';

class Factory {
    private function __constructor()
    {
    }

    private function __clone()
    {
    }

    /**
     * Creates an process related entity based on arguments.
     *
     * @param string $type
     * @param integer|string $id
     * @static
     */
    static function create($type, $id=null)
    {
        switch ($type) {
            case 'process':
                return new Process($id);
                break;
            case 'user':
                return new User($id);
                break;
            case 'group':
                return new Group($id);
                break;
        }
    }

    /**
     * Helps to create many similar items in one call
     *
     * @param string $type
     * @param array $list
     * @return array
     */
    static function createMany($type, $list)
    {
        $result = array();
        foreach ($list as $item) {
            $result[] = self::create($type, $item);
        }
        return $result;
    }
}