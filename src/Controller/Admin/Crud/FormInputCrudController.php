<?php

namespace App\Controller\Admin\Crud;

use App\Entity\Form;
use App\Entity\FormInput;
use App\Entity\FormType;
use App\Service\Admin\CrudService;
use App\Service\Modules\LangService;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use App\Service\Modules\TranslateService;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use App\Trait\Admin\ModalCrud;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class FormInputCrudController extends AbstractCrudController
{
    use ModalCrud;
    private string $lang;

    public function __construct(
        private readonly AdminUrlGenerator $adminUrlGenerator,
        private readonly CrudService $crudService,
        private readonly LangService $langService,
        private readonly TranslateService $translateService,
        private readonly RequestStack $requestStack,
        private readonly TranslatorInterface $translator,
        private readonly Security $security,
        private readonly EntityManagerInterface $entityManager,
        private readonly RouterInterface $router,
        private readonly FormFactoryInterface $formFactory
    ) {
        $this->lang = $this->langService->getDefault();
        if($this->requestStack->getCurrentRequest()){
            $locale = $this->requestStack->getCurrentRequest()->getSession()->get('_locale');
            if($locale){
                $this->lang = $this->requestStack->getCurrentRequest()->getSession()->get('_locale');
                $this->translateService->setLangs($this->lang);
                $this->langService->setLang($this->lang);
            }
        }
    }
    
    public static function getEntityFqcn(): string
    {
        return FormInput::class;
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof FormInput) return;

        $entityInstance->setMapped(false);
        $this->crudService->setEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof FormInput) return;

        $entityInstance->setMapped(false);
        $this->crudService->setEntity($entityManager, $entityInstance);
    }

    public function configureFields(string $pageName): iterable
    {
        //$request = $this->requestStack->getCurrentRequest();
        //$type = $request->query->get("type");
        $entity = $this->getContext()?->getEntity()?->getInstance();
        if ($entity instanceof FormInput && $entity->getType()) {
            $type = $entity->getType()->getId();
        }
        $this->getContext()->getRequest()->setLocale($this->lang);
        $this->translator->getCatalogue($this->lang);
        $this->translator->setLocale($this->lang);
        
        /**
         * on forms
         */
        yield FormField::addTab(
            $this->translateService->translateWords("form_input","Form Input")." ".
            $this->translateService->translateWords("options"),propertySuffix: 'forminput1');
            yield BooleanField::new('active',$this->translateService->translateWords("active"))
                ->renderAsSwitch(true)
                ->setFormTypeOptions(['data' => true])
                ->onlyOnForms();
            if ($this->security->isGranted('ROLE_SUPER_ADMIN')) { 
                yield BooleanField::new('mapped',$this->translateService->translateWords("mapped"))
                    ->renderAsSwitch(true)
                    ->setFormTypeOptions(['data' => false])
                    ->onlyOnForms();
            }
            yield BooleanField::new('required',$this->translateService->translateWords("required"))
                ->renderAsSwitch(true)
                ->setFormTypeOptions(['data' => true])
                ->onlyOnForms();
        
        // ✅ Default language tab - use custom getter/setter
        yield FormField::addTab(
            $this->translateService->translateWords("form_input","Form Input")." ".
            $this->translateService->translateWords($this->langService->getDefaultObject()->getName()),propertySuffix: 'forminput2');
            yield TextField::new('name', $this->translateService->translateWords("name"))
                ->setFormTypeOption('getter', function(FormInput $entity) {
                    return $entity->getName($this->langService->getDefault());
                })
                ->setFormTypeOption('setter', function(FormInput &$entity, $value) {
                    $entity->setName($value, $this->langService->getDefault());
                })
                ->hideOnIndex();
            yield TextField::new('label', $this->translateService->translateWords("label"))
                ->setFormTypeOption('getter', function(FormInput $entity) {
                    return $entity->getLabel($this->langService->getDefault());
                })
                ->setFormTypeOption('setter', function(FormInput &$entity, $value) {
                    $entity->setLabel($value, $this->langService->getDefault());
                })
                ->hideOnIndex();
            yield TextField::new('default_value', $this->translateService->translateWords("default_value", "Default value"))
                ->setFormTypeOption('getter', function(FormInput $entity) {
                    return $entity->getDefaultValue($this->langService->getDefault());
                })
                ->setFormTypeOption('setter', function(FormInput &$entity, $value) {
                    $entity->setDefaultValue($value, $this->langService->getDefault());
                })
                ->hideOnIndex();
            if (in_array($type, [2, 7, 8])) {
                yield ArrayField::new('options', $this->translateService->translateWords("options"))
                    ->setFormTypeOption('getter', function(FormInput $entity) {
                        return $entity->getOptions($this->langService->getDefault());
                    })
                    ->setFormTypeOption('setter', function(FormInput &$entity, $value) {
                        $entity->setOptions($value, $this->langService->getDefault());
                    })
                    ->hideOnIndex();
            }
        
        // ✅ Other language tabs - use custom getter/setter for each
        foreach($this->langService->getLangs() as $lang){
            if(!$lang->isDefault()){
                $langCode = $lang->getCode();
                
                yield FormField::addTab(
                    $this->translateService->translateWords("form_input","Form Input")." ".
                    $this->translateService->translateWords($lang->getName()),propertySuffix: 'forminput3');
                
                yield TextField::new('name_' . $langCode, $this->translateService->translateWords("name"))
                    ->setFormTypeOption('getter', function(FormInput $entity) use ($langCode) {
                        return $entity->getName($langCode);
                    })
                    ->setFormTypeOption('setter', function(FormInput &$entity, $value) use ($langCode) {
                        $entity->setName($value, $langCode);
                    })
                    ->hideOnIndex();
                
                yield TextField::new('label_' . $langCode, $this->translateService->translateWords("label"))
                    ->setFormTypeOption('getter', function(FormInput $entity) use ($langCode) {
                        return $entity->getLabel($langCode);
                    })
                    ->setFormTypeOption('setter', function(FormInput &$entity, $value) use ($langCode) {
                        $entity->setLabel($value, $langCode);
                    })
                    ->hideOnIndex();
                
                yield TextField::new('default_value_' . $langCode, $this->translateService->translateWords("default_value", "Default value"))
                    ->setFormTypeOption('getter', function(FormInput $entity) use ($langCode) {
                        return $entity->getDefaultValue($langCode);
                    })
                    ->setFormTypeOption('setter', function(FormInput &$entity, $value) use ($langCode) {
                        $entity->setDefaultValue($value, $langCode);
                    })
                    ->hideOnIndex();
                
                if (in_array($type, [2, 7, 8])) {
                    yield ArrayField::new('options_' . $langCode, $this->translateService->translateWords("options"))
                        ->setFormTypeOption('getter', function(FormInput $entity) use ($langCode) {
                            return $entity->getOptions($langCode);
                        })
                        ->setFormTypeOption('setter', function(FormInput &$entity, $value) use ($langCode) {
                            $entity->setOptions($value, $langCode);
                        })
                        ->hideOnIndex();
                }
            }
        }

        /**
         * index
         */
        yield TextField::new('name', $this->translateService->translateWords("name"))
            ->formatValue(function ($value, FormInput $entity) {
                $default = $this->langService->getDefault();
                $name = $entity->getName($default);
                
                $url = $this->adminUrlGenerator
                    ->setController(self::class)
                    ->setAction('edit')
                    ->setEntityId($entity->getId())
                    ->generateUrl();

                return sprintf('<a href="%s">%s</a>', $url, htmlspecialchars($name));
            })
            ->onlyOnIndex()
            ->renderAsHtml();
        yield DateField::new('created_at', $this->translateService->translateWords("created_at","created"))->hideOnForm();
        yield DateField::new('modified_at',$this->translateService->translateWords("modified_at","modified"))->hideOnForm();
        yield BooleanField::new('active', $this->translateService->translateWords("active"))
            ->renderAsSwitch(true)
            ->onlyOnIndex();
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->addFormTheme('@EasyAdmin/crud/form_theme.html.twig')
            ->overrideTemplates([
                'crud/new' => 'admin/modules/modal_crud_new.html.twig',
            ]);
    }
    
    public function new(AdminContext $context): KeyValueStore
    {
        $response = parent::new($context); // returns KeyValueStore
        $request = $this->requestStack->getCurrentRequest();
        $id = $request->query->get('parent');
        $type = $request->query->get("type");

        $url = $this->router->generate('admin_form_input_ajax_create', [
            'parent' => $id,
            'type' => $type
        ]);

        $response = parent::new($context);
        $response->set('url', $url);
        return $response;
    }

    #[Route('/admin/form-input/ajax-create', name: 'admin_form_input_ajax_create', methods: ['POST'])]
    public function ajaxCreate(Request $request): JsonResponse
    {
        $entity = new FormInput();

        $form = $this->buildAccordionItemForm($entity);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $entity->setOrderNum(0);

                $parentId = $request->query->get('parent');
                if ($parentId !== null) {
                    $parent = $this->entityManager->getRepository(Form::class)->find($parentId);
                    if ($parent) {
                        $parent->addChild($entity);
                        $this->entityManager->persist($parent);
                        $entity->setParent($parent);
                    }
                }

                $type = $request->query->get('type');
                if ($type !== null) {
                    $typeEntity = $this->entityManager->getRepository(FormType::class)->find($type);
                    $entity->setType($typeEntity);
                }

                $formData = $request->request->all('FormInput');
            
                // ✅ Process each language using the setter methods with language parameter
                foreach ($this->langService->getLangs() as $lang) { 
                    $code = $lang->getCode();
                    
                    // Set name
                    if (isset($formData['name_' . $code])) {
                        $entity->setName($formData['name_' . $code], $code);
                    }
                    
                    // Set label
                    if (isset($formData['label_' . $code])) {
                        $entity->setLabel($formData['label_' . $code], $code);
                    }
                    
                    // Set default_value
                    if (isset($formData['default_value_' . $code])) {
                        $entity->setDefaultValue($formData['default_value_' . $code], $code);
                    }
                    
                    // Set options
                    $optionsKey = 'options_' . $code;
                    if (isset($formData[$optionsKey]) && is_array($formData[$optionsKey])) {
                        // Remove empty values and reindex array
                        $options = array_values(array_filter($formData[$optionsKey], function($value) {
                            return !empty(trim($value));
                        }));
                        
                        $entity->setOptions($options, $code);
                    } else {
                        // Set empty array if no options provided
                        $entity->setOptions([], $code);
                    }
                }

                $entity->setCreatedAt(new \DateTimeImmutable());
                $entity->setModifiedAt(new \DateTimeImmutable());

                $this->translateService->localizePersistEntity($entity);

                $this->entityManager->persist($entity);
                $this->entityManager->flush();

                $this->addFlash('success', $this->translateService->translateWords("success_upload_form_type","Form type uploaded successfully"));

                return new JsonResponse(['success' => true, 'id' => $entity->getId()]);
            }

            $errors = $this->getFormErrors($form);
            return new JsonResponse(['success' => false, 'errors' => $errors], 422);
        }

        return new JsonResponse([
            'success' => false,
            'message' => 'Invalid request',
            'request_data' => $request->request->all(), // POST data
        ], 400);
    }

    private function buildAccordionItemForm(FormInput $entity)
    {
        $formBuilder = $this->formFactory->createNamedBuilder('FormInput', \Symfony\Component\Form\Extension\Core\Type\FormType::class, $entity, [
            'csrf_protection' => false,
            'method' => 'POST',
            'allow_extra_fields' => true,
        ]);

        $formBuilder->add('active', CheckboxType::class, ['required' => false]);
        $formBuilder->add('required', CheckboxType::class, ['required' => false]);
        if ($this->security->isGranted('ROLE_SUPER_ADMIN')) {   
            $formBuilder->add('mapped', CheckboxType::class, ['required' => false]);
        }

        foreach ($this->langService->getLangs() as $lang) {
            $code = $lang->getCode();
            
            // ✅ Use getter/setter with property_path set to null to prevent automatic mapping
            $formBuilder->add('name_' . $code, TextType::class, [
                'label' => 'Name ' . $lang->getName(),
                'required' => false,
                'mapped' => false,
                'data' => $entity->getName($code) ?? '',
            ]);
            
            $formBuilder->add('label_' . $code, TextType::class, [
                'label' => 'Label ' . $lang->getName(),
                'required' => false,
                'mapped' => false,
                'data' => $entity->getLabel($code) ?? '',
            ]);
            
            $formBuilder->add('default_value_' . $code, TextType::class, [
                'label' => 'Default value ' . $lang->getName(),
                'required' => false,
                'mapped' => false,
                'data' => $entity->getDefaultValue($code) ?? '',
            ]);

            if($entity){
                $type = $entity->getType();
                if($type){
                    $id = $type->getId();
                    if($id){
                        if (in_array($id, [2, 7, 8])) {
                            $formBuilder->add('options_' . $code, CollectionType::class, [
                                'label' => 'Options ' . $lang->getName(),
                                'entry_type' => TextType::class,
                                'allow_add' => true,
                                'allow_delete' => true,
                                'required' => false,
                                'mapped' => false,
                                'data' => $entity->getOptions($code) ?? [],
                                'entry_options' => [
                                    'label' => false,
                                ],
                            ]);
                        }
                    }
                }
            }
        }

        return $formBuilder->getForm();
    }
}
