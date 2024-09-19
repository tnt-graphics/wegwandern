<?php

namespace DevOwl\RealCookieBanner\lite\settings;

use DevOwl\RealCookieBanner\Vendor\DevOwl\CookieConsentManagement\consent\Transaction;
use DevOwl\RealCookieBanner\Vendor\DevOwl\CookieConsentManagement\settings\AbstractCountryBypass;
use DevOwl\RealCookieBanner\Core;
use DevOwl\RealCookieBanner\lite\MaxMindDatabase;
use DevOwl\RealCookieBanner\MyConsent;
use DevOwl\RealCookieBanner\settings\CountryBypass as SettingsCountryBypass;
use DevOwl\RealCookieBanner\Utils;
use DevOwl\RealCookieBanner\Vendor\MatthiasWeb\Utils\Utils as UtilsUtils;
use WP_Error;
use WP_REST_Request;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/** @internal */
trait CountryBypass
{
    /**
     * Save the state of currently active so we can update country database at toggle time.
     *
     * @var boolean
     */
    private $previousActive = null;
    // Documented in IOverrideGeneral
    public function overrideEnableOptionsAutoload()
    {
        UtilsUtils::enableOptionAutoload(SettingsCountryBypass::SETTING_COUNTRY_BYPASS_ACTIVE, SettingsCountryBypass::DEFAULT_COUNTRY_BYPASS_ACTIVE, 'boolval');
        UtilsUtils::enableOptionAutoload(SettingsCountryBypass::SETTING_COUNTRY_BYPASS_COUNTRIES, SettingsCountryBypass::DEFAULT_COUNTRY_BYPASS_COUNTRIES);
        UtilsUtils::enableOptionAutoload(SettingsCountryBypass::SETTING_COUNTRY_BYPASS_TYPE, SettingsCountryBypass::DEFAULT_COUNTRY_BYPASS_TYPE);
        UtilsUtils::enableOptionAutoload(SettingsCountryBypass::SETTING_COUNTRY_BYPASS_DB_DOWNLOAD_TIME, SettingsCountryBypass::DEFAULT_COUNTRY_BYPASS_DB_DOWNLOAD_TIME, 'strval');
        \add_action('RCB/Settings/Updated', [$this, 'updated_option_active'], 10, 2);
        \add_filter('rest_pre_get_setting', [$this, 'rest_pre_get_setting'], 10, 3);
    }
    // Documented in IOverrideCountryBypass
    public function overrideRegister()
    {
        \register_setting(SettingsCountryBypass::OPTION_GROUP, SettingsCountryBypass::SETTING_COUNTRY_BYPASS_ACTIVE, ['type' => 'boolean', 'show_in_rest' => \true]);
        // WP < 5.3 does not support array types yet, so we need to store serialized
        \register_setting(SettingsCountryBypass::OPTION_GROUP, SettingsCountryBypass::SETTING_COUNTRY_BYPASS_COUNTRIES, ['type' => 'string', 'show_in_rest' => \true]);
        \register_setting(SettingsCountryBypass::OPTION_GROUP, SettingsCountryBypass::SETTING_COUNTRY_BYPASS_TYPE, ['type' => 'string', 'show_in_rest' => ['schema' => ['type' => 'string', 'enum' => [SettingsCountryBypass::TYPE_ALL, SettingsCountryBypass::TYPE_ESSENTIALS]]]]);
        \register_setting(SettingsCountryBypass::OPTION_GROUP, SettingsCountryBypass::SETTING_COUNTRY_BYPASS_DB_DOWNLOAD_TIME, ['type' => 'string', 'show_in_rest' => \true]);
        $this->previousActive = $this->isActive();
    }
    // Documented in IOverrideCountryBypass
    public function probablyUpdateDatabase()
    {
        if ($this->isActive() && \time() > \get_option(SettingsCountryBypass::OPTION_COUNTRY_DB_NEXT_DOWNLOAD_TIME, 0)) {
            $this->updateDatabase();
        }
    }
    /**
     * Determines, if the current page request is outside our defined countries so
     * all cookies are automatically accepted.
     *
     * @param false|string $result
     * @param WP_REST_Request $request
     */
    public function dynamicPredecision($result, $request)
    {
        if ($result === \false) {
            $transaction = new Transaction();
            $transaction->setIpAddress(Utils::getIpAddress());
            $transaction->setUserAgent($_SERVER['HTTP_USER_AGENT']);
            $transaction->setReferer($request->get_param('referer'));
            $transaction->setViewPort($request->get_param('viewPortWidth'), $request->get_param('viewPortHeight'));
            $bypass = $this->probablyCreateTransaction(MyConsent::getInstance()->getCurrentUser(), $transaction);
            if ($bypass) {
                $persist = MyConsent::getInstance()->persist($transaction);
                if (!\is_wp_error($persist)) {
                    return 'consent';
                    // Through the above code we have ensured there is a cookie set, so we can use the current consent
                }
            }
        }
        return $result;
    }
    // Documented in IOverrideCountryBypass
    public function updateDatabase($force = \false)
    {
        if ($this->isActive() || $force) {
            $license = Core::getInstance()->getRpmInitiator()->getPluginUpdater()->getCurrentBlogLicense();
            $result = MaxMindDatabase::getInstance()->download(['licenseKey' => $license->getActivation()->getCode(), 'clientUuid' => $license->getUuid()]);
            // Persist the timestamp when it should get automatically be updated
            if ($result === \true) {
                \update_option(SettingsCountryBypass::SETTING_COUNTRY_BYPASS_DB_DOWNLOAD_TIME, \current_time('mysql'));
                \update_option(SettingsCountryBypass::OPTION_COUNTRY_DB_NEXT_DOWNLOAD_TIME, AbstractCountryBypass::getNextUpdateTime());
            }
            return $result;
        }
        return new WP_Error('rcb_update_country_db_not_active', \__('This functionality is currently not available.', RCB_TD), ['status' => 500]);
    }
    // Documented in IOverrideCountryBypass
    public function clearDatabase()
    {
        MaxMindDatabase::getInstance()->clear();
        \update_option(SettingsCountryBypass::SETTING_COUNTRY_BYPASS_DB_DOWNLOAD_TIME, '');
        \update_option(SettingsCountryBypass::OPTION_COUNTRY_DB_NEXT_DOWNLOAD_TIME, '');
    }
    /**
     * The option to enable Country Bypass got updated, let's automatically download the country database.
     *
     * @param WP_REST_Response $response
     * @param WP_REST_Request $request
     */
    public function updated_option_active($response, $request)
    {
        $active = $request->get_param(SettingsCountryBypass::SETTING_COUNTRY_BYPASS_ACTIVE);
        // Clear database if Country Bypass gets disabled
        if ($active === \false && $this->previousActive) {
            $this->clearDatabase();
        }
        // Check if switch from "Disabled" to "Enabled" is done and automatically download database
        if ($active === \true && !$this->previousActive && Core::getInstance()->isLicenseActive()) {
            $this->updateDatabase(\true);
        }
        // Output as ISO strings
        $response->data[SettingsCountryBypass::SETTING_COUNTRY_BYPASS_DB_DOWNLOAD_TIME] = \mysql2date('c', $this->getDatabaseDownloadTime(), \false);
        return $response;
    }
    /**
     * Output the download time as ISO string instead of mysql formatted string.
     *
     * @param mixed  $result Value to use for the requested setting. Can be a scalar
     *                       matching the registered schema for the setting, or null to
     *                       follow the default get_option() behavior.
     * @param string $name   Setting name (as shown in REST API responses).
     * @param array  $args   Arguments passed to register_setting() for this setting.
     */
    public function rest_pre_get_setting($result, $name, $args)
    {
        if (!\in_array($args['option_name'], [SettingsCountryBypass::DEFAULT_COUNTRY_BYPASS_DB_DOWNLOAD_TIME], \true)) {
            return $result;
        }
        $value = \get_option($args['option_name'], $args['schema']['default']);
        return empty($value) ? null : \mysql2date('c', $value, \false);
    }
    // Documented in AbstractCountryBypass
    public function lookupCountryCode($ipAddress)
    {
        return MaxMindDatabase::getInstance()->lookupCountryCode($ipAddress);
    }
    // Documented in AbstractCountryBypass
    public function isActive()
    {
        return \get_option(SettingsCountryBypass::SETTING_COUNTRY_BYPASS_ACTIVE) && Core::getInstance()->isLicenseActive();
    }
    // Documented in AbstractCountryBypass
    public function getCountriesRaw()
    {
        $list = \get_option(SettingsCountryBypass::SETTING_COUNTRY_BYPASS_COUNTRIES);
        if (empty($list)) {
            return [];
        }
        return \explode(',', $list);
    }
    // Documented in AbstractCountryBypass
    public function getType()
    {
        return \get_option(SettingsCountryBypass::SETTING_COUNTRY_BYPASS_TYPE, SettingsCountryBypass::DEFAULT_COUNTRY_BYPASS_TYPE);
    }
    // Documented in IOverrideCountryBypass
    public function getDatabaseDownloadTime()
    {
        return \get_option(SettingsCountryBypass::SETTING_COUNTRY_BYPASS_DB_DOWNLOAD_TIME, SettingsCountryBypass::DEFAULT_COUNTRY_BYPASS_DB_DOWNLOAD_TIME);
    }
}
