<?php

declare(strict_types=1);

namespace App\Enums;

enum SiteNavigationPlacement: string
{
    case Desktop = 'desktop';
    case Noscript = 'noscript';
    case Mobile = 'mobile';
    case FooterExplore = 'footer-explore';
    case FooterConnect = 'footer-connect';
    case Recovery = 'recovery';
}
