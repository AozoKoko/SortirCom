<?php

namespace App\Controller;

use App\Entity\Campus;
use App\Entity\Participant;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Sortie;
use App\Form\SortieType;

class SortieController extends AbstractController
{
    /**
     * @Route("/sortie", name="app_sortie")
     */
    public function index(): Response
    {
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('App\Entity\Sortie')->findAll();

        return $this->render('home/sortie.html.twig', ['listeSortie' => $repo]);
    }

    /**
     * @Route("/modifSortie/{id}", name="app_modif_sortie")
     */
    public function modifSortie($id): Response
    {
        $em = $this->getDoctrine()->getManager();
        $sortie = $em->getRepository('App\Entity\Sortie')->findBy($id);

        return $this->render('sortie/modifSortie.html.twig', [
            "sortie" => $sortie,
        ]);
    }

    /**
     * @Route("/showSortie/{id}", name="app_show_sortie")
     */
    public function showSortie($id): Response
    {
        $em = $this->getDoctrine()->getManager();
        $sortie = $em->getRepository('App\Entity\Sortie')->findBy($id);

        return $this->render('sortie/showSortie.html.twig', [
            "sortie" => $sortie,
        ]);
    }

    /**
     * @Route("/supprSortie/{id}", name="app_suppr_sortie")
     */
    public function supprSortie($id): Response
    {
        $em = $this->getDoctrine()->getManager();
        $sortie = $em->getRepository('App\Entity\Sortie')->findBy($id);

        return $this->render('sortie/supprSortie.html.twig', [
            "sortie" => $sortie,
        ]);
    }

    /**
     * @Route("/newSortie/", name="app_sortie_form")
     */
    public function getFormSortie(Request $request): Response
    {
        $sortie = new Sortie();
        $prodForm = $this->createForm(SortieType::class,$sortie);


        $em = $this->getDoctrine()->getManager();
        $prodForm->handleRequest($request);
        if ($prodForm->isSubmitted()&&$prodForm->isValid()) {

            //Appelle le repository pour la classe User, me permettant d'utiliser
            //des méthodes SQL liées à cette classe
            $userRepo = $em->getRepository(User::class);

            //Je récupère l'email de la session actuelle
            $idOrga = $this->getUser()->getUserIdentifier();

            //Récupère l'objet [User] correspondant à l'utilisateur
            //en se servant de son email pour le retrouver dans la base de donnée
            $currentUser = $userRepo->findOneBy(['email'=> $idOrga]);

            //Utilise l'ID de l'objet [User] pour identifier l'objet [Participant] lié
            $orgaRepo = $em->getRepository(Participant::class);
            $orga = $orgaRepo->findOneBy(['id'=>$currentUser]);
            $campus = $orga->getCampus();

            //Renseigne le champ "Organisateur" de la sortie avec l'utilisateur actuel
            $sortie->setOrganisateur($orga);
            $sortie->setCampus($campus);

            //Debug
            dump($orga);

            //TODO Renseigner le Campus
            //$camp = $em->find
            //TODO faire en sorte que le paramètre état soit renseigné comme "ouvert"
            //$etatRepo = $em->getRepository(Campus::class);
            //TODO Rajouter un champ dans le form pour le lieu de la sortie


            $em->persist($sortie);
            $em->flush();
            $this->addFlash('Good', 'Sortie créé !');
            return $this->redirectToRoute('app_home');
        }
        return $this->render('sortie/newSortie.html.twig', ['Form'=>$prodForm->createView()]);
    }

}
