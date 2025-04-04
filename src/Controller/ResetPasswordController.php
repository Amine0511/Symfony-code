<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

#[Route('/reset-password')]
class ResetPasswordController extends AbstractController
{
    #[Route('/forgot', name: 'app_forgot_password')]
    public function forgotPassword(Request $request): Response
    {
        return $this->render('reset_password/forgot.html.twig');
    }

    #[Route('/reset/{token}', name: 'app_reset_password')]
    public function resetPassword(string $token): Response
    {
        return $this->render('reset_password/reset.html.twig', [
            'token' => $token
        ]);
    }
} 