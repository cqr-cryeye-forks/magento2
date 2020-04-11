<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Shipping\Test\Unit\Model\Order;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\OfflineShipping\Model\Carrier\Freeshipping;
use Magento\Sales\Model\Order\ShipmentRepository;
use Magento\Shipping\Model\CarrierFactory;
use Magento\Shipping\Model\Order\Track;
use PHPUnit\Framework\TestCase;

class TrackTest extends TestCase
{
    public function testLookup()
    {
        $helper = new ObjectManager($this);

        $carrier = $this->createPartialMock(
            Freeshipping::class,
            ['setStore', 'getTrackingInfo']
        );
        $carrier->expects($this->once())->method('setStore')->with('');
        $carrier->expects($this->once())->method('getTrackingInfo')->will($this->returnValue('trackingInfo'));

        $carrierFactory = $this->createPartialMock(CarrierFactory::class, ['create']);
        $carrierFactory->expects($this->once())->method('create')->will($this->returnValue($carrier));

        $shipment = $this->createMock(Freeshipping::class);

        $shipmentRepository = $this->createPartialMock(ShipmentRepository::class, ['get']);
        $shipmentRepository->expects($this->any())->method('get')->willReturn($shipment);

        /** @var Track $model */
        $model = $helper->getObject(
            Track::class,
            ['carrierFactory' => $carrierFactory, 'shipmentRepository' => $shipmentRepository]
        );
        $model->setParentId(1);
        $this->assertEquals('trackingInfo', $model->getNumberDetail());
    }
}
