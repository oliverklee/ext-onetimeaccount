<?php

declare(strict_types=1);

namespace OliverKlee\Onetimeaccount\ViewHelpers;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Trait for providing access to the enabled fields from the settings.
 *
 * @mixin AbstractViewHelper
 */
trait FieldEnabledViewHelperTrait
{
    /**
     * @var non-empty-string
     */
    private static $settingForEnabledFields = 'fieldsToShow';

    /**
     * @param RenderingContextInterface $renderingContext
     *
     * @return array<int, string>
     *
     * @throws \UnexpectedValueException
     */
    private static function getEnabledFields(RenderingContextInterface $renderingContext): array
    {
        $settings = $renderingContext->getVariableProvider()->get('settings');
        if (!\is_array($settings)) {
            throw new \UnexpectedValueException('No settings in the variable container found.', 1651153736);
        }
        $enabledFieldsVariable = self::$settingForEnabledFields;
        if (!isset($settings[$enabledFieldsVariable])) {
            throw new \UnexpectedValueException(
                'No field "' . $enabledFieldsVariable . '" in settings found.',
                1651154598
            );
        }
        $enabledFieldsConfiguration = $settings[$enabledFieldsVariable];
        if (!\is_string($enabledFieldsConfiguration)) {
            throw new \UnexpectedValueException(
                'The setting "' . $enabledFieldsVariable . '" needs to be a string.',
                1651155151
            );
        }

        return GeneralUtility::trimExplode(',', $enabledFieldsConfiguration, true);
    }
}
