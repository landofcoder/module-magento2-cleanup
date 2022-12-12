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
namespace Lof\Cleanup\Console\Command;

use Lof\Cleanup\Model\Cron;
use Lof\Cleanup\Model\Handler\Resolver;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

class CleanupCommand extends Command
{
    const PARAM_HANDLER = 'handler';

    /**
     * @var Cron
     */
    protected $cron;

    /**
     * @var Resolver
     */
    protected $handlerResolver;


    /**
     * CleanupCommand constructor.
     *
     * @param Cron $cron
     * @param Resolver $handlerResolver
     */
    public function __construct(
        Cron $cron,
        Resolver $handlerResolver
    ) {
        $this->cron = $cron;
        $this->handlerResolver = $handlerResolver;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('lof:cleanup:run')
            ->setDescription('Run cleanup handler')
            ->setDefinition(
                [
                    new InputArgument(
                        self::PARAM_HANDLER,
                        InputArgument::OPTIONAL,
                        'Handler name, possible values: '.implode(', ', $this->handlerResolver->getHandlers())
                    )
                ]
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($handlerKey = $input->getArgument(self::PARAM_HANDLER)) {
            $handler = $this->handlerResolver->get($handlerKey);
            if ($handler->isEnabled()) {
                $handler->cleanup();
            }
        } else {
            $res = $this->cron->execute();
            $output->writeln($res);
        }

        $output->writeln('Completed.');
    }
}
