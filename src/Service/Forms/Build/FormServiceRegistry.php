<?php

namespace App\Service\Forms\Build;

use App\Interface\FormBuilderServiceInterface;

class FormServiceRegistry
{
    private array $services = [];

    public function __construct(iterable $services)
    {
        foreach ($services as $service) {
            if (!$service instanceof FormBuilderServiceInterface) {
                continue;
            }
            $this->services[$service->supports()] = $service;
        }
    }

    public function getServiceFor(int|string $formId): FormBuilderServiceInterface
    {
        if (isset($this->services[$formId])) {
            return $this->services[$formId];
        }
        if (isset($this->services['default'])) {
            return $this->services['default'];
        }

        throw new \RuntimeException("No form service found for form ID '$formId' and no default defined.");
    }
}
