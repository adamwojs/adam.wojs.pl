<?php

namespace AppBundle\QueryType;

use eZ\Publish\Core\QueryType\QueryType;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;

class LocationChildrenQueryType implements QueryType
{
    /**
     * @inheritdoc
     */
    public function getQuery(array $parameters = [])
    {
        $query = new Query();
        $query->filter = new Criterion\LogicalAnd([
            new Criterion\Visibility(Criterion\Visibility::VISIBLE),
            new Criterion\ParentLocationId($parameters['parent_location_id'])
        ]);
        $query->sortClauses = [
            new SortClause\DatePublished(Query::SORT_DESC)
        ];

        if (isset($parameters['limit'])) {
            $query->limit = $parameters['limit'];
        }

        return $query;
    }

    /**
     * @inheritdoc
     */
    public function getSupportedParameters()
    {
        return ['parent_location_id', 'limit'];
    }

    /**
     * @inheritdoc
     */
    public static function getName()
    {
        return 'LocationChildren';
    }
}
