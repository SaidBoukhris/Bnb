<?php

namespace App\Controller;

use App\Entity\Ad;
use App\Form\AnnonceType;
use App\Repository\AdRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminAdController extends AbstractController
{
    /**
     * @Route("/admin/ads", name="admin_ads_index")
     */
    public function index(AdRepository $repository)
    {
        
        return $this->render('admin/ad/index.html.twig', [
            'ads' => $repository->findAll()
        ]);
    }

        /**
     * Permet de modifier une annonce côté administration
     *
     * @Route("/admin/ads/{id}/edit", name="admin_ads_edit")
     * 
     * @param Ad $ad
     * @param Request $request
     * @return Response
     */
    public function edit(Ad $ad, Request $request) {
        $form = $this->createForm(AnnonceType::class, $ad);
        
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $manager = $this->getDoctrine()->getManager();
            $manager->persist($ad);
            $manager->flush();
            
            $this->addFlash(
                'success',
                "L'annonce {$ad->getTitle()} a bien été enregistrée"
            );
        }

        return $this->render('admin/ad/edit.html.twig', [
            'form' => $form->createView(),
            'ad' => $ad
        ]);
    }

    /**
     * Permet de supprimer une annonce
     *
     * @Route("/admin/ads/{id}/delete", name="admin_ads_delete")
     * 
     * @param Ad $ad
     * @return Response
     */
    public function delete(Ad $ad, EntityManagerInterface $manager) {

        if(count($ad->getBookings()) > 0) {
            $this->addFlash(
                'warning', 
                "Vous ne pouvez pas supprimer {$ad->getTitle()} car elle posséde déjà des réservations"
            );
        } else {
            $manager->remove($ad);
            $manager->flush();

            $this->addFlash(
                'success',
                "L'annonce {$ad->getTitle()}a bien été supprimée"
            );
        }

        return $this->redirectToRoute('admin_ads_index');
    } 
}
