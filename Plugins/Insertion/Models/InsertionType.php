<?php

namespace Insertion\Models;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Oforge\Engine\Modules\Core\Abstracts\AbstractModel;
use Oforge\Engine\Modules\Media\Models\Media;
use phpDocumentor\Reflection\Types\Integer;

/**
 * @ORM\Table(name="oforge_insertion_type")
 * @ORM\Entity
 */
class InsertionType extends AbstractModel {
    /**
     * @var int
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="insertion_type_name", type="string", nullable=false)
     */
    private $name;

    /**
     * @var Media|null
     * @ORM\ManyToOne(targetEntity="Oforge\Engine\Modules\Media\Models\Media", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="image_id", referencedColumnName="id")
     */

    private $image;

    /**
     * @var string|null
     * @ORM\Column(name="insertion_type_description", type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity="InsertionType")
     * @ORM\JoinColumn(name="insertion_parent_id", referencedColumnName="id", nullable=true)
     */
    private $parent;

    /**
     * @ORM\OneToMany(targetEntity="InsertionTypeAttribute", mappedBy="insertionType")
     * @ORM\JoinColumn(name="insertion_type", referencedColumnName="id")
     * @ORM\OrderBy({"quickSearchOrder" = "ASC"})
     */
    private $attributes;

    /**
     * @var boolean
     * @ORM\Column(name="insertion_type_quick_search", type="boolean", nullable=false)
     */
    private $quickSearch;

    /**
     * @var boolean
     * @ORM\Column(name="insertion_type_image_required", type="boolean", nullable=false)
     */
    private $imageRequired = false;

    /**
     * @var int
     * @ORM\Column(name="insertion_type_min_price", type="integer", nullable=false)
     */
    private $minPrice = 0;

    /**
     * @var int
     * @ORM\Column(name="insertion_type_max_price", type="integer", nullable=false)
     */
    private $maxPrice = 2000000;

    public function __construct() {
        $this->attributes = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId() : int {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName() : string {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return InsertionType
     */
    public function setName(string $name) : InsertionType {
        $this->name = $name;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getParent() {
        return $this->parent;
    }

    /**
     * @param mixed $parent
     *
     * @return InsertionType
     */
    public function setParent($parent) : InsertionType {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAttributes() {
        return $this->attributes;
    }

    /**
     * @param mixed $attributes
     *
     * @return InsertionType
     */
    public function setAttributes($attributes) : InsertionType {
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * @return bool
     */
    public function isQuickSearch() : ?bool {
        return $this->quickSearch;
    }

    /**
     * @param bool $quickSearch
     *
     * @return InsertionType
     */
    public function setQuickSearch(bool $quickSearch) : InsertionType {
        $this->quickSearch = $quickSearch;

        return $this;
    }

    /**
     * @return Media|null
     */
    public function getImage() : ?Media {
        return $this->image;
    }

    /**
     * @param Media|null $image
     * @return InsertionType
     */
    public function setImage(?Media $image) : InsertionType {
        $this->image = $image;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescription() : ?string {
        return $this->description;
    }

    /**
     * @param string|null $description
     * @return InsertionType
     */
    public function setDescription(?string $description) : InsertionType {
        $this->description = $description;

        return $this;
    }

    /**
     * @return bool
     */
    public function isImageRequired(): bool
    {
        return $this->imageRequired;
    }

    /**
     * @param bool $imageRequired
     * @return InsertionType
     */
    public function setImageRequired(bool $imageRequired): InsertionType
    {
        $this->imageRequired = $imageRequired;
        return $this;
    }

    /**
     * @return int
     */
    public function getMinPrice(): int {
        return $this->minPrice;
    }


    /**
     * @param int $minPrice
     * @return InsertionType
     */
    public function setMinPrice(int $minPrice): InsertionType {
        $this->minPrice = $minPrice;
        return $this;
    }

    /**
     * @return int
     */
    public function getMaxPrice(): int {
        return $this->maxPrice;
    }


    /**
     * @param int $maxPrice
     * @return InsertionType
     */
    public function setMaxPrice(int $maxPrice): InsertionType {
        $this->maxPrice = $maxPrice;
        return $this;
    }
}
