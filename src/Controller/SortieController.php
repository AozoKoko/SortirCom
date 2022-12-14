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
use Symfony\Component\Validator\Constraints\DateTime;


class SortieController extends AbstractController
{

    public function archivageSortie($listeSortieUnfiltered): array
    {
        $listeSortie = array();

        $now = new \DateTime("now");

        $length = count($listeSortieUnfiltered);

        for ($i = 0; $i < $length; $i++) {
            if ($listeSortieUnfiltered[$i]->getDateHeureDebut()->modify('+1 month') >= $now) {
                array_push($listeSortie, $listeSortieUnfiltered[$i]);
            }
        }
        return $listeSortie;
    }

    public function betweenDate($date1, $date2, $tableauATrier): array
    {
        $tableau = array();

        $length = count($tableauATrier);

        $date1 = new \DateTime($date1);
        $date2 = new \DateTime($date2);
        for ($i = 0; $i < $length; $i++) {
            if (($tableauATrier[$i]->getDateHeureDebut() >= $date1) AND ($tableauATrier[$i]->getDateHeureDebut() <= $date2)) {
                $tableau[] = $tableauATrier[$i];
            }
        }
        dump($tableau);
        return $tableau;
    }

    /**
     * @Route("/sortie", name="app_sortie")
     * @throws \Exception
     */
    public function index(
        Request          $request,
        SortieRepository $repoSortie, ParticipantRepository $repoParticipant
    ): Response
    {
        if($this->getUser() == null){
            return $this->redirectToRoute("app_login");
        }

        $listeSortieUnfiltered = $repoSortie->findAll();
        //on r??cup??re tous les participants
        $listeParticipant = $repoParticipant->findAll();

        $listeSortie = $this->archivageSortie($listeSortieUnfiltered);

        //on cr???? un formulaire qui va afficher les campus dans le select
        $sortieForm = $this->createForm(triSortieType::class);
        $sortieForm->handleRequest($request);

        //si le formulaire a ??t?? valid??
        if ($sortieForm->isSubmitted() && $sortieForm->isValid()) {
            //on r??cup??re les param??tres rentr??s
            $resultat = $request->get("tri_sortie");;
            if ($resultat['BetweenDate1'] != null && $resultat['BetweenDate2'] != null) {
                $listeSortie = $this->betweenDate($resultat['BetweenDate1'], $resultat['BetweenDate2'], $listeSortie);
            }

            //on effectue une recherche par le campus selectionn??
            if ($resultat["Campus"] == '') {
                $listeSortie;
            } else {
                $listeSortie = $repoSortie->searchByCampus($resultat["Campus"]);
            }

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
        if($this->getUser() == null){
            return $this->redirectToRoute("app_login");
        }

        //R??cup??ration du manager
        $em = $this->getDoctrine()->getManager();

        //R??cupuration des diff??rents Repo n??cessaire pour les appel de donn??es
        $repo = $em->getRepository(Sortie::class);
        $repoParticipant = $em->getRepository(Participant::class);
        $repoUser = $em->getRepository(User::class);

        //D??finition de la sortie ?? afficher
        $sortie = $repo->findOneBy(['id' => $id]);

        //R??cup??ration et initialisation des donn??es n??cessaires
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
        $participant = $repoParticipant->findOneBy(['id' => $userID]);
        $listeInscription = $participant->getInscrits()->getValues();
        $length = count($listeInscription);

        //Passe ?? travers tout l'array listeInscription
        for ($y = 0; $y < $length; $y++) {

            //V??rifie si l'ID de l'objet Participant inscrit correspond ?? celui de la bonne sortie
            if ($listeInscription[$y]->getId() == $sortie->getId()) {
                $isInscrit = true;
            }
        }

        //Obtient la liste compl??te des participants inscrits ?? la sortie
        $listeParticipantReal = $listeParticipant->getValues();


        for ($i = 0; $i < $size; $i++) {
            //Retire 1 au nombre d'inscriptions pour chaque entit?? dans la liste
            $inscriptions--;
        }

        return $this->render('sortie/showSortie.html.twig', [
            "sortie" => $sortie,
            "getInscriptionsRestantes" => $inscriptions,
            "nomOrga" => $nomOrga,
            "prenomOrga" => $prenomOrga,
            "userID" => $userID,
            "listeParticipant" => $listeParticipantReal,
            "isInscrit" => $isInscrit,
            "dateNow" => $dateNow,
        ]);
    }


    /**
     * @Route("/new-sortie/", name="app_sortie_form")
     */
    public function getFormSortie(Request $request): Response
    {

        if($this->getUser() == null){
            return $this->redirectToRoute("app_login");
        }
        //cr??e et initialise le formulaire de cr??ation de sorties
        $sortie = new Sortie();
        $prodForm = $this->createForm(SortieType::class, $sortie);

        //r??cup??ration du manager et d??tection de la request
        $em = $this->getDoctrine()->getManager();
        $prodForm->handleRequest($request);

        //V??rification de si la requ??te a ??t?? soumise et est valide
        if ($prodForm->isSubmitted() && $prodForm->isValid()) {

            //Appelle le repository pour la classe User, me permettant d'utiliser
            //des m??thodes SQL li??es ?? cette classe
            $userRepo = $em->getRepository(User::class);

            //Je r??cup??re l'email de la session actuelle
            $idOrga = $this->getUser()->getUserIdentifier();

            //R??cup??re l'objet [User] correspondant ?? l'utilisateur
            //en se servant de son email pour le retrouver dans la base de donn??e
            $currentUser = $userRepo->findOneBy(['email' => $idOrga]);

            //Utilise l'ID de l'objet [User] pour identifier l'objet [Participant] li??
            $orgaRepo = $em->getRepository(Participant::class);
            $orga = $orgaRepo->findOneBy(['id' => $currentUser]);
            $campus = $orga->getCampus();
            $etatRepo = $em->getRepository(Etat::class);
            $etat = $etatRepo->findOneBy(['id' => 2]);


            //Renseigne le champ "Organisateur" de la sortie avec l'utilisateur actuel
            $sortie->setOrganisateur($orga);
            $sortie->setCampus($campus);
            $sortie->setEtats($etat);


            //Debug
            dump($orga);

            $em->persist($sortie);
            $em->flush();
            $this->addFlash('Good', 'Sortie cr???? !');
            return $this->redirectToRoute('app_sortie');
        }
        return $this->render('sortie/newSortie.html.twig', ['Form' => $prodForm->createView()]);
    }

    /**
     * @Route("/sortie-annulee/{id}", name="app_annulee")
     */
    public function annulerSortie($id): Response
    {

        if($this->getUser() == null){
            return $this->redirectToRoute("app_login");
        }
        //R??cup??ration du manager et des Repository
        $em = $this->getDoctrine()->getManager();
        $sortie = $em->getRepository(Sortie::class)->findOneBy(["id" => $id]);
        $etat = $em->getRepository(Etat::class)->findOneBy(["id" => 6]);

        //Change l'??tat de la sortie
        $sortie->setEtats($etat);

        //Envoie la sortie modifi??e en database
        $em->persist($sortie);
        $em->flush();

        return $this->redirectToRoute('app_sortie');
    }

    /**
     * @Route("/modifSortie/{id}", name="app_modif_sortie")
     */
    public function getFormSortieModify(Request $request, $id): Response
    {
        if($this->getUser() == null){
            return $this->redirectToRoute("app_login");
        }

        //Permet l'initialisation du formulaire
        $sortie = new Sortie();
        $prodForm = $this->createForm(SortieType::class, $sortie);

        $em = $this->getDoctrine()->getManager();
        $sortieB = $em->getRepository(Sortie::class)->findOneBy(['id' => $id]);
        $prodForm->handleRequest($request);
        if ($prodForm->isSubmitted() && $prodForm->isValid()) {
            //Appelle le repository pour la classe User, me permettant d'utiliser
            //des m??thodes SQL li??es ?? cette classe

            $em->persist($sortie);
            $em->flush();
            $this->addFlash('Good', 'Sortie cr???? !');
            return $this->redirectToRoute('app_main');
        }
        return $this->render('sortie/modifSortie.html.twig', ['Form' => $prodForm->createView(),
            "item" => $sortieB]);
    }

    /**
     * @Route("/inscription/{id}/{idParticipant}", name="app_inscription_sortie")
     */
    public function inscriptionSortie($id, $idParticipant): Response
    {
        if($this->getUser() == null){
            return $this->redirectToRoute("app_login");
        }

        //R??cup??ration de l'entityManager et des Repo n??cessaires au bon fonctionnement de la m??thode
        $em = $this->getDoctrine()->getManager();
        $sortieRepo = $em->getRepository(Sortie::class);
        $participantRepo = $em->getRepository(Participant::class);

        //R??cup??ration de la date au moment de l'execution de la fonction
        $currentDate = new \DateTime('now');

        //R??cup??ration de l'utilisateur et de la sortie utilis??e par la m??thode
        $participant = $participantRepo->findOneBy(['id' => $idParticipant]);
        $sortie = $sortieRepo->findOneBy(['id' => $id]);

        //V??rification de l'??tat de la sortie et de sa date de limite d'inscription
        if ($sortie->getEtats()->getId() == 2 && $sortie->getDateLimiteInscription() > $currentDate) {

            //Ajoute le participant ?? la sortie et l'ajoute ?? la base de donn??e
            $sortie->addParticipant($participant);
            $em->persist($sortie);
            $em->flush();

            return $this->redirectToRoute('app_sortie');
        }

        $this->addFlash("message_fail", sprintf("Probl??me dans l'inscription"));
        return $this->redirectToRoute('app_sortie');
    }

    /**
     * @Route("/desister/{id}/{idParticipant}", name="app_desister_sortie")
     */
    public function desisterSortie($id, $idParticipant): Response
    {
        if($this->getUser() == null){
            return $this->redirectToRoute("app_login");
        }
        //R??cup??ration de l'entityManager et des Repo n??cessaires au bon fonctionnement de la m??thode
        $em = $this->getDoctrine()->getManager();
        $sortieRepo = $em->getRepository(Sortie::class);
        $participantRepo = $em->getRepository(Participant::class);

        //R??cup??ration de la date au moment de l'execution de la fonction
        $currentDate = new \DateTime('now');

        //R??cup??ration de l'utilisateur et de la sortie utilis??e par la m??thode
        $participant = $participantRepo->findOneBy(['id' => $idParticipant]);
        $sortie = $sortieRepo->findOneBy(['id' => $id]);

        //V??rification de l'??tat de la sortie et de sa date de limite d'inscription
        if ($sortie->getEtats()->getId() == 2 && $sortie->getDateLimiteInscription() > $currentDate) {

            //Ajoute le participant ?? la sortie et l'ajoute ?? la base de donn??e
            $sortie->removeParticipant($participant);
            $em->persist($sortie);
            $em->flush();

            return $this->redirectToRoute('app_sortie');
        }

        $this->addFlash("message_fail", sprintf("Probl??me dans l'inscription"));
        return $this->redirectToRoute('app_sortie');
    }






}
