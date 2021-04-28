<?php

namespace App\Controller;

use App\Entity\Ad;
use App\Entity\Image;
use App\Form\AnnonceType;;
use App\Repository\AdRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdController extends AbstractController
{
    /**
     * @Route("/ads", name="ads_index")
     * 
     * @return Response
     */
    public function index(AdRepository $adRepository)
    {
        //$repo =$this->getDoctrine()->getRepository(Ad::class);
        $ads = $adRepository->findAll();

        return $this->render('ad/index.html.twig', [
            'ads' => $ads,
        ]);
    }
    
    /**
     * Permet de créer une annonce
     * @Route("/ads/new", name="ads_create")
     * @IsGranted("ROLE_USER")
     * @return Response
     */
    public function create(Request $request) {

        //$title = $request->request->get('title');

        $ad = new Ad();
        
        $form = $this->createForm(AnnonceType::class, $ad);

        $form->handleRequest($request);    
        
        if($form->isSubmitted() && $form->isValid()) {
            $manager = $this->getDoctrine()->getManager();
            
            foreach($ad->getImages() as $image) {
                $image->setAd($ad);
                $manager->persist($image);
            }

            $ad->setAuthor($this->getUser());

            $manager->persist($ad);
            $manager->flush();

            $this->addFlash(
                'success', 
                "L'annonce {$ad->getTitle()} a bien été enregistrée"
            );
            
            return  $this->redirectToRoute('ads_show', [
                'slug' => $ad->getSlug()
            ]);

            
        }

        return $this->render('ad/new.html.twig', [
            'form' => $form->createView()
        ]);
    } 

    /**
     * Permet d'afficher le formulaire d'édition
     * @Route("/ads/{slug}/edit", name="ads_edit")
     * @Security("is_granted('ROLE_USER') and user === ad.getAuthor()", message="Vous n'avez pas les droits pour modifier cette annonce")
     * @return Response
     */
    public function edit(Ad $ad, Request $request) {

        // Ici grâce au ParamConverter Symfony a implicitement demander au repository l'annonce correspondant au slug, $ad est donc nourrit de toutes les informations de la BDD, le formulaire est donc crée avec toutes les infos de l'objet $ad
        $form = $this->createForm(AnnonceType::class, $ad);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $manager = $this->getDoctrine()->getManager();
            
            foreach($ad->getImages() as $image) {
                $image->setAd($ad);
                $manager->persist($image);
            }

            $manager->persist($ad); // ici en réalité pas besoin car l'objet est déjà en session
            $manager->flush();

            $this->addFlash(
                'success', 
                "Les modifications de l'annonce {$ad->getTitle()} ont bien été enregistrées"
            );
            
            return  $this->redirectToRoute('ads_show', [
                'slug' => $ad->getSlug()
            ]);

            
        }

        return $this->render('ad/edit.html.twig', [
            'form' => $form->createView(),
            'ad' => $ad
        ]);
    }
    
    
    /**
     * Permet d'afficher une annonce
     * 
     * @Route("/ads/{slug}", name="ads_show")
     *
     * @return Response
     */
    public function show(Ad $ad) {

        // Plus besoin de ça grâce au ParamConverter
        //$ad = $adRepository->findOneBySlug($slug);

        return $this->render('ad/show.html.twig',[
            'ad' => $ad,
        ]);
    }

    /**
     * Permet de supprimer une annonce
     * @Route("/ads/{slug}/delete", name="ads_delete")
     * 
     * @Security("is_granted('ROLE_USER') and user == ad.getAuthor()", message="Vous n'avez pas les droits pour supprimer cette annonce")
     * 
     * @param Ad
     * @param EntityManager
     * @return Response
     */
    public function delete(Ad $ad, EntityManagerInterface $manager) {
        $manager->remove($ad);
        $manager->flush();

        $this->addFlash('success', "Votre annonce {$ad->getTitle()} a bien été supprimée");
        
        return $this->redirectToRoute('ads_index');
    }


}
