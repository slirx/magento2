<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GiftMessage\Model;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\State\InvalidTransitionException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Shopping cart gift message item repository object.
 */
class ItemRepository implements \Magento\GiftMessage\Api\ItemRepositoryInterface
{
    /**
     * Quote repository.
     *
     * @var \Magento\Quote\Model\QuoteRepository
     */
    protected $quoteRepository;

    /**
     * Store manager interface.
     *
     * @var \Magento\Framework\Store\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Gift message manager.
     *
     * @var \Magento\GiftMessage\Model\GiftMessageManager
     */
    protected $giftMessageManager;

    /**
     * Message helper.
     *
     * @var \Magento\GiftMessage\Helper\Message
     */
    protected $helper;

    /**
     * Message factory.
     *
     * @var \Magento\GiftMessage\Model\MessageFactory
     */
    protected $messageFactory;

    /**
     * @param \Magento\Quote\Model\QuoteRepository $quoteRepository
     * @param \Magento\Framework\Store\StoreManagerInterface $storeManager
     * @param GiftMessageManager $giftMessageManager
     * @param \Magento\GiftMessage\Helper\Message $helper
     * @param MessageFactory $messageFactory
     */
    public function __construct(
        \Magento\Quote\Model\QuoteRepository $quoteRepository,
        \Magento\Framework\Store\StoreManagerInterface $storeManager,
        \Magento\GiftMessage\Model\GiftMessageManager $giftMessageManager,
        \Magento\GiftMessage\Helper\Message $helper,
        \Magento\GiftMessage\Model\MessageFactory $messageFactory
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->giftMessageManager = $giftMessageManager;
        $this->storeManager = $storeManager;
        $this->helper = $helper;
        $this->messageFactory = $messageFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function get($cartId, $itemId)
    {
        /**
         * Quote.
         *
         * @var \Magento\Quote\Model\Quote $quote
         */
        $quote = $this->quoteRepository->getActive($cartId);
        if (!$item = $quote->getItemById($itemId)) {
            throw new NoSuchEntityException('There is no item with provided id in the cart');
        };
        $messageId = $item->getGiftMessageId();
        if (!$messageId) {
            return null;
        }

        /**
         * Model.
         *
         * @var \Magento\GiftMessage\Model\Message $model
         */
        return $this->messageFactory->create()->load($messageId);
    }

    /**
     * {@inheritDoc}
     */
    public function save($cartId, \Magento\GiftMessage\Api\Data\MessageInterface $giftMessage, $itemId)
    {
        /**
         * Quote.
         *
         * @var \Magento\Quote\Model\Quote $quote
         */
        $quote = $this->quoteRepository->getActive($cartId);

        if (!$item = $quote->getItemById($itemId)) {
            throw new NoSuchEntityException("There is no product with provided  itemId: $itemId in the cart");
        };

        if ($item->getIsVirtual()) {
            throw new InvalidTransitionException('Gift Messages is not applicable for virtual products');
        }
        if (!$this->helper->getIsMessagesAvailable('items', $quote, $this->storeManager->getStore())) {
            throw new CouldNotSaveException('Gift Message is not available');
        }
        $this->giftMessageManager->setMessage($quote, 'quote_item', $giftMessage, $itemId);
        return true;
    }
}
