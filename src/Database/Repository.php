<?php

namespace Tavurn\Database;

use Doctrine\ORM\EntityRepository;

/**
 * @template T
 */
class Repository extends EntityRepository
{
    /**
     * @return QueryBuilder<T>
     */
    public function createQueryBuilder($alias, $indexBy = null): QueryBuilder
    {
        return (new QueryBuilder($this->_em))
            ->select($alias)
            ->from($this->_entityName, $alias, $indexBy);
    }
}
