<?php

namespace App\Service\Forms\Build;

use App\Service\Forms\Attributes\FormField;
use App\Entity\EvcUser;
use ReflectionClass;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType; 
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use App\Service\Modules\LangService;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Bundle\SecurityBundle\Security;
use App\Repository\EvcFormRepository;

class EntityAttributeFormService extends BaseFormService
{
    public function __construct(
        protected EvcFormRepository $evcFormRepo,
        protected LangService $langService,
        protected FormFactoryInterface $formFactory,
        protected UrlGeneratorInterface $urlGenerator,
        protected RequestStack $requestStack,
        protected Security $security
    ) {
        parent::__construct(
            $evcFormRepo,
            $langService,
            $formFactory,
            $urlGenerator,
            $requestStack,
            $security
        );
    }

    public function getForm(int|string $id, ?object $entity = null): ?FormInterface
    {
        if ($entity === null) {
            throw new \InvalidArgumentException('Entity must be given for attribute-based form building');
        }

        $formName = (string)$id;

        $builder = $this->formFactory->createBuilder(FormType::class, $entity, [
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id' => 'form_intention_' . $formName,
            'attr' => [
                'id' => "form_".$id
            ],
        ])
        ->setAction($this->urlGenerator->generate('app_form', ['id' => $formName]))
        ->setMethod('POST');

        $refClass = new ReflectionClass($entity);

        $formFields = [];

        foreach ($refClass->getProperties() as $property) {
            $attributes = $property->getAttributes(FormField::class);
            foreach ($attributes as $attribute) {
                /** @var FormField $formField */
                $formField = $attribute->newInstance();

                if (!in_array($formName, $formField->form, true)) {
                    continue;
                }

                $fieldName = $formField->name ?? $property->getName();
                $fieldLabel = $this->langService->fordito($formField->label);
                $required = $formField->required;
                $mapped = $formField->mapped;

                if (!$property->isPublic()) {
                    $property->setAccessible(true);
                }
                $fieldValue = $mapped ? $property->getValue($entity) : "";

                $formFields[] = [
                    'order' => $formField->order,
                    'type' => strtolower($formField->type),
                    'fieldName' => $fieldName,
                    'label' => $fieldLabel,
                    'value' => $fieldValue,
                    'required' => $required,
                    'mapped' => $mapped,
                    // Add more as needed
                ];
            }
        }

        usort($formFields, function($a, $b) {
            return $a['order'] <=> $b['order'];
        });

        // Now add to builder in order:
        foreach ($formFields as $field) {
            match ($field['type']) {
                'szoveg' => $this->ContentInputAttribute($builder, $field['fieldName'], $field['label'], $field['value']),
                'text' => $this->textInputAttribute($builder, $field['fieldName'], $field['label'], $field['value'], $field['required'], $field['mapped']),
                'email' => $this->emailInputAttribute($builder, $field['fieldName'], $field['label'], $field['value'], $field['required'], $field['mapped']),
                'password' => $this->passwordInputAttribute($builder, $field['fieldName'], $field['label'], $field['value'], $field['required']),
                'checkbox' => $this->checkboxInputAttribute($builder, $field['fieldName'], $field['label'], $field['value'], $field['required'], $field['mapped']),
                'select' => $this->selectInputAttribute($builder, $field['fieldName'], $field['label'], $field['value'], $field['required'], $field['mapped']),
                'radio' => $this->radioInputAttribute($builder, $field['fieldName'], $field['label'], $field['value'], $field['required'], $field['mapped']),
                'file' => $this->fileInputAttribute($builder, $field['fieldName'], $field['label'], $field['required']),
                'datum' => $this->dateInputAttribute($builder, $field['fieldName'], $field['label'], $field['value'], $field['required'], $field['mapped']),
                'submit' => $this->submitInputAttribute($builder, $field['fieldName'], $field['label']),
                'textarea' => $this->textareaInputAttribute($builder, $field['fieldName'], $field['label'], $field['value'], $field['required'], $field['mapped']),
                default => $this->textInputAttribute($builder, $field['fieldName'], $field['label'], $field['value'], $field['required'], $field['mapped']),
            };
        }

        $builder->add("recaptcha_response", HiddenType::class, [
            "attr" => ["id" => "recaptchaResponse"],
            "mapped" => false,
        ]);

        return $builder->getForm();
    }

    public function supports(): string|int
    {
        return 'default-entity';
    }

    // Custom form element methods, examples:

    protected function ContentInputAttribute(FormBuilderInterface $builder, string $fieldName, string $label, mixed $value): void
    {
        $builder->add($fieldName, TextType::class, [
            'required' => false,
            'disabled' => true,
            'data' => $value,
            'label' => $label,
            'attr' => ['class' => 'justText'],
        ]);
    }

