<?php

namespace App\Service\Forms\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class FormField
{
    public function __construct(
        public array $form,
        public string $type,
        public ?string $name = null,
        public ?string $label = null,
        public bool $required = false,
        public bool $mapped = true,
        public int $order = 0
    ) {}
}