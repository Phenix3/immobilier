<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class ActiveClassExtension extends AbstractExtension
{

    public function getFunctions(): array
    {
        return [
            new TwigFunction('active_class', [$this, 'getActiveClass'], [
                'needs_context' => true
            ]),
        ];
    }

    public function getActiveClass(array $context, string $routeName, string $activeClass = 'active')
    {
        $request = $context['app']->getRequest();
        return $request->attributes->get('_route') === $routeName ? " {$activeClass} " : '';
    }
}
