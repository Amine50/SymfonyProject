<?php

namespace TeckEventsBundle\Entity;

use AppBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use SBC\NotificationsBundle\Builder\NotificationBuilder;
use SBC\NotificationsBundle\Model\NotifiableInterface;

/**
 * Event
 *
 * @ORM\Table(name="event")
 * @ORM\Entity(repositoryClass="TeckEventsBundle\Repository\EventRepository")
 */
class Event implements NotifiableInterface, \JsonSerializable
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
     * @ORM\Column(name="nameEvent", type="string", length=255)
     */
    private $nameEvent;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dateEvent", type="date")
     */
    private $dateEvent;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dateCreationEvent", type="date")
     */
    private $dateCreationEvent;

    /**
     * @var \Address
     *
     * @ORM\ManyToOne(targetEntity="Address")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_address", referencedColumnName="id")
     * })
     */
    private $addressEvent;

    /**
     * @var \Categorie
     *
     * @ORM\ManyToOne(targetEntity="Categorie")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_categorie", referencedColumnName="id")
     * })
     */
    private $categorieEvent;

    /**
     * @var \User
     *
     * @ORM\ManyToOne(targetEntity="\AppBundle\Entity\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_user", referencedColumnName="id")
     * })
     */
    private $adminEvent;

    /**
     * @var string
     *
     * @ORM\Column(name="descriptionEvent", type="string", length=255)
     */
    private $descriptionEvent;


    /**
     * @ORM\Column(type="string")
     *
     * @Assert\NotBlank(message="Please, upload the product brochure as a PDF file.")
     * @Assert\File(mimeTypes={ "application/pdf" })
     */
    private $photoEvent;

    /**
     * @var \@var string
     *
     * @ORM\Column(name="statusEvent", type="string", length=255)
     */
    private $statusEvent;



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
     * Set nameEvent
     *
     * @param string $nameEvent
     *
     * @return Event
     */
    public function setNameEvent($nameEvent)
    {
        $this->nameEvent = $nameEvent;

        return $this;
    }

    /**
     * Get nameEvent
     *
     * @return string
     */
    public function getNameEvent()
    {
        return $this->nameEvent;
    }

    /**
     * Set dateEvent
     *
     * @param \DateTime $dateEvent
     *
     * @return Event
     */
    public function setDateEvent($dateEvent)
    {
        $this->dateEvent = $dateEvent;

        return $this;
    }

    /**
     * Get dateEvent
     *
     * @return \DateTime
     */
    public function getDateEvent()
    {
        return $this->dateEvent;
    }

    /**
     * Set dateCreationEvent
     *
     * @param \DateTime $dateCreationEvent
     *
     * @return Event
     */
    public function setDateCreationEvent($dateCreationEvent)
    {
        $this->dateCreationEvent = $dateCreationEvent;

        return $this;
    }

    /**
     * Get dateCreationEvent
     *
     * @return \DateTime
     */
    public function getDateCreationEvent()
    {
        return $this->dateCreationEvent;
    }

    /**
     * Set descriptionEvent
     *
     * @param string $descriptionEvent
     *
     * @return Event
     */
    public function setDescriptionEvent($descriptionEvent)
    {
        $this->descriptionEvent = $descriptionEvent;

        return $this;
    }

    /**
     * Get descriptionEvent
     *
     * @return string
     */
    public function getDescriptionEvent()
    {
        return $this->descriptionEvent;
    }

    /**
     * @return mixed
     */
    public function getPhotoEvent()
    {
        return $this->photoEvent;
    }

    /**
     * @param mixed $photoEvent
     */
    public function setPhotoEvent($photoEvent)
    {
        $this->photoEvent = $photoEvent;
    }

    /**
     * @return \Categorie
     */
    public function getCategorieEvent()
    {
        return $this->categorieEvent;
    }

    /**
     * @param \Categorie $categorieEvent
     */
    public function setCategorieEvent($categorieEvent)
    {
        $this->categorieEvent = $categorieEvent;
    }

    /**
     * @return \User
     */
    public function getAdminEvent()
    {
        return $this->adminEvent;
    }

    /**
     * @param \User $adminEvent
     */
    public function setAdminEvent($adminEvent)
    {
        $this->adminEvent = $adminEvent;
    }

    /**
     * @return \Address
     */
    public function getAddressEvent()
    {
        return $this->addressEvent;
    }

    /**
     * @param \Address $addressEvent
     */
    public function setAddressEvent($addressEvent)
    {
        $this->addressEvent = $addressEvent;
    }

    /**
     * @return \
     */
    public function getStatusEvent()
    {
        return $this->statusEvent;
    }

    /**
     * @param \ $statusEvent
     */
    public function setStatusEvent($statusEvent)
    {
        $this->statusEvent = $statusEvent;
    }


    public function notificationsOnCreate(NotificationBuilder $builder)
    {
        return $builder;
    }

    public function notificationsOnUpdate(NotificationBuilder $builder)
    {
        return $builder;
    }

    public function notificationsOnDelete(NotificationBuilder $builder)
    {
        $notification = new Notification();
        $notification
            ->setTitle('Event Deleted')
            ->setDescription('"'.$this->nameEvent.'" has been deleted')
            ->setParameters(array('id' => $this->id))

        ;
        $notification->setIdUser($this->adminEvent);

        $builder->addNotification($notification);
        return $builder;
    }

    function jsonSerialize()
    {
        return get_object_vars($this);
    }


}

