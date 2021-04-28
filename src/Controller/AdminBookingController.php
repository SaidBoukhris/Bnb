<?php

namespace App\Controller;

use App\Entity\Booking;
use App\Form\AdminBookingType;
use App\Repository\BookingRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminBookingController extends AbstractController
{
    /**
     * Permet d'afficher la liste des réservations
     * 
     * @Route("/admin/bookings", name="admin_bookings_index")
     * 
     * @return Response
     */
    
    public function index(BookingRepository $repo)
    {

        return $this->render('admin/booking/index.html.twig', [
            'bookings' => $repo->findAll()
        ]);
    }

    /**
     * Permet d'éditer une réservation
     *
     * @Route("admin/booking/{id}/edit", name="admin_booking_edit")
     * 
     * @return Response
     */
    public function edit(Booking $booking, Request $request) {
        
        $form = $this->createForm(AdminBookingType::class, $booking);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            // Soit je recalcule le montant ici
            //$booking->setAmount($booking->getDuration()* $booking->getAd()->getPrice());

            //ou je force l'entité à le faire grâce à l'événement PreUpdate et au fait de mettre le prix à 0
            $booking->setAmount(0);

            $manager = $this->getDoctrine()->getManager();
            $manager->flush();

            $this->addFlash(
                'success',
                "La réservation n° {$booking->getId()} a bien été prise en compte"
            );

            return $this->redirectToRoute('admin_bookings_index');
        }

        return $this->render('admin/booking/edit.html.twig', [
            'form' => $form->createView(),
            'booking' => $booking
        ]);
    }

    /**
     * Permet de supprimer une réservation
     * 
     * @Route("admin/booking/{id}/delete", name="admin_booking_delete")
     * 
     * @return Response
     */
    public function delete(Booking $booking) {
        $id = $booking->getId();
        $manager = $this->getDoctrine()->getManager();
        $manager->remove($booking);
        $manager->flush();

        $this->addFlash(
            'success', 
            "La réservation n°$id a bien été supprimée"
        );

        return $this->redirectToRoute('admin_bookings_index');
    }
}
