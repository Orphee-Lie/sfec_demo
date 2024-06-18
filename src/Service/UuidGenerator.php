<?php

namespace App\Service;

use DateTime;
use App\Entity\Caisse;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Uid\UuidV6;
use Symfony\Component\Uid\Factory\UuidFactory;

class UuidGenerator
{
    private UuidFactory $uuidFactory;

    public function __construct(UuidFactory $uuidFactory)
    {
        $this->uuidFactory = $uuidFactory;
    }

    public function generate()
    {
        $uuid = Uuid::v7();
        return $uuid->__toString();
    }


    public function generateForNumberBox(Caisse $caisse)
    {
        $uuid = Uuid::v7();

        $u =  explode('-', $uuid->toRfc4122());
        $u  = end($u);
        $code = 'DGTT-'.explode('-', $caisse->getUuid())[0].$this->supprimerCaracteresSpeciaux($caisse->getCreatedAt()->format('Y-m-d:H:i:s')).$u;
        return $code;
    }

    function supprimerCaracteresSpeciaux($chaine) {
        // Supprimer les caractères vides 
        // $result = preg_replace('/[^\dT]/', '', $dataFacture['createdAt']);
        $chaine = trim($chaine);
        
        // Supprimer les espaces
        $chaine = str_replace(' ', '', $chaine);
        
        // Supprimer les caractères spéciaux
        $chaine = preg_replace('/[^A-Za-z0-9]/', '', $chaine);
        
        return $chaine;
    }

    function formatDateString($inputString) {
        // Convertir la chaîne en objet DateTime
        $date = new DateTime($inputString);

        // Formater la date selon le format désiré
        $resultat = $date->format('Ymd\THisu');

        // Si la chaîne dépasse 19 caractères, la tronquer
        if (strlen($resultat) > 19) {
            $resultat = substr($resultat, 0, 19);
        }

        return $resultat;
    }
}
