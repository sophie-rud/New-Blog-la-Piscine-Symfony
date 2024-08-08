<?php

declare(strict_types = 1);

namespace App\Controller\Admin;



use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;


// injection de classe
class AdminUserController extends AbstractController {

    #[Route('/admin/user/insert', name: 'admin_user_insert')]
    public function insertAdmin(UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager, Request $request): Response
    {
        if( $request->getMethod() === 'POST' ) {
            $email = $request->get('email');
            $password = $request->get('password');
        }

        $user = new User();

        try {
            // on hashe le mot de passe et c'est le mdp hashé qu'on enregistre en bdd
            $hashedPassword = $passwordHasher ->hashPassword($user, $email);

            $user->setEmail($email);
            $user->setPassword($hashedPassword);
            $user->setRoles(['ROLE_ADMIN']);

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Nouvel utilisateur ajouté');

        } catch(\Exception $exception) {
            // attention, éviter de renvoyer le message directement récupéré depuis les erreurs SQL
            $this->addFlash('error', $exception->getMessage());
        }

        return $this->render('admin/page/user/insertUser.html.twig', [
            'user' => $user
        ]);

    }


}
