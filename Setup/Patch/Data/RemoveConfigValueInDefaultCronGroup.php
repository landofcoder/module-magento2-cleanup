<?php declare(strict_types=1);
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

namespace Lof\Cleanup\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Config\Model\ResourceModel\Config\Data;
use Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory;

class RemoveConfigValueInDefaultCronGroup implements DataPatchInterface
{
    const CONFIG_PATH_TO_REMOVE = 'crontab/default/jobs/lof_cleanup_magento/schedule';

    /**
     * @var Data
     */
    private $configResource;
    /**
     * @var CollectionFactory
     */
    private $configCollectionFactory;


    public function __construct(Data $configResource, CollectionFactory $configCollectionFactory)
    {
        $this->configResource = $configResource;
        $this->configCollectionFactory = $configCollectionFactory;
    }

    /**
     * @inheritDoc
     */
    public function apply()
    {
        $collection = $this->configCollectionFactory->create()
            ->addPathFilter(self::CONFIG_PATH_TO_REMOVE);
        foreach ($collection as $config) {
            $this->configResource->delete($config);
        }
    }

    /**
     * @inheritDoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getAliases()
    {
        return [];
    }
}
