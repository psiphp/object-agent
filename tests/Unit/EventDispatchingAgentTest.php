<?php

declare(strict_types=1);

namespace Psi\Component\ObjectAgent\Tests\Unit;

use Psi\Component\ObjectAgent\AgentInterface;
use Psi\Component\ObjectAgent\Capabilities;
use Psi\Component\ObjectAgent\Event\ObjectEvent;
use Psi\Component\ObjectAgent\EventDispatchingAgent;
use Psi\Component\ObjectAgent\Events;
use Psi\Component\ObjectAgent\Query\Query;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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
     * It should return the inner agent's capabilities.
     */
    public function testDelegateCapabilities()
    {
        $this->innerAgent->getCapabilities()->willReturn(Capabilities::create([]));

        return $this->agent->getCapabilities();
    }

    /**
     * It should delegate find.
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
     * It should delegate find many.
     */
    public function testDelegateFindMany()
    {
        $ids = ['abc', 'bcd'];
        $class = \stdClass::class;
        $this->innerAgent->findMany($ids, $class)->willReturn([
            $object1 = new \stdClass(),
            $object2 = new \stdClass(),
        ]);

        $result = $this->agent->findMany($ids, $class);
        $this->assertSame([
            $object1, $object2,
        ], $result);
    }

    /**
     * It should delegate remove.
     */
    public function testDelegateDelete()
    {
        $object = new \stdClass();
        $this->innerAgent->remove($object)->shouldBeCalled();
        $this->agent->remove($object);
    }

    /**
     * It should delegate getIdentifier.
     */
    public function testDelegateGetIdentifier()
    {
        $object = new \stdClass();
        $this->innerAgent->getIdentifier($object)->willReturn('123');
        $result = $this->agent->getIdentifier($object);
        $this->assertEquals('123', $result);
    }

    /**
     * It should delegate "supports".
     */
    public function testDelegateSupports()
    {
        $this->innerAgent->supports(\stdClass::class)->willReturn(true);
        $result = $this->agent->supports(\stdClass::class);
        $this->assertTrue($result);
    }

    /**
     * It should delegate setParent.
     */
    public function testDelegateSetParent()
    {
        $object = new \stdClass();
        $parent = new \stdClass();

        $this->innerAgent->setParent($object, $parent)->shouldBeCalled();
        $this->agent->setParent($object, $parent);
    }

    /**
     * It should delegate query.
     */
    public function testDelegateQuery()
    {
        $query = Query::create(\stdClass::class, [
            'criteria' => Query::comparison('eq', 'bar', 1)
        ]);
        $this->innerAgent->query($query)->shouldBeCalled();

        $this->agent->query($query);
    }

    /**
     * It should dispatch events for persist.
     */
    public function testPersistDispatch()
    {
        $object = new \stdClass();
        $this->expectObjectEvent('psi_object_agent.pre_persist', $object);
        $this->innerAgent->persist($object)->shouldBeCalled();
        $this->expectObjectEvent('psi_object_agent.post_persist', $object);

        $this->agent->persist($object);
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
