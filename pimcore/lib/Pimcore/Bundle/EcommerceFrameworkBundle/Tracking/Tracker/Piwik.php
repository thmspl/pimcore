<?php

declare(strict_types=1);

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Enterprise License (PEL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 * @license    http://www.pimcore.org/license     GPLv3 and PEL
 */

namespace Pimcore\Bundle\EcommerceFrameworkBundle\Tracking\Tracker;

use Pimcore\Analytics\Tracking\Piwik\Tracker as PiwikTracker;
use Pimcore\Bundle\EcommerceFrameworkBundle\CartManager\ICart;
use Pimcore\Bundle\EcommerceFrameworkBundle\Model\AbstractOrder;
use Pimcore\Bundle\EcommerceFrameworkBundle\Model\IProduct;
use Pimcore\Bundle\EcommerceFrameworkBundle\Tracking\ICartProductActionAdd;
use Pimcore\Bundle\EcommerceFrameworkBundle\Tracking\ICartProductActionRemove;
use Pimcore\Bundle\EcommerceFrameworkBundle\Tracking\ICartUpdate;
use Pimcore\Bundle\EcommerceFrameworkBundle\Tracking\ICategoryPageView;
use Pimcore\Bundle\EcommerceFrameworkBundle\Tracking\ICheckoutComplete;
use Pimcore\Bundle\EcommerceFrameworkBundle\Tracking\IProductView;
use Pimcore\Bundle\EcommerceFrameworkBundle\Tracking\ITrackingItemBuilder;
use Pimcore\Bundle\EcommerceFrameworkBundle\Tracking\Tracker;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Piwik extends Tracker implements
    IProductView,
    ICategoryPageView,
    ICartUpdate,
    ICartProductActionAdd,
    ICartProductActionRemove,
    ICheckoutComplete
{
    /**
     * @var PiwikTracker
     */
    private $tracker;

    public function __construct(
        PiwikTracker $tracker,
        ITrackingItemBuilder $trackingItemBuilder,
        EngineInterface $templatingEngine,
        array $options = []
    )
    {
        $this->tracker = $tracker;

        parent::__construct($trackingItemBuilder, $templatingEngine, $options);
    }

    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'template_prefix' => 'PimcoreEcommerceFrameworkBundle:Tracking/piwik'
        ]);
    }

    /**
     * @inheritDoc
     */
    public function trackProductView(IProduct $product)
    {
        $item = $this->trackingItemBuilder->buildProductViewItem($product);

        $call = [
            'setEcommerceView',
            $item->getId(),
            $item->getName(),
        ];

        $call[] = $this->filterCategories($item->getCategories());

        $price = $item->getPrice();
        if (!empty($price)) {
            $call[] = $price;
        }

        $result = $this->renderCalls([$call]);

        $this->tracker->addCodePart($result, PiwikTracker::BLOCK_ACTIONS, true);
    }

    /**
     * @inheritDoc
     */
    public function trackCategoryPageView($page, $category)
    {
        $category = $this->filterCategories($category);

        $result = $this->renderCalls([
            [
                'setEcommerceView',
                false,
                false,
                $category
            ]
        ]);

        $this->tracker->addCodePart($result, PiwikTracker::BLOCK_ACTIONS, true);
    }

    /**
     * @inheritDoc
     */
    public function trackCartProductActionAdd(ICart $cart, IProduct $product, $quantity = 1)
    {
        $this->trackCartUpdate($cart);
    }

    /**
     * @inheritDoc
     */
    public function trackCartProductActionRemove(ICart $cart, IProduct $product, $quantity = 1)
    {
        $this->trackCartUpdate($cart);
    }

    /**
     * @inheritDoc
     */
    public function trackCartUpdate(ICart $cart)
    {
        $items = $this->trackingItemBuilder->buildCheckoutItemsByCart($cart);

        $calls   = $this->buildItemCalls($items);
        $calls[] = [
            'trackEcommerceCartUpdate',
            $cart->getPriceCalculator()->getGrandTotal()->getAmount()->asNumeric()
        ];

        $result = $this->renderCalls($calls);

        $this->tracker->addCodePart($result, PiwikTracker::BLOCK_ACTIONS, true);
    }

    /**
     * @inheritDoc
     */
    public function trackCheckoutComplete(AbstractOrder $order)
    {
        $items       = $this->trackingItemBuilder->buildCheckoutItems($order);
        $transaction = $this->trackingItemBuilder->buildCheckoutTransaction($order);

        $calls   = $this->buildItemCalls($items);
        $calls[] = [
            'trackEcommerceOrder',
            $transaction->getId(),
            $transaction->getTotal(),
            $transaction->getSubTotal(),
            $transaction->getTax(),
            $transaction->getShipping(),
        ];

        $result = $this->renderCalls($calls);

        $this->tracker->addCodePart($result, PiwikTracker::BLOCK_ACTIONS, true);
    }

    private function renderCalls(array $calls): string
    {
        return $this->renderTemplate('calls', [
            'calls' => $calls
        ]);
    }

    private function buildItemCalls(array $items): array
    {
        $calls = [];
        foreach ($items as $item) {
            $calls[] = [
                'addEcommerceItem',
                $item->getId(),
                $item->getName(),
                $item->getCategories(),
                $item->getPrice(),
                $item->getQuantity()
            ];
        }

        return $calls;
    }

    private function filterCategories($categories, int $limit = 5)
    {
        if (null === $categories) {
            return $categories;
        }

        $result = null;

        if (is_array($categories)) {
            // add max 5 categories
            $categories = array_slice($categories, 0, 5);

            $result = [];
            foreach ($categories as $category) {
                $category = trim((string)$category);
                if (!empty($category)) {
                    $result[] = $category;
                }
            }

            $result = array_slice($result, 0, $limit);
        } else {
            $result = trim((string)$categories);
        }

        if (!empty($result)) {
            return $result;
        }
    }
}
