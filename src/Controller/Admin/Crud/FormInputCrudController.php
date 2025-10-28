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

        $request = $this->requestStack->getCurrentRequest();
        $type = $request->query->get("type");
        $this->getContext()->getRequest()->setLocale($this->lang);
        $this->translator->getCatalogue($this->lang);
        $this->translator->setLocale($this->lang);
        /**
         * on forms
         */
        yield FormField::addTab(
            $this->translateService->translateSzavak("form_input","Form Input")." ".
            $this->translateService->translateSzavak("options"),propertySuffix: 'forminput1');
            yield BooleanField::new('active',$this->translateService->translateSzavak("active"))
                ->renderAsSwitch(true)
                ->setFormTypeOptions(['data' => true])
                ->onlyOnForms();
            if ($this->security->isGranted('ROLE_SUPER_ADMIN')) { 
                yield BooleanField::new('mapped',$this->translateService->translateSzavak("mapped"))
                    ->renderAsSwitch(true)
                    ->setFormTypeOptions(['data' => false])
                    ->onlyOnForms();
            }
            yield BooleanField::new('required',$this->translateService->translateSzavak("required"))
                ->renderAsSwitch(true)
                ->setFormTypeOptions(['data' => true])
                ->onlyOnForms();
            
        
        yield FormField::addTab(
                $this->translateService->translateSzavak("form_input","Form Input")." ".
                $this->translateService->translateSzavak($this->langService->getDefaultObject()->getName()),propertySuffix: 'forminput2');
            yield TextField::new('name_'.$this->langService->getDefault(), $this->translateService->translateSzavak("name"))
                ->hideOnIndex();
            yield TextField::new('label_'.$this->langService->getDefault(), $this->translateService->translateSzavak("label"))
                ->hideOnIndex();
            yield TextField::new('default_value_'.$this->langService->getDefault(), $this->translateService->translateSzavak("default_value", "Default value"))
                ->hideOnIndex();
            if (in_array($type, [2, 7, 8])) {
                yield ArrayField::new('options_'.$this->langService->getDefault(), $this->translateService->translateSzavak("options"))
                    ->hideOnIndex();
            }
        
        foreach($this->langService->getLangs() as $lang){
            if(!$lang->isDefault()){
                yield FormField::addTab(
                    $this->translateService->translateSzavak("form_input","Form Input")." ".
                    $this->translateService->translateSzavak($lang->getName()),propertySuffix: 'forminput3');
                yield TextField::new('name_'.$lang->getCode(), $this->translateService->translateSzavak("name"))
                    ->hideOnIndex();
                yield TextField::new('label_'.$lang->getCode(), $this->translateService->translateSzavak("label"))
                    ->hideOnIndex();
                yield TextField::new('default_value_'.$lang->getCode(), $this->translateService->translateSzavak("default_value", "Default value"))
                    ->hideOnIndex();
                if (in_array($type, [2, 7, 8])) {
                    yield ArrayField::new('options_'.$lang->getCode(), $this->translateService->translateSzavak("options"))
                        ->hideOnIndex();
                }
            }
        }

        /**
         * index
         */
        yield TextField::new('name', $this->translateService->translateSzavak("name"))
            ->formatValue(function ($value, $entity) {
                $url = $this->adminUrlGenerator
                    ->setController(self::class)
                    ->setAction('edit')
                    ->setEntityId($entity->getId())
                    ->generateUrl();

                return sprintf('<a href="%s">%s</a>', $url, htmlspecialchars($value));
            })
            ->onlyOnIndex()
            ->renderAsHtml();
        yield DateField::new('created_at', $this->translateService->translateSzavak("created_at","created"))->hideOnForm();
        yield DateField::new('modified_at',$this->translateService->translateSzavak("modified_at","modified"))->hideOnForm();
        yield BooleanField::new('active', $this->translateService->translateSzavak("active"))
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

        //$entity = $this->entityManager->getRepository(\App\Entity\Form::class)->find($id);

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
            
                foreach ($this->langService->getLangs() as $lang) { 
                    $code = $lang->getCode();
                    $optionsKey = 'options_' . $code;
                    
                    // Check if options data exists in the request
                    if (isset($formData[$optionsKey]) && is_array($formData[$optionsKey])) {
                        // Remove empty values and reindex array
                        $options = array_values(array_filter($formData[$optionsKey], function($value) {
                            return !empty(trim($value));
                        }));
                        
                        // Set options using setter method
                        $setter = 'setOptions' . ucfirst($code);
                        if (method_exists($entity, $setter)) {
                            $entity->$setter($options);
                        }
                    } else {
                        // Set empty array if no options provided
                        $setter = 'setOptions' . ucfirst($code);
                        if (method_exists($entity, $setter)) {
                            $entity->$setter([]);
                        }
                    }
                }

                $entity->setCreatedAt(new \DateTimeImmutable());
                $entity->setModifiedAt(new \DateTimeImmutable());

                $this->translateService->localizePersistEntity($entity);

                $this->entityManager->persist($entity);
                $this->entityManager->flush();

                $this->addFlash('success', $this->translateService->translateSzavak("success_upload_form_type","Form type uploaded successfully"));

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
            $formBuilder->add('name_' . $code, TextType::class, [
                'label' => 'Name ' . $lang->getName(),
                'required' => false,
            ]);
            $formBuilder->add('label_' . $code, TextType::class, [
                'label' => 'Label ' . $lang->getName(),
                'required' => false,
            ]);
            $formBuilder->add('default_value_' . $code, TextType::class, [
                'label' => 'Default value ' . $lang->getName(),
                'required' => false,
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
