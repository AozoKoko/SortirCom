<?php

namespace App\Controller;

use App\Entity\Campus;
use App\Entity\Etat;
use App\Entity\Participant;
use App\Entity\User;
use App\Form\CampusType;
use App\Form\TriSortieType;
use App\Repository\CampusRepository;
use App\Repository\ParticipantRepository;
use App\Repository\SortieRepository;
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
    public function index(
        SortieRepository $repoSortie, CampusRepository $repoCampus, ParticipantRepository $repoParticipant
    ): Response
    {
        $listeCampus = $repoCampus->findAll();
        $listeSortie = $repoSortie->findAll();
        $listeParticipant = $repoParticipant->findAll();

        dump($listeCampus);

        $sortieForm=$this->createForm(triSortieType::class);

        return $this->render('sortie/sortie.html.twig',[
            "sortieForm"=>$sortieForm->CreateView(),
            'listeSortie' => $listeSortie,
            'listeCampus'=> $listeCampus,
            'listeParticipant'=> $listeParticipant,
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
        $repoUser = $em->getRepository(User::class);
        $sortie = $repo->findOneBy(['id'=> $id]);
        $listeParticipant = $sortie->getParticipants();
        $size = count($listeParticipant);
        $nomOrga = $sortie->getOrganisateur()->getNom();
        $prenomOrga = $sortie->getOrganisateur()->getPrenom();
        $inscriptions = $sortie->getNbInscriptionsMax() - 1;
        $userEmail = $this->getUser()->getUserIdentifier();
        $user = $repoUser->findOneBy(['email' => $userEmail]);
        $userID = $user->getId();



        for($i = 0; $i < $size; $i++){

                $inscriptions--;
        }


        return $this->render('sortie/showSortie.html.twig', [
            "sortie" => $sortie,
            "getInscriptionsRestantes" => $inscriptions,
            "nomOrga"=> $nomOrga,
            "prenomOrga"=> $prenomOrga,
            "userID"=>$userID,
            "listeParticipant"=>$listeParticipant,

        ]);
    }

    /**
     * @Route("/delete-sortie/{id}", name="app_delete_sortie")
     */
    public function deleteSortie($id): Response
    {
        //TODO: security check
        //ajouter une sécurité : il faut que ce soit uniquement le créateur qui puisse supprimer la sortie
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
            return $this->redirectToRoute('app_sortie');
        }
        return $this->render('sortie/newSortie.html.twig', ['Form'=>$prodForm->createView()]);
    }

    /**
     * @Route("/sortie-annulee", name="app_annulee")
     */
    public function annulee(): Response
    {
        $em = $this->getDoctrine()->getManager();
        $repoSortie =  $em->getRepository(Sortie::class);

        $listeSortie = $repoSortie->findAll();




        return $this->render('sortie/sortie.html.twig',
            ['listeSortie' => $listeSortie]);
    }

    /**
     * @Route("/recherche-sortie/{id}", name="app_recherche_sortie")
     */
    public function rechercheSortie($id): Response
    {
        $em = $this->getDoctrine()->getManager();

        $repoSortie =  $em->getRepository(Sortie::class);
        $listeSortie = $repoSortie->searchByCampus($id);


        $repoCampus = $em->getRepository(Campus::class);
        $listeCampus = $repoCampus->findAll();
        $sortieForm=$this->createForm(triSortieType::class);

        dump($listeSortie);
        return $this->redirectToRoute('sortie/recherche-sortie.html.twig',[

            'sortieForm'=>$sortieForm->CreateView(),
            'listeSortie' => $listeSortie->getId(),
            'listeCampus'=> $listeCampus
        ]);
    }

    /**
     * @Route("/modifSortie/{id}", name="app_modif_sortie")
     */
    public function getFormSortieModify(Request $request, $id): Response
    {
        $sortie = new Sortie();
        $prodForm = $this->createForm(SortieType::class,$sortie);

        $em = $this->getDoctrine()->getManager();
        $prodForm->handleRequest($request);
        if ($prodForm->isSubmitted()&&$prodForm->isValid()) {
            //Appelle le repository pour la classe User, me permettant d'utiliser
            //des méthodes SQL liées à cette classe
            $userRepo = $em->getRepository(Participant::class);

            $em->persist($sortie);
            $em->flush();
            $this->addFlash('Good', 'Sortie créé !');
            return $this->redirectToRoute('app_main');
        }
        return $this->render('sortie/newSortie.html.twig', ['Form'=>$prodForm->createView()]);
    }

    /**
     * @Route("/inscription/{id}/{idParticipant}", name="app_inscription_sortie")
     */
    public function inscriptionSortie($id, $idParticipant): Response
    {

        $em = $this->getDoctrine()->getManager();
        $sortieRepo = $em->getRepository(Sortie::class);
        $participantRepo = $em->getRepository(Participant::class);
        $currentDate = new \DateTime('now');

        $participant = $participantRepo->findOneBy(['id' =>$idParticipant]);
        $sortie =  $sortieRepo->findOneBy(['id' => $id]);

        if($sortie->getEtats()->getId() == 2 && $sortie->getDateLimiteInscription() < $currentDate){
            $sortie->addParticipant($participant);

            $em->persist($sortie);
            $em->flush();

            return $this->redirectToRoute('app_sortie');
        }



        $this->addFlash("message_fail", sprintf("Problème dans l'inscription"));
        return $this->redirectToRoute('app_sortie');
    }

    /**
     * @Route("/desister/{id}/{idParticipant}", name="app_inscription_sortie")
     */
    public function desisterSortie($id, $idParticipant): Response
    {

        $em = $this->getDoctrine()->getManager();
        $sortieRepo = $em->getRepository(Sortie::class);
        $participantRepo = $em->getRepository(Participant::class);
        $currentDate = new \DateTime('now');

        $participant = $participantRepo->findOneBy(['id' =>$idParticipant]);
        $sortie =  $sortieRepo->findOneBy(['id' => $id]);

        if($sortie->getEtats()->getId() == 2 && $sortie->getDateLimiteInscription() < $currentDate){
            $sortie->removeParticipant($participant);

            $em->persist($sortie);
            $em->flush();

            return $this->redirectToRoute('app_sortie');
        }



        $this->addFlash("message_fail", sprintf("Problème dans l'inscription"));
        return $this->redirectToRoute('app_sortie');
    }
}
