<?php

declare(strict_types=1);

/*
 * Web-Tools Bundle for Contao Open Source CMS
 *
 * Copyright (c) 2022 pdir / digital agentur // pdir GmbH
 *
 * @package    webtools-bundle
 * @link       https://pdir.de/contao-webtools/
 * @license    LGPL-3.0-or-later
 * @author     Mathias Arzberger <develop@pdir.de>
 * @author     Christian Mette <develop@pdir.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pdir\ContaoWebtools\EventListener;

use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\System;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\RequestStack;
use Webmozart\PathUtil\Path;

/**
 * @Hook("initializeSystem")
 */
class InitializeSystemListener
{
    private $scripts;

    public function __construct(RequestStack $requestStack)
    {
        $this->scripts = $requestStack->getCurrentRequest()->get('scripts');
    }

    public function __invoke(): void
    {
        if (
            'true' === $_ENV['WEBTOOLS_ALLOW_PURGE'] &&
            null !== $this->scripts &&
            'purge' === $this->scripts
        ) {
            $container = System::getContainer();
            $projectDir = $container->getParameter('kernel.project_dir');
            //$strCachePath = StringUtil::stripRootDir($container->getParameter('kernel.cache_dir'));
            $targetPath = Path::join($projectDir, 'files', 'mate', 'sass');

            // build a Finder
            $finder = Finder::create()->in($targetPath)->files()->date('> now - 2 seconds');

            // check if there are any search results
            if ($finder->hasResults()) {
                $files = glob('assets/css/*.*');

                foreach ($files as $file) {
                    if (is_file($file)) {
                        unlink($file);
                    }
                }
            }
        }
    }
}
