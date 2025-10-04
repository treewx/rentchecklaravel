<?php

namespace App\Mail;

use App\Services\MailtrapService;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\MessageConverter;

class MailtrapTransport extends AbstractTransport
{
    protected MailtrapService $mailtrapService;

    public function __construct(MailtrapService $mailtrapService)
    {
        parent::__construct();
        $this->mailtrapService = $mailtrapService;
    }

    protected function doSend(SentMessage $message): void
    {
        $email = MessageConverter::toEmail($message->getOriginalMessage());

        $to = $email->getTo()[0];
        $toAddress = $to->getAddress();
        $toName = $to->getName() ?? '';

        $subject = $email->getSubject() ?? '';
        $textBody = $email->getTextBody() ?? '';
        $htmlBody = $email->getHtmlBody() ?? null;

        $this->mailtrapService->send($toAddress, $toName, $subject, $textBody, $htmlBody);
    }

    public function __toString(): string
    {
        return 'mailtrap';
    }
}
