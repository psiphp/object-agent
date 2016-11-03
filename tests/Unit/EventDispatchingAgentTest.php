<?php

namespace Psi\Component\ObjectAgent\Tests\Unit;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Psi\Component\ObjectAgent\AgentInterface;
use Psi\Component\ObjectAgent\EventDispatchingAgent;
use Psi\Component\ObjectAgent\Events;
use Psi\Component\ObjectAgent\Event\ObjectEvent;
use Psi\Component\ObjectAgent\Query\Query;

class EventDispatchingAgentTest extends \PHPUnit_Framework_TestCase
{
    private $dispatcher;
    private $agent;

    public function setUp()
    {
        $this->dispatcher = $this->prophesize(EventDispatcherInterface::class);
        $this->innerAgent = $this->prophesize(AgentInterface::class);
        $this->agent = new EventDispatchingAgent(
            $this->innerAgent->reveal(),
            $this->dispatcher->reveal()
        );
    }

    /**
     * It should delegate find
     */
    public function testDelegateFind()
    {
        $id = 'abc';
        $class = \stdClass::class;
        $object = new \stdClass();
        $this->innerAgent->find($id, $class)->willReturn($object);

        $result = $this->agent->find($id, $class);
        $this->assertSame($object, $result);
    }

    /**
     * It should delegate delete
     */
    public function testDelegateDelete()
    {
        $object = new \stdClass();
        $this->innerAgent->delete($object)->shouldBeCalled();
        $this->agent->delete($object);
    }

    /**
     * It should delegate getIdentifier
     */
    public function testDelegateGetIdentifier()
    {
        $object = new \stdClass();
        $this->innerAgent->getIdentifier($object)->willReturn('123');
        $result = $this->agent->getIdentifier($object);
        $this->assertEquals('123', $result);
    }

    /**
     * It should delegate "supports"
     */
    public function testDelegateSupports()
    {
        $this->innerAgent->supports(\stdClass::class)->willReturn(true);
        $result = $this->agent->supports(\stdClass::class);
        $this->assertTrue($result);
    }

    /**
     * It should delegate setParent
     */
    public function testDelegateSetParent()
    {
        $object = new \stdClass();
        $parent = new \stdClass();

        $this->innerAgent->setParent($object, $parent)->shouldBeCalled();
        $this->agent->setParent($object, $parent);
    }

    /**
     * It should delegate query
     */
    public function testDelegateQuery()
    {
        $query = Query::create(\stdClass::class, Query::comparison('eq', 'bar', 1));
        $this->innerAgent->query($query)->shouldBeCalled();

        $this->agent->query($query);
    }

    /**
     * It should dispatch events for save.
     */
    public function testSaveDispatch()
    {
        $object = new \stdClass();
        $this->expectObjectEvent('psi_object_agent.pre_save', $object);
        $this->innerAgent->save($object)->shouldBeCalled();
        $this->expectObjectEvent('psi_object_agent.post_save', $object);

        $this->agent->save($object);
    }

    private function expectObjectEvent($eventName, $object)
    {
        $this->dispatcher->dispatch(
            $eventName,
            new ObjectEvent(
                $this->innerAgent->reveal(),
                $object
            )
        )->shouldBeCalled();
    }
}
