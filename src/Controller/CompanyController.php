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
    #[Route('/company', name: 'app_company')]
    public function index(): Response
    {
        return $this->render('company/index.html.twig', [
            'controller_name' => 'CompanyController',
        ]);
    }

    #[Route('/loginCompany', name: 'loginCompany')]
    public function login(): Response
    {
        return $this->render('company/loginCompany.html.twig', [
            'controller_name' => 'CompanyController',
        ]);
    }

    #[Route('/loginSociety', name: 'loginSociety', methods: ['POST'])]
    public function auth(
        Request $request,
        CompanyRepository $companyRepository,
        SessionInterface $session
    ): Response {

        $companyName = $request->request->get('company_name');
        $password    = $request->request->get('password');

        $company = $companyRepository->findOneBy([
            'companyName' => $companyName,
            'password'     => $password,
        ]);

        if (! $company) {
            $this->addFlash('error', 'Nom du société ou mot de passe incorrect');
            return $this->redirectToRoute('connexion_department');
        }

        $session->set('companyId', $company->getId());
        $session->set('companyName', $company->getCompanyName());

        return $this->redirectToRoute('app_department_ajout');
    }

}
