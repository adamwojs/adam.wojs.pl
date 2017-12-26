<?php

namespace AppBundle\Menu\Voter;

use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\Core\MVC\Symfony\View\ContentView;
use Knp\Menu\ItemInterface;
use Knp\Menu\Matcher\Voter\VoterInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class LocationVoter implements VoterInterface
{
    private const EZURLALIAS_ROUTE_NAME = 'ez_urlalias';

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var LocationService
     */
    private $locationService;

    /**
     * LocationVoter constructor.
     *
     * @param RequestStack $requestStack
     * @param LocationService $locationService
     */
    public function __construct(RequestStack $requestStack, LocationService $locationService)
    {
        $this->requestStack = $requestStack;
        $this->locationService = $locationService;
    }

    /**
     * @inheritdoc
     */
    public function matchItem(ItemInterface $item)
    {
        $request = $this->requestStack->getCurrentRequest();

        $contentView = $request->attributes->get('view');
        if (!$contentView instanceof ContentView) {
            return null;
        }

        /** @var Location $currentLocation */
        $currentLocation = $contentView->getLocation();
        foreach($item->getExtra('routes', []) as $route) {
            if (isset($route['route']) && $route['route'] === self::EZURLALIAS_ROUTE_NAME) {
                /** @var Location $routeLocation */
                $routeLocation = $this->locationService->loadLocation(
                    $route['parameters']['locationId']
                );

                if ($routeLocation->depth === 1) {
                    return $routeLocation->id === $currentLocation->id;
                }

                return in_array($routeLocation->id, $currentLocation->path);
            }
        }

        return null;
    }
}
