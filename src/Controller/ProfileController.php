<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Entity\User;
use App\Form\ParticipantType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ProfileController extends AbstractController
{
    /**
     * @Route("/vue-profile/", name="app_vue_profile")
     */
    public function displayProfile(): Response
    {
        $em = $this->getDoctrine()->getManager();

        $userRepo = $em->getRepository(User::class);

        $email = $this->getUser()->getUserIdentifier();

        $currentUser = $userRepo->findOneBy(['email' => $email]);

        $participantRepo = $em->getRepository(Participant::class);
        $currentParticipant = $participantRepo->findOneBy(['id' => $currentUser->getId()]);

        dump($currentParticipant);

        return $this->render('profile/profile.html.twig', ["participant" => $currentParticipant]);
    }

    /**
     * @Route("/edit-profile/", name="app_profile_edit")
     */
    public function editProfile(Request $request): Response
    {
        $em = $this->getDoctrine()->getManager();

        $userRepo = $em->getRepository(User::class);

        $email = $this->getUser()->getUserIdentifier();

        $currentUser = $userRepo->findOneBy(['email' => $email]);

        $participantRepo = $em->getRepository(Participant::class);
        $currentParticipant = $participantRepo->findOneBy(['id' => $currentUser]);

        dump($currentParticipant);

        $participantForm = $this->createForm(ParticipantType::class, $currentParticipant);

        $participantForm->handleRequest($request);

        if ($participantForm->isSubmitted() && $participantForm->isValid()) {
            //dump($participantForm);
            $participantToSave = $participantForm->getData();

            $em->persist($participantToSave);
            $em->flush();

            return $this->redirectToRoute('app_vue_profile', [
                'id' => $currentParticipant->getId()
            ]);
        }

        return $this->render('profile/profileEdit.html.twig', [
            "participantForm" => $participantForm->createView()
        ]);
    }
}
