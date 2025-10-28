<?php

namespace App\Service\Forms\Submit;

use App\Service\Forms\Submit\BaseSubmitService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use App\Service\Modules\LangService;
use App\Repository\EvcFormFieldRepository;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\HttpFoundation\RequestStack;
use \DateTime;
use \DateTimeImmutable;

class DefaultSubmitService extends BaseSubmitService
{
    
	public function __construct(
        private readonly LangService $langService,
        private readonly EvcFormFieldRepository $formFieldRepo,
        private readonly MailerInterface $mailer,
		private readonly RequestStack $requestStack,
    ) {
        parent::__construct($langService, $formFieldRepo, $mailer);
    }
	
	public function processData(): void
    {
		$this->html = "";

		foreach($this->data as $key => $value) {
			$field = $this->formFieldRepo->findOneBy(['field_name' => $key]);
			if($field && $field->getFieldType() != 'file') {
				if(is_array($value)){
					$value = implode(",", $value);
				}
				if ($value instanceof DateTime) {
					$value = $value->format('Y-m-d'); // Adjust format as needed
				} elseif ($value instanceof DateTimeImmutable) {
					$value = $value->format('Y-m-d'); // Handle immutable DateTime objects
				}
				if ($value instanceof DateTime || $value instanceof DateTimeImmutable) {
					$value = $value->format('Y-m-d'); // Convert DateTime object to string
				}
				$value = (is_object($value) && method_exists($value, 'format')) 
					? $value->format('Y-m-d') 
					: (string) $value;
				$this->html .= "<p><strong>{$field->getFieldLabel()}: </strong>{$value}</p>";
			}
		}
		$this->sendMail();
    }

	public function sendMail(): void
	{
		$email = (new Email())
			->from($_ENV['MAIL_FROM'])
			->to($this->to)
			->subject($this->subject)
			->html($this->html);

		if(isset($this->data['file'])){
			if ($this->data['file'] instanceof \Symfony\Component\HttpFoundation\File\UploadedFile) {
				$email->attachFromPath($this->data['file']->getRealPath(), $this->data['file']->getClientOriginalName(), $this->data['file']->getClientMimeType());
			}
		}
			
		try {
			// Attempt to send the email
			$this->mailer->send($email);
		} catch (TransportExceptionInterface $e) {
			// If sending the email fails, throw an error
			throw new \RuntimeException('Failed to send email: ' . $e->getMessage());
		}
	}

	public function success(): RedirectResponse
	{
		return new RedirectResponse("/{$this->thankYouPage}");
	}

	public function failed(): RedirectResponse
	{
		$currentRequest = $this->requestStack->getCurrentRequest();
		$currentUrl = $currentRequest ? $currentRequest->getRequestUri() : '/';

		return new RedirectResponse($currentUrl);
	}

	public function validate(): bool
	{
		return true;
	}

	public function supports(): string
    {
        return 'default';
    }
}
