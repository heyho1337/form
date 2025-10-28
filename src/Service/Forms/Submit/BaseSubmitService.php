<?php

namespace App\Service\Forms\Submit;

use Symfony\Component\HttpFoundation\Response;
use App\Service\Modules\LangService;
use App\Repository\EvcFormFieldRepository;
use Symfony\Component\Mailer\MailerInterface;
use App\Interface\FormSubmitServiceInterface;

abstract class BaseSubmitService implements FormSubmitServiceInterface
{

    protected string $thankYouPage;
    protected string $to;
    protected string $subject;
    protected array|object $data;
    protected string $html;

    public function __construct(
        private readonly LangService $langService,
        private readonly EvcFormFieldRepository $formFieldRepo,
        private readonly MailerInterface $mailer,
    )
    {
        
    }

    public function setThankYouPage(string $thankYouPage): static
    {
        $this->thankYouPage = $thankYouPage;
        return $this;
    }

    public function setTo(string $to): static
    {
        $this->to = $to;
        return $this;
    }

    public function setSubject(string $subject): static
    {
        $this->subject = $subject;
        return $this;
    }

    public function setData(array|object $data): static
    {
        $this->data = $data;
        return $this;
    }

    abstract public function processData(): void;

    abstract public function sendMail(): void;

    abstract public function success(): mixed;

    abstract public function failed(): mixed;

    abstract public function validate(): bool;

    abstract public function supports(): string;
}
