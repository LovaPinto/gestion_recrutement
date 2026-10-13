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
    /* =======================================================
     *  PAGE LOGIN
     * ======================================================= */
    #[Route('/company/login', name: 'loginCompany')]
    public function login(SessionInterface $session): Response
    {
        // Si déjà connecté → dashboard
        if ($session->has('company')) {
            return $this->redirectToRoute('app_department_ajout');
        }

        return $this->render('company/loginCompany.html.twig');
    }

    /* =======================================================
     *  AUTHENTIFICATION
     * ======================================================= */
    #[Route('/company/auth', name: 'loginSociety', methods: ['POST'])]
    public function auth(
        Request $request,
        CompanyRepository $companyRepository,
        SessionInterface $session
    ): Response {
        $companyName = trim((string) $request->request->get('company_name'));
        $password    = trim((string) $request->request->get('password'));

        // Sécurité minimale
        if ($companyName === '' || $password === '') {
            return $this->render('company/loginCompany.html.twig', [
                'loginError' => true
            ]);
        }

        $company = $companyRepository->findOneBy([
            'companyName' => $companyName
        ]);

        // Vérification
        if (!$company || $company->getPassword() !== $password) {
            return $this->render('company/loginCompany.html.twig', [
                'loginError' => true
            ]);
        }

        /* ================= SESSION ENTREPRISE ================= */
        $session->set('company', [
            'id'   => $company->getId(),
            'name' => $company->getCompanyName(),
        ]);

        // Redirection logique après login
        return $this->redirectToRoute('app_department_ajout');
    }

    /* =======================================================
     *  LOGOUT
     * ======================================================= */
    #[Route('/company/logout', name: 'logoutCompany')]
    public function logout(SessionInterface $session): Response
    {
        // Supprime toute la session (propre)
        $session->invalidate();

        return $this->redirectToRoute('loginCompany', [
            'logout' => 1
        ]);
    }
}
