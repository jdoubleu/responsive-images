<?php

namespace Codemonkey1988\ResponsiveImages\Resource\Rendering;

/***************************************************************
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use Codemonkey1988\ResponsiveImages\Resource\Rendering\TagRenderer\ImgTagRenderer;
use Codemonkey1988\ResponsiveImages\Resource\Rendering\TagRenderer\PictureTagRenderer;
use Codemonkey1988\ResponsiveImages\Resource\Rendering\TagRenderer\SourceTagRenderer;
use Codemonkey1988\ResponsiveImages\Resource\Service\PictureVariantsRegistry;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Resource\ProcessedFile;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Service\ImageService;
use TYPO3\CMS\Core\Resource\Rendering\FileRendererInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Service\EnvironmentService;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;

/**
 * Class PictureTagRenderer
 *
 * @package    Codemonkey1988\ResponsiveImages
 * @subpackage Resource\Rendering
 * @author     Tim Schreiner <schreiner.tim@gmail.com>
 */
class ResponsiveImage implements FileRendererInterface
{
    const DEFAULT_IMAGE_VARIANT_KEY = 'default';
    const REGISTER_IMAGE_VARIANT_KEY = 'IMAGE_VARIANT_KEY';

    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var array
     */
    protected $possibleMimeTypes = ['image/jpeg', 'image/jpg', 'image/gif', 'image/png'];

    /**
     * @return int
     */
    public function getPriority()
    {
        return 5;
    }

    /**
     * @param FileInterface $file
     * @return bool
     */
    public function canRender(FileInterface $file)
    {
        /** @var EnvironmentService $evironmentService */
        $evironmentService = GeneralUtility::makeInstance(EnvironmentService::class);
        $registry          = PictureVariantsRegistry::getInstance();

        return $evironmentService->isEnvironmentInFrontendMode()
            && $registry->imageVariantKeyExists(self::DEFAULT_IMAGE_VARIANT_KEY)
            && in_array($file->getMimeType(), $this->possibleMimeTypes, true);
    }

    /**
     * Renders a responsive image tag.
     *
     * @param FileInterface $file
     * @param int|string    $width
     * @param int|string    $height
     * @param array         $options
     * @param bool          $usedPathsRelativeToCurrentScript
     * @return string
     */
    public function render(FileInterface $file, $width, $height, array $options = [], $usedPathsRelativeToCurrentScript = false)
    {
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        // Check if a responsive image tag should be rendered. If not, just return the normal image tag.
        if (isset($options['disablePictureTag']) && $options['disablePictureTag'] == true) {
            return $this->generateImgTag($file, $width, $height, $options);
        } else {
            return $this->generatePictureTag($file, $width, $height, $options);
        }
    }

    /**
     * Generates a normal img-tag.
     *
     * @param FileInterface $file
     * @param int|string    $width
     * @param int|string    $height
     * @param array         $options
     * @return string
     */
    protected function generateImgTag(FileInterface $file, $width, $height, array $options = [])
    {
        $allowedAdditionalAttributes = ['alt', 'title', 'class', 'id', 'lang', 'style', 'accesskey', 'tabindex', 'onclick'];
        $additionalParameters        = '';

        if (isset($options['grayscale']) && $options['grayscale'] == true) {
            $additionalParameters .= ' -colorspace Gray';
        }

        $processedImage = $this->processImage($file, $width, $height, $additionalParameters);

        /** @var ImgTagRenderer $tagRenderer */
        $tagRenderer = $this->objectManager->get(ImgTagRenderer::class);
        $tagRenderer->initialize();

        $tagRenderer->addAttribute('width', $processedImage->getProperty('width'));
        $tagRenderer->addAttribute('height', $processedImage->getProperty('height'));
        $tagRenderer->addAttribute('src', $this->getImageUri($processedImage));

        if ($file->getProperty('alternative')) {
            $tagRenderer->addAttribute('alt', $file->getProperty('alternative'));
        }
        if ($file->getProperty('title')) {
            $tagRenderer->addAttribute('title', $file->getProperty('title'));
        }

        if (isset($options['additionalAttributes']) && is_array($options['additionalAttributes'])) {
            foreach ($options['additionalAttributes'] as $attrName => $attrValue) {
                if ($attrValue) {
                    $tagRenderer->addAttribute($attrName, $attrValue);
                }
            }
        }

        if (isset($options['data']) && is_array($options['data'])) {
            foreach ($options['data'] as $attrName => $attrValue) {
                if ($attrValue) {
                    $tagRenderer->addAttribute('data-' . $attrName, $attrValue);
                }
            }
        }

        foreach ($options as $attrName => $attrValue) {
            if (in_array($attrName, $allowedAdditionalAttributes) && $attrValue) {
                $tagRenderer->addAttribute($attrName, $attrValue);
            }
        }

        return $tagRenderer->render();
    }

