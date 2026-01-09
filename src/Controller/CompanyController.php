<?php

namespace App\Controller;

use App\Repository\CompanyRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

final class CompanyController extends AbstractController
{
    /* ===================== PAGE LOGIN ===================== */
    #[Route('/company/login', name: 'loginCompany')]
    public function login(SessionInterface $session): Response
    {
        if ($session->get('company')) {
            return $this->redirectToRoute('app_department_ajout');
        }
        return $this->render('company/loginCompany.html.twig');
    }

    /* ===================== AUTHENTIFICATION ===================== */
    #[Route('/company/auth', name: 'loginSociety', methods: ['POST'])]
    public function auth(
        Request $request,
        CompanyRepository $companyRepository,
        SessionInterface $session
    ): Response {

        $companyName = $request->request->get('company_name');
        $password    = $request->request->get('password');
        $company = $companyRepository->findOneBy([
            'companyName' => $companyName
        ]);

        if (!$company || $company->getPassword() !== $password) {
            $this->addFlash('error', 'Nom de la société ou mot de passe incorrect');
            return $this->redirectToRoute('loginCompany');
        }

        // ✅ CRÉATION DE LA SESSION UNIQUE "company"
        $session->set('company', [
            'id'   => $company->getId(),
            'name' => $company->getCompanyName(),
        ]);
        $this->addFlash('success', 'Connexion réussie !');
        return $this->redirectToRoute('app_department_ajout');
    }

    /* ===================== LOGOUT (FIN DE SESSION) ===================== */
    #[Route('/company/logout', name: 'logoutCompany')]
    public function logout(SessionInterface $session): Response
    {
        $session->remove('company');
        $session->invalidate();
        $this->addFlash('success', 'Session terminée. À bientôt 👋');
        return $this->redirectToRoute('loginCompany');
    }
}
