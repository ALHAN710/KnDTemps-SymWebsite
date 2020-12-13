<?php

namespace App\Controller\Admin;

use App\Repository\RoleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminUserController extends AbstractController
{
    /**
     * @Route("/admin/users/home", name="admin_users_index")
     */
    public function index(EntityManagerInterface $manager, RoleRepository $roleRepo)
    {
        $roles = [];
        foreach ($roleRepo->findAll() as $role) {
            $str = str_replace('_', ' ', substr($role->getTitle(), 5));
            $roles['' . $role->getTitle()] = $str;
        }
        //dd($roles);

        return $this->render('admin/users/index_users.html.twig', [
            'users' => $manager->getRepository('App:User')->findAll(),
            'roles' => $roles,
        ]);
    }
}
