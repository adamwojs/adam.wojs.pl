<?php

namespace AppBundle\Menu;

use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use eZ\Publish\Core\Repository\SearchService;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

class BlogCategoriesMenuBuilder
{
    use LoggerAwareTrait;

    /**
     * @var \Knp\Menu\FactoryInterface
     */
    private $factory;

    /**
     * @var \eZ\Publish\API\Repository\LocationService
     */
    private $locationService;

    /**
     * @var \eZ\Publish\Core\Repository\SearchService
     */
    private $searchService;

    /**
     * @var int
     */
    private $blogLocationId;

    /**
     * BlogCategoriesMenu constructor.
     *
     * @param \Knp\Menu\FactoryInterface $factory
     * @param \eZ\Publish\API\Repository\LocationService $locationService
     * @param \eZ\Publish\Core\Repository\SearchService $searchService
     * @param int $blogLocationId
     */
    public function __construct(FactoryInterface $factory, LocationService $locationService, SearchService $searchService, int $blogLocationId)
    {
        $this->factory = $factory;
        $this->searchService = $searchService;
        $this->locationService = $locationService;
        $this->blogLocationId = $blogLocationId;
        $this->logger = new NullLogger();
    }

    public function createCategoriesMenu(array $options)
    {
        $menu = $this->factory->createItem('root');

        try {
            $map = [$this->blogLocationId => $menu];

            $searchResults = $this->searchService->findLocations($this->getCategoriesMenuQuery());
            foreach ($searchResults->searchHits as $searchHit) {
                /** @var Location $location */
                $location = $searchHit->valueObject;

                $map[$location->id] = $this->createLocationItem($location);
                $map[$location->parentLocationId]->addChild($map[$location->id]);
            }
        } catch (\Exception $e) {
            $this->logger->error("Unable to create blog categories menu: " . $e->getMessage());
        }

        return $menu;
    }

    private function getCategoriesMenuQuery(): LocationQuery
    {
        $blogLocation = $this->locationService->loadLocation($this->blogLocationId);

        $query = new LocationQuery();
        $query->filter = new Criterion\LogicalAnd([
            new Criterion\Visibility(Criterion\Visibility::VISIBLE),
            new Criterion\ContentTypeIdentifier('folder'),
            new Criterion\Subtree($blogLocation->pathString),
            new Criterion\Location\Depth(Operator::GT, $blogLocation->depth),
        ]);
        $query->sortClauses = [
            new SortClause\Location\Priority()
        ];

        return $query;

    }

    private function createLocationItem(Location $location): ItemInterface
    {
        return $this->factory->createItem('location-' . $location->id, [
            'label' => $location->getContentInfo()->name,
            'route' => 'ez_urlalias',
            'routeParameters' => [
                'locationId' => $location->id
            ]
        ]);
    }
}
