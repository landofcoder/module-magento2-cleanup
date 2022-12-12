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

class FilesFolders extends AbstractFiles implements HandlerInterface
{
    /**
     * Returns is configuration allowed execution
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->config->isOptionalFolderCleanupEnabled();
    }

    /**
     * Runs cleanup
     *
     * @return $this
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function cleanup()
    {
        $this->init();

        foreach ($this->config->getCleanupOptionalFolders() as $folder) {
            $pathToCleanup = $this->directoryList->getPath(DirectoryList::ROOT) . DIRECTORY_SEPARATOR . $folder['path'];
            if (substr($pathToCleanup, -1) !== DIRECTORY_SEPARATOR) {
                $pathToCleanup .= DIRECTORY_SEPARATOR;
            }
            $pathToCleanup = str_replace('//', DIRECTORY_SEPARATOR, $pathToCleanup);

            if (file_exists($pathToCleanup) === true) {
                $archivePath = $pathToCleanup . self::CLEANUP_ARCHIVE_DIR;
                if (file_exists($archivePath) === false) {
                    if ($this->io->mkdir($archivePath) === false) {
                        $this->log('folder: ' . $pathToCleanup . ' is not writable: skipping!');
                    }
                }
                $archivePath .= DIRECTORY_SEPARATOR;

                $currentArchive = $archivePath . date('Ymd_His');
                if ($this->io->mkdir($currentArchive) === false) {
                    $this->log('error creating archive subfolder: ' . $currentArchive);
                }
                $currentArchive .= DIRECTORY_SEPARATOR;

                $skipDays = array_key_exists('skip_days', $folder) ? (int)$folder['skip_days'] : 0;
                //check if there are any files
                $files = $this->getFileList($pathToCleanup, $folder['mask'], $skipDays);
                $this->log('cleaning up ' . count($files) . ' files in optional path: ' . $folder['path']);

                foreach ($files as $file) {
                    $this->logger->debug('cleaning up: ' . $file);
                    $this->rotateFiles($file, $currentArchive . basename($file) . self::CLEANUP_ARCHIVE_FILE_EXTENSION, $this->dryRun);
                    $this->pingDb();
                }

                $this->cleanupArchive($archivePath, $folder['days']);

                $this->log('cleaning up of optional path: ' . $folder['path'] . ' finished. ' . count($files) . ' files cleaned.');
                $this->log(str_repeat('-', 72));
            } else {
                $this->log('optional path: ' . $folder['path'] . ' does not exist in Magento base directory.');
                $this->log(str_repeat('-', 72));
            }
        }

        $this->logSavedBytes();

        return $this;
    }
}
