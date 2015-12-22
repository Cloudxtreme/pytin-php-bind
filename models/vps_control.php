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

class VPSControl extends Resource {
    public static function getResourceName() {
        return 'vps';
    }

    public static function start($vmid, $node_id, $user = '') {
        if ($vmid <= 0) {
            throw new \InvalidArgumentException('vmid');
        }
        if ($node_id <= 0) {
            throw new \InvalidArgumentException('node_id');
        }

        $payload = array(
            'vmid' => $vmid,
            'node' => $node_id,
            'user' => $user
        );

        return self::internalFromArray(self::makeRequest(\Httpful\Http::PATCH, "/vps/$vmid/start/", false, $payload));
    }

    public static function stop($vmid, $node_id, $user = '') {
        if ($vmid <= 0) {
            throw new \InvalidArgumentException('vmid');
        }
        if ($node_id <= 0) {
            throw new \InvalidArgumentException('node_id');
        }

        $payload = array(
            'vmid' => $vmid,
            'node' => $node_id,
            'user' => $user
        );

        return self::internalFromArray(self::makeRequest(\Httpful\Http::PATCH, "/vps/$vmid/stop/", false, $payload));
    }
}
