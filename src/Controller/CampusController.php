<?php

namespace App\Controller;

use App\Entity\Campus;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CampusController extends AbstractController
{
    /**
     * @Route("/campus", name="app_campus")
     */
    public function campus(): Response
    {

        $repoCampus = $this->getDoctrine()->getRepository(Campus::class);
        $listeCampus = $repoCampus->findAll();

        return $this->render('campus/campus.html.twig',
            ['liste_Campus' => $listeCampus]);
    }

//    /**
//     * @Route("/suppr_campus", name="app_suppr_campus")
//     */
//    public function supprimerCampus(): Response
//    {
//
//        // Repo Article
//        $repoArticle = $this->getDoctrine()->getRepository(Article::class); // Récuperer l'entity manager doctrine
//
//        // Je récupere un article
//        $article = $repoArticle->find($id);
//
//
//        return $this->render('article/article-show.html.twig', [
//            "article" => $article,
//        ]);
//    }
}
