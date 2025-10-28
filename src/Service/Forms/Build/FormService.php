<?php

namespace App\Service\Forms\Build;

use App\Entity\EvcForms;
use App\Repository\EvcFormRepository;
use Symfony\Component\Form\FormInterface;

class FormService
{
    public EvcForms $formEntity;

    public function __construct(
        private readonly EvcFormRepository $evcFormRepo,
        private readonly FormServiceRegistry $formServiceRegistry,
    ) {
    }

    /**
     * @param int $id Form ID or string form name (e.g. "ShopForm" for attribute forms)
     * @param object|null $entity Optional entity for attribute-based forms prefill (e.g. EvcUser)
     * @return FormInterface|null
     */
    public function getForm(int|string $id, ?object $entity = null): ?FormInterface
    {
        // For DB-defined forms, load the form entity to verify active status
        if (is_int($id)) {
            $this->formEntity = $this->evcFormRepo->findOneBy(['form_id' => $id, 'form_aktiv' => true]);
            if (!$this->formEntity) {
                return null;
            }
        }

        //dd($id);

        // Select appropriate form builder service by form ID or name
        $formService = $this->formServiceRegistry->getServiceFor($id);

        // Pass entity as parameter — needed for attribute-driven builder
        return $formService->getForm($id, $entity);
    }
}
