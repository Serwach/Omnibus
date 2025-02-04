<?php

namespace Serwach\OmnibusExtension\Plugin\Cart;

use Magento\Checkout\Block\Cart\AbstractCart as BaseAbstractCart;

class AbstractCart
{
    public function afterGetItemRenderer(BaseAbstractCart $subject, $result)
    {
        $result->setTemplate('Serwach_OmnibusExtension::cart/item/default.phtml');

        return $result;
    }
}
