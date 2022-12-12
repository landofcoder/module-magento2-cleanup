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
namespace Lof\Cleanup\Model;

use Lof\Cleanup\Logger\Logger;
use Lof\Cleanup\Model\Handler\Resolver;

class Cron
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var Flag
     */
    protected $flag;

    /**
     * @var Resolver
     */
    protected $handlerResolver;

    /**
     * Cron constructor.
     * @param Config $config
     * @param Logger $logger
     * @param FlagFactory $flagFactory
     * @param Resolver $handlerResolver
     * @param array $handler
     */
    public function __construct(
        Config $config,
        Logger $logger,
        FlagFactory $flagFactory,
        Resolver $handlerResolver
    ) {
        $this->config = $config;
        $this->logger = $logger;
        $this->flag = $flagFactory->create()->loadSelf();
        $this->handlerResolver = $handlerResolver;
    }

    /**
     * start the cleanup process
     */
    public function execute()
    {
        if ($this->flag->isRunning()) {
            $msg = 'Skipping process. Another process is still running.';
            $this->logger->info($msg);
            return $msg;
        }

        $this->flag->start();

        try {
            if ($this->config->isEnabled()) {
                foreach ($this->handlerResolver->getHandlers() as $handlerKey) {
                    $handler = $this->handlerResolver->get($handlerKey);
                    if ($handler->isEnabled()) {
                        $handler->cleanup();
                        $this->logger->info('Completed cleanup for '.$handlerKey);
                        $this->logger->info(str_repeat('-', 72));
                    }
                }
            } else {
                $this->logger->debug('Disabled.');
            }
        } catch (\Exception $e) {
            $this->logger->error('error during cleanup: ' . $e->getMessage());
            $this->logger->error('aborting');
            $this->logger->critical($e);
        }

        $this->flag->stop();

        return 'finished: ' . date('Y-m-d H:i:s');
    }
}
