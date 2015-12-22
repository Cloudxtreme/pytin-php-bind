<?php
/**
 * Created by PhpStorm.
 * User: dmitry
 * Date: 17.06.15
 * Time: 13:16
 *
 * Project: hapi
 */

namespace pytin;

require_once 'resource.php';

class CloudTasks extends Resource {
    public static function getResourceName() {
        return 'cloud_tasks';
    }
}
