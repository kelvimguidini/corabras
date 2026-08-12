<?php 
namespace Application\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Venda
{
    
    /**
     * @ORM\Id
     * @ORM\GeneratedValue("AUTO")
     * @ORM\Column(type="integer")
     */
    public $id;
    
    /**
     * @ORM\Column(type="datetime")
     */
    public $data;
    
    /**
     * @ORM\ManyToOne(targetEntity="Application\Model\Cliente", inversedBy="id_cliente")
     * @ORM\JoinColumn(name="id_cliente")
     */
    public $cliente;
    
    /**
     * @ORM\Column(type="integer")
     */
    public $id_cliente;
    
    /**
     * @ORM\Column(type="integer")
     */
    public $forma_pagamento;
    
    /**
     * @ORM\Column(type="datetime")
     */
    public $data_entrega;
    
   /**
	 * @ORM\Column(type="boolean")
	 */
    public $envio;

   
    public function getId() {
        return $this->id;
    }
    
    public function setId($id) {
        $this->id = $id;
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

    
    public function getCliente() {
        return $this->cliente;
    }
    
    public function setCliente($cliente) {
        $this->cliente = $cliente;
    }
    
    
    public function getForma_pagamento() {
        return $this->forma_pagamento;
    }
    
    public function setForma_pagamento($forma_pagamento) {
        $this->forma_pagamento = $forma_pagamento;
    }
    
    
    public function getData_entrega() {
        return ($this->data_entrega != null)?$this->data_entrega->format('d/m/Y'):null;
    }
    
    public function setData_entrega($data) {
        if($data != null){
            $ano= substr($data, 6);
            $mes= substr($data, 3,-5);
            $dia= substr($data, 0,-8);
            $data = $ano."-".$mes."-".$dia;
            $this->data_entrega =  new \DateTime($data);
        }

    }

    
	public function getEnvio() {
        return $this->envio;
    }

    public function setEnvio($val) {
        $this->envio = $val;
    }
}