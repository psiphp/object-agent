<?php

declare(strict_types=1);

namespace Psi\Component\ObjectAgent;

class Events
{
    const PRE_PERSIST = 'psi_object_agent.pre_persist';
    const POST_PERSIST = 'psi_object_agent.post_persist';

    const PRE_REMOVE = 'psi_object_agent.pre_remove';
    const POST_REMOVE = 'psi_object_agent.post_remove';
}
