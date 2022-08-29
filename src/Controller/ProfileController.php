<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Entity\User;
use App\Form\ParticipantType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
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
    public function editProfile(Request $request, UserPasswordHasherInterface $passwordHasher): Response
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

            $participantToSave = $participantForm->getData();
            $user = $participantForm->get('user')->getData();
            $password = $user->getPassword();
            dump($password);
            $currentUser->setPassword(
                $passwordHasher->hashPassword(
                    $currentUser,
                    $password
                )
            );


            $em->persist($participantToSave);
            $em->persist($currentUser);
            $em->flush();

            return $this->redirectToRoute('app_vue_profile', [
                'id' => $currentParticipant->getId()
            ]);
        }

        return $this->render('profile/profileEdit.html.twig', [
            "participantForm" => $participantForm->createView()
        ]);
    }

    /**
     * @Route("/vue-other-profile/{id}", name="app_vue_other_profile")
     */
    public function displayOtherUserProfile($id): Response
    {
        $em = $this->getDoctrine()->getManager();

        $userRepo = $em->getRepository(User::class);
        //on récupère le USER grâce à son id rentré en paramètre.
        $chosenUser = $userRepo->find($id);


        $participantRepo = $em->getRepository(Participant::class);
        //on récupère le participant grâce à son id retourné par l'USER.
        $chosenParticipant = $participantRepo->find(['id' => $chosenUser->getId()]);


        return $this->render('profile/otherUserProfile.html.twig', ["participant" => $chosenParticipant]);
    }


}
