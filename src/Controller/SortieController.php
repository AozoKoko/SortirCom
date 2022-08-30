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
use PhpParser\Node\Expr\Array_;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Sortie;
use App\Form\SortieType;


class SortieController extends AbstractController
{

    public function archivageSortie($listeSortieUnfiltered): array {
        $listeSortie = array();

        $now = new \DateTime("now");

        $length = count($listeSortieUnfiltered);

        for ($i = 0; $i < $length ; $i++) {
            if (!($listeSortieUnfiltered[$i]->getDateHeureDebut()->modify('+1 month') < $now)) {
                array_push($listeSortie, $listeSortieUnfiltered[$i]);
            }
        }
        return $listeSortie;
    }
    /**
     * @Route("/sortie", name="app_sortie")
     * @throws \Exception
     */
    public function index(
        Request $request,
        SortieRepository $repoSortie, ParticipantRepository $repoParticipant
    ): Response
    {
        $listeSortieUnfiltered = $repoSortie->findAll();
        //on récupère tous les participants
        $listeParticipant = $repoParticipant->findAll();

        $listeSortie = $this->archivageSortie($listeSortieUnfiltered);

        //on créé un formulaire qui va afficher les campus dans le select
        $sortieForm=$this->createForm(triSortieType::class);
        $sortieForm->handleRequest($request);

        //si le formulaire a été validé
        if($sortieForm->isSubmitted() && $sortieForm->isValid()) {
            //on récupère les paramètres rentrés
            $resultat = $request->get("tri_sortie");
            //on effectue une recherche par le campus selectionné
            if($resultat["Campus"] == '')
                $listeSortie;
            else
                $listeSortie = $repoSortie->searchByCampus($resultat["Campus"]);

            $listeSortie = $this->archivageSortie($listeSortie);


            return $this->render('sortie/sortie.html.twig', [
                'sortieForm' => $sortieForm->CreateView(),
                'listeSortie' => $listeSortie,
                'listeParticipant' => $listeParticipant,
            ]);
        }

        return $this->render('sortie/sortie.html.twig', [
            'sortieForm' => $sortieForm->CreateView(),
            'listeSortie' => $listeSortie,
            'listeParticipant' => $listeParticipant
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
        $dateNow = new \DateTime("now");

        $isInscrit = false;

        $participant = $repoParticipant->findOneBy(['id'=>$userID]);
        $listeInscription = $participant->getInscrits()->getValues();
        $length = count($listeInscription);

        for($y = 0; $y < $length; $y++){
            dump($listeInscription[$y]->getId());
            dump($sortie->getId());

            if($listeInscription[$y]->getId() == $sortie->getId() ){
                $isInscrit = true;
            }
        }

        dump($isInscrit);



        $listeParticipantReal = $listeParticipant->getValues();
        for($i = 0; $i < $size; $i++){
                $inscriptions--;
        }

        return $this->render('sortie/showSortie.html.twig', [
            "sortie" => $sortie,
            "getInscriptionsRestantes" => $inscriptions,
            "nomOrga"=> $nomOrga,
            "prenomOrga"=> $prenomOrga,
            "userID"=> $userID,
            "listeParticipant"=>$listeParticipantReal,
            "isInscrit"=>$isInscrit,
            "dateNow"=>$dateNow,
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
     * @Route("/sortie-annulee/{id}", name="app_annulee")
     */
    public function annulerSortie($id): Response
    {
        $em = $this->getDoctrine()->getManager();
        $sortie =  $em->getRepository(Sortie::class)->findOneBy(["id" => $id]);
        $em = $this->getDoctrine()->getManager();
        $etat =  $em->getRepository(Etat::class)->findOneBy(["id" => 6]);
        $sortie->setEtats($etat);

        $em->persist($sortie);
        $em->flush();

        return $this->redirectToRoute('app_sortie');
    }

    /**
     * @Route("/modifSortie/{id}", name="app_modif_sortie")
     */
    public function getFormSortieModify(Request $request, $id): Response
    {
        $sortie = new Sortie();
        $prodForm = $this->createForm(SortieType::class,$sortie);

        $em = $this->getDoctrine()->getManager();
        $sortieB = $em->getRepository(Sortie::class)->findOneBy(['id'=>$id]);
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
        return $this->render('sortie/modifSortie.html.twig', ['Form'=>$prodForm->createView(),
        "item"=>$sortieB]);
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

        if($sortie->getEtats()->getId() == 2 && $sortie->getDateLimiteInscription() > $currentDate){
            $sortie->addParticipant($participant);

            $em->persist($sortie);
            $em->flush();

            return $this->redirectToRoute('app_sortie');
        }

        $this->addFlash("message_fail", sprintf("Problème dans l'inscription"));
        return $this->redirectToRoute('app_sortie');
    }

    /**
     * @Route("/desister/{id}/{idParticipant}", name="app_desister_sortie")
     */
    public function desisterSortie($id, $idParticipant): Response
    {
        $em = $this->getDoctrine()->getManager();
        $sortieRepo = $em->getRepository(Sortie::class);
        $participantRepo = $em->getRepository(Participant::class);
        $currentDate = new \DateTime('now');

        $participant = $participantRepo->findOneBy(['id' =>$idParticipant]);
        $sortie =  $sortieRepo->findOneBy(['id' => $id]);

        if($sortie->getEtats()->getId() == 2 && $sortie->getDateLimiteInscription() > $currentDate){
            $sortie->removeParticipant($participant);

            $em->persist($sortie);
            $em->flush();

            return $this->redirectToRoute('app_sortie');
        }

        $this->addFlash("message_fail", sprintf("Problème dans l'inscription"));
        return $this->redirectToRoute('app_sortie');
    }
}
