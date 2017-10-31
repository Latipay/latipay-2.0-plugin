<?php
namespace Magento5\Latipay\Model;


class Environment implements \Magento\Framework\Option\ArrayInterface
{
    const ENVIRONMENT_PRODUCTION    = 'production';
    const ENVIRONMENT_SANDBOX       = 'sandbox';

    /**
     * Possible environment types
     * 
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::ENVIRONMENT_SANDBOX,
                'label' => 'Sandbox',
            ],
            [
                'value' => self::ENVIRONMENT_PRODUCTION,
                'label' => 'Production'
            ]
        ];
    }
}
