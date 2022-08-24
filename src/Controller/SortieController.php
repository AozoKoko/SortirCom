<?php

namespace App\Controller;

use App\Entity\Participant;
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
            $idOrga = $this->getUser()->getUserIdentifier();
            $orga = $em->find(Participant::class,$idOrga);

            $sortie->setOrganisateur($orga);
            var_dump($sortie);
            $em->persist($sortie);
            $em->flush();
            $this->addFlash('Good', 'Sortie créé !');
            return $this->redirectionToRoute('app_home');
        }
        return $this->render('sortie/newSortie.html.twig', ['Form'=>$prodForm->createView()]);
    }

}
