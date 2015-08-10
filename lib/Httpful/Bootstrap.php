<?php

namespace Httpful;

require_once_recursive(dirname(__FILE__) . '/Exception');
require_once(dirname(__FILE__) . '/Handlers/MimeHandlerAdapter.php');
require_once_recursive(dirname(__FILE__) . '/Handlers');
require_once_recursive(dirname(__FILE__) . '/Response');
require_once_recursive(dirname(__FILE__) . '/');

/**
 * Bootstrap class that facilitates autoloading.  A naive
 * PSR-0 autoloader.
 *
 * @author Nate Good <me@nategood.com>
 */
class Bootstrap {

    public static $registered = false;

    /**
     * Register the autoloader and any other setup needed
     */
    public static function init() {
        self::registerHandlers();
    }

    /**
     * Register default mime handlers.  Is idempotent.
     */
    public static function registerHandlers() {
        if (self::$registered === true) {
            return;
        }

        // @todo check a conf file to load from that instead of
        // hardcoding into the library?
        $handlers = array(
            \Httpful\Mime::JSON => new \Httpful\Handlers\JsonHandler(),
            \Httpful\Mime::XML => new \Httpful\Handlers\XmlHandler(),
            \Httpful\Mime::FORM => new \Httpful\Handlers\FormHandler(),
            \Httpful\Mime::CSV => new \Httpful\Handlers\CsvHandler(),
        );

        foreach ($handlers as $mime => $handler) {
            // Don't overwrite if the handler has already been registered
            if (Httpful::hasParserRegistered($mime))
                continue;
            Httpful::register($mime, $handler);
        }

        self::$registered = true;
    }
}


Bootstrap::init();
