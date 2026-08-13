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
     * @ORM\Column(type="time", nullable=true)
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
        if (!empty($hora)) {
            if ($hora instanceof \DateTimeInterface) {
                $this->saida = $hora;
            } else {
                try {
                    $this->saida = new \DateTime(trim((string)$hora));
                } catch (\Exception $e) {
                    $this->saida = new \DateTime("00:00:00");
                }
            }
        }
    }

    public function getRetorno() {
        return ($this->retorno != null)?$this->retorno->format('H:i'):null;
    }

    public function setRetorno($hora) {
        if (!empty($hora)) {
            if ($hora instanceof \DateTimeInterface) {
                $this->retorno = $hora;
            } else {
                try {
                    $this->retorno = new \DateTime(trim((string)$hora));
                } catch (\Exception $e) {
                    $this->retorno = null;
                }
            }
        } else {
            $this->retorno = null;
        }
    }

    public function getData() {
        return ($this->data != null)?$this->data->format('d/m/Y'):null;
    }

    public function setData($data) {
        if (!empty($data)) {
            if ($data instanceof \DateTimeInterface) {
                $this->data = $data;
            } else {
                $data = trim((string)$data);
                if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $data, $matches)) {
                    $this->data = new \DateTime(sprintf('%04d-%02d-%02d', $matches[3], $matches[2], $matches[1]));
                } else {
                    try {
                        $this->data = new \DateTime($data);
                    } catch (\Exception $e) {
                        $this->data = new \DateTime();
                    }
                }
            }
        }
    }

    
	public function getVendas() {
        return $this->vendas;
    }

    public function setVendas($val) {
        $this->vendas = $val;
    }

}