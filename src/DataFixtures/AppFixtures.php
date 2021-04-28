<?php

namespace App\DataFixtures;

use DateTime;
use App\Entity\Ad;
use Faker\Factory;
use App\Entity\Role;
use App\Entity\User;
use App\Entity\Image;
use App\Entity\Booking;
use App\Entity\Comment;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder) {
        $this->encoder = $encoder;    
    }

    public function load(ObjectManager $manager)
    {
        
        $faker = Factory::create('fr-FR');


        // Gestion des rôles
        $adminRole = new Role();
        $adminRole->setTitle('ROLE_ADMIN');
        $manager->persist($adminRole);

        $adminUser = new User();
        $adminUser->setFirstName('Said')
                  ->setLastName('Boukhris')
                  ->setEmail('said@gmail.fr')
                  ->setHash($this->encoder->encodePassword($adminUser, 'sasasasa'))
                  ->setPicture("https://images.unsplash.com/photo-1535065397200-568b59549ae9?ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&ixlib=rb-1.2.1&auto=format&fit=crop&w=1050&q=80")
                  ->setIntroduction($faker->sentence())
                  ->setDescription('<p>' . join('</p><p>', $faker->paragraphs(5)) . '</p>')
                  ->addUserRole($adminRole);
        
        $manager->persist($adminUser);

        // Gestion des utilisateurs
        $users = [];
        $genres = ['male', 'female'];

        for($i = 0; $i <=10; $i++) {
            $user = new User();
            
            // https://randomuser.me/api/portraits/men/99.jpg

            $genre = $faker->randomElement($genres);
            $picture = "https://randomuser.me/api/portraits/";
            $pictureGenre = $genre == 'male' ? 'men' : 'women';
            $pictureId = $faker->numberBetween(0,99);
    
            $picture .= $pictureGenre . '/' . $pictureId . '.jpg';
            $hash = $this->encoder->encodePassword($user, 'sasasasa'); // ici je passe l'entité $user juste pour que l'encoder sache quel algo il faut utiliser (et que j'ai définit dans le security.yaml, où je précise que pour l'entité User, j'utilise bcrypt)

            $user->setFirstName($faker->firstname($genre))
                 ->setLastName($faker->lastname)
                 ->setEmail($faker->email)
                 ->setIntroduction($faker->sentence())
                 ->setDescription('<p>' . join('</p><p>', $faker->paragraphs(5)) . '</p>')
                 ->setHash($hash)
                 ->setPicture($picture);
            
            $manager->persist($user);
            $users[] = $user;

        }        


        // Gestion des annonces
        for($i = 0; $i < 30; $i++) {

            $ad = new Ad();
            $title = $faker->sentence(5);
            $coverImage = "https://images.unsplash.com/photo-1472224371017-08207f84aaae?ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&ixlib=rb-1.2.1&auto=format&fit=crop&w=1050&q=80";
            $content ='<p>' . join('</p><p>', $faker->paragraphs(5)) . '</p>';
            $introduction = $faker->paragraph(2);
            $userId = $users[mt_rand(0,count($users) - 1)]; 
        
            $ad->setTitle($title)
               ->setCoverImage($coverImage)
               ->setContent($content)
               ->setPrice(mt_rand(40,200))
               ->setIntroduction($introduction)
               ->setRooms(mt_rand(1,5))
               ->setAuthor($userId);
            
            for($j = 0; $j < mt_rand(2,5); $j++) {
                $image = new Image();
                $image->setUrl("https://images.unsplash.com/photo-1444392061186-9fc38f84f726?ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&ixlib=rb-1.2.1&auto=format&fit=crop&w=1052&q=80");
                $image->setCaption($faker->sentence());
                $image->setAd($ad);

                $manager->persist($image);
            }

            // Gestion des réservations
            for($j = 1; $j <= mt_rand(0,10); $j++) {
                $booking = new Booking();
                
                $createdAt = $faker->dateTimeBetween('-6 months');
                $startDate = $faker->dateTimeBetween('-3 months');
                $duration  = mt_rand(3,10);
                $endDate   = (clone $startDate)->modify("+$duration days");
                
                $amount    = $ad->getPrice() * $duration;

                $booker    = $users[mt_rand(0,count($users) - 1)];

                $booking->setAd($ad);
                $booking->setBooker($booker);    
                $booking->setStartDate($startDate);
                $booking->setEndDate($endDate);
                $booking->setCreatedAt($createdAt);
                $booking->setAmount($amount);
                $booking->setComment($faker->paragraph());
                
                $manager->persist($booking);

                // Gestion des commentaires
                if(mt_rand(0,1)) { // Permet de jouer à pile ou face, pour faire en sorte que toutes les annonces n'aient pas forcément un commentaire
                    $comment = new Comment();
                    $comment->setContent($faker->paragraph())
                            ->setRating(mt_rand(1,5))
                            ->setAuthor($booker)
                            ->setAd($ad);

                    $manager->persist($comment);
                }

            }

            
            $manager->persist($ad);

        }

        $manager->flush();
    }
}
