<?php

/*
 * This file is part of the TYPO3 responsive images project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read
 * LICENSE file that was distributed with this source code.
 *
 */

if (!defined('TYPO3_MODE')) {
    die('Access denied');
}

/** @var \TYPO3\CMS\Core\Resource\Rendering\RendererRegistry $rendererRegistry */
$rendererRegistry = \TYPO3\CMS\Core\Resource\Rendering\RendererRegistry::getInstance();
$rendererRegistry->registerRendererClass(\Codemonkey1988\ResponsiveImages\Resource\Rendering\ResponsiveImage::class);

// Add default configs
call_user_func(function () {
    $extConfig = \Codemonkey1988\ResponsiveImages\Utility\ConfigurationUtility::getExtensionConfig();
    $desktopWidth = $extConfig['maxDesktopImageWidth'];
    $tabletWidth = $extConfig['maxTabletImageWidth'];
    $smartphoneWidth = $extConfig['maxSmartphoneImageWidth'];

    /** @var \Codemonkey1988\ResponsiveImages\Resource\Service\PictureImageVariant $default */
    $default = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\Codemonkey1988\ResponsiveImages\Resource\Service\PictureImageVariant::class,
        'default');
    $default->setDefaultWidth($desktopWidth)
            ->addSourceConfig('(max-width: 40em)',
                [
                    '1x' => ['width' => $smartphoneWidth, 'quality' => 65],
                    '2x' => ['width' => $smartphoneWidth * 2, 'quality' => 40],
                ])
            ->addSourceConfig('(min-width: 64.0625em)',
                [
                    '1x' => ['width' => $desktopWidth],
                    '2x' => ['width' => $desktopWidth * 2, 'quality' => 80],
                ])
            ->addSourceConfig('(min-width: 40.0625em)',
                [
                    '1x' => ['width' => $tabletWidth, 'quality' => 80],
                    '2x' => ['width' => $tabletWidth * 2, 'quality' => 60],
                ]);

    /** @var \Codemonkey1988\ResponsiveImages\Resource\Service\PictureImageVariant $half */
    $half = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\Codemonkey1988\ResponsiveImages\Resource\Service\PictureImageVariant::class,
        'half');
    $half->setDefaultWidth($desktopWidth / 2)
         ->addSourceConfig('(max-width: 40em)',
             [
                 '1x' => ['width' => $smartphoneWidth, 'quality' => 65],
                 '2x' => ['width' => $smartphoneWidth * 2, 'quality' => 40],
             ])
         ->addSourceConfig('(min-width: 64.0625em)',
             [
                 '1x' => ['width' => $desktopWidth / 2],
                 '2x' => ['width' => $desktopWidth * 2 / 2, 'quality' => 80],
             ])
         ->addSourceConfig('(min-width: 40.0625em)',
             [
                 '1x' => ['width' => $tabletWidth / 2, 'quality' => 80],
                 '2x' => ['width' => $tabletWidth * 2 / 2, 'quality' => 60],
             ]);

    /** @var \Codemonkey1988\ResponsiveImages\Resource\Service\PictureImageVariant $third */
    $third = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\Codemonkey1988\ResponsiveImages\Resource\Service\PictureImageVariant::class,
        'third');
    $third->setDefaultWidth($desktopWidth / 3)
          ->addSourceConfig('(max-width: 40em)',
              [
                  '1x' => ['width' => $smartphoneWidth, 'quality' => 65],
                  '2x' => ['width' => $smartphoneWidth * 2, 'quality' => 40],
              ])
          ->addSourceConfig('(min-width: 64.0625em)',
              [
                  '1x' => ['width' => $desktopWidth / 3],
                  '2x' => ['width' => $desktopWidth * 2 / 3, 'quality' => 80],
              ])
          ->addSourceConfig('(min-width: 40.0625em)',
              [
                  '1x' => ['width' => $tabletWidth / 3, 'quality' => 80],
                  '2x' => ['width' => $tabletWidth * 2 / 3, 'quality' => 60],
              ]);

    /** @var \Codemonkey1988\ResponsiveImages\Resource\Service\PictureImageVariant $quarter */
    $quarter = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\Codemonkey1988\ResponsiveImages\Resource\Service\PictureImageVariant::class,
        'quarter');
    $quarter->setDefaultWidth($desktopWidth / 4)
            ->addSourceConfig('(max-width: 40em)',
                [
                    '1x' => ['width' => $smartphoneWidth, 'quality' => 65],
                    '2x' => ['width' => $smartphoneWidth * 2, 'quality' => 40],
                ])
            ->addSourceConfig('(min-width: 64.0625em)',
                [
                    '1x' => ['width' => $desktopWidth / 4],
                    '2x' => ['width' => $desktopWidth * 2 / 4, 'quality' => 80],
                ])
            ->addSourceConfig('(min-width: 40.0625em)',
                [
                    '1x' => ['width' => $tabletWidth / 4, 'quality' => 80],
                    '2x' => ['width' => $tabletWidth * 2 / 4, 'quality' => 60],
                ]);

    /** @var \Codemonkey1988\ResponsiveImages\Resource\Service\PictureImageVariant $two_thirds */
    $two_thirds = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\Codemonkey1988\ResponsiveImages\Resource\Service\PictureImageVariant::class,
        'two-thirds');
    $two_thirds->setDefaultWidth($desktopWidth / 0.66666)
               ->addSourceConfig('(max-width: 40em)',
                   [
                       '1x' => ['width' => $smartphoneWidth, 'quality' => 65],
                       '2x' => ['width' => $smartphoneWidth * 2, 'quality' => 40],
                   ])
               ->addSourceConfig('(min-width: 64.0625em)',
                   [
                       '1x' => ['width' => $desktopWidth / 0.66666],
                       '2x' => ['width' => $desktopWidth * 2 / 0.66666, 'quality' => 80],
                   ])
               ->addSourceConfig('(min-width: 40.0625em)',
                   [
                       '1x' => ['width' => $tabletWidth / 0.66666, 'quality' => 80],
                       '2x' => ['width' => $tabletWidth * 2 / 0.66666, 'quality' => 60],
                   ]);

    /** @var \Codemonkey1988\ResponsiveImages\Resource\Service\PictureImageVariant $three_quarters */
    $three_quarters = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\Codemonkey1988\ResponsiveImages\Resource\Service\PictureImageVariant::class,
        'three-quarters');
    $three_quarters->setDefaultWidth($desktopWidth / 0.75)
                   ->addSourceConfig('(max-width: 40em)',
                       [
                           '1x' => ['width' => $smartphoneWidth, 'quality' => 65],
                           '2x' => ['width' => $smartphoneWidth * 2, 'quality' => 40],
                       ])
                   ->addSourceConfig('(min-width: 64.0625em)',
                       [
                           '1x' => ['width' => $desktopWidth / 0.75],
                           '2x' => ['width' => $desktopWidth * 2 / 0.75, 'quality' => 80],
                       ])
                   ->addSourceConfig('(min-width: 40.0625em)',
                       [
                           '1x' => ['width' => $tabletWidth / 0.75, 'quality' => 80],
                           '2x' => ['width' => $tabletWidth * 2 / 0.75, 'quality' => 60],
                       ]);

    $registry = \Codemonkey1988\ResponsiveImages\Resource\Service\PictureVariantsRegistry::getInstance();
    $registry->registerImageVariant($default);
    $registry->registerImageVariant($half);
    $registry->registerImageVariant($third);
    $registry->registerImageVariant($quarter);
    $registry->registerImageVariant($two_thirds);
    $registry->registerImageVariant($three_quarters);
});
