<?php

namespace DevOwl\RealCookieBanner\lite\settings;

use DevOwl\RealCookieBanner\Vendor\DevOwl\CookieConsentManagement\settings\AbstractTcf;
use DevOwl\RealCookieBanner\Vendor\DevOwl\CookieConsentManagement\tcf\VendorConfiguration;
use DevOwl\RealCookieBanner\Core;
use DevOwl\RealCookieBanner\lite\view\TcfBanner;
use DevOwl\RealCookieBanner\settings\Consent;
use DevOwl\RealCookieBanner\settings\Revision;
use DevOwl\RealCookieBanner\settings\TCF as SettingsTCF;
use DevOwl\RealCookieBanner\Vendor\MatthiasWeb\Utils\Constants;
use DevOwl\RealCookieBanner\Vendor\MatthiasWeb\Utils\Utils;
use WP_Error;
use WP_REST_Request;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/** @internal */
trait TCF
{
    /**
     * Save the state of currently active so we can calculate the timestamps for (first) accepted time.
     *
     * @var boolean
     */
    private $previousActive = null;
    // Documented in IOverrideGeneral
    public function overrideEnableOptionsAutoload()
    {
        Utils::enableOptionAutoload(SettingsTCF::SETTING_TCF, SettingsTCF::DEFAULT_TCF, 'boolval');
        Utils::enableOptionAutoload(SettingsTCF::SETTING_TCF_FIRST_ACCEPTED_TIME, SettingsTCF::DEFAULT_TCF_FIRST_ACCEPTED_TIME, 'strval');
        Utils::enableOptionAutoload(SettingsTCF::SETTING_TCF_ACCEPTED_TIME, SettingsTCF::DEFAULT_TCF_ACCEPTED_TIME, 'strval');
        Utils::enableOptionAutoload(SettingsTCF::SETTING_TCF_SCOPE_OF_CONSENT, SettingsTCF::DEFAULT_TCF_SCOPE_OF_CONSENT, function ($value) {
            if (!\in_array($value, SettingsTCF::ALLOWED_SCOPE_OF_CONSENT, \true)) {
                return SettingsTCF::DEFAULT_TCF_SCOPE_OF_CONSENT;
            }
            return $value;
        });
        Utils::enableOptionAutoload(SettingsTCF::SETTING_TCF_GVL_DOWNLOAD_TIME, SettingsTCF::DEFAULT_TCF_GVL_DOWNLOAD_TIME, 'strval');
        \add_action('RCB/Settings/Updated', [$this, 'updated_option_active'], 10, 2);
        \add_filter('rest_pre_get_setting', [$this, 'rest_pre_get_setting'], 10, 3);
    }
    // Documented in IOverrideTCF
    public function overrideRegister()
    {
        \register_setting(SettingsTCF::OPTION_GROUP, SettingsTCF::SETTING_TCF, ['type' => 'boolean', 'show_in_rest' => \true]);
        \register_setting(SettingsTCF::OPTION_GROUP, SettingsTCF::SETTING_TCF_ACCEPTED_TIME, ['type' => 'string', 'show_in_rest' => \true]);
        \register_setting(SettingsTCF::OPTION_GROUP, SettingsTCF::SETTING_TCF_FIRST_ACCEPTED_TIME, ['type' => 'string', 'show_in_rest' => \true]);
        \register_setting(SettingsTCF::OPTION_GROUP, SettingsTCF::SETTING_TCF_SCOPE_OF_CONSENT, ['type' => 'string', 'show_in_rest' => \true]);
        \register_setting(SettingsTCF::OPTION_GROUP, SettingsTCF::SETTING_TCF_GVL_DOWNLOAD_TIME, ['type' => 'string', 'show_in_rest' => \true]);
        $this->previousActive = $this->isActive();
    }
    // Documented in IOverrideTCF
    public function probablyUpdateGvl()
    {
        if ($this->isActive() && \time() > \get_option(SettingsTCF::OPTION_TCF_GVL_NEXT_DOWNLOAD_TIME, 0)) {
            $this->updateGvl();
        }
    }
    // Documented in IOverrideTCF
    public function updateGvl($force = \false)
    {
        if ($this->isActive() || $force) {
            $license = Core::getInstance()->getRpmInitiator()->getPluginUpdater()->getCurrentBlogLicense();
            $normalizer = Core::getInstance()->getTcfVendorListNormalizer();
            $normalizer->setFetchQueryArgs(['licenseKey' => $license->getActivation()->getCode(), 'clientUuid' => $license->getUuid()]);
            $result = $normalizer->update();
            if (\is_wp_error($result)) {
                return new WP_Error('tcf_gvl_fetch_failed', \sprintf(
                    // translators:
                    \__('Downloading the GVL has failed. Please try again later! (%1$s: %2$s)', RCB_TD),
                    $result->get_error_code(),
                    $result->get_error_message()
                ), $result->get_error_data());
            }
            // Persist last successful download and save the timestamp when it should get automatically be updated
            if ($result === \true) {
                \update_option(SettingsTCF::SETTING_TCF_GVL_DOWNLOAD_TIME, \current_time('mysql'));
                // Determine next update
                $hasDefectVendors = $normalizer->getQuery()->hasDefectVendors();
                \update_option(SettingsTCF::OPTION_TCF_GVL_NEXT_DOWNLOAD_TIME, $hasDefectVendors ? \strtotime('+6 hours') : AbstractTcf::getNextUpdateTime());
                // Automatically request new consent
                Revision::getInstance()->getRevision()->create(\true);
            }
            return $result;
        }
        return new WP_Error('rcb_update_gvl_not_active', \__('This functionality is currently not available.', RCB_TD), ['status' => 500]);
    }
    // Documented in IOverrideTCF
    public function clearGvl()
    {
        Core::getInstance()->getTcfVendorListNormalizer()->getPersist()->clear();
        \update_option(SettingsTCF::SETTING_TCF_GVL_DOWNLOAD_TIME, '');
        \update_option(SettingsTCF::OPTION_TCF_GVL_NEXT_DOWNLOAD_TIME, '');
    }
    /**
     * The option to enable TCF got updated, let's save the time of consent.
     *
     * @param WP_REST_Response $response
     * @param WP_REST_Request $request
     */
    public function updated_option_active($response, $request)
    {
        $active = $request->get_param(SettingsTCF::SETTING_TCF);
        // Clear GVL if TCF gets disabled
        if ($active === \false && $this->previousActive) {
            $this->clearGvl();
        }
        // Check if switch from "Disabled" to "Enabled" is done and save timestamps
        if ($active === \true && !$this->previousActive && Core::getInstance()->isLicenseActive()) {
            $firstAcceptedTime = $this->getFirstAcceptedTime();
            if (empty($firstAcceptedTime)) {
                $firstAcceptedTime = empty($firstAcceptedTime) ? \current_time('mysql') : $firstAcceptedTime;
                \update_option(SettingsTCF::SETTING_TCF_FIRST_ACCEPTED_TIME, $firstAcceptedTime);
                // Deactivate "Respect DnT header" option
                \update_option(Consent::SETTING_RESPECT_DO_NOT_TRACK, \false);
            }
            $acceptedTime = \current_time('mysql');
            \update_option(SettingsTCF::SETTING_TCF_ACCEPTED_TIME, $acceptedTime);
            // Normalize `vendor-list` and persist to database
            $this->updateGvl(\true);
        }
        // Output as ISO strings
        $response->data[SettingsTCF::SETTING_TCF_FIRST_ACCEPTED_TIME] = \mysql2date('c', $this->getFirstAcceptedTime(), \false);
        $response->data[SettingsTCF::SETTING_TCF_ACCEPTED_TIME] = \mysql2date('c', $this->getAcceptedTime(), \false);
        $response->data[SettingsTCF::SETTING_TCF_GVL_DOWNLOAD_TIME] = \mysql2date('c', $this->getGvlDownloadTime(), \false);
        return $response;
    }
    /**
     * Output the accepted time as ISO string instead of mysql formatted string.
     *
     * @param mixed  $result Value to use for the requested setting. Can be a scalar
     *                       matching the registered schema for the setting, or null to
     *                       follow the default get_option() behavior.
     * @param string $name   Setting name (as shown in REST API responses).
     * @param array  $args   Arguments passed to register_setting() for this setting.
     */
    public function rest_pre_get_setting($result, $name, $args)
    {
        if (!\in_array($args['option_name'], [SettingsTCF::SETTING_TCF_ACCEPTED_TIME, SettingsTCF::SETTING_TCF_FIRST_ACCEPTED_TIME, SettingsTCF::SETTING_TCF_GVL_DOWNLOAD_TIME], \true)) {
            return $result;
        }
        $value = \get_option($args['option_name'], $args['schema']['default']);
        return empty($value) ? null : \mysql2date('c', $value, \false);
    }
    /**
     * Localize frontend.
     *
     * @param array $arr
     * @param string $context
     */
    public function localize($arr, $context)
    {
        $isActive = $this->isActive();
        $isFrontend = $context === Constants::ASSETS_TYPE_FRONTEND || \is_customize_preview();
        $banner = TcfBanner::getInstance();
        if ($isFrontend && $isActive || $context === Constants::ASSETS_TYPE_ADMIN) {
            $arr['bannerI18n'] = \array_merge($arr['bannerI18n'], $banner->localizeTexts());
        }
        return $arr;
    }
    // Documented in AbstractTcf
    public function isActive()
    {
        return \get_option(SettingsTCF::SETTING_TCF) && Core::getInstance()->isLicenseActive();
    }
    // Documented in IOverrideTCF
    public function getFirstAcceptedTime()
    {
        return \get_option(SettingsTCF::SETTING_TCF_FIRST_ACCEPTED_TIME, SettingsTCF::DEFAULT_TCF_FIRST_ACCEPTED_TIME);
    }
    // Documented in IOverrideTCF
    public function getAcceptedTime()
    {
        return \get_option(SettingsTCF::SETTING_TCF_ACCEPTED_TIME, SettingsTCF::DEFAULT_TCF_ACCEPTED_TIME);
    }
    // Documented in IOverrideTCF
    public function getGvlDownloadTime()
    {
        return \get_option(SettingsTCF::SETTING_TCF_GVL_DOWNLOAD_TIME, SettingsTCF::DEFAULT_TCF_GVL_DOWNLOAD_TIME);
    }
    // Documented in AbstractTcf
    public function getScopeOfConsent()
    {
        return \get_option(SettingsTCF::SETTING_TCF_SCOPE_OF_CONSENT);
    }
    // Documented in AbstractTcf
    public function getVendorConfigurations()
    {
        $vendorConfigurations = \DevOwl\RealCookieBanner\lite\settings\TcfVendorConfiguration::getInstance()->getOrdered();
        $gvl = $this->getGvl();
        // Collect all vendors and read them in batch
        $vendorIds = [];
        foreach ($vendorConfigurations as $vendorConfiguration) {
            $vendorIds[] = $vendorConfiguration->metas['vendorId'];
        }
        $vendors = \count($vendorIds) > 0 ? $gvl->vendors(['in' => $vendorIds])['vendors'] : [];
        $result = [];
        // Prepare the vendor rows
        foreach ($vendorConfigurations as $vendorConfiguration) {
            $vendorId = $vendorConfiguration->metas['vendorId'];
            if (!isset($vendors[$vendorId])) {
                // Vendor does no longer exist?
                continue;
            }
            $vendor = $vendors[$vendorId];
            $row = VendorConfiguration::fromJson(\array_merge(['id' => $vendorConfiguration->ID], $vendorConfiguration->metas), $vendor);
            $row->applyRestrictivePurposes($this->getScopeOfConsent());
            $result[] = $row;
        }
        return $result;
    }
    // Documented in AbstractTcf
    public function getGvl()
    {
        return Core::getInstance()->getTcfVendorListNormalizer()->getQuery();
    }
}
