<?php

namespace App\Entity;

use App\Repository\ProductsRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Hateoas\Configuration\Annotation as Hateoas;

/**
 * @Hateoas\Relation(
 *      name = "liste",
 *      href = @Hateoas\Route(
 *          "products",
 *      )
 * )
 *
 * @Hateoas\Relation(
 *      name = "detail",
 *      href = @Hateoas\Route(
 *          "singleProduct",
 *          parameters = { "id" = "expr(object.getId())" }
 *      )
 * )
 */

#[ORM\Entity(repositoryClass: ProductsRepository::class)]
class Products
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\NotNull]
    #[Assert\Length(
        min: 2,
        max: 255,
    )]
    #[Assert\Type('string')]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\NotNull]
    #[Assert\Length(
        min: 2,
        max: 255,
    )]
    #[Assert\Type('string')]
    private ?string $brand = null;

    #[ORM\Column]
    #[Assert\NotBlank]
    #[Assert\NotNull]
    #[Assert\PositiveOrZero]
    #[Assert\Type('integer')]
    private ?int $stock = null;

    #[ORM\Column(length: 5000)]
    #[Assert\NotBlank]
    #[Assert\NotNull]
    #[Assert\Length(
        min: 50,
        max: 5000,
    )]
    #[Assert\Type('string')]
    private ?string $description = null;

    #[ORM\Column]
    #[Assert\NotBlank]
    #[Assert\NotNull]
    #[Assert\PositiveOrZero]
    #[Assert\Type('integer')]
    private ?int $price = null;


    public function getId(): ?int
    {
        return $this->id;

    }


    public function getName(): ?string
    {
        return $this->name;
    }


    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }


    public function getBrand(): ?string
    {
        return $this->brand;
    }


    public function setBrand(string $brand): static
    {
        $this->brand = $brand;
        return $this;
    }


    public function getStock(): ?int
    {
        return $this->stock;
    }


    public function setStock(int $stock): static
    {
        $this->stock = $stock;
        return $this;
    }


    public function getDescription(): ?string
    {
        return $this->description;
    }


    public function setDescription(string $description): static
    {
        $this->description = $description;
        return $this;
    }


    public function getPrice(): ?int
    {
        return $this->price;
    }


    public function setPrice(int $price): static
    {
        $this->price = $price;
        return $this;
    }


}
