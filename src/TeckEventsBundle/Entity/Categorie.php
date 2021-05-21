<?php

namespace TeckEventsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Categorie
 *
 * @ORM\Table(name="categorie")
 * @ORM\Entity(repositoryClass="TeckEventsBundle\Repository\CategorieRepository")
 */
class Categorie
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="nameCategorie", type="string", length=255)
     */
    private $nameCategorie;

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getNameCategorie()
    {
        return $this->nameCategorie;
    }

    /**
     * @param string $nameCategorie
     */
    public function setNameCategorie($nameCategorie)
    {
        $this->nameCategorie = $nameCategorie;
    }


}

