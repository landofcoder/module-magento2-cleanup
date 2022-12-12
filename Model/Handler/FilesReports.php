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

class FilesReports extends AbstractFiles implements HandlerInterface
{
    /**
     * path for report files
     *
     * @var string
     */
    protected $reportPath;

    /**
     * path for the report archive
     *
     * @var string
     */
    protected $reportArchivePath;


    /**
     * Returns is configuration allowed execution
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->config->getCleanupReports();
    }

    /**
     * Runs cleanup
     *
     * @return $this
     */
    public function cleanup()
    {
        $this->init();
        $this->io->mkdir($this->reportArchivePath);

        //check if there are any files and create archive directory only if there is something to archive
        $files = glob($this->reportPath . '/*');
        foreach($files as $key => $file) {
            if (is_file($file) == false) {
                unset($files[$key]);
            }
        }

        $currentArchive = $this->reportArchivePath . DIRECTORY_SEPARATOR . date('Ymd_His');
        if (count($files)) {
            $this->io->mkdir($currentArchive);
            $currentArchive .= DIRECTORY_SEPARATOR;
        }

        // process reports
        foreach ($files as $file) {
            $this->logger->debug('cleaning up: ' . $file);
            $this->rotateFiles($file, $currentArchive . basename($file) . self::CLEANUP_ARCHIVE_FILE_EXTENSION, $this->dryRun);
            $this->pingDb();
        }

        $this->cleanupArchive($this->reportArchivePath, $this->config->getKeepReportsDays());
        $this->logSavedBytes();

        return $this;
    }

    protected function init()
    {
        parent::init();
        $this->reportPath = $this->directoryList->getPath(DirectoryList::VAR_DIR) . DIRECTORY_SEPARATOR . 'report';
        $this->reportArchivePath = $this->reportPath . DIRECTORY_SEPARATOR . self::CLEANUP_ARCHIVE_DIR;
    }
}
