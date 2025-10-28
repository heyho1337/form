<?php

namespace App\Service\Forms\Submit;

use App\Interface\FormSubmitServiceInterface;

class SubmitServiceRegistry
{
    /**
     * @var FormSubmitServiceInterface[]
     */
    private array $services = [];

    public function __construct(iterable $services)
    {
        foreach ($services as $service) {
            if (!$service instanceof FormSubmitServiceInterface) {
                continue;
            }

            $id = $service->supports();
            $this->services[$id] = $service;
        }
    }

    public function getServiceFor(int $formId): FormSubmitServiceInterface
    {
        if (isset($this->services[$formId])) {
            $service = $this->services[$formId];
        } elseif (isset($this->services['default'])) {
            $service = $this->services['default'];
        } else {
            throw new \RuntimeException("No submit service found for form ID $formId and no default service defined.");
        }

        //dd("Loaded service for form ID $formId:", get_class($service));

        return $service;
    }
}

