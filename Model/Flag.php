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

class Flag extends \Magento\Framework\Flag
{
    const FLAG_TTL = 300;
    const FLAG_STATE_RUNNING = 1;
    const FLAG_STATE_STOPPED = 0;

    /**
     * @var string
     */
    protected $_flagCode = 'lof_cleanup';


    /**
     * @return bool
     */
    public function isRunning()
    {
        if (($this->getState() == self::FLAG_STATE_RUNNING) &&
            (time() <= strtotime($this->getLastUpdate()) + self::FLAG_TTL)) {
            return true;
        }
        return false;
    }

    /**
     * @return \Magento\Framework\Flag
     */
    public function start()
    {
        try {
            $this->setState(self::FLAG_STATE_RUNNING)->save();
        } catch (\Exception $e) {
            // do nothing
        }
        return $this;
    }

    /**
     * @return \Magento\Framework\Flag
     */
    public function stop()
    {
        try {
            $this->setState(self::FLAG_STATE_STOPPED)->save();
        } catch (\Exception $e) {
            // do nothing
        }
        return $this;
    }
}
