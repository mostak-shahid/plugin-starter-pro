<?php
namespace MosPress\PluginStarterPro\API;
if ( ! defined( 'ABSPATH' ) ) exit;
use Plugin_Upgrader;
use WP_Ajax_Upgrader_Skin;

use MosPress\PluginStarterPro\Helpers\CryptoHelper;

class Ajax_API
{
    private static $instance = null;
    public static function get_instance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    public function __construct()
	{		
    }   
}

// new Ajax_API();