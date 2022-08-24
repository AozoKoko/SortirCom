<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SortieController extends AbstractController
{
    /**
     * @Route("/sortie", name="app_sortie")
     */
    public function index(): Response
    {
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('App\Entity\Sortie')->findAll();

        return $this->render('sortie/index.html.twig', ['listeSortie' => $repo]);
    }

    /**
     * @Route("/newSortie", name="app_new_sortie")
     */
    public function newSortie(): Response
    {
        return $this->render('sortie/newSortie.html.twig');
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


}
