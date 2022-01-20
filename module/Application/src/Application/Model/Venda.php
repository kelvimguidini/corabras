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
	 * @ORM\Column(type="string", length=200)
	 */
    public $nome_vendedor;

     /**
	 * @ORM\Column(type="string", length=200)
	 */
    public $nome;

	/**
	 * @ORM\Column(type="string", length=14)
	 */
    public $cpfcnpj;

	/**
	 * @ORM\Column(type="string", length=300)
	 */
    public $endereco;

	/**
	 * @ORM\Column(type="string", length=200)
	 */
    public $cidade;

	/**
	 * @ORM\Column(type="datetime")
	 */
    public $data_cadastro;

    /**
     * @ORM\Column(type="datetime")
     */
    public $data_para_entrega;

    /**
     * @ORM\Column(type="boolean")
     */
    public $nota_fiscal;

    /**
	 * @ORM\Column(type="string", length=2)
	 */
    public $tipo_nf;

    /**
	 * @ORM\Column(type="string", length=2)
	 */
    public $pagamento;

    /**
	 * @ORM\Column(type="string", length=2)
	 */
    public $forma_pagamento;

    /**
     * @ORM\Column(type="string")
     */
    public $descricao_outra_forma_pagamento;
    /**
     * @ORM\Column(type="string")
     */
    public $telefone; 

   /**
     * @ORM\Column(type="string")
     */
    public $contato;

	/**
	 * @ORM\Column(type="string")
	 */
    public $urgente;

   /**
	 * @ORM\Column(type="boolean")
	 */
    public $envio;

	/**
	 * @ORM\Column(type="string", length=300)
	 */
    public $endereco_entrega;

	/**
	 * @ORM\Column(type="string", length=300)
	 */
    public $obs;

	/**
	 * @ORM\Column(type="boolean")
	 */
    public $local_entrega;
    
	/**
	 * @ORM\Column(type="boolean")
	 */
    public $ja_aberto;

    /**
     * @var Carga
     * @ORM\ManyToOne(targetEntity="Application\Model\Carga", inversedBy="id_carga")
     * @ORM\JoinColumn(name="id_carga")
     */
    public $carga;

     /**
     * @ORM\OneToMany(targetEntity="Application\Model\Produto", mappedBy="venda")
     */
     public $produtos;

     /**
     * @ORM\OneToMany(targetEntity="Application\Model\Situacao", mappedBy="venda")
     */
     public $situacoes;


	public function getCarga() {
        return $this->carga;
    }

    public function setCarga($val) {
        $this->carga = $val;
    }

	public function getEnvio() {
        return $this->envio;
    }

    public function setEnvio($val) {
        $this->envio = $val;
    }

    public function getEnderecoEntrega() {
        return $this->endereco_entrega;
    }

    public function setEnderecoEntrega($val) {
        $this->endereco_entrega = $val;
    }

    public function getObs() {
        return $this->obs;
    }

    public function setObs($val) {
        $this->obs = $val;
    }

    public function getContato() {
        return $this->contato;
    }

    public function setContato($val) {
        $this->contato = $val;
    }

    public function getLocalEntrega() {
        return $this->local_entrega;
    }

    public function setLocalEntrega($val) {
        $this->local_entrega = $val;
    }
    
    public function getAberto() {
        return $this->ja_aberto;
    }

    public function setAberto($val) {
        $this->ja_aberto = $val;
    }


    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getNome_vendedor() {
        return $this->nome_vendedor;
    }

    public function setNome_vendedor($nome_vendedor) {
        $this->nome_vendedor = $nome_vendedor;
    }

    public function getNome() {
        return $this->nome;
    }

    public function setNome($nome) {
        $this->nome = $nome;
    }

    public function getCpfcnpj() {
        return $this->cpfcnpj;
    }

    public function setCpfcnpj($cpfcnpj) {
        $this->cpfcnpj = $cpfcnpj;
    }

    public function getEndereco() {
        return $this->endereco;
    }

    public function setEndereco($endereco) {
        $this->endereco = $endereco;
    }


    public function getCidade() {
        return $this->cidade;
    }

    public function setCidade($cidade) {
        $this->cidade = $cidade;
    }

    public function getData_cadastro() {
        return ($this->data_cadastro != null)?$this->data_cadastro->format('d/m/Y'):null;
    }

    public function setData_cadastro($data_cadastro) {
        if($data_cadastro != null){
            $ano= substr($data_cadastro, 6);
            $mes= substr($data_cadastro, 3,-5);
            $dia= substr($data_cadastro, 0,-8);
            $data_cadastro = $ano."-".$mes."-".$dia;
            $this->data_cadastro =  new \DateTime($data_cadastro);
        }

    }


    public function getData_entrega() {
        return ($this->data_para_entrega != null)?$this->data_para_entrega->format('d/m/Y'):null;
    }

    public function setData_entrega($data) {
        if($data != null){
            $ano= substr($data, 6);
            $mes= substr($data, 3,-5);
            $dia= substr($data, 0,-8);
            $data = $ano."-".$mes."-".$dia;
            $this->data_para_entrega =  new \DateTime($data);
        }

    }


    public function getNota_fiscal() {
        return $this->nota_fiscal;
    }

    public function setNota_fiscal($nota_fiscal) {
        $this->nota_fiscal = $nota_fiscal;
    }


    public function getTipo_nf() {
        return $this->tipo_nf;
    }

    public function setTipo_nf($tipo_nf) {
        $this->tipo_nf = $tipo_nf;
    }


    public function getPagamento() {
        return $this->pagamento;
    }

    public function setPagamento($pagamento) {
        $this->pagamento = $pagamento;
    }

    public function getTelefone() {
        return $this->telefone;
    }

    public function setTelefone($val) {
        $this->telefone = $val;
    }


    public function getForma_pagamento() {
        return $this->forma_pagamento;
    }

    public function setForma_pagamento($forma_pagamento) {
        $this->forma_pagamento = $forma_pagamento;
    }


    public function getOutra() {
        return $this->descricao_outra_forma_pagamento;
    }

    public function setOutra($outra) {
        $this->descricao_outra_forma_pagamento = $outra;
    }

    public function getUrgente() {
        return $this->urgente;
    }

    public function setUrgente($urgente) {
        $this->urgente = $urgente;
    }

}