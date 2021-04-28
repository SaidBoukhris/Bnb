<?php 

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class HomeController extends AbstractController {

    /**
     * @Route("/hello/{prenom}/test/{age}", name="nomRoute")
     * @Route("/hello")
     * @return void
     */
    public function hello($prenom = "un prenom bidon", $age = "un age bidon") {
        return $this->render('hello.html.twig', [
            'prenom' => $prenom,
            'age' => $age
        ]);

    }


    /**
     * @Route("/", name="homepage")
     *
     */
    public function home() {
        $title = "Bonjour à tous";
        return $this->render(
            'home.html.twig',
            [ 
                'title' => $title,
                'tab' => ["Toto" => 10, "Tata" => 15] 
            ]
        );
    }
}