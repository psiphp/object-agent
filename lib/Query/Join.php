<?php

declare(strict_types=1);

namespace Psi\Component\ObjectAgent\Query;

class Join
{
    const INNER_JOIN    = 'INNER';
    const LEFT_JOIN     = 'LEFT';

    private static $validTypes = [
        self::INNER_JOIN,
        self::LEFT_JOIN
    ];

    /**
     * @var string
     */
    private $join;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $alias;

    public function __construct(string $join, string $alias, string $type = self::INNER_JOIN)
    {
        if (!in_array($type, self::$validTypes)) {
            throw new \InvalidArgumentException(sprintf(
                'Unknown join type "%s". Known joins: "%s"',
                $type,
                implode('", "', self::$validTypes)
            ));
        }

        $this->type = $type;
        $this->alias = $alias;
        $this->join = $join;
    }

    public function getType() 
    {
        return $this->type;
    }

    public function getAlias() 
    {
        return $this->alias;
    }

    public function getJoin() 
    {
        return $this->join;
    }
}
