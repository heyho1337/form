<?php

namespace App\Controller\Admin\Crud;

use App\Entity\Form;
use App\Service\Admin\CrudService;
use App\Service\Modules\LangService;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use App\Service\Modules\TranslateService;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;

class FormCrudController extends AbstractCrudController
{
    private string $lang;

    public function __construct(
        private readonly AdminUrlGenerator $adminUrlGenerator,
        private readonly CrudService $crudService,
        private readonly LangService $langService,
        private readonly TranslateService $translateService,
        private readonly RequestStack $requestStack,
        private readonly TranslatorInterface $translator,
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
        return Form::class;
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof Form) return;

        $this->crudService->setEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof Form) return;

        $this->crudService->setEntity($entityManager, $entityInstance);
    }

    public function configureFields(string $pageName): iterable
    {
        $this->getContext()->getRequest()->setLocale($this->lang);
        $this->translator->getCatalogue($this->lang);
        $this->translator->setLocale($this->lang);
        
        /**
         * on forms
         */
        yield FormField::addTab($this->translateService->translateWords("options"),propertySuffix: 'form');
            yield BooleanField::new('active',$this->translateService->translateWords("active"))
                ->renderAsSwitch(true)
                ->setFormTypeOptions(['data' => true])
                ->onlyOnForms();
            yield BooleanField::new('captcha',$this->translateService->translateWords("captcha"))
                ->renderAsSwitch(true)
                ->setFormTypeOptions(['data' => true])
                ->onlyOnForms();
            yield BooleanField::new('any',$this->translateService->translateWords("any","Privacy Policy"))
                ->renderAsSwitch(true)
                ->setFormTypeOptions(['data' => true])
                ->onlyOnForms();
            yield BooleanField::new('aszf',$this->translateService->translateWords("aszf","General Terms and Conditions"))
                ->renderAsSwitch(true)
                ->setFormTypeOptions(['data' => true])
                ->onlyOnForms();
            yield TextField::new('name', $this->translateService->translateWords("name"))
                ->hideOnIndex();
            yield TextField::new('email', $this->translateService->translateWords("email"))
                ->hideOnIndex();
            yield AssociationField::new('redirrect', $this->translateService->translateWords("redirrect", "Thank You page"))
                ->setRequired(false)
                ->autocomplete()
                ->hideOnIndex();
        
        // ✅ Default language tab - use custom getter/setter
        yield FormField::addTab($this->translateService->translateWords($this->langService->getDefaultObject()->getName()),propertySuffix: 'form1');
            yield TextField::new('subject', $this->translateService->translateWords("subject"))
                ->setFormTypeOption('getter', function(Form $entity) {
                    return $entity->getSubject($this->langService->getDefault());
                })
                ->setFormTypeOption('setter', function(Form &$entity, $value) {
                    $entity->setSubject($value, $this->langService->getDefault());
                })
                ->hideOnIndex();
        
        // ✅ Other language tabs - use custom getter/setter for each
        foreach($this->langService->getLangs() as $lang){
            if(!$lang->isDefault()){
                $langCode = $lang->getCode();
                
                yield FormField::addTab($this->translateService->translateWords($lang->getName()),propertySuffix: 'form2');
                
                yield TextField::new('subject_' . $langCode, $this->translateService->translateWords("subject"))
                    ->setFormTypeOption('getter', function(Form $entity) use ($langCode) {
                        return $entity->getSubject($langCode);
                    })
                    ->setFormTypeOption('setter', function(Form &$entity, $value) use ($langCode) {
                        $entity->setSubject($value, $langCode);
                    })
                    ->hideOnIndex();
            }
        }

        if ($pageName === Crud::PAGE_EDIT) {
            yield FormField::addTab($this->translateService->translateWords("form_input","Form input"),propertySuffix: 'form3');
                yield AssociationField::new('children', $this->translateService->translateWords("form_input","Form input"))
                    ->setRequired(false)
                    ->hideOnIndex();
        }

        /**
         * index
         */
        yield TextField::new('name', $this->translateService->translateWords("name"))
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
            ->addFormTheme('admin/form/form_input_tab.html.twig');
    }
}