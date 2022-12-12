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

use Magento\Quote\Model\ResourceModel\Quote\CollectionFactory;
use Lof\Cleanup\Api\HandlerInterface;
use Lof\Cleanup\Logger\Logger;
use Lof\Cleanup\Model\Config;

class QuotesCustomer extends AbstractHandler implements HandlerInterface
{
    /**
     * @var CollectionFactory
     */
    protected $quoteCollectionFactory;

    /**
     * Quotes constructor.
     * @param Config $config
     * @param Logger $logger
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(Config $config, Logger $logger, CollectionFactory $collectionFactory)
    {
        parent::__construct($config, $logger);
        $this->quoteCollectionFactory = $collectionFactory;
    }

    /**
     * Returns is configuration allowed execution
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->config->getCleanupSalesQuoteCustomers();
    }

    /**
     * Runs cleanup
     *
     * @return $this
     */
    public function cleanup()
    {
        $keepCartQuotesDays = $this->config->getKeepCartQuotesCustomerDays();
        $this->log('days to keep quotes for registered users in database: ' .$keepCartQuotesDays);
        $lifetime = $keepCartQuotesDays * 86400;

        /** @var $quotes \Magento\Quote\Model\ResourceModel\Quote\Collection */
        $quotes = $this->quoteCollectionFactory->create();

        $quotes->addFieldToFilter('updated_at', ['to' => date('Y-m-d', time() - $lifetime)]);
        $quotes->addFieldToFilter('customer_id', ['neq' => 0]);
        $quotes->addFieldToFilter('customer_id', ['notnull' => true]);

        if ($this->config->isDryRun() === false) {
            $recordCount = $quotes->count();
            $quotes->walk('delete');
            $this->log($recordCount . ' records deleted');
        }

        return $this;
    }
}
