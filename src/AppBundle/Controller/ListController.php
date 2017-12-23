<?php

namespace AppBundle\Controller;

use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\Core\MVC\Symfony\View\ContentView;
use eZ\Publish\Core\Pagination\Pagerfanta\ContentSearchHitAdapter;
use eZ\Publish\Core\QueryType\ContentViewQueryTypeMapper;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;

class ListController
{
    /**
     * @var \eZ\Publish\API\Repository\SearchService
     */
    private $searchService;

    /**
     * @var \eZ\Publish\Core\QueryType\ContentViewQueryTypeMapper
     */
    private $contentViewQueryTypeMapper;

    /**
     * ListController constructor.
     *
     * @param \eZ\Publish\API\Repository\SearchService $searchService
     * @param \eZ\Publish\Core\QueryType\ContentViewQueryTypeMapper $contentViewQueryTypeMapper
     */
    public function __construct(SearchService $searchService, ContentViewQueryTypeMapper $contentViewQueryTypeMapper)
    {
        $this->searchService = $searchService;
        $this->contentViewQueryTypeMapper = $contentViewQueryTypeMapper;
    }

    /**
     * Runs a content search with pagination support.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \eZ\Publish\Core\MVC\Symfony\View\ContentView $view
     * @return \eZ\Publish\Core\MVC\Symfony\View\ContentView
     */
    public function contentAction(Request $request, ContentView $view): ContentView
    {
        $query = $this->contentViewQueryTypeMapper->map($view);

        $searchResults = new Pagerfanta(
            new ContentSearchHitAdapter($query, $this->searchService)
        );
        $searchResults->setMaxPerPage($view->getParameter('page_limit'));
        $searchResults->setCurrentPage($request->get('page', 1));

        $view->addParameters([
            $view->getParameter('query')['assign_results_to'] => $searchResults,
        ]);

        return $view;
    }
}
