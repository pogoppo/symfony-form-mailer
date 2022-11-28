<?php

namespace App\Controller;

use App\Form\Type\FormType;
use App\Service\RecaptchaV3Service;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class MailerController extends AbstractController
{
  private MailerInterface $mailer;

  public function __construct(MailerInterface $mailer)
  {
    $this->mailer = $mailer;
  }

  #[Route('/', methods: ['GET'])]
  public function form(): JsonResponse
  {
    $form = $this->createForm(FormType::class);

    $csrfManager = $form->getConfig()->getOption('csrf_token_manager');
    $csrfId = $form->getConfig()->getOption('csrf_token_id');
    $csrfField = $form->getConfig()->getOption('csrf_field_name');
    $tokenId = $csrfId ?: ($form->getName() ?: \get_class($form->getConfig()->getType()->getInnerType()));

    return new JsonResponse([
      'name' => $form->getName(),
      'csrfField' => $csrfField,
      'token' => (string) $csrfManager->getToken($tokenId)
    ]);
  }

  #[Route('/', methods: ['POST'])]
  public function send(Request $request): JsonResponse
  {
    $form = $this->createForm(FormType::class);
    $form->handleRequest($request);

    $validationErrors = [];
    foreach ($form as $fieldName => $formField) {
      foreach ($formField->getErrors() as $error) {
        $validationErrors[$fieldName][] = $error->getMessage();
      }
    }

    if (!$form->isValid()) {
      return new JsonResponse(['success' => false, 'valid' => false, 'error' => null, 'validationErrors' => $validationErrors]);
    }

    $recaptcha = new RecaptchaV3Service(
      $this->getParameter('google_recaptcha_secret_key'),
      $this->getParameter('google_recaptcha_score_threshold'),
    );

    if (!$recaptcha->verify($form->get('recaptchaV3')->getData())) {
      return new JsonResponse(['success' => false, 'valid' => true, 'error' => null, 'validationErrors' => null]);
    }

    $sender = $this->getParameter('mailer_default_sender');
    $subject = $this->getParameter('mailer_default_subject');
    $email = (new TemplatedEmail())
      ->from($sender)
      ->to('you@example.com')
      ->subject($subject)
      ->htmlTemplate('contact.html.twig')
      ->context([
        'your_name' => $form->get('name')->getData(),
        'your_gender' => $form->get('gender')->getData(),
        'your_email' => $form->get('email')->getData(),
        'your_tel' => $form->get('tel')->getData(),
        'email_body' => $form->get('body')->getData(),
      ]);

    try {
      $this->mailer->send($email);
    } catch (TransportExceptionInterface $e) {
      $errors = [$e->getMessage()];
      return new JsonResponse(['success' => false, 'valid' => true, 'errors' => $errors, 'validationErrors' => null]);
    }

    return new JsonResponse(['success' => true, 'valid' => true, 'errors' => null, 'validationErrors' => null]);
  }
}
