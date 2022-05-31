<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AppExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return array(
            'json_decode' => new TwigFilter('json_decode', [$this,'jsonDecode']),
        );
    }
    public function jsonDecode($str)
    {
        return json_decode($str);
    }
}
