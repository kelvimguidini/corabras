<?php

namespace Application\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="Produto")
 */
class Produto
{

    /**
     * @ORM\Id
     * @ORM\GeneratedValue("AUTO")
     * @ORM\Column(type="integer")
     */
    public $id;

    /**
     * @ORM\Column(type="string", length=50)
     */
    public $modelo;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    public $medida;

    /**
     * @ORM\Column(type="integer")
     */
    public $quantidade;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    public $valor;

    /**
     * @var Venda
     * @ORM\ManyToOne(targetEntity="Application\Model\Venda", inversedBy="produtos")
     * @ORM\JoinColumn(name="id_venda", referencedColumnName="id")
     */
    public $venda;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getVenda()
    {
        return $this->venda;
    }

    public function setVenda($venda)
    {
        $this->venda = $venda;
    }

    public function getModelo()
    {
        return $this->modelo;
    }

    public function setModelo($modelo)
    {
        $this->modelo = $modelo;
    }

    public function getMedida()
    {
        return $this->medida;
    }

    public function setMedida($medida)
    {
        $this->medida = $medida;
    }

    /**
     * Alias for backward compatibility
     */
    public function getCor()
    {
        return $this->medida;
    }

    /**
     * Alias for backward compatibility
     */
    public function setCor($cor)
    {
        $this->medida = $cor;
    }

    public function getQuantidade()
    {
        return $this->quantidade;
    }

    public function setQuantidade($quantidade)
    {
        $this->quantidade = $quantidade;
    }

    public function getValor()
    {
        $source = array('.');
        $replace = array(',');
        return str_replace($source, $replace, (string)$this->valor);
    }

    public function setValor($valor)
    {
        $source = array('.', ',');
        $replace = array('', '.');
        $this->valor = str_replace($source, $replace, (string)$valor);
    }
}
