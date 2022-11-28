<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ExampleFormController extends AbstractController
{
  #[Route('/example-form', methods: ['GET'], condition: "'dev' === '%kernel.environment%'")]
  public function form(): Response
  {
    return $this->render('form.html.twig', [
      'google_recaptcha_site_key' => $this->getParameter('google_recaptcha_site_key'),
    ]);
  }
}
