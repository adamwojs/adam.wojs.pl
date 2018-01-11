<?php

namespace AppBundle\Menu;

use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use eZ\Publish\API\Repository\SearchService;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

class MainMenuBuilder
{
    use LoggerAwareTrait;

    /**
     * @var \Knp\Menu\FactoryInterface
     */
    private $factory;

    /**
     * @var \eZ\Publish\Core\Repository\SearchService
     */
    private $searchService;

    /**
     * @var int[]
     */
    private $locations;

    /**
     * MainMenuBuilder constructor.
     *
     * @param \Knp\Menu\FactoryInterface $factory
     * @param \eZ\Publish\API\Repository\SearchService $searchService
     * @param array $locations
     */
    public function __construct(
        FactoryInterface $factory,
        SearchService $searchService,
        array $locations)
    {
        $this->factory = $factory;
        $this->searchService = $searchService;
        $this->locations = $locations;
        $this->logger = new NullLogger();
    }

    public function createMainMenu(array $options)
    {
        $menu = $this->factory->createItem('root');

        try {
            $searchResults = $this->searchService->findLocations($this->getMainMenuQuery());
            foreach($searchResults->searchHits as $searchHit) {
                $menu->addChild($this->createLocationItem($searchHit->valueObject));
            }
        } catch (\Exception $e) {
            $this->logger->error("Unable to create main menu: ".$e->getMessage());
        }

        return $menu;
    }

    protected function getMainMenuQuery(): LocationQuery
    {
        $query = new LocationQuery();
        $query->filter = new Criterion\LogicalAnd([
            new Criterion\Visibility(Criterion\Visibility::VISIBLE),
            new Criterion\LocationId($this->locations)
        ]);
        $query->sortClauses = [
            new SortClause\Location\Priority()
        ];

        return $query;
    }

    private function createLocationItem(Location $location): ItemInterface
    {
        return $this->factory->createItem($location->getContentInfo()->name, [
            'route' => 'ez_urlalias',
            'routeParameters' => [
                'locationId' => $location->id
            ]
        ]);
    }
}
