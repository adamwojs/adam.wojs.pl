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
    public function doGetQuery(array $parameters): Query
    {
        $criteria = [
            new Criterion\Visibility(Criterion\Visibility::VISIBLE)
        ];

        $criteria[] = $this->createParentCriterion($parameters['parent_location']);
        if (isset($parameters['content_type'])) {
            $criteria[] = new Criterion\ContentTypeIdentifier($parameters['content_type']);
        }

        $query = new Query();
        $query->filter = new Criterion\LogicalAnd($criteria);
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
    protected function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefined(['parent_location', 'limit', 'content_type']);
        $resolver->setRequired(['parent_location']);
        $resolver->setAllowedTypes('parent_location', ['int', 'string']);
        $resolver->setAllowedTypes('limit', 'int');
        $resolver->setAllowedTypes('content_type', 'string');
    }

    /**
     * @inheritdoc
     */
    public static function getName(): string
    {
        return 'LocationChildren';
    }

    /**
     * Creates a parent location criterion.
     *
     * @param int|string $parentLocation
     * @return \eZ\Publish\API\Repository\Values\Content\Query\Criterion
     */
    private function createParentCriterion($parentLocation): Criterion
    {
        if ($this->isPathString($parentLocation)) {
            return new Criterion\Subtree($parentLocation);
        }

        return new Criterion\ParentLocationId($parentLocation);
    }

    /**
     * Checks if $value is a path string.
     *
     * @param int|string $value
     * @return bool
     */
    private function isPathString($value): bool
    {
        /** @see \eZ\Publish\API\Repository\Values\Content\Query\Criterion\Subtree::__construct */
        return preg_match('/^(\/\w+)+\/$/', (string) $value) === 1;
    }
}
