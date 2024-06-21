<?php

namespace DevOwl\RealCookieBanner\lite;

use DevOwl\RealCookieBanner\Stats as RealCookieBannerStats;
use DevOwl\RealCookieBanner\UserConsent;
use DevOwl\RealCookieBanner\view\Blocker;
use DevOwl\RealCookieBanner\view\customize\banner\individual\Texts as IndividualTexts;
use DevOwl\RealCookieBanner\view\customize\banner\Texts;
use DevOwl\RealCookieBanner\view\shortcode\LinkShortcode;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/** @internal */
trait Stats
{
    // Documented in IOverrideStats
    public function fetchMainStats($from, $to, $context)
    {
        global $wpdb;
        $table_name = $this->getTableName(RealCookieBannerStats::TABLE_NAME_TERMS);
        $rows = $wpdb->get_results(
            // phpcs:disable WordPress.DB.PreparedSQL
            $wpdb->prepare("SELECT IFNULL(t.name, CONCAT(s.term_name, %s)) AS term_name, s.accepted, SUM(s.count) AS `count`\n                FROM {$table_name} AS s\n                LEFT JOIN {$wpdb->terms} AS t\n                ON s.term_id = t.term_id\n                WHERE s.day BETWEEN %s AND %s\n                AND s.context = %s\n                GROUP BY 1, 2\n                ORDER BY s.term_id ASC, s.accepted ASC", ' (' . \__('deleted', RCB_TD) . ')', $from, $to, $context),
            ARRAY_A
        );
        // Transform object types
        foreach ($rows as $key => $row) {
            $rows[$key]['count'] = \intval($row['count']);
        }
        return $rows;
    }
    // Documented in IOverrideStats
    public function fetchButtonsClickedStats($from, $to, $context = null)
    {
        global $wpdb;
        $table_name = $this->getTableName(RealCookieBannerStats::TABLE_NAME_BUTTONS_CLICKED);
        $result = [];
        $defaultButtonTexts = Texts::getDefaultButtonTexts();
        $defaultIndividualButtonTexts = IndividualTexts::getDefaultButtonTexts();
        $individualPrivacyButtonText = \get_option(Texts::SETTING_ACCEPT_INDIVIDUAL, $defaultButtonTexts['acceptIndividual']);
        foreach (UserConsent::CLICKABLE_BUTTONS as $btn) {
            $label = \__('None', RCB_TD);
            switch ($btn) {
                case 'main_all':
                    $label = \get_option(Texts::SETTING_ACCEPT_ALL, $defaultButtonTexts['acceptAll']);
                    break;
                case 'main_essential':
                    $label = \get_option(Texts::SETTING_ACCEPT_ESSENTIALS, $defaultButtonTexts['acceptEssentials']);
                    break;
                case 'main_close_icon':
                    $label = \get_option(Texts::SETTING_ACCEPT_ESSENTIALS, $defaultButtonTexts['acceptEssentials']) . ' (' . \__('Close icon', RCB_TD) . ')';
                    break;
                case 'main_custom':
                    $label = \get_option(IndividualTexts::SETTING_SAVE, $defaultIndividualButtonTexts['save']);
                    break;
                case 'ind_all':
                    $label = $individualPrivacyButtonText . ': ' . \get_option(Texts::SETTING_ACCEPT_ALL, $defaultButtonTexts['acceptAll']);
                    break;
                case 'ind_essential':
                    $label = $individualPrivacyButtonText . ': ' . \get_option(Texts::SETTING_ACCEPT_ESSENTIALS, $defaultButtonTexts['acceptEssentials']);
                    break;
                case 'ind_close_icon':
                    $label = $individualPrivacyButtonText . ': ' . \get_option(Texts::SETTING_ACCEPT_ESSENTIALS, $defaultButtonTexts['acceptEssentials']) . ' (' . \__('Close icon', RCB_TD) . ')';
                    break;
                case 'ind_custom':
                    $label = $individualPrivacyButtonText . ': ' . \get_option(IndividualTexts::SETTING_SAVE, $defaultIndividualButtonTexts['save']);
                    break;
                case LinkShortcode::BUTTON_CLICKED_IDENTIFIER:
                    $label = \__('Shortcode revoke', RCB_TD);
                    break;
                case Blocker::BUTTON_CLICKED_IDENTIFIER:
                    $label = \__('Button in Content Blocker', RCB_TD);
                    break;
                default:
                    break;
            }
            $result[$btn] = [$label, 0];
        }
        // Build WHERE statement for filtering
        // If you add a new filter, keep in mind to add the column to the index `filters` of `wp_rcb_consent`
        $where = [];
        $where[] = $context === null ? '1 = 1' : $wpdb->prepare('context = %s', $context);
        // phpcs:disable WordPress.DB
        $sql = $wpdb->prepare("SELECT button_clicked, SUM(`count`) as cnt\n            FROM {$table_name}\n            WHERE `day` BETWEEN %s AND %s\n                AND button_clicked <> 'none'\n                AND " . \join(' AND ', $where) . ' GROUP BY 1', $from, $to);
        $rows = $wpdb->get_results($sql, ARRAY_A);
        // phpcs:enable WordPress.DB
        foreach ($rows as $row) {
            $result[$row['button_clicked']][1] = \intval($row['cnt']);
        }
        return $result;
    }
    // Documented in IOverrideStats
    public function fetchCustomBypassStats($from, $to, $context = null)
    {
        global $wpdb;
        $result = [];
        $table_name = $this->getTableName(RealCookieBannerStats::TABLE_NAME_CUSTOM_BYPASS);
        // Build WHERE statement for filtering
        // If you add a new filter, keep in mind to add the column to the index `filters` of `wp_rcb_consent`
        $where = [];
        $where[] = $context === null ? '1 = 1' : $wpdb->prepare('context = %s', $context);
        // phpcs:disable WordPress.DB
        $sql = $wpdb->prepare("SELECT custom_bypass, SUM(`count`) as cnt\n            FROM {$table_name}\n            WHERE `day` BETWEEN %s AND %s\n                AND " . \join(' AND ', $where) . ' GROUP BY 1', $from, $to);
        $rows = $wpdb->get_results($sql, ARRAY_A);
        // phpcs:enable WordPress.DB
        foreach ($rows as $row) {
            $result[$row['custom_bypass']] = \intval($row['cnt']);
        }
        return $result;
    }
}
