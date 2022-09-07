<?php

declare(strict_types=1);

namespace OliverKlee\Onetimeaccount\ViewHelpers;

use OliverKlee\Oelib\ViewHelpers\AbstractConfigurationCheckViewHelper;
use OliverKlee\Onetimeaccount\Configuration\ConfigurationCheck;

/**
 * View helper for the configuration check.
 *
 * @extends AbstractConfigurationCheckViewHelper<ConfigurationCheck>
 */
class ConfigurationCheckViewHelper extends AbstractConfigurationCheckViewHelper
{
    protected static function getExtensionKey(): string
    {
        return 'onetimeaccount';
    }

    protected static function getConfigurationCheckClassName(): string
    {
        return ConfigurationCheck::class;
    }
}
