<?php
namespace Croapp\Integration\Logger;

use Monolog\Logger;

class Handler extends \Magento\Framework\Logger\Handler\Base
{
    /**
     * Logging level
     * @var int
     */
    protected $loggerType = Logger::INFO;

    /**
     * Log File name
     * @var string
     */
    protected $fileName = '/var/log/croapp_integration.log';
}
