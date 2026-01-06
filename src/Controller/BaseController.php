<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

abstract class BaseController extends AbstractController
{
    protected function getMenus(SessionInterface $session): array
    {
        if ($session->get('manager_id')) {
            return ['dashboard','departement','utilisateur'];
        }

        if ($session->get('rh_id')) {
            return ['dashboard','recrutement','offre','candidat'];
        }

        if ($session->get('department_id')) {
            return ['dashboard','recrutement','offre'];
        }

        return [];
    }
}
