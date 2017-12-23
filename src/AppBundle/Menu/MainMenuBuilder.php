<?php

namespace AppBundle\Menu;

use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use eZ\Publish\Core\Repository\SearchService;
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
    private $rootLocationId;

    /**
     * MainMenuBuilder constructor.
     *
     * @param FactoryInterface $factory
     * @param LocationService $locationService
     * @param SearchService $searchService
     * @param int $rootLocationId
     */
    public function __construct(
        FactoryInterface $factory,
        LocationService $locationService,
        SearchService $searchService,
        int $rootLocationId)
    {
        $this->factory = $factory;
        $this->searchService = $searchService;
        $this->locationService = $locationService;
        $this->rootLocationId = $rootLocationId;
        $this->logger = new NullLogger();
    }

    public function createMainMenu(array $options)
    {
        $menu = $this->factory->createItem('root');

        try {
            $menu->addChild($this->createLocationItem(
                $this->locationService->loadLocation($this->rootLocationId)
            ));

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
            new Criterion\ParentLocationId($this->rootLocationId)
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
