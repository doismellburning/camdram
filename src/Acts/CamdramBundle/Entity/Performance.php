<?php

namespace Acts\CamdramBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Performance
 *
 * @ORM\Table(name="acts_performances")
 * @ORM\Entity(repositoryClass="Acts\CamdramBundle\Entity\PerformanceRepository")
 * @Serializer\ExclusionPolicy("all")
 * @Gedmo\Loggable
 * @Serializer\XmlRoot("performance")
 *
 */
class Performance
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \Show
     *
     * @ORM\ManyToOne(targetEntity="Show", inversedBy="performances")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="sid", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Gedmo\Versioned
     */
    private $show;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="startdate", type="date", nullable=false)
     * @Serializer\Expose
     * @Gedmo\Versioned
     * @Serializer\XmlElement(cdata=false)
     */
    private $start_date;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="enddate", type="date", nullable=false)
     * @Serializer\Expose
     * @Gedmo\Versioned
     * @Serializer\XmlElement(cdata=false)
     */
    private $end_date;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="excludedate", type="date", nullable=true)
     * @Serializer\Expose
     * @Gedmo\Versioned
     */
    private $exclude_date;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="time", type="time", nullable=false)
     * @Serializer\Expose
     * @Gedmo\Versioned
     * @Serializer\XmlElement(cdata=false)
     */
    private $time;

    /**
     * @var \Venue
     *
     * @ORM\ManyToOne(targetEntity="Venue", inversedBy="performances")
     * @ORM\JoinColumn(name="venid", referencedColumnName="id", onDelete="SET NULL")
     * @Gedmo\Versioned
     */
    private $venue;

    /**
     * @var string
     *
     * @ORM\Column(name="venue", type="string", length=255, nullable=true)
     * @Serializer\Expose
     * @Gedmo\Versioned
     * @Serializer\XmlElement(cdata=false)
     */
    private $other_venue;

    public function __construct()
    {
    }

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
     * Set show_id
     *
     * @param int $showId
     *
     * @return Performance
     */
    public function setShowId($showId)
    {
        $this->show_id = $showId;

        return $this;
    }

    /**
<<<<<<< HEAD
=======
     * Get show_id
     *
     * @return int
     */
    public function getShowId()
    {
        return $this->show_id;
    }

    /**
>>>>>>> master
     * Set start_date
     *
     * @param \DateTime $startDate
     *
     * @return Performance
     */
    public function setStartDate($startDate)
    {
        $this->start_date = $startDate;

        return $this;
    }

    /**
     * Get start_date
     *
     * @return \DateTime
     */
    public function getStartDate()
    {
        return $this->start_date;
    }

    /**
     * Set end_date
     *
     * @param \DateTime $endDate
     *
     * @return Performance
     */
    public function setEndDate($endDate)
    {
        $this->end_date = $endDate;

        return $this;
    }

    /**
     * Get end_date
     *
     * @return \DateTime
     */
    public function getEndDate()
    {
        return $this->end_date;
    }

    /**
     * Set exclude_date
     *
     * @param \DateTime $excludeDate
     *
     * @return Performance
     */
    public function setExcludeDate($excludeDate)
    {
        $this->exclude_date = $excludeDate;

        return $this;
    }

    /**
     * Get exclude_date
     *
     * @return \DateTime
     */
    public function getExcludeDate()
    {
        return $this->exclude_date;
    }

    /**
     * Set time
     *
     * @param \DateTime $time
     *
     * @return Performance
     */
    public function setTime($time)
    {
        $this->time = $time;

        return $this;
    }

    /**
     * Get time
     *
     * @return \DateTime
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * Set other_venue
     *
     * @param string $venueName
     *
     * @return Performance
     */
    public function setOtherVenue($venueName)
    {
        $this->other_venue = $venueName;

        return $this;
    }

    /**
     * Get other_venue
     *
     * @return string
     */
    public function getOtherVenue()
    {
        return $this->other_venue;
    }

    /**
     * Get venue_name
     *
     * @return string
     */
    public function getVenueName()
    {
        if ($this->venue) {
            return $this->venue->getName();
        } elseif ($this->other_venue) {
            return $this->other_venue;
        }
    }

    /**
     * Set show
     *
     * @param \Acts\CamdramBundle\Entity\Show $show
     *
     * @return Performance
     */
    public function setShow(\Acts\CamdramBundle\Entity\Show $show = null)
    {
        $this->show = $show;

        return $this;
    }

    /**
     * Get show
     *
     * @return \Acts\CamdramBundle\Entity\Show
     */
    public function getShow()
    {
        return $this->show;
    }

    /**
     * Get venue
     *
     * @return \Acts\CamdramBundle\Entity\Venue
     */
    public function getVenue()
    {
        return $this->venue;
    }

    /**
     * Set venue
     *
     * @param \Acts\CamdramBundle\Entity\Venue $venue
     *
     * @return Performance
     */
    public function setVenue(\Acts\CamdramBundle\Entity\Venue $venue = null)
    {
        $this->venue = $venue;

        return $this;
    }

    /**
     * Generate a more useful view of the performance dates, making it easier
     * to render the diary page.
     *
     * @return
     */
    public function getDiaryEntries()
    {
        $entries = array();
        $entry = array();

        if (($this->exclude_date > $this->start_date) &&
            ($this->exclude_date < $this->end_date)) {
            /* If there's a valid exclude date then they'll be two entries */
            $entry['startdate'] = $this->start_date;
            $entry['enddate'] = $this->exclude_date->modify('-1 day');
            /* Dates are inclusive */
            $entry['numdays'] = $entry['enddate']->diff($entry['startdate'])->d + 1;

            $entries[] = $entry;

            $entry['startdate'] = $this->exclude_date->modify('+1 day');
            $entry['enddate'] = $this->end_date;
            $entry['numdays'] = $entry['enddate']->diff($entry['startdate'])->d + 1;

            $entries[] = $entry;
        } else {
            /* Just one simple entry */
            $entry['startdate'] = $this->start_date;
            $entry['enddate'] = $this->end_date;
            $entry['numdays'] = $entry['enddate']->diff($entry['startdate'])->d + 1;

            $entries[] = $entry;
        }

        return $entries;
    }
}
