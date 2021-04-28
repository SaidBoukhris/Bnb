<?php

namespace App\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class FrenchToDateTimeTransformer implements DataTransformerInterface {

    public function transform($date) 
    {
        // Je transforme la date de l'entité au format string
        if($date === null) {
            return '';
        }
        return $date->format('Y-m-d');
    }

    // Je transforme la date du formulaire (au format texte) au format DateTime pour l'entité
    public function reverseTransform($frenchDate)
    {
        if($frenchDate === null) {
            // Exception
            throw new TransformationFailedException("Vous devez fournir une date");
        }

        $date = \DateTime::createFromFormat('d/m/Y', $frenchDate);

        if($date === false) {
            // Exception
            throw new TransformationFailedException("Le format de la date n'est pas le bon");
        }

        return $date;
    }
} 