<?php 
namespace Application\Model;
use Doctrine\ORM\Mapping as ORM;

/**

 * @ORM\Entity

 */

class Custo
{

    /**
     * @ORM\Id
     * @ORM\GeneratedValue("AUTO")
     * @ORM\Column(type="integer")
     */
    public $id;

    /**
     * @ORM\Column(type="string", length=100)
     */
    public $produto;

    /**
     * @ORM\Column(type="datetime")
     */
    public $datacadastro;

    /**
     * @ORM\Column(type="string", length=20)
     */
    public $cor;

    /**
     * @ORM\Column(type="decimal")
     */
    public $avista;

    /**
     * @ORM\Column(type="decimal")
     */
    public $aprazo;

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getData() {
        return ($this->datacadastro != null)?$this->datacadastro->format('d/m/Y'):null;
    }

    public function setData($data) { 
        if($data != null){
            $ano= substr($data, 6);
            $mes= substr($data, 3,-5);
            $dia= substr($data, 0,-8);
            $data = $ano."-".$mes."-".$dia;
            $this->datacadastro =  new \DateTime($data);
        }
    }

    public function getProduto() {
        return $this->produto;
    }

    public function setProduto($produto) {
        $this->produto = $produto;
    }

    public function getCor() {
        return $this->cor;
    }

    public function setCor($cor) {
        $this->cor = $cor;
    }

    public function getAvista() {
        $source = array('.');
        $replace = array(',');
        return str_replace($source, $replace, $this->avista);
    }

    public function setAvista($avista) {
        $source = array('.', ',');
        $replace = array('', '.');
        $this->avista = str_replace($source, $replace, $avista);;
        
    }

    public function getAprazo() {
        $source = array('.');
        $replace = array(',');
        return str_replace($source, $replace, $this->aprazo);
    }

    public function setAprazo($valor) {
        $source = array('.', ',');
        $replace = array('', '.');
        $this->aprazo = str_replace($source, $replace, $valor);
        
    }
}