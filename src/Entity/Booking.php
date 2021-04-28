<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\BookingRepository")
 * @ORM\HasLifeCycleCallbacks()
 * 
 */
class Booking
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="bookings")
     * @ORM\JoinColumn(nullable=false)
     */
    private $booker;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Ad", inversedBy="bookings")
     * @ORM\JoinColumn(nullable=false)
     */
    private $ad;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\GreaterThan("today", message = "La date d'arrivée doit être ultérieure à la date d'aujourd'hui", groups={"front"})
     */
    private $startDate;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\GreaterThan(propertyPath="startDate", message = "La date de départ doit être plus éloignée que la date d'arrivée")
     */
    private $endDate;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="float")
     */
    private $amount;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $comment;

    public function isBookableDates() {

        // Récupérer les dates qui sont déjà reservées 
        $notAvailableDays = $this->ad->getNotAvailableDays();

        // Comparer les dates choisies avec les dates impossibles
        $bookingDays = $this->getDays();

        $formatDay = function($day) {
            return $day->format('Y-m-d');
        };
        
        // Tableau qui contient des chaines de caractéres de mes journées
        $days         = array_map($formatDay, $bookingDays);
        $notAvailable = array_map($formatDay, $notAvailableDays);

        foreach($days as $day) {
            // array_search renvoit l'indice si la recherche est fructueuse, sinon false
            if(array_search($day, $notAvailable) !== false) return false;
        }

        return true;

    }
    
    /**
     * Permet de récupérer un tableau des journées qui correspondent à ma réservation
     * 
     * @return array Un tableau d'objets DateTime représentant les jours de la réservation
     */
    public function getDays() {

        $resultat = range(
            $this->startDate->getTimestamp(),
            $this->endDate->getTimestamp(),
            24 * 60 * 60 
        );

        $days = array_map(function($day) {
            return new \DateTime(date('Y-m-d', $day)); // donne une date sous forme de chaine de caractére grâce à la fonctin date()
        }, $resultat);

        return $days;
    }

    public function getDuration() {
        $diff = $this->endDate->diff($this->startDate); // retourne un obj de type DateInterval
        return $diff->days;
    }

    /**
     * Permet d'initaliser la date de création du booking et le montant
     * @ORM\PrePersist
     * @ORM\PreUpdate
     * @return void
     */
    public function prePersist() {
        if(empty($this->created)) {
            $this->createdAt = new DateTime();
        }
        if(empty($this->amount)) {
            $this->amount =  $this->getAd()->getPrice() * $this->getDuration();
        }

    }



    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBooker(): ?User
    {
        return $this->booker;
    }

    public function setBooker(?User $booker): self
    {
        $this->booker = $booker;

        return $this;
    }

    public function getAd(): ?Ad
    {
        return $this->ad;
    }

    public function setAd(?Ad $ad): self
    {
        $this->ad = $ad;

        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }
}
