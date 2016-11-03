Object Agent
============

What is it?
-----------

The ObjectAgent component offers a simple object manager (ODM/ORM) abstraction. This
allows components to be developed which are agnostic to the actual
object-management system in question.

The abstraction allows objects to be retrieved, saved, removed, found and
queried for. We call the abstraction an **agent**.

Agents can also be "found" for any given class using the ``AgentFinder``.

Usage
-----

Basic usage is as follows:

.. code-block:: php

    <?php

    use Psi\Bridge\ObjectAgent\Doctrine\PhpcrOdm\PhpcrOdmAgent;
    use Psi\Bridge\ObjectAgent\Doctrine\Orm\OrmAgent;

    // instantiate the agents
    $phpcrAgent = new PhpcrOdmAgent($documentManager);
    $ormAgent = new OrmAgent($entityManager);

    $agentFinder = new AgentFinder([
        'agent-one' => $phpcrAgent,
        'agent-two' => $ormAgent,
    ]);

    // try and find an agent which knows about SomeRandomClass
    $agent = $agentFinder->findAgentFor(SomeRandomClass::class);
    // or by name
    $agnet = $agnetFinder->getAgent('agent-one');

    // use the agent
    $object = $agent->find(1234, SomeRandomClass::class);
    $agent->save($object);
    $agent->remove($object);


Events
------

TODO


