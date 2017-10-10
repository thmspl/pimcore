<?php

declare(strict_types=1);

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Enterprise License (PEL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 * @license    http://www.pimcore.org/license     GPLv3 and PEL
 */

namespace Pimcore\Event\Tracking\Piwik;

use Pimcore\Config\Config;
use Pimcore\Model\Site;
use Symfony\Component\EventDispatcher\Event;

class TrackingDataEvent extends Event
{
    /**
     * @var array
     */
    private $data;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Config
     */
    private $siteConfig;

    /**
     * @var Site|null
     */
    private $site;

    public function __construct(array $data, Config $config, Config $siteConfig, Site $site = null)
    {
        $this->data       = $data;
        $this->config     = $config;
        $this->siteConfig = $siteConfig;
        $this->site       = $site;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data)
    {
        $this->data = $data;
    }

    public function getConfig(): Config
    {
        return $this->config;
    }

    public function getSiteConfig(): Config
    {
        return $this->siteConfig;
    }

    /**
     * @return null|Site
     */
    public function getSite()
    {
        return $this->site;
    }
}
