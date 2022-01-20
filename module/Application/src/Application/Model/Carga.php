<?php
namespace Application\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Carga
{

    /**
     * @ORM\Id
     * @ORM\GeneratedValue("AUTO")
     * @ORM\Column(type="integer")
     */
    public $id;

    /**
     * @ORM\Column(type="date")
     */
    public $data;  

    /**
     * @ORM\Column(type="time")
     */
    public $saida;

    /**
     * @ORM\Column(type="time")
     */
    public $retorno;

    /**
	 * @ORM\Column(type="string")
	 */
    public $motorista; 

   /**
	 * @ORM\Column(type="string")
	 */
    public $situacao;

     /**
     * @ORM\OneToMany(targetEntity="Application\Model\Venda", mappedBy="carga")
     */
     public $vendas;

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

	public function getMotorista() {
        return $this->motorista;
    }

    public function setMotorista($motorista) {
        $this->motorista = $motorista;
    }

	public function getSituacao() {
        return $this->situacao;
    }

    public function setSituacao($val) {
        $this->situacao = $val;
    }

    public function getSaida() {
        return ($this->saida != null)?$this->saida->format('H:i'):null;
    }

    public function setSaida($hora) {
        if($hora != null){
            //$hora= substr($data, 0, 2);
            //$min= substr($data, 3);
            $this->saida =  new \DateTime($hora);
        }
    }

    public function getRetorno() {
        return ($this->retorno != null)?$this->retorno->format('H:i'):null;
    }

    public function setRetorno($hora) {
        if($hora != null){
            $this->retorno =  new \DateTime($hora);
        }
    }

    public function getData() {
        return ($this->data != null)?$this->data->format('d/m/Y'):null;
    }

    public function setData($data) {
        if($data != null){
            $ano= substr($data, 6);
            $mes= substr($data, 3,-5);
            $dia= substr($data, 0,-8);
            $data = $ano."-".$mes."-".$dia;
            $this->data =  new \DateTime($data);
        }
    }

    
	public function getVendas() {
        return $this->vendas;
    }

    public function setVendas($val) {
        $this->vendas = $val;
    }

}