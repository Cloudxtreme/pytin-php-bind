<?php
/**
 * Created by PhpStorm.
 * User: dmitry
 * Date: 17.06.15
 * Time: 13:16
 *
 * Project: hapi
 */

require_once 'Resource.php';

class IpManager extends Resource {
    public static function rentIPs($pool_ids = array(), $count = 1) {
        if (empty($pool_ids)) {
            throw new InvalidArgumentException('pool_ids');
        }

        if ($count <= 0) {
            throw new InvalidArgumentException('count');
        }

        $payload = array(
            'count' => $count,
            'pool' => $pool_ids
        );

        $ready_ips = self::makeRequest(\Httpful\Http::GET, '/ipman/rent', true, $payload);

        $resources = array();
        foreach($ready_ips as $res_array) {
            $resources[] = self::internalFromArray($res_array);
        }

        return $resources;
    }
}
