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
namespace Lof\Cleanup\Block\Adminhtml\System\Config\Fieldset;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;

class Folders extends AbstractFieldArray
{
    /**
     * Prepare to render
     * @return void
     */
    protected function _prepareToRender()
    {
        $this->addColumn(
            'path',
            [
                'label' => __('Path'),
                'style' => 'width:260px'
            ]
        );

        $this->addColumn(
            'skip_days',
            [
                'label' => __('Skip Days'),
                'style' => 'width:80px'
            ]
        );

        $this->addColumn(
            'days',
            [
                'label' => __('Cleanup Days'),
                'style' => 'width:80px'
            ]
        );

        $this->addColumn(
            'mask',
            [
                'label' => __('File Mask'),
                'style' => 'width:40px'
            ]
        );

        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add Folder');
    }
}
