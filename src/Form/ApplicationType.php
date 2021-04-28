<?php 

namespace App\Form;

use Symfony\Component\Form\AbstractType;

class ApplicationType extends AbstractType {

    /**
     * Permet d'avoir la configuration de base d'un champ
     *
     * @param string $label
     * @param string $placeholder
     * @param array $options
     * @return array
     */
    protected function getConfiguration($label, string $placeholder, $options = []): array {
        
        // recursive permet de faire en sorte que si j'envoie dans le tab d'options une clé déjà existante comme 'attr' avec une autre valeur que placeholder, placeholder ne sera pas écrasé
        return array_merge_recursive([
            'label' => $label,
            'attr' => [
                'placeholder' => $placeholder, 
            ],
        ], $options);
    }

}