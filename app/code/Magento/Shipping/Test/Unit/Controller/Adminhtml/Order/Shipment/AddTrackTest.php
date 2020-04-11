<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Shipping\Test\Unit\Controller\Adminhtml\Order\Shipment;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\ViewInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\View\Element\BlockInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Framework\View\Page\Config;
use Magento\Framework\View\Page\Title;
use Magento\Framework\View\Result\Page;
use Magento\Sales\Api\Data\ShipmentTrackInterfaceFactory;
use Magento\Sales\Model\Order\Shipment;
use Magento\Sales\Model\Order\Shipment\Track;
use Magento\Shipping\Controller\Adminhtml\Order\Shipment\AddTrack;
use Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class AddTrackTest covers AddTrack controller.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AddTrackTest extends TestCase
{
    /**
     * @var ShipmentLoader|MockObject
     */
    private $shipmentLoader;

    /**
     * @var AddTrack
     */
    private $controller;

    /**
     * @var Context|MockObject
     */
    private $context;

    /**
     * @var Http|MockObject
     */
    private $request;

    /**
     * @var ResponseInterface|MockObject
     */
    private $response;

    /**
     * @var  ViewInterface|MockObject
     */
    private $view;

    /**
     * @var Page|MockObject
     */
    private $resultPageMock;

    /**
     * @var Config|MockObject
     */
    private $pageConfigMock;

    /**
     * @var Title|MockObject
     */
    private $pageTitleMock;

    /**
     * @var ShipmentTrackInterfaceFactory|MockObject
     */
    private $trackFactory;

    /**
     * @var ResultInterface|MockObject
     */
    private $rawResult;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->shipmentLoader = $this->getMockBuilder(
            ShipmentLoader::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['setShipmentId', 'setOrderId', 'setShipment', 'setTracking', 'load'])
            ->getMock();
        $this->context = $this->createPartialMock(
            Context::class,
            [
                'getRequest',
                'getResponse',
                'getRedirect',
                'getObjectManager',
                'getTitle',
                'getView',
                'getResultFactory'
            ]
        );
        $this->response = $this->createPartialMock(
            ResponseInterface::class,
            ['setRedirect', 'sendResponse', 'setBody']
        );
        $this->request = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()->getMock();
        $this->view = $this->createMock(ViewInterface::class);
        $this->resultPageMock = $this->getMockBuilder(Page::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->pageConfigMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->pageTitleMock = $this->getMockBuilder(Title::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->trackFactory = $this->getMockBuilder(ShipmentTrackInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMockForAbstractClass();
        $this->rawResult = $this->getMockBuilder(ResultInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['setContents'])
            ->getMockForAbstractClass();
        $resultFactory = $this->getMockBuilder(ResultFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMockForAbstractClass();
        $this->context->expects($this->once())
            ->method('getRequest')
            ->will($this->returnValue($this->request));
        $this->context->expects($this->once())
            ->method('getResponse')
            ->will($this->returnValue($this->response));
        $this->context->expects($this->once())
            ->method('getView')
            ->will($this->returnValue($this->view));
        $resultFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->rawResult);
        $this->context->expects($this->once())
            ->method('getResultFactory')
            ->willReturn($resultFactory);
        $this->controller = $objectManagerHelper->getObject(
            AddTrack::class,
            [
                'context' => $this->context,
                'shipmentLoader' => $this->shipmentLoader,
                'request' => $this->request,
                'response' => $this->response,
                'view' => $this->view,
                'trackFactory' => $this->trackFactory,
            ]
        );
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testExecute()
    {
        $carrier = 'carrier';
        $number = 'number';
        $title = 'title';
        $shipmentId = 1000012;
        $orderId = 10003;
        $tracking = [];
        $shipmentData = ['items' => [], 'send_email' => ''];
        $shipment = $this->createPartialMock(
            Shipment::class,
            ['addTrack', '__wakeup', 'save']
        );
        $this->request->expects($this->any())
            ->method('getParam')
            ->will(
                $this->returnValueMap(
                    [
                        ['order_id', null, $orderId],
                        ['shipment_id', null, $shipmentId],
                        ['shipment', null, $shipmentData],
                        ['tracking', null, $tracking],
                    ]
                )
            );
        $this->request->expects($this->any())
            ->method('getPost')
            ->will(
                $this->returnValueMap(
                    [
                        ['carrier', null, $carrier],
                        ['number', null, $number],
                        ['title', null, $title],
                    ]
                )
            );
        $this->shipmentLoader->expects($this->any())
            ->method('setShipmentId')
            ->with($shipmentId);
        $this->shipmentLoader->expects($this->any())
            ->method('setOrderId')
            ->with($orderId);
        $this->shipmentLoader->expects($this->any())
            ->method('setShipment')
            ->with($shipmentData);
        $this->shipmentLoader->expects($this->any())
            ->method('setTracking')
            ->with($tracking);
        $this->shipmentLoader->expects($this->once())
            ->method('load')
            ->will($this->returnValue($shipment));
        $track = $this->getMockBuilder(Track::class)
            ->disableOriginalConstructor()
            ->setMethods(['__wakeup', 'setNumber', 'setCarrierCode', 'setTitle'])
            ->getMock();
        $this->trackFactory->expects($this->once())
            ->method('create')
            ->willReturn($track);
        $track->expects($this->once())
            ->method('setNumber')
            ->with($number)
            ->will($this->returnSelf());
        $track->expects($this->once())
            ->method('setCarrierCode')
            ->with($carrier)
            ->will($this->returnSelf());
        $track->expects($this->once())
            ->method('setTitle')
            ->with($title)
            ->will($this->returnSelf());
        $this->view->expects($this->once())
            ->method('loadLayout')
            ->will($this->returnSelf());
        $layout = $this->createMock(LayoutInterface::class);
        $menuBlock = $this->createPartialMock(BlockInterface::class, ['toHtml']);
        $html = 'html string';
        $this->view->expects($this->once())
            ->method('getLayout')
            ->will($this->returnValue($layout));
        $layout->expects($this->once())
            ->method('getBlock')
            ->with('shipment_tracking')
            ->will($this->returnValue($menuBlock));
        $menuBlock->expects($this->once())
            ->method('toHtml')
            ->will($this->returnValue($html));
        $shipment->expects($this->once())
            ->method('addTrack')
            ->with($this->equalTo($track))
            ->will($this->returnSelf());
        $shipment->expects($this->any())
            ->method('save')
            ->will($this->returnSelf());
        $this->view->expects($this->any())
            ->method('getPage')
            ->willReturn($this->resultPageMock);
        $this->resultPageMock->expects($this->any())
            ->method('getConfig')
            ->willReturn($this->pageConfigMock);
        $this->pageConfigMock->expects($this->any())
            ->method('getTitle')
            ->willReturn($this->pageTitleMock);
        $this->rawResult->expects($this->once())
            ->method('setContents')
            ->with($html)
            ->willReturnSelf();
        $this->assertInstanceOf(ResultInterface::class, $this->controller->execute());
    }
}
