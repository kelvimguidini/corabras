<?php 
namespace Application\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
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
     * @ORM\Column(type="string", length=25)
     */
    public $modelo;
        
    /**
     * @ORM\Column(type="string", length=20)
     */
    public $cor;
    
    /**
     * @ORM\Column(type="integer")
     */
    public $quantidade;
    
    /**
     * @ORM\Column(type="decimal")
     */
    public $valor;

    /**
     * @var Venda
     * @ORM\ManyToOne(targetEntity="Application\Model\Venda", inversedBy="id_venda")
     * @ORM\JoinColumn(name="id_venda")
     */
    public $venda;
  
    public function getId() {
        return $this->id;
    }
    
    public function setId($id) {
        $this->id = $id;
    }
    
    public function getVenda() {
        return $this->venda;
    }
    
    public function setVenda($venda) {
        $this->venda = $venda;
    }
    
    public function getModelo() {
        return $this->modelo;
    }
    
    public function setModelo($modelo) {
        $this->modelo = $modelo;
    }
    
    public function getCor() {
        return $this->cor;
    }
    
    public function setCor($cor) {
        $this->cor = $cor;
    }
    
    public function getQuantidade() {
        return $this->quantidade;
    }
    
    public function setQuantidade($quantidade) {
        $this->quantidade = $quantidade;
    }
    
    public function getValor() {
        $source = array('.');
        $replace = array(',');
        return str_replace($source, $replace, $this->valor);
    }
    
    public function setValor($valor) {
        $source = array('.', ',');
        $replace = array('', '.');
//         \Zend\Debug\Debug::dump(str_replace($source, $replace, $valor));
        $this->valor = str_replace($source, $replace, $valor);
    }
    
}