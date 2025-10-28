<?php

namespace App\Service\Modules;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use App\Form\Admin\ContentType;

class FormInputService
{
    private FormBuilderInterface $formBuilder;

    public function __construct() {}

    public function setFormBuilder(FormBuilderInterface $formBuilder): static
    {
        $this->formBuilder = $formBuilder;
        return $this;
    }

    public function createTextType($child): void
    {
        $this->formBuilder->add('field', TextType::class, [
            'mapped' => false,
            'required' => false,
            'label' => $child->getName(),
            'attr' => ['readonly' => true],
        ]);
    }

    public function createTextareaType($child): void
    {
        $this->formBuilder->add('field', TextareaType::class, [
            'mapped' => false,
            'required' => false,
            'label' => $child->getName(),
            'attr' => ['readonly' => true],
        ]);
    }

    public function createCheckboxType($child): void
    {
        $options = $child->getOptions();
        if (is_array($options) && count($options) > 1) {
            // Multiple checkboxes: use ChoiceType with multiple=true, expanded=true
            $this->formBuilder->add('field', ChoiceType::class, [
                'choices'  => array_combine($options, $options),
                'multiple' => true,
                'expanded' => true,
                'mapped'   => false,
                'required' => false,
                'label'    => $child->getName(),
                'attr'     => ['readonly' => true], // 'disabled' might be preferable here
            ]);
        } else {
            // Single checkbox
            $this->formBuilder->add('field', CheckboxType::class, [
                'mapped' => false,
                'required' => false,
                'label' => $child->getName(),
                'attr' => ['readonly' => true],
            ]);
        }
    }

    public function createDateTimeType($child): void
    {
        $this->formBuilder->add('field', DateTimeType::class, [
            'mapped' => false,
            'required' => false,
            'label' => $child->getName(),
            'attr' => ['readonly' => true],
            'widget' => 'single_text', // to improve UX for display
        ]);
    }

    public function createFileType($child): void
    {
        $this->formBuilder->add('field', FileType::class, [
            'mapped' => false,
            'required' => false,
            'label' => $child->getName(),
            'attr' => ['readonly' => true, 'disabled' => true], // readonly on file inputs is not always honored, set disabled
        ]);
    }

    public function createEmailType($child): void
    {
        $this->formBuilder->add('field', EmailType::class, [
            'mapped' => false,
            'required' => false,
            'label' => $child->getName(),
            'attr' => ['readonly' => true],
        ]);
    }

    public function createChoiceType($child): void
    {
        $options = $child->getOptions();
        $choices = is_array($options) ? array_combine($options, $options) : [];
        $this->formBuilder->add('field', ChoiceType::class, [
            'choices'  => $choices,
            'mapped'   => false,
            'required' => false,
            'label'    => $child->getName(),
            'attr'     => ['readonly' => true], // or use 'disabled' if needed
        ]);
    }

    public function createRadioType($child): void
    {
        $options = $child->getOptions();
        $choices = is_array($options) ? array_combine($options, $options) : [];
        $this->formBuilder->add('field', ChoiceType::class, [
            'choices'  => $choices,
            'expanded' => true, // radio buttons
            'multiple' => false,
            'mapped'   => false,
            'required' => false,
            'label'    => $child->getName(),
            'attr'     => ['readonly' => true], // or 'disabled' if radios must be non-interactive
        ]);
    }

    public function createTelType($child): void
    {
        $this->formBuilder->add('field', TelType::class, [
            'mapped' => false,
            'required' => false,
            'label' => $child->getName(),
            'attr' => ['readonly' => true],
        ]);
    }

    public function createSubmitType($child): void
    {
        $this->formBuilder->add('field', SubmitType::class, [
            'label' => $child->getName(),
            'attr' => ['readonly' => true, 'disabled' => true], // submit should typically be disabled in "readonly" context
        ]);
    }

    public function createContenType($child): void
    {
        $this->formBuilder->add('field', ContentType::class, [
            'content_label' => $child->getLabel(),
            'content_text' => $child->getDefaultValue(),
            'mapped' => false,
        ]);
    }
}