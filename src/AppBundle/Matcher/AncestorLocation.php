<?php

namespace AppBundle\Matcher;

use eZ\Publish\Core\MVC\Symfony\Matcher\ViewMatcherInterface;
use eZ\Publish\Core\MVC\Symfony\View\LocationValueView;
use eZ\Publish\Core\MVC\Symfony\View\View;

class AncestorLocation implements ViewMatcherInterface
{
    /**
     * @var array
     */
    private $values = [];

    /**
     * @inheritdoc
     */
    public function setMatchingConfig($matchingConfig): void
    {
        if (!is_array($matchingConfig)) {
            $matchingConfig = [ $matchingConfig ];
        }

        $this->values = $matchingConfig;
    }

    /**
     * @inheritdoc
     */
    public function match(View $view): bool
    {
        if (!$view instanceof LocationValueView) {
            return false;
        }

        foreach($view->getLocation()->path as $locationId) {
            if (in_array($locationId, $this->values)) {
                return true;
            }
        }

        return false;
    }
}