    /**
     * Processes an image.
     *
     * @param FileInterface $file
     * @param int|string    $width
     * @param int|string    $height
     * @param string        $additionalParameters
     * @return FileInterface
     */
    protected function processImage(FileInterface $file, $width, $height, $additionalParameters = '')
    {
        $imageService = $this->getImageService();
        $crop         = $file instanceof FileReference ? $file->getProperty('crop') : null;

        $processingInstructions = array(
            'width'                => $width,
            'height'               => $height,
            'crop'                 => $crop,
            'additionalParameters' => $additionalParameters
        );

        return $imageService->applyProcessingInstructions($file, $processingInstructions);
    }

    /**
     * Return an instance of ImageService
     *
     * @return ImageService
     */
    protected function getImageService()
    {
        return $this->objectManager->get(ImageService::class);
    }

    /**
     * @param FileInterface $file
     * @return string
     */
    protected function getImageUri(FileInterface $file)
    {
        $imageService = $this->getImageService();

        return $imageService->getImageUri($file);
    }

    /**
     * Generate a picture-tag width different sources and a fallback img-tag.
     *
     * @param FileInterface $file
     * @param int|string    $width
     * @param int|string    $height
     * @param array         $options
     * @return string
     */
    protected function generatePictureTag(FileInterface $file, $width, $height, array $options = [])
    {
        /** @var PictureTagRenderer $pictureTagRenderer */
        $pictureTagRenderer    = $this->objectManager->get(PictureTagRenderer::class);
        $imageVarientConfigKey = self::DEFAULT_IMAGE_VARIANT_KEY;
        $registry              = PictureVariantsRegistry::getInstance();
        $sources               = [];

        if (isset($GLOBALS['TSFE']->register[self::REGISTER_IMAGE_VARIANT_KEY]) && $registry->imageVariantKeyExists($GLOBALS['TSFE']->register[self::REGISTER_IMAGE_VARIANT_KEY])) {
            $imageVarientConfigKey = $GLOBALS['TSFE']->register[self::REGISTER_IMAGE_VARIANT_KEY];
        }

        $imageVariantConfig = $registry->getImageVariant($imageVarientConfigKey);
        $fallbackImage      = $this->generateImgTag($file, $imageVariantConfig->getDefaultWidth(),
            $imageVariantConfig->getDefaultHeight(), $options);

        foreach ($imageVariantConfig->getAllSourceConfig() as $sourceConfig) {
            $sources[] = $this->generateSource($file, $sourceConfig, $options);
        }

        if ($sources) {
            return $pictureTagRenderer->render(implode('', $sources) . $fallbackImage);
        } else {
            return $fallbackImage;
        }
    }

    /**
     * @param FileInterface $file
     * @param array         $config
     * @param array         $options
     * @return string
     */
    protected function generateSource(FileInterface $file, $config, array $options = [])
    {
        $srcsets              = array();
        $sourceTagRenderer    = $this->objectManager->get(SourceTagRenderer::class);
        $additionalParameters = '';

        if (isset($options['grayscale']) && $options['grayscale'] == true) {
            $additionalParameters .= ' -colorspace Gray';
        }

        if (!is_array($config['srcset']) || !$config['srcset']) {
            return '';
        }

        if (isset($config['media']) && $config['media']) {
            $sourceTagRenderer->addAttribute('media', $config['media']);
        }

        foreach ($config['srcset'] as $density => $srcstConfig) {
            $width  = $srcstConfig['width'] ?? '';
            $height = $srcstConfig['height'] ?? '';

            if (isset($srcstConfig['quality']) && is_numeric($srcstConfig['quality'])) {
                $additionalParameters .= ' -quality ' . intval($srcstConfig['quality']);
            }

            $processedImage = $this->processImage($file, $width, $height, $additionalParameters);

            $srcsets[] = $this->getImageUri($processedImage) . ' ' . $density;
        }

        $sourceTagRenderer->addAttribute('srcset', implode(',', $srcsets));

        return $sourceTagRenderer->render();
    }
}