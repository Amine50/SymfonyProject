<?php

namespace TeckEventsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Address
 *
 * @ORM\Table(name="address")
 * @ORM\Entity(repositoryClass="TeckEventsBundle\Repository\AddressRepository")
 */
class Address
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
     * @ORM\Column(name="cityAddress", type="string", length=255)
     */
    private $cityAddress;

    /**
     * @var string
     *
     * @ORM\Column(name="addressExact", type="string", length=255)
     */
    private $addressExact;

    /**
     * @var string
     *
     * @ORM\Column(name="descriptionAddress", type="string", length=255)
     */
    private $descriptionAddress;


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
     * Set cityAddress
     *
     * @param string $cityAddress
     *
     * @return Address
     */
    public function setCityAddress($cityAddress)
    {
        $this->cityAddress = $cityAddress;

        return $this;
    }

    /**
     * Get cityAddress
     *
     * @return string
     */
    public function getCityAddress()
    {
        return $this->cityAddress;
    }

    /**
     * Set addressExact
     *
     * @param string $addressExact
     *
     * @return Address
     */
    public function setAddressExact($addressExact)
    {
        $this->addressExact = $addressExact;

        return $this;
    }

    /**
     * Get addressExact
     *
     * @return string
     */
    public function getAddressExact()
    {
        return $this->addressExact;
    }

    /**
     * Set descriptionAddress
     *
     * @param string $descriptionAddress
     *
     * @return Address
     */
    public function setDescriptionAddress($descriptionAddress)
    {
        $this->descriptionAddress = $descriptionAddress;

        return $this;
    }

    /**
     * Get descriptionAddress
     *
     * @return string
     */
    public function getDescriptionAddress()
    {
        return $this->descriptionAddress;
    }
}

