<?php
namespace MosPress\PluginStarterPro\API;

use MosPress\PluginStarterPro\Helpers\Utils;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_Query;
use WP_REST_Server;
class LogsController
{

    private $logs_table_name;
    public function __construct()
    {
    }
}