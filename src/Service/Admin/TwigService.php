<?php

namespace App\Service\Admin;

use App\Repository\FormTypeRepository;
use App\Service\Modules\FormInputService;
use App\Service\Modules\LangService;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Twig\TwigFunction;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Provider\AdminContextProvider;

class TwigService extends AbstractExtension implements GlobalsInterface
{
    public function __construct(
        private readonly AdminUrlGenerator $adminUrlGenerator,
        private readonly FormTypeRepository $formTypeRepo,
        private readonly FormFactoryInterface $formFactory,
        private readonly AdminContextProvider $adminContextProvider,
        private FormInputService $formInputService,
    )
    {
    }

    public function getGlobals(): array
    {
        $formType = $this->formTypeRepo->findBy(['active' => 1]);
        return [
            'adminUrlGenerator' => $this->adminUrlGenerator,
            'formType' => $formType,
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('render_child_forms', [$this, 'renderChildForms'], [
                'is_safe' => ['html'],
                'needs_environment' => true,
            ]),
        ];
    }

    public function renderChildForms(\Twig\Environment $twig, $childId): string
    {
        $context = $this->adminContextProvider->getContext();
        if (!$context instanceof AdminContext) {
            return '';
        }

        $entity = $context->getEntity()->getInstance();

        if (!$entity || !method_exists($entity, 'getChildren')) {
            return '';
        }

        $children = $entity->getChildren() ?? new ArrayCollection();

        $formBuilder = $this->formFactory->createNamedBuilder(
            'form_show',
            FormType::class,
            null,
            ['csrf_protection' => false]
        );

        $this->formInputService->setFormBuilder($formBuilder);

        $childFormView = null;
        foreach ($children as $child) {
            $inputMethod = "create" . $child->getType()->getClass();
            $this->formInputService->$inputMethod($child);
            if ($child->getId() === $childId) {
                $childFormView = $formBuilder->getForm()->createView();
                break;
            }
        }

        return $twig->render('admin/modules/child_forms.html.twig', [
            'childFormView' => $childFormView,
            'id' => $childId,
        ]);
    }
}

