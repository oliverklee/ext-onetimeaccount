<?php

declare(strict_types=1);

namespace OliverKlee\Onetimeaccount\ViewHelpers;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractConditionViewHelper;

/**
 * This ViewHelper implements a condition based on whether the provided field (or fields) are enabled in the
 * settings.
 *
 * You can provide either a single field name or multiple field names separated by the pipe character (which serves
 * as a logical OR).
 *
 * The name of the fields in the settings carrying the list of enabled fields needs to be provided in the
 * :php:`SETTING_FOR_ENABLED_FIELDS` constant.
 *
 * Examples
 * ========
 *
 * Basic usage
 * -----------
 *
 * ::
 *     {namespace ota=OliverKlee\Onetimeaccount\ViewHelpers}
 *     <ota:isFieldEnabled fieldName="name">
 *         Here the "name" field should be displayed.
 *     </ota:isFieldEnabled>
 *
 * Output::
 *
 *     Everything inside the :xml:`<ota:isFieldEnabled>` tag is being displayed if the field is enabled in the
 * configuration.
 *
 * You can also use if/then/else constructs like with the `f:if` ViewHelper.
 *
 *
 * If / then / else
 * ----------------
 *
 * ::
 *
 *     <ota:isFieldEnabled fieldName="company|name">
 *         <f:then>
 *             This is being shown in case the condition matches.
 *         </f:then>
 *         <f:else>
 *             This is being displayed in case the condition evaluates to FALSE.
 *         </f:else>
 *     </ota:isFieldEnabled>
 *
 * @api
 */
class IsFieldEnabledViewHelper extends AbstractConditionViewHelper
{
    /**
     * @var non-empty-string
     */
    protected const SETTING_FOR_ENABLED_FIELDS = 'fieldsToShow';

    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('fieldName', 'string', 'The name(s) of the fields to check, separated by |.', true);
    }

    /**
     * @param array{
     *               then: null, else: null,
     *               fieldName: string,
     *               __thenClosure: \Closure, __elseClosures: array<int, \Closure>
     *        } $arguments
     */
    public static function verdict(array $arguments, RenderingContextInterface $renderingContext): bool
    {
        $enabledFields = self::getEnabledFields($renderingContext);

        $verdict = false;
        foreach (self::getFieldsToCheck($arguments) as $fieldName) {
            if (\in_array($fieldName, $enabledFields, true)) {
                $verdict = true;
                break;
            }
        }

        return $verdict;
    }

    /**
     * @param array{
     *               then: null, else: null,
     *               fieldName: string,
     *               __thenClosure: \Closure, __elseClosures: array<int, \Closure>
     *        } $arguments
     *
     * @return array<int, string>
     *
     * @throws \InvalidArgumentException
     */
    private static function getFieldsToCheck(array $arguments): array
    {
        $fieldsNamesArgument = $arguments['fieldName'];
        if ($fieldsNamesArgument === '') {
            throw new \InvalidArgumentException('The argument "fieldName" must not be empty.', 1651155957);
        }

        return GeneralUtility::trimExplode('|', $fieldsNamesArgument, true);
    }

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
        $enabledFieldsVariable = self::SETTING_FOR_ENABLED_FIELDS;
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
