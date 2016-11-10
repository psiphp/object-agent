<?php

namespace Psi\Component\ObjectAgent\Tests;

use Psi\Component\ObjectAgent\AgentFinder;
use Psi\Component\ObjectAgent\AgentInterface;

class AgentFinderTest extends \PHPUnit_Framework_TestCase
{
    private $finder;

    public function setUp()
    {
        $this->agent1 = $this->prophesize(AgentInterface::class);
        $this->agent2 = $this->prophesize(AgentInterface::class);
        $this->finder = new AgentFinder([
            'one' => $this->agent1->reveal(),
            'two' => $this->agent2->reveal(),
        ]);
    }

    /**
     * It should return an agent that supports the given object class.
     */
    public function testFindAgent()
    {
        $this->agent1->supports(\stdClass::class)->willReturn(false);
        $this->agent2->supports(\stdClass::class)->willReturn(true);

        $agent = $this->finder->findFor(\stdClass::class);
        $this->assertSame($this->agent2->reveal(), $agent);
    }

    /**
     * It should throw an exception if no agents support the given class.
     *
     * @expectedException Psi\Component\ObjectAgent\Exception\AgentNotFoundException
     * @expectedExceptionMessage Could not find an agent supporting class "stdClass".
     */
    public function testFindAgentNotFound()
    {
        $this->agent1->supports(\stdClass::class)->willReturn(false);
        $this->agent2->supports(\stdClass::class)->willReturn(false);

        $agent = $this->finder->findFor(\stdClass::class);
        $this->assertSame($this->agent2->reveal(), $agent);
    }

    /**
     * It should get an agent by name.
     */
    public function testGetAgentByName()
    {
        $agent = $this->finder->get('one');
        $this->assertSame($this->agent1->reveal(), $agent);
    }

    /**
     * It should throw an exception if the agent is not found.
     *
     * @expectedException \Psi\Component\ObjectAgent\Exception\AgentNotFoundException
     * @expectedExceptionMessage Could not find an agent named "three". Registered agent names: "one", "two"
     */
    public function testGetAgentNotFound()
    {
        $this->finder->get('three');
    }

    /**
     * It should return the registered name for a given agent.
     */
    public function testGetName()
    {
        $name = $this->finder->getName($this->agent1->reveal());
        $this->assertEquals('one', $name);
    }

    /**
     * It should throw an exception if it could not determine the name of an agent.
     *
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Could not identify agent of class "
     */
    public function testGetNameUnknownAgent()
    {
        $agent = $this->prophesize(AgentInterface::class);
        $this->finder->getName($agent->reveal());
    }
}
