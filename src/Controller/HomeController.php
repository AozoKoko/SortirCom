<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Entity\Sortie;
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

        return $this->render('home/main.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/", name="app_home")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        return $this->redirectToRoute('app_login');
    }

    /**
     * @Route("/find", name="app_findall_sorties")
     */
    public function index(): Response
    {
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('App\Entity\Sortie')->findAll();

        return $this->render('sortie/index.html.twig', ['listeSortie' => $repo]);
    }

    /**
     * @Route("/select-sortie/{id}", name="app_select_sortie")
     */
    public function selectSortie($id): Response
    {

        // Repo Sortie
        $repoSortie = $this->getDoctrine()->getRepository(Sortie::class); // Récuperer l'entity manager doctrine
        // Je récupere la sortie correspondante à l'id
        $sortie = $repoSortie->find($id);

        $organisateurId = $sortie->getOrganisateur();


        // Repo Participant
        $repoParticipant = $this->getDoctrine()->getRepository(Participant::class); // Récuperer l'entity manager doctrine
        // Je récupere l'organisateur de la sortie
        $organisateur = $repoParticipant->find($organisateurId->getId());


        // tableau
        $array = array($sortie, $organisateur);

        //return $this->render('home/index.html.twig');

        return new Response("Sortie : " . $sortie . "<br>Organisateur : " . $organisateur);
    }


}
