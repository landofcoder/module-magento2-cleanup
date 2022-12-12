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

use Magento\AdminNotification\Model\InboxFactory;
use Lof\Cleanup\Api\HandlerInterface;
use Lof\Cleanup\Logger\Logger;
use Lof\Cleanup\Model\Config;

class AdminNotifications extends AbstractHandler implements HandlerInterface
{
    /**
     * @var InboxFactory
     */
    protected $adminNotificationInboxFactory;

    public function __construct(Config $config, Logger $logger, InboxFactory $adminNotificationInboxFactory)
    {
        parent::__construct($config, $logger);
        $this->adminNotificationInboxFactory = $adminNotificationInboxFactory;
    }

    /**
     * Returns is configuration allowed execution
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->config->isAdminNotificationsCleanupEnabled();
    }

    /**
     * Runs cleanup
     *
     * @return $this
     */
    public function cleanup()
    {
        try {
            $notifications      = $this->adminNotificationInboxFactory->create();
            $notificationIds    = $notifications->getCollection()->addFieldToFilter(
                array(
                    'is_read',
                    'is_remove'
                ),
                array(
                    array('eq' => 0),
                    array('eq' => 0)
                )
            )->getAllIds();

            $deleteDays = $this->config->getAdminNotificationsKeepDays();

            foreach ($notificationIds as $notificationId) {
                $notification = $notifications->load($notificationId);

                //get days
                $today = new \Zend_Date();
                $dateAdded = new \Zend_Date();
                $dateAdded->setLocale('en');
                $dateAddedValue = $notification->getDateAdded();
                $dateAdded->set($dateAddedValue);
                $diff = $today->sub($dateAdded)->toValue();
                $days = ceil($diff / 60 / 60 / 24) + 1;

                //@todo: check strange behavior where some dates are returned as future dates
                if ($days > $deleteDays || $deleteDays == 0 || $days < 0) {
                    if ($notification->getIsRead() != 1) {
                        $notification->setIsRead(1);
                    }

                    if ($this->config->getDeleteAdminNotifications()) {
                        $notification->setIsRemove(1);
                    }

                    if ($notification->hasDataChanges()) {
                        $notification->save();
                    }
                }
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }

        return $this;
    }
}
