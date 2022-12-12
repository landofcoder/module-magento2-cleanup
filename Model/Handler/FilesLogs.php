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

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Io\File;
use Lof\Cleanup\Api\HandlerInterface;
use Lof\Cleanup\Helper\Data as Helper;
use Lof\Cleanup\Logger\Logger;
use Lof\Cleanup\Model\Config;

class FilesLogs extends AbstractFiles implements HandlerInterface
{
    /**
     * Returns is configuration allowed execution
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->config->getCleanupLogFiles();
    }

    /**
     * Runs cleanup
     *
     * @return $this
     */
    public function cleanup()
    {
        $this->init();
        $this->io->mkdir($this->logArchivePath);

        if ($this->config->getCleanupAllFiles()) {
            $fileMask = '*';
        } else {
            $fileMask = '*.log';
        }

        // check if there are any files
        $logFiles = glob($this->logPath . $fileMask);
        foreach($logFiles as $key => $file) {
            if (is_file($file) == false) {
                unset($logFiles[$key]);
            }
        }

        $currentArchive = $this->logArchivePath . DIRECTORY_SEPARATOR . date('Ymd_His');
        if (count($logFiles) > 0) {
            $this->io->mkdir($currentArchive);
            $currentArchive .= DIRECTORY_SEPARATOR;
        }

        // cleanup old log first
        if (file_exists($this->cleanupLogFile) === true) {
            $this->rotateFiles($this->cleanupLogFile, $currentArchive . self::CLEANUP_LOG_FILE . self::CLEANUP_ARCHIVE_FILE_EXTENSION);
            $this->logger->debug('cleaned up old '.basename($this->cleanupLogFile).' file');
        }

        // check if it is a dry-run
        if ($this->dryRun == true) {
            $this->log('!!! running in dry-run mode !!! No files or folders are deleted');
        }

        //process other log files
        $this->log('cleaning up log-files');
        foreach ($logFiles as $logFile) {
            if ($logFile != $this->cleanupLogFile) {
                $this->logger->debug('cleaning up: ' . $logFile);
                $this->rotateFiles($logFile, $currentArchive . basename($logFile) . self::CLEANUP_ARCHIVE_FILE_EXTENSION, $this->dryRun);
                $this->pingDb();
            }
        }

        $this->cleanupArchive($this->logArchivePath);
        $this->logSavedBytes();

        return $this;
    }
}
