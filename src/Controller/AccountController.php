<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\AccountType;
use App\Entity\PasswordUpdate;
use App\Form\RegistrationType;
use App\Form\PasswordUpdateType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AccountController extends AbstractController
{
    /**
     * Permet de gérer le formulaire de connexion
     * 
     * @Route("/login", name="account_login")
     * 
     * @return Response
     */
    public function index(AuthenticationUtils $utils)
    {
        $error = $utils->getLastAuthenticationError();
        $username = $utils->getLastUsername();

        return $this->render('account/login.html.twig', [
            'hasError' => $error != null,
            'username' => $username
        ]);
    }

    /**
     * Permet de se déconnecter
     * 
     * @Route("/logout", name="account_logout")
     *
     * @return Response
     */
    public function logout() {}
    
    /**
     * Permet d'afficher le formulaire d'inscription
     *
     * @Route("/register", name="account_register")
     * @return Response
     */
    public function register(Request $request, UserPasswordEncoderInterface $encoder) {

        $user = new User();

        $form = $this->createForm(RegistrationType::class, $user);

        $form->handleRequest($request);
        dump($form);

        if($form->isSubmitted() && $form->isValid()) {
            $manager = $this->getDoctrine()->getManager();

            $hash = $encoder->encodePassword($user, $user->getHash()); // ici je passe l'entité $user juste pour que l'encoder sache quel algo il faut utiliser (et que j'ai définit dans le security.yaml, où je précise que pour l'entité User, j'utilise bcrypt)
            
            $user->setHash($hash);
            $manager->persist($user);
            $manager->flush();

            $this->addFlash(
                'success',  
                "Votre compte a bien été crée"
            );

            return $this->redirectToRoute('account_login');
        }

        return $this->render('account/registration.html.twig', [
            'form' => $form->createView()
        ]);

    }

    /**
     * Permet d'afficher et de traiter le formulaire de modification de profil
     * @IsGranted("ROLE_USER")
     * @Route("/account/profile", name="account_profile")
     *
     * @return Response
     */
    public function profile(Request $request) {

        $user = $this->getUser(); // Donne l'utilisateur connecté

        $form = $this->createForm(AccountType::class, $user);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $manager = $this->getDoctrine()->getManager();
            $manager->persist($user); // théoriquement pas besoin car l'objet est déjà en session
            $manager->flush();

            $this->addFlash('success', 'Les données du profil enregistrées avec succés');

        }

        return $this->render('account/profile.html.twig', [
            'form' => $form->createView()
        ]);

    }

    /**
     * Permet de modifier le mot de passse
     * @IsGranted("ROLE_USER")
     * @Route("/account/password-update", name="account_password")
     * 
     * @return Response
     */
    public function updatePassword(Request $request, UserPasswordEncoderInterface $encoder) {

        $user = $this->getUser(); // met probablement le user en persistance

        $passwordUpdate = new PasswordUpdate(); // ici passwordUpdate est une class PHP, mais pas une entité (car pas de relation avec la BDD)
        $form = $this->createForm(PasswordUpdateType::class, $passwordUpdate);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) { 

            if(!password_verify($passwordUpdate->getOldPassword(), $user->getHash())) {
                // Gérer l'erreur
                $form->get('oldPassword')->addError(new FormError("Le mot de passe que vous avez tapé ne correspond pas à votre mot de passe actuel"));
                $this->redirectToRoute('account_password');
            } else {
                $hash = $encoder->encodePassword($user, $passwordUpdate->getNewPassword());
                $user->setHash($hash);
                $manager = $this->getDoctrine()->getManager();
                //$manager->persist($user); // donc ici pas besoin de persister
                $manager->flush();

                $this->addFlash('success', 'Votre mot de passe a bien été modifié');

                return $this->redirectToRoute('homepage');
            }
    
        }

        return $this->render('account/password.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * Permet d'afficher le profile de l'utilisateur connecté
     * @IsGranted("ROLE_USER")
     * @Route("/account", name="account_index")
     * 
     * @return Response
     */
    public function myAccount() {
        return $this->render('user/index.html.twig', [
            'user' => $this->getUser()
        ]);
    }

    /**
     * Permet d'afficher toutes les réservations liées à un utilisateur
     *
     * @Route("/account/bookings", name="account_bookings")
     * 
     * @return Response
     */
    public function bookings() {
        return $this->render('account/bookings.html.twig');
    }



}
