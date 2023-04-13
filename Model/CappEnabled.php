<?php

namespace Croapp\Integration\Model;

class CappEnabled implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options for CRO App Enabled dropdown in magento config
     */
    public function toOptionArray()
    {
        return [
            ['value' => '1', 'label' => __('Yes')],
            ['value' => '0', 'label' => __('No')],
        ];
    }
}
