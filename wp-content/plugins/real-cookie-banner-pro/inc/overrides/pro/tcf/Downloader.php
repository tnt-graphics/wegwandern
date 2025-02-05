<?php

namespace DevOwl\RealCookieBanner\lite\tcf;

use DevOwl\RealCookieBanner\Vendor\JsonMachine\Items;
use DevOwl\RealCookieBanner\Vendor\MatthiasWeb\Utils\Utils;
use WP_Error;
/**
 * Download the TCF list from a remote address. It holds all the data of the downloaded GVL in a memory-efficient way
 * with the help of `JsonMachine`.
 * @internal
 */
class Downloader
{
    const FILENAME_VENDOR_LIST = 'v3/vendor-list.json';
    const FILENAME_PURPOSES_TRANSLATION = 'v3/purposes.json';
    const TCF_DEFAULT_LANGUAGE = 'en';
    /**
     * GVL specification version.
     *
     * @var int
     */
    private $gvlSpecificationVersion;
    /**
     * Vendor list version.
     *
     * @var int
     */
    private $vendorListVersion;
    /**
     * TCF policy version.
     *
     * @var int
     */
    private $tcfPolicyVersion;
    /**
     * Last updated timestamp.
     *
     * @var string
     */
    private $lastUpdated;
    /**
     * Purposes.
     *
     * @var array
     */
    private $purposes = [];
    /**
     * Special purposes.
     *
     * @var array
     */
    private $specialPurposes = [];
    /**
     * Features.
     *
     * @var array
     */
    private $features = [];
    /**
     * Special features.
     *
     * @var array
     */
    private $specialFeatures = [];
    /**
     * Data categories.
     *
     * @var array
     */
    private $dataCategories = [];
    /**
     * Stacks.
     *
     * @var array
     */
    private $stacks = [];
    /**
     * Vendors as stream.
     *
     * @var Items|array
     */
    private $vendors;
    /**
     * The normalizer.
     *
     * @var TcfVendorListNormalizer
     */
    private $normalizer;
    /**
     * C'tor.
     *
     * @param TcfVendorListNormalizer $normalizer
     */
    public function __construct($normalizer)
    {
        $this->normalizer = $normalizer;
    }
    /**
     * Fetch the `vendor-list.json` from an external URL.
     *
     * @param string $url
     * @param array $queryArgs Additional query parameters, e.g. license key
     * @return WP_Error|true
     */
    public function fetchVendorList($url, $queryArgs = [])
    {
        $data = Utils::pullJson(\add_query_arg($queryArgs, $url), ['/gvlSpecificationVersion', '/vendorListVersion', '/tcfPolicyVersion', '/lastUpdated'], ['/purposes', '/specialPurposes', '/features', '/specialFeatures', '/dataCategories', '/stacks'], ['/vendors']);
        if (\is_wp_error($data)) {
            return $data;
        }
        foreach ($data as $key => $value) {
            $this->{$key} = $value;
        }
        return \true;
    }
    /**
     * Fetch the `purpose-{language}.json` from an external URL.
     *
     * @param string $url Add `%s` to your URL so the language code gets added to it
     * @param string $language The 2-letter code
     * @param array $queryArgs Additional query parameters, e.g. license key
     * @return WP_Error|array
     */
    public function fetchTranslation($url, $language, $queryArgs = [])
    {
        $url = \add_query_arg($queryArgs, $url);
        $url = \add_query_arg('language', $language, $url);
        $body = $this->requestToArray(\wp_remote_get($url));
        if (\is_wp_error($body)) {
            return $body;
        }
        return \json_decode($body, ARRAY_A);
    }
    /**
     * Convert a result of `wp_remote_get` to a PHP array.
     *
     * @param WP_Error|array $request
     * @return WP_Error|string
     */
    protected function requestToArray($request)
    {
        if (\is_wp_error($request)) {
            return $request;
        }
        return \wp_remote_retrieve_body($request);
    }
    /**
     * Getter.
     */
    public function getGvlSpecificationVersion()
    {
        return $this->gvlSpecificationVersion;
    }
    /**
     * Getter.
     */
    public function getVendorListVersion()
    {
        return $this->vendorListVersion;
    }
    /**
     * Getter.
     */
    public function getTcfPolicyVersion()
    {
        return $this->tcfPolicyVersion;
    }
    /**
     * Getter.
     */
    public function getLastUpdated()
    {
        return $this->lastUpdated;
    }
    /**
     * Getter.
     */
    public function getPurposes()
    {
        return $this->purposes;
    }
    /**
     * Getter.
     */
    public function getSpecialPurposes()
    {
        return $this->specialPurposes;
    }
    /**
     * Getter.
     */
    public function getFeatures()
    {
        return $this->features;
    }
    /**
     * Getter.
     */
    public function getSpecialFeatures()
    {
        return $this->specialFeatures;
    }
    /**
     * Getter.
     */
    public function getDataCategories()
    {
        return $this->dataCategories;
    }
    /**
     * Getter.
     */
    public function getStacks()
    {
        return $this->stacks;
    }
    /**
     * Getter.
     */
    public function getVendors()
    {
        return $this->vendors;
    }
    /**
     * Getter.
     *
     * @codeCoverageIgnore
     */
    public function getNormalizer()
    {
        return $this->normalizer;
    }
}
