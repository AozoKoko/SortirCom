<?php

namespace App\Controller;

use App\Entity\Campus;
use App\Entity\Etat;
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
        $repoSortie =  $em->getRepository(Sortie::class);

        $listeSortie = $repoSortie->findAll();




        return $this->render('sortie/sortie.html.twig',
            ['listeSortie' => $listeSortie]);
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
     * @Route("/show-sortie/{id}", name="app_show_sortie")
     */
    public function showSortie($id): Response
    {
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository(Sortie::class);
        $repoParticipant = $em->getRepository(Participant::class);
        $sortie = $repo->findOneBy(['id'=> $id]);
        $listeParticipants =  $repoParticipant->findAll();
        $size = count($listeParticipants);
        $nomOrga = $sortie->getOrganisateur()->getNom();
        $prenomOrga = $sortie->getOrganisateur()->getPrenom();
        $inscriptions = $sortie->getNbInscriptionsMax();

        dump($nomOrga);

        for($i = 0; $i < $size; $i++){

            $currentParticipant = $listeParticipants[$i]->getInscrits();

            if($currentParticipant = $id){
                $inscriptions--;
            }
        }

        return $this->render('sortie/showSortie.html.twig', [
            "sortie" => $sortie,
            "getInscriptionsRestantes" => $inscriptions,
            "nomOrga"=> $nomOrga,
            "prenomOrga"=> $prenomOrga,
        ]);
    }

    /**
     * @Route("/delete-sortie/{id}", name="app_delete_sortie")
     */
    public function deleteSortie($id): Response
    {
        $em = $this->getDoctrine()->getManager();

        $sortie = $em->getRepository(Sortie::class)->find($id);
        dump($sortie);
        $em->remove($sortie);
        $em->flush();

        return $this->redirectToRoute('app_sortie');
    }

    /**
     * @Route("/new-sortie/", name="app_sortie_form")
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
            $etatRepo = $em->getRepository(Etat::class);
            $etat = $etatRepo->findOneBy(['id'=>2]);

            //Renseigne le champ "Organisateur" de la sortie avec l'utilisateur actuel
            $sortie->setOrganisateur($orga);
            $sortie->setCampus($campus);
            $sortie->setEtats($etat);

            //Debug
            dump($orga);

            $em->persist($sortie);
            $em->flush();
            $this->addFlash('Good', 'Sortie créé !');
            return $this->redirectToRoute('app_main');
        }
        return $this->render('sortie/newSortie.html.twig', ['Form'=>$prodForm->createView()]);
    }

}
