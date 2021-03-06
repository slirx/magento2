<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OfflinePayments\Model;

class CheckmoTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\OfflinePayments\Model\Checkmo
     */
    protected $_object;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_scopeConfig;

    protected function setUp()
    {
        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $eventManager = $this->getMock('Magento\Framework\Event\ManagerInterface', [], [], '', false);
        $paymentDataMock = $this->getMock('Magento\Payment\Helper\Data', [], [], '', false);
        $this->_scopeConfig = $this->getMock(
            'Magento\Framework\App\Config\ScopeConfigInterface',
            ['getValue', 'isSetFlag'],
            [],
            '',
            false
        );
        $this->_object = $objectManagerHelper->getObject(
            'Magento\OfflinePayments\Model\Checkmo',
            [
                'eventManager' => $eventManager,
                'paymentData' => $paymentDataMock,
                'scopeConfig' => $this->_scopeConfig,
            ]
        );
    }

    public function testGetPayableTo()
    {
        $this->_object->setStore(1);
        $this->_scopeConfig->expects($this->once())
            ->method('getValue')
            ->with('payment/checkmo/payable_to', 'store', 1)
            ->willReturn('payable');
        $this->assertEquals('payable', $this->_object->getPayableTo());
    }

    public function testGetMailingAddress()
    {
        $this->_object->setStore(1);
        $this->_scopeConfig->expects($this->once())
            ->method('getValue')
            ->with('payment/checkmo/mailing_address', 'store', 1)
            ->willReturn('blah@blah.com');
        $this->assertEquals('blah@blah.com', $this->_object->getMailingAddress());
    }

    public function testAssignData()
    {
        $details['payable_to'] = 'payable';
        $details['mailing_address'] = 'blah@blah.com';
        $this->_object->setStore(1);
        $this->_scopeConfig->expects($this->any())
            ->method('getValue')
            ->willReturnMap([
                ['payment/checkmo/payable_to', 'store', 1, 'payable'],
                ['payment/checkmo/mailing_address', 'store', 1, 'blah@blah.com']
            ]);
        $instance = $this->getMock('Magento\Payment\Model\Info', ['setAdditionalData'], [], '', false);
        $instance->expects($this->once())
            ->method('setAdditionalData')
            ->with(serialize($details));
        $this->_object->setData('info_instance', $instance);
        $this->_object->assignData('');
    }
}
