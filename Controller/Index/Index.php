<?php

namespace GoMage\LightCheckout\Controller\Index;

use GoMage\LightCheckout\Model\Config\CheckoutConfigurationsProvider;
use GoMage\LightCheckout\Model\IsEnableLightCheckout;
use Magento\Checkout\Helper\Data;
use Magento\Checkout\Model\Session;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Quote\Api\CartRepositoryInterface;

class Index extends \Magento\Checkout\Controller\Onepage
{
    /**
     * @var Session
     */
    private $session;

    /**
     * @var CheckoutConfigurationsProvider
     */
    private $checkoutConfigurationsProvider;

    /**
     * @var IsEnableLightCheckout
     */
    private $isEnableLightCheckout;
    /**
     * @var CheckoutHelper
     */
    public $checkoutHelperData;

    /**
     * Index constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param CustomerRepositoryInterface $customerRepository
     * @param AccountManagementInterface $accountManagement
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\Translate\InlineInterface $translateInline
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     * @param CartRepositoryInterface $quoteRepository
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param Session $session
     * @param CheckoutConfigurationsProvider $checkoutConfigurationsProvider
     * @param IsEnableLightCheckout $isEnableLightCheckout
     * @param Data $checkoutHelperData
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        CustomerRepositoryInterface $customerRepository,
        AccountManagementInterface $accountManagement,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Translate\InlineInterface $translateInline,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        CartRepositoryInterface $quoteRepository,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        Session $session,
        CheckoutConfigurationsProvider $checkoutConfigurationsProvider,
        IsEnableLightCheckout $isEnableLightCheckout,
        Data $checkoutHelperData
    ) {
        parent::__construct(
            $context,
            $customerSession,
            $customerRepository,
            $accountManagement,
            $coreRegistry,
            $translateInline,
            $formKeyValidator,
            $scopeConfig,
            $layoutFactory,
            $quoteRepository,
            $resultPageFactory,
            $resultLayoutFactory,
            $resultRawFactory,
            $resultJsonFactory
        );

        $this->session = $session;
        $this->checkoutConfigurationsProvider = $checkoutConfigurationsProvider;
        $this->isEnableLightCheckout = $isEnableLightCheckout;
        $this->checkoutHelperData = $checkoutHelperData;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        if ($this->isEnableLightCheckout->execute()) {
            $quote = $this->getOnepage()->getQuote();
            $checkoutHelper = $this->checkoutHelperData;
            if (!$this->_customerSession->isLoggedIn() && !$checkoutHelper->isAllowedGuestCheckout($quote)
                || !$quote->hasItems() || !$quote->validateMinimumAmount()) {
                //redirect not to cart, because if cart is off in configuration it will come to endless redirect.
                $this->messageManager->addErrorMessage(__('Please sign in to make your purchase. Thank you!'));
                return $this->resultRedirectFactory->create()->setPath('/');
            }
            $this->_customerSession->regenerateId();
            $this->session->setCartWasUpdated(false);
            $this->getOnepage()->initCheckout();

            $resultPage = $this->resultPageFactory->create();
            $resultPage->getConfig()->getTitle()->set(__($this->checkoutConfigurationsProvider->getPageTitle()));
        } else {
            $resultPage = null;
        }

        return $resultPage;
    }
}
