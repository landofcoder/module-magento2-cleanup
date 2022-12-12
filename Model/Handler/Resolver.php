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

use Magento\Framework\ObjectManagerInterface;
use Lof\Cleanup\Api\HandlerInterface;

/**
 * Class Resolver
 * @package Lof\Cleanup
 */
class Resolver
{
    /**
     * Handler pool
     *
     * @var array
     */
    protected $handlerPool = [];

	/**
	 * @var ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * @param ObjectManagerInterface $objectManager
	 * @param array $handlerPool
	 */
    public function __construct(
		ObjectManagerInterface $objectManager,
        $handlerPool = []
    ) {
        $this->objectManager = $objectManager;
        $this->handlerPool = $handlerPool;
	}

    /**
     * Get handles keys
     *
     * @return array
     */
	public function getHandlers()
    {
        return array_keys($this->handlerPool);
    }

    /**
     * Return license block type.
     *
     * @param string $handlerKey
     *
     * @return HandlerInterface
     * @throws \InvalidArgumentException
     */
    public function get($handlerKey)
    {
        if (!isset($this->handlerPool[$handlerKey])) {
            throw new \InvalidArgumentException('Requested handler "'.$handlerKey.'" not found.');
        }

        $handler = $this->objectManager->create($this->handlerPool[$handlerKey]);
        if (!($handler instanceof HandlerInterface)) {
            throw new \InvalidArgumentException('Handler does not implement HandlerInterface');
        }
        return $handler;
    }
}
