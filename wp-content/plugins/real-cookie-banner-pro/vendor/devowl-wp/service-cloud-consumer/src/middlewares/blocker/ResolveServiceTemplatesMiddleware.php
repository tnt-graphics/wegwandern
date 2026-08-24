<?php

namespace DevOwl\RealCookieBanner\Vendor\DevOwl\ServiceCloudConsumer\middlewares\blocker;

use DevOwl\RealCookieBanner\Vendor\DevOwl\ServiceCloudConsumer\consumer\ServiceCloudConsumer;
use DevOwl\RealCookieBanner\Vendor\DevOwl\ServiceCloudConsumer\middlewares\AbstractTemplateMiddleware;
use DevOwl\RealCookieBanner\Vendor\DevOwl\ServiceCloudConsumer\templates\AbstractTemplate;
use DevOwl\RealCookieBanner\Vendor\DevOwl\ServiceCloudConsumer\templates\BlockerTemplate;
/**
 * Middleware that transforms `serviceTemplateIdentifiers` expressions into an array of
 * `ServiceTemplate`. This allows us to create a service directly within the blocker template.
 * @internal
 */
class ResolveServiceTemplatesMiddleware extends AbstractTemplateMiddleware
{
    /**
     * Prefetched and resolved service templates by identifier.
     *
     * @var array[]
     */
    private $serviceTemplatesAsArrayMap = [];
    // Documented in AbstractTemplateMiddleware
    public function beforePersistTemplate($template, &$allTemplates)
    {
        // Silence is golden.
    }
    // Documented in AbstractTemplateMiddleware
    public function beforeUsingTemplate($template)
    {
        if (!$template instanceof BlockerTemplate) {
            return;
        }
        $data = [];
        foreach ($template->serviceTemplateIdentifiers as $serviceIdentifier) {
            if (isset($this->serviceTemplatesAsArrayMap[$serviceIdentifier])) {
                $data[] = $this->serviceTemplatesAsArrayMap[$serviceIdentifier];
            }
        }
        $template->consumerData['serviceTemplates'] = $data;
    }
    // Documented in AbstractTemplateMiddleware
    public function beforeUsingTemplates(&$templates)
    {
        /**
         * Service consumer.
         *
         * @var ServiceCloudConsumer
         */
        $consumer = $this->getVariableResolver()->resolveRequired('service.consumer');
        $blockerTemplates = [];
        $allIdentifiers = [];
        foreach ($templates as $template) {
            if ($template instanceof BlockerTemplate) {
                $blockerTemplates[] = $template;
                $allIdentifiers = \array_merge($allIdentifiers, $template->serviceTemplateIdentifiers);
            }
        }
        if (\count($blockerTemplates) === 0) {
            return;
        }
        $allIdentifiers = \array_values(\array_unique($allIdentifiers));
        $this->serviceTemplatesAsArrayMap = [];
        if (\count($allIdentifiers) > 0) {
            $serviceTemplates = $consumer->retrieveBy('identifier', $allIdentifiers);
            $serviceTemplates = $consumer->use($serviceTemplates);
            $this->serviceTemplatesAsArrayMap = \array_reduce($serviceTemplates, function ($carry, $serviceTemplate) {
                $carry[$serviceTemplate->identifier] = AbstractTemplate::toArray($serviceTemplate);
                return $carry;
            }, []);
        }
        parent::beforeUsingTemplates($templates);
    }
}
