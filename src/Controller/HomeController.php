<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class HomeController extends AbstractController
{

    /**
     * @Route("/main", name="app_main")
     */
    public function main(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

            return $this->render('home/main.html.twig',['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/", name="app_home")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        return $this->redirectToRoute('app_login');
    }

    /**
     * @Route("/home", name="app_home")
     */
    public function index(): Response
    {
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('App\Entity\Sortie')->findAll();

        return $this->render('sortie/index.html.twig', ['listeSortie' => $repo]);
    }
}
