<?php

namespace AppBundle\QueryType;

use eZ\Publish\Core\QueryType\OptionsResolverBasedQueryType;
use eZ\Publish\Core\QueryType\QueryType;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LocationChildrenQueryType extends OptionsResolverBasedQueryType implements QueryType
{
    /**
     * @inheritdoc
     */
    public function doGetQuery(array $parameters)
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
    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined(['parent_location_id', 'limit']);
        $resolver->setRequired(['parent_location_id']);
        $resolver->setAllowedTypes('parent_location_id', 'int');
        $resolver->setAllowedTypes('limit', 'int');
    }

    /**
     * @inheritdoc
     */
    public static function getName()
    {
        return 'LocationChildren';
    }
}
