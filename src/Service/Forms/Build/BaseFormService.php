<?php

namespace App\Service\Forms\Build;

use App\Entity\EvcForms;
use App\Repository\EvcFormRepository;
use App\Service\Modules\LangService;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Bundle\SecurityBundle\Security;
use App\Interface\FormBuilderServiceInterface;

abstract class BaseFormService implements FormBuilderServiceInterface
{
    public EvcForms $formEntity;

    public function __construct(
        protected EvcFormRepository $evcFormRepo,
        protected LangService $langService,
        protected FormFactoryInterface $formFactory,
        protected UrlGeneratorInterface $urlGenerator,
        protected RequestStack $requestStack,
        protected Security $security
    ) {
    }

    abstract public function getForm(int|string $id, ?object $entity = null): ?FormInterface;

    abstract public function supports(): string|int;
}
