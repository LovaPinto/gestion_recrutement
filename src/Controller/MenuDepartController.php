<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends BaseController
{
    #[Route('/dashboard', name: 'dashboard')]
    public function index(Request $request): Response
    {
        $session = $request->getSession();

        // Sécurité minimale : vérifier qu'on est connecté
        if (! $session->get('manager_id') &&
            ! $session->get('rh_id') &&
            ! $session->get('department_id')) {
            throw $this->createAccessDeniedException('Accès refusé.');
        }

        // Menus dynamiques
        $menus = $this->getMenus($session);

        return $this->render('department/menu.html.twig', [
            'menus' => $menus,
        ]);
    }
}
