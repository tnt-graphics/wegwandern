<?php

namespace DevOwl\RealCookieBanner\Vendor\YahnisElsts\PluginUpdateChecker\v5p4\DebugBar;

use DevOwl\RealCookieBanner\Vendor\YahnisElsts\PluginUpdateChecker\v5p4\Theme\UpdateChecker;
if (!\class_exists(ThemePanel::class, \false)) {
    /** @internal */
    class ThemePanel extends Panel
    {
        /**
         * @var UpdateChecker
         */
        protected $updateChecker;
        protected function displayConfigHeader()
        {
            $this->row('Theme directory', \htmlentities($this->updateChecker->directoryName));
            parent::displayConfigHeader();
        }
        protected function getUpdateFields()
        {
            return \array_merge(parent::getUpdateFields(), array('details_url'));
        }
    }
}
