<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ParticipantType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProfileController extends AbstractController
{
    /**
     * @Route("/profile", name="app_profile")
     */
    public function displayProfile(): Response
    {
        $profile = new Participant();
        $profileForm = $this->createForm(ParticipantType::class, $profile);
        return $this->render('profile/profile.html.twig',[
            "participant" => $profileForm->CreateView()
        ]);
    }

    /**
     * @Route("/edit_profile", name="app_profile_edit")
     */
    public function editProfile(): Response
    {
        return $this->render('profile/profileEdit.html.twig');
    }
}
