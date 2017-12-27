<?php

namespace AppBundle\Controller;

use AppBundle\Form\Type\BlogSearchType;
use eZ\Bundle\EzPublishCoreBundle\Controller;
use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\Core\Pagination\Pagerfanta\ContentSearchHitAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class BlogSearchController extends Controller
{
    /**
     * @var \eZ\Publish\API\Repository\SearchService
     */
    private $searchService;

    /**
     * @var \eZ\Publish\API\Repository\LocationService
     */
    private $locationService;

    /**
     * @var int
     */
    private $blogLocationId;

    /**
     * BlogSearchController constructor.
     *
     * @param \eZ\Publish\API\Repository\SearchService $searchService
     * @param \eZ\Publish\API\Repository\LocationService $locationService
     * @param int $blogLocationId
     */
    public function __construct(SearchService $searchService, LocationService $locationService, int $blogLocationId)
    {
        $this->searchService = $searchService;
        $this->locationService = $locationService;
        $this->blogLocationId = $blogLocationId;
    }

    /**
     * Render a search form.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function formAction(): Response
    {
        $form = $this->createSearchForm();

        return $this->render(':blog:search_form.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Handles search form submission.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function searchAction(Request $request): Response
    {
        $query = new Query([
            'filter' => new Criterion\MatchNone()
        ]);

        $form = $this->createSearchForm();
        $form->handleRequest($request);
        if ($form->isValid()) {
            $query = $this->createSearchQuery($form['query']->getData());
        }

        $results = new Pagerfanta(
            new ContentSearchHitAdapter($query, $this->searchService)
        );
        $results->setMaxPerPage(10);
        $results->setCurrentPage($request->get('page', 1));

        return $this->render(':blog:search_results.html.twig', [
            'form' => $form->createView(),
            'results' => $results
        ]);
    }

    /**
     * Build a search query.
     *
     * @param string $searchString
     * @return \eZ\Publish\API\Repository\Values\Content\Query
     */
    private function createSearchQuery(string $searchString): Query
    {
        $blogLocation = $this->locationService->loadLocation($this->blogLocationId);

        $query = new Query();
        $query->filter = new Criterion\LogicalAnd([
            new Criterion\Subtree($blogLocation->pathString),
            new Criterion\FullText($searchString),
            new Criterion\Visibility(Criterion\Visibility::VISIBLE)
        ]);

        return $query;
    }

    /**
     * Creates a search form.
     *
     * @param array|null $data
     * @return \Symfony\Component\Form\FormInterface
     */
    private function createSearchForm(array $data = null): FormInterface
    {
        return $this->createForm(BlogSearchType::class, $data, [
            'action' => $this->generateUrl('blog_search'),
            'method' => Request::METHOD_GET,
            'csrf_protection' => false,
        ]);
    }
}
