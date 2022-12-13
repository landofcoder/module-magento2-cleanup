<?php

namespace Lof\Cleanup\Block\Adminhtml\Image\Grid\Renderer;

class Action extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @inheritdoc
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $valueId = $row->getValueId();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $producturl = $objectManager->get('\Magento\Backend\Helper\Data')
            ->getUrl('cleanup/image/delete', ['id' => $valueId]);

        if (!empty($valueId)) {
            return '<a
                href="'.$producturl.'"
                onclick="return confirm(\'' . __('Are you sure you want to delete this image?') . '\')
            ">' . __('Delete') .  '</a>';
        }

        return false;
    }
}
