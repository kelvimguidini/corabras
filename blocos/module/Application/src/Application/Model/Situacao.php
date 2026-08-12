<?php
namespace Application\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Situacao
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
	 * @ORM\Column(type="string")
	 */
    public $situacao;

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


    public function getSituacao() {
        return $this->situacao;
    }

    public function setSituacao($situacao) {
        $this->situacao = $situacao;
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

}