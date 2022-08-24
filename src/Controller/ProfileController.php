<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ParticipantType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ProfileController extends AbstractController
{
    /**
     * @Route("/vue_profile/{id}", name="app_vue_profile")
     */
    public function displayProfile($id): Response
    {
        $repoParticipant = $this->getDoctrine()->getRepository(Participant::class);
        $participant = $repoParticipant->find($id);
        return $this->render('profile/profile.html.twig', ["participant"=>$participant]);
    }

    /**
     * @Route("/edit-profile", name="app_profile_edit")
     */
    public function editProfile(Request $request): Response
    {
       $participant = new Participant();
       $participantForm = $this->createForm(ParticipantType::class, $participant);

       $participantForm->handleRequest($request);

       if($participantForm->isSubmitted() && $participantForm->isValid())
       {
           $participantToSave = $participantForm->getData();
           $em = $this->getDoctrine()->getManager();
           $em->persist($participantToSave);
           $em->flush();

           return $this->redirectToRoute('app_vue_profile', [
               'id' => $participant->getId()
           ]);
       }

       return $this->render('profile/profileEdit.html.twig',[
           "participantForm" => $participantForm->createView()
       ]);
    }
}