    protected function textareaInputAttribute(FormBuilderInterface $builder, string $fieldName, string $label, mixed $value, bool $required, bool $mapped): void
    {
        $constraints = [];
        if ($required) {
            $constraints[] = new NotBlank(['message' => $this->langService->fordito('kotelezo')]);
        }

        $builder->add($fieldName, TextareaType::class, [
            'label' => $label,
            'required' => $required,
            'data' => $value,
            'constraints' => $constraints,
            'attr' => ['placeholder' => $label],
            'mapped' => $mapped,
        ]);
    }

    protected function textInputAttribute(FormBuilderInterface $builder, string $fieldName, string $label, mixed $value, bool $required, bool $mapped): void
    {
        $constraints = [];
        if ($required) {
            $constraints[] = new NotBlank(['message' => $this->langService->fordito('kotelezo')]);
        }
        $builder->add($fieldName, TextType::class, [
            'label' => $label,
            'required' => $required,
            'data' => $value,
            'constraints' => $constraints,
            'attr' => ['placeholder' => $label],
            'mapped' => $mapped,
        ]);
    }

    protected function emailInputAttribute(FormBuilderInterface $builder, string $fieldName, string $label, mixed $value, bool $required, bool $mapped): void
    {
        $constraints = [];
        if ($required) {
            $constraints[] = new NotBlank(['message' => $this->langService->fordito('kotelezo')]);
        }
        $builder->add($fieldName, \Symfony\Component\Form\Extension\Core\Type\EmailType::class, [
            'label' => $label,
            'required' => $required,
            'data' => $value,
            'constraints' => $constraints,
            'attr' => ['placeholder' => $label],
            'mapped' => $mapped,
        ]);
    }
    
    protected function passwordInputAttribute(FormBuilderInterface $builder, string $fieldName, string $label, mixed $value, bool $required): void
    {
        $constraints = [];
        if ($required) {
            $constraints[] = new NotBlank(['message' => $this->langService->fordito('kotelezo')]);
        }
        // Usually password fields shouldn't have a default value for security
        $builder->add($fieldName, \Symfony\Component\Form\Extension\Core\Type\PasswordType::class, [
            'label' => $label,
            'required' => $required,
            'constraints' => $constraints,
            'attr' => ['placeholder' => $label],
        ]);
    }

    protected function checkboxInputAttribute(FormBuilderInterface $builder, string $fieldName, string $label, mixed $value, bool $required, bool $mapped): void
    {
        $builder->add($fieldName, CheckboxType::class, [
            'label' => $label,
            'required' => $required,
            // For unchecked checkbox default value can be false
            'data' => (bool)$value,
            'mapped' => $mapped,
        ]);
    }

    protected function selectInputAttribute(FormBuilderInterface $builder, string $fieldName, string $label, mixed $value, bool $required,bool $mapped): void
    {
        // NOTE: You’ll want to extend the FormField attribute to hold 'choices' data for this to be effective.
        // For now, using empty choices.
        $choices = []; // Add logic here to retrieve choices if you extend the attribute.

        $builder->add($fieldName, ChoiceType::class, [
            'label' => $label,
            'choices' => $choices,
            'required' => $required,
            'data' => $value,
            'placeholder' => $label,
            'mapped' => $mapped,
        ]);
    }

    protected function radioInputAttribute(FormBuilderInterface $builder, string $fieldName, string $label, mixed $value, bool $required,bool $mapped): void
    {
        $choices = []; // same as above: extend attribute to provide choices.

        $builder->add($fieldName, ChoiceType::class, [
            'label' => $label,
            'choices' => $choices,
            'required' => $required,
            'data' => $value,
            'expanded' => true,
            'multiple' => false,
            'mapped' => $mapped,
        ]);
    }

    protected function fileInputAttribute(FormBuilderInterface $builder, string $fieldName, string $label, bool $required): void
    {
        $builder->add($fieldName, FileType::class, [
            'label' => $label,
            'required' => $required,
        ]);
    }

    protected function dateInputAttribute(FormBuilderInterface $builder, string $fieldName, string $label, mixed $value, bool $required, bool $mapped): void
    {
        $builder->add($fieldName, DateType::class, [
            'label' => $label,
            'required' => $required,
            'data' => $value,
            'mapped' => $mapped,
            'widget' => 'single_text',
            // add more options as needed
        ]);
    }

    protected function submitInputAttribute(FormBuilderInterface $builder, string $fieldName, string $label): void
    {
        $builder->add($fieldName, SubmitType::class, [
            'label' => $label,
        ]);
    }
}
