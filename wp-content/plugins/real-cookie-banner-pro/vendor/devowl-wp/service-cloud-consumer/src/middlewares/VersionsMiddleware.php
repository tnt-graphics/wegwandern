<?php

namespace DevOwl\RealCookieBanner\Vendor\DevOwl\ServiceCloudConsumer\middlewares;

/**
 * Middleware to replace `consumerData['versions'] (number[])` with the template object when using.
 * It automatically omits the latest version from the array.
 * @internal
 */
class VersionsMiddleware extends AbstractTemplateMiddleware
{
    /**
     * Prefetched version templates by request key (`identifier|version,version,...`).
     *
     * @var array[]
     */
    private $resolvedVersions = [];
    // Documented in AbstractTemplateMiddleware
    public function beforePersistTemplate($template, &$allTemplates)
    {
        // Silence is golden.
    }
    // Documented in AbstractTemplateMiddleware
    public function beforeUsingTemplate($template)
    {
        if (!isset($template->consumerData['versions'])) {
            return;
        }
        $versions = $this->getNormalizedVersions($template);
        if (\count($versions) === 0) {
            $template->consumerData['versions'] = [];
            return;
        }
        $requestKey = $this->getRequestKey($template->identifier, $versions);
        $template->consumerData['versions'] = $this->resolvedVersions[$requestKey] ?? [];
    }
    // Documented in AbstractTemplateMiddleware
    public function beforeUsingTemplates(&$templates)
    {
        $requestMap = [];
        $this->resolvedVersions = [];
        foreach ($templates as $template) {
            if (!isset($template->consumerData['versions'])) {
                continue;
            }
            $versions = $this->getNormalizedVersions($template);
            if (\count($versions) === 0) {
                continue;
            }
            $requestKey = $this->getRequestKey($template->identifier, $versions);
            $requestMap[$requestKey] = [$template->identifier, $versions];
        }
        // Avoid recursive version resolving.
        $this->suspend(\true);
        try {
            foreach ($requestMap as $requestKey => $request) {
                list($identifier, $versions) = $request;
                $retrieved = $this->getConsumer()->retrieveBy('versions', \array_merge([$identifier], $versions));
                $this->resolvedVersions[$requestKey] = \array_values(\array_map(function ($template) {
                    return $template->use();
                }, $retrieved));
            }
        } finally {
            $this->suspend(\false);
        }
        parent::beforeUsingTemplates($templates);
    }
    /**
     * Normalize available versions for a template usage context.
     *
     * @param mixed $template
     * @return int[]
     */
    private function getNormalizedVersions($template)
    {
        $versions = $template->consumerData['versions'];
        foreach ($versions as $key => $version) {
            if ($version === $template->version) {
                unset($versions[$key]);
            }
        }
        $versions = \array_values($versions);
        \rsort($versions);
        return $versions;
    }
    /**
     * Build request cache key for an identifier and versions.
     *
     * @param string $identifier
     * @param int[] $versions
     */
    private function getRequestKey($identifier, $versions)
    {
        return $identifier . '|' . \join(',', $versions);
    }
}
