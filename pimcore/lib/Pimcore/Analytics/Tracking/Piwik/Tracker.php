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

namespace Pimcore\Analytics\Tracking\Piwik;

use Pimcore\Analytics\Tracking\AbstractTracker;
use Pimcore\Analytics\Tracking\Code\CodeBlock;
use Pimcore\Analytics\Tracking\Code\CodeContainer;
use Pimcore\Analytics\Tracking\SiteConfig\SiteConfig;
use Pimcore\Analytics\Tracking\SiteConfig\SiteConfigResolver;
use Pimcore\Config\Config;
use Pimcore\Event\Tracking\Piwik\TrackingDataEvent;
use Pimcore\Event\Tracking\PiwikEvents;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Templating\EngineInterface;

class Tracker extends AbstractTracker
{
    const BLOCK_BEFORE_SCRIPT_TAG = 'beforeScriptTag';
    const BLOCK_AFTER_SCRIPT_TAG = 'afterScriptTag';
    const BLOCK_BEFORE_SCRIPT = 'beforeScript';
    const BLOCK_AFTER_SCRIPT = 'afterScript';
    const BLOCK_BEFORE_ASYNC = 'beforeAsync';
    const BLOCK_AFTER_ASYNC = 'afterAsync';
    const BLOCK_ACTIONS = 'actions';

    /**
     * @var EventDispatcher
     */
    private $eventDispatcher;

    /**
     * @var EngineInterface
     */
    private $templatingEngine;

    /**
     * @var CodeContainer
     */
    private $codeContainer;

    /**
     * @var array
     */
    private $blocks = [
        self::BLOCK_BEFORE_SCRIPT_TAG,
        self::BLOCK_BEFORE_SCRIPT,
        self::BLOCK_ACTIONS,
        self::BLOCK_BEFORE_ASYNC,
        self::BLOCK_AFTER_ASYNC,
        self::BLOCK_AFTER_SCRIPT,
        self::BLOCK_AFTER_SCRIPT_TAG,
    ];

    public function __construct(
        SiteConfigResolver $siteConfigResolver,
        EventDispatcherInterface $eventDispatcher,
        EngineInterface $templatingEngine
    )
    {
        parent::__construct($siteConfigResolver);

        $this->eventDispatcher  = $eventDispatcher;
        $this->templatingEngine = $templatingEngine;
    }

    protected function getCodeContainer(): CodeContainer
    {
        if (null === $this->codeContainer) {
            $this->codeContainer = new CodeContainer($this->blocks, self::BLOCK_ACTIONS);
        }

        return $this->codeContainer;
    }

    protected function generateCode(SiteConfig $siteConfig)
    {
        $reportConfig = \Pimcore\Config::getReportConfig();
        if (!$reportConfig->piwik) {
            return null;
        }

        $configKey     = $siteConfig->getConfigKey();
        $config        = $reportConfig->piwik;
        $trackerConfig = $config->sites->$configKey;

        if (empty($config->piwik_url)) {
            return null;
        }

        if (!$trackerConfig || empty($trackerConfig->site_id)) {
            return null;
        }

        $data = [
            'site'          => $siteConfig->getSite(),
            'config'        => $config,
            'trackerConfig' => $trackerConfig,
            'site_id'       => $trackerConfig->site_id,
            'piwik_url'     => $config->piwik_url
        ];

        $blocks = $this->buildCodeBlocks($siteConfig, $config, $trackerConfig);

        $template = '@PimcoreCore/Analytics/Tracking/Piwik/trackingCode.html.twig';

        $event = new TrackingDataEvent($siteConfig, $data, $blocks, $config, $trackerConfig, $template);
        $this->eventDispatcher->dispatch(PiwikEvents::CODE_TRACKING_DATA, $event);

        return $this->renderTemplate($event);
    }

    private function renderTemplate(TrackingDataEvent $event): string
    {
        $data           = $event->getData();
        $data['blocks'] = $event->getBlocks();

        $code = $this->templatingEngine->render(
            $event->getTemplate(),
            $data
        );

        $code = trim($code);

        return $code;
    }

    private function buildCodeBlocks(SiteConfig $siteConfig, Config $config, Config $trackerConfig): array
    {
        $blocks = [];
        foreach ($this->blocks as $block) {
            $codeBlock = new CodeBlock();

            if (self::BLOCK_BEFORE_SCRIPT === $block && !empty($trackerConfig->code_before_init)) {
                $codeBlock->append($trackerConfig->code_before_init);
            }

            if (self::BLOCK_ACTIONS === $block) {
                if (!empty($trackerConfig->code_before_track)) {
                    $codeBlock->append($trackerConfig->code_before_track);
                }

                $codeBlock->append([
                    "_paq.push(['trackPageView']);",
                    "_paq.push(['enableLinkTracking']);",
                ]);

                if (!empty($trackerConfig->code_after_track)) {
                    $codeBlock->append($trackerConfig->code_after_track);
                }
            }

            if (self::BLOCK_BEFORE_ASYNC === $block) {
                $codeBlock->append([
                    "_paq.push(['setTrackerUrl', u+'piwik.php']);",
                    sprintf("_paq.push(['setSiteId', '%d']);", $trackerConfig->site_id)
                ]);
            }

            $this->getCodeContainer()->addToCodeBlock($siteConfig, $codeBlock, $block);

            $blocks[$block] = $codeBlock;
        }

        return $blocks;
    }
}