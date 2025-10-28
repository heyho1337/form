<?php

namespace App\Service\Forms\Build;

use App\Entity\EvcFormsFields;
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
use App\Service\Modules\MenuService;
use App\Repository\EvcFormRepository;
use App\Service\Modules\LangService;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Bundle\SecurityBundle\Security;

class DefaultFormService extends BaseFormService
{
    
	
	public function __construct(
        protected EvcFormRepository $evcFormRepo,
        protected LangService $langService,
        protected FormFactoryInterface $formFactory,
        protected UrlGeneratorInterface $urlGenerator,
        protected RequestStack $requestStack,
        protected Security $security,
		protected MenuService $menuService
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
        $this->formEntity = $this->evcFormRepo->findOneBy(['form_id' => (int)$id, 'form_aktiv' => true]);
        if (!$this->formEntity) {
            return null;
        }

        $builder = $this->formFactory->createBuilder(FormType::class, null, [
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id'   => 'form_intention_' . $id,
        ])
        ->setAction($this->urlGenerator->generate('app_form', ['id' => $id]))
        ->setMethod('POST');

        $fields = $this->formEntity->getEvcFormFields();

        foreach ($fields as $field) {
            match (strtolower($field->getFieldType())) {
                'szoveg' => $this->ContentInput($builder, $field),
                'text' => $this->textInput($builder, $field),
                'select' => $this->selectInput($builder, $field),
                'textarea' => $this->textareaInput($builder, $field),
                'checkbox' => $this->checkboxInput($builder, $field),
                'radio' => $this->radioInput($builder, $field),
                'file' => $this->fileInput($builder, $field),
                'datum' => $this->dateInput($builder, $field),
                'submit' => $this->submitInput($builder, $field, $this->formEntity->isAny()),
                default => null,
            };
        }

        $builder->add("recaptcha_response", HiddenType::class, [
            "attr" => ["id" => "recaptchaResponse"]
        ]);

        return $builder->getForm();
    }

    public function supports(): string|int
    {
        return 'default'; // fallback for DB-driven forms
    }

    protected function fileInput(FormBuilderInterface $builder, EvcFormsFields $field): void
    {
        $builder->add($field->getFieldName(), FileType::class, [
            'label' => $field->getFieldLabel(),
        ]);
    }

    protected function dateInput(FormBuilderInterface $builder, EvcFormsFields $field): void
    {
        $builder->add($field->getFieldName(), DateType::class, [
            'label' => $field->getFieldLabel(),
        ]);
    }

    protected function ContentInput(FormBuilderInterface $builder, EvcFormsFields $field): void
    {
        $content = $field->getFieldOpt();
        $builder->add($field->getFieldName(), TextType::class, [
            'required' => false,
            'disabled' => true,
            'data' => $content,
            'label' => $content,
            'attr' => [
                'class' => 'justText ',
            ]
        ]);
    }

    protected function selectInput(FormBuilderInterface $builder, EvcFormsFields $field): void
    {
        $constraints = [];

        if ($field->isFieldRequired()) {
            $constraints[] = new NotBlank(['message' => $this->langService->fordito('kotelezo')]);
        }

        $fieldOptions = explode(';', $field->getFieldOpt());
        $choices = array_combine($fieldOptions, $fieldOptions);

        $builder->add($field->getFieldName(), ChoiceType::class, [
            'choices' => $choices,
            'constraints' => $constraints,
            'required' => $field->isFieldRequired(),
            'placeholder' => $field->getFieldFirst(),
            'label' => false
        ]);
    }

    protected function checkboxInput(FormBuilderInterface $builder, EvcFormsFields $field): void
    {
        $fieldOptions = explode(';', $field->getFieldOpt());

        $choices = array_combine($fieldOptions, $fieldOptions); // Create key-value pairs

        $builder->add($field->getFieldName(), ChoiceType::class, [
            'choices' => $choices,
            'expanded' => true,
            'multiple' => true,
            'label' => false,
            'required' => $field->isFieldRequired(),
        ]);
    }

    protected function radioInput(FormBuilderInterface $builder, EvcFormsFields $field): void
    {
        $fieldOptions = explode(';', $field->getFieldOpt());

        $choices = array_combine($fieldOptions, $fieldOptions);

        $builder->add($field->getFieldName(), ChoiceType::class, [
            'choices' => $choices,
            'expanded' => true, // Render as radio buttons
            'multiple' => false, // Single selection only
            'label' => false,
            'required' => $field->isFieldRequired(),
            'placeholder' => false,
        ]);
    }

    protected function submitInput(FormBuilderInterface $builder, EvcFormsFields $field, bool $any): void
    {

        if($any){
            $anyMenu = $this->menuService->getMenuById(228);
            $anyText = $this->langService->fordito('adatvedelem');
            $accept = $this->langService->fordito('elfogadasa');
            $anyLabel = "<a target='_blank' href='/{$anyMenu->getMenuAlias()}'>
                <strong>{$anyText}</strong>{$accept}
            </a>";
            $builder->add('any',CheckboxType::class,[
                'label' => $anyLabel,
                'required' => true,
                'label_html' => true,
                'attr' => [
                    'class' => 'any',
					'required' => 'required'
                ],
				'invalid_message' => 'invalid',
            ]);
        }

        $builder->add($field->getFieldName(), SubmitType::class, [
            'label' => $field->getFieldLabel(),
        ]);
    }

    protected function textInput(FormBuilderInterface $builder, EvcFormsFields $field): void
    {
        $constraints = [];

        if ($field->isFieldRequired()) {
            $constraints[] = new NotBlank([
                'message' => $this->langService->fordito('kotelezo'),
            ]);
        }

        $fieldName = $field->getFieldName();
        $fieldLabel = $field->getFieldLabel();

        $options = [
            'attr' => ['placeholder' => $fieldLabel],
            'constraints' => $constraints,
            'label' => $fieldLabel,
            'required' => $field->isFieldRequired(),
        ];

        $builder->add($fieldName, TextType::class, $options);
    }


    protected function textareaInput(FormBuilderInterface $builder, EvcFormsFields $field): void
    {
        $constraints = [];

        if ($field->isFieldRequired()) {
            $constraints[] = new NotBlank(['message' => $this->langService->fordito('kotelezo')]);
        }

        $builder->add($field->getFieldName(), TextareaType::class, [
            'attr' => ['placeholder' => $field->getFieldLabel()],
            'constraints' => $constraints,
            'label' => $field->getFieldLabel(),
            'required' => $field->isFieldRequired(),
        ]);
    }
}