<?php
/*
 * PHP Unit tests for CMDB API Php binding.
 * (c) Dmitry Shilyaev
 *
 * Using tests
 *      Start development CMDB server:
 *
 *      remove DB
 *      ./manage.py migrate
 *      ./manage.py runserver
 */

require_once dirname(dirname(__FILE__)) . '/models/resource.php';
require_once dirname(dirname(__FILE__)) . '/models/ip_manager.php';

class CmdbApiTest extends PHPUnit_Framework_TestCase {

    public static function setUpBeforeClass() {
    }

    public function _test_paged_retrieve() {
        for ($x = 1; $x < 25; $x++) {
            $resource1 = new pytin\Resource(array(
                'type' => 'assets.ServerPort',
                'status' => 'free',
                'options' => array()
            ));
            $resource1->save();
        }

        $resources = pytin\Resource::filter(array(
            'type' => 'ServerPort'
        ));

        $this->assertEquals(24, count($resources));
    }

    public function testIpManager_Rent_IPs_From_AddressPool() {
        $address_pool = new pytin\Resource(array(
            'type' => 'ipman.IPAddressPool',
            'status' => 'free',
            'name' => 'Address Pool',
            'options' => array()
        ));
        $address_pool->save();

        for ($x = 3; $x < 25; $x++) {
            $res = new pytin\Resource(array(
                'type' => 'ipman.IPAddress',
                'parent' => $address_pool->id,
                'status' => 'free',
                'options' => array(
                    array(
                        'name' => 'address',
                        'value' => '172.168.1.' . $x
                    ),
                )
            ));
            $res->save();
        }

        // Check before rent
        $locked_ips = pytin\Resource::filter(array('type' => 'IPAddress', 'status' => 'locked', 'parent' => $address_pool->id));
        $this->assertEquals(0, count($locked_ips));

        $locked_ips = pytin\Resource::filter(array('type' => 'IPAddress', 'status' => 'free', 'parent' => $address_pool->id));
        $this->assertEquals(22, count($locked_ips));


        $ip_resources = pytin\IpManager::rentIPs(array($address_pool->id), 3);
        $this->assertEquals(3, count($ip_resources));
        $this->assertEquals('172.168.1.3', $ip_resources[0]->address);
        $this->assertEquals('172.168.1.4', $ip_resources[1]->address);
        $this->assertEquals('172.168.1.5', $ip_resources[2]->address);

        // Check after rent
        $locked_ips = pytin\Resource::filter(array('type' => 'IPAddress', 'status' => 'locked'));
        $this->assertEquals(3, count($locked_ips));

        $locked_ips = pytin\Resource::filter(array('type' => 'IPAddress', 'status' => 'free'));
        $this->assertEquals(19, count($locked_ips));
    }

    public function testIpManager_Rent_IPs_From_NetworkPool() {
        $resource1 = new pytin\Resource(array(
            'type' => 'ipman.IPNetworkPool',
            'status' => 'free',
            'options' => array(
                array(
                    'name' => 'network',
                    'value' => '192.168.1.1/24'
                ),
            )
        ));
        $resource1->save();

        $resource2 = new pytin\Resource(array(
            'type' => 'ipman.IPNetworkPool',
            'status' => 'free',
            'options' => array(
                array(
                    'name' => 'network',
                    'value' => '192.169.1.1/24'
                ),
            )
        ));
        $resource2->save();

        $ip_resources = pytin\IpManager::rentIPs(array($resource1->id, $resource2->id), 3);

        $this->assertEquals(3, count($ip_resources));
        $this->assertEquals('192.168.1.2', $ip_resources[0]->address);
        $this->assertEquals('192.169.1.2', $ip_resources[1]->address);
        $this->assertEquals('192.168.1.3', $ip_resources[2]->address);
    }

    public function testResourceAddEditDelete() {
        // Create resource
        $resource = new pytin\Resource(array(
            'name' => 'This is a test resource',
            'type' => 'ipman.IPAddress',
            'status' => 'free',
            'options' => array(
                array(
                    'name' => 'address',
                    'value' => '192.168.1.10'
                ),
            )
        ));

        $resource->save();

        $this->assertTrue($resource->id > 0);
        $this->assertEquals('This is a test resource', $resource->name);
        $this->assertEquals('IPAddress', $resource->type);
        $this->assertEquals('free', $resource->status);
        $this->assertEquals(1, count($resource->options));
        $this->assertEquals('address', $resource->options[0]->name);
        $this->assertEquals('192.168.1.10', $resource->options[0]->value);


        // Edit resource
        $resource->status = 'inuse';
        $resource->save();

        $this->assertEquals('inuse', $resource->status);


        // Edit resource options
        $resource->setOption('address', '192.168.1.11');
        $resource->save();

        $this->assertEquals(1, count($resource->options));
        $this->assertEquals('address', $resource->options[0]->name);
        $this->assertEquals('192.168.1.11', $resource->options[0]->value);


        // Delete object
        $resource->delete();

        try {
            $resource = pytin\Resource::get($resource->id);
            print_r($resource);
            $this->fail("Exception expected.");
        } catch (Exception $ex) {
        }
    }

    public function testResourceGet() {
        // Create resource
        $resource = new pytin\Resource(array(
            'name' => 'Test getter',
            'type' => 'ipman.IPAddress',
            'status' => 'inuse',
            'options' => array(
                array(
                    'name' => 'address1',
                    'value' => '192.168.1.101'
                ),
            )
        ));

        $resource->save();

        $resource = pytin\Resource::get($resource->id);
        $this->assertTrue($resource->id > 0);
        $this->assertEquals('Test getter', $resource->name);
        $this->assertEquals('IPAddress', $resource->type);
        $this->assertEquals('inuse', $resource->status);
        $this->assertEquals(1, count($resource->options));
        $this->assertEquals('address1', $resource->options[0]->name);
        $this->assertEquals('192.168.1.101', $resource->options[0]->value);
    }

    public function testResourceFilter() {
        // Create resource
        $resource = new pytin\Resource(array(
            'name' => 'Test filter 1',
            'type' => 'ipman.IPAddress',
            'status' => 'inuse',
            'options' => array(
                array(
                    'name' => 'address1',
                    'value' => '192.168.1.101'
                ),
            )
        ));
        $resource->save();

        $resource = new pytin\Resource(array(
            'name' => 'Test filter 2',
            'type' => 'ipman.IPAddress',
            'status' => 'free',
            'options' => array(
                array(
                    'name' => 'address1',
                    'value' => '192.168.1.111'
                ),
            )
        ));
        $resource->save();

        // Search by model fields exact match
        $resources = pytin\Resource::filter(array(
            'name' => 'Test filter 2',
            'status' => 'free',
        ));
        $this->assertEquals(1, count($resources));

        // More complex searches is not implemented yet
    }

}
