<?php
/**
 * Landofcoder
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Landofcoder.com license that is
 * available through the world-wide-web at this URL:
 * https://landofcoder.com/terms
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category   Landofcoder
 * @package    Lof_Cleanup
 * @copyright  Copyright (c) 2022 Landofcoder (https://landofcoder.com/)
 * @license    https://landofcoder.com/terms
 */
namespace Lof\Cleanup\Model\Handler;

use Magento\Framework\App\ResourceConnection;
use Lof\Cleanup\Api\HandlerInterface;
use Lof\Cleanup\Logger\Logger;
use Lof\Cleanup\Model\Config;

class LogReports extends AbstractHandler implements HandlerInterface
{
    /**
     * @var ResourceConnection
     */
    protected $resource;

    public function __construct(Config $config, Logger $logger, ResourceConnection $resource)
    {
        parent::__construct($config, $logger);
        $this->resource = $resource;
    }

    /**
     * Returns is configuration allowed execution
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->config->isLogReportsCleanupEnabled();
    }

    /**
     * Runs cleanup
     *
     * @return $this
     */
    public function cleanup()
    {
        try {
            $connection     = $this->resource->getConnection();

            $tablesToTruncate = array(
                                'report_event',
                                'report_viewed_product_index',
                                'report_compared_product_index',
                                'customer_visitor'
                            );
            foreach ($tablesToTruncate as $_key => $value) {
                $tableName  = $this->resource->getTableName($value);
                $sql        = "TRUNCATE ".$tableName;
                $connection->query($sql);
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }

        return $this;
    }
}
