<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Zend\Cache\Storage\ExceptionEvent;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Storage\SessionStorage;
use Zend\Session\SessionManager;
use Zend\Session\Container;


class IndexController extends AbstractActionController
{
    public function sairAction()
    {
        session_start();
        if (!isset($_SESSION['usuarioNome'])) {
            return $this->redirect()->toRoute('login');
        }
        if (isset($_SESSION['usuarioNome'])) {
            unset($_SESSION['usuarioNome']);
        }
        
        return $this->redirect()->toRoute('login');
    }
    public function loginAction()
    {
        session_start();
        $request = $this->getRequest();
        if($request->isPost())
        {
            $usuario = $request->getPost("usuario");
            $senha = $request->getPost("senha");
            
            if($usuario == "admin" && $senha == "vendas102030@"){
               
                $_SESSION['usuarioNome'] = $usuario;
                
                
                return $this->redirect()->toRoute('home');
            }
            
        }
        return new ViewModel();
    }
    
    public function indexAction()
    {
        session_start();
        if (!isset($_SESSION['usuarioNome'])) {
            return $this->redirect()->toRoute('login');
        }
        $em = $this->getServiceLocator()->get("Doctrine\ORM\EntityManager");
        $lista = $em->getRepository("Application\Model\Venda")->findBy(
                   array(),        // $where 
                   array('id' => 'desc'),    // $orderBy
                   1000,                        // $limit
                   0                          // $offset
        );
        return new ViewModel(array('lista' => $lista));
    }
    
    public function cadastrarAction()
    {
        session_start();
        if (!isset($_SESSION['usuarioNome'])) {
            return $this->redirect()->toRoute('login');
        }
        $idVenda = $this->params()->fromRoute("id", 0);
        $em = $this->getServiceLocator()->get("Doctrine\ORM\EntityManager");
        
        $request = $this->getRequest();
        $clientes = $em->getRepository("Application\Model\Cliente")->findAll();
        
        
        $result = array();
        if($request->isPost())
        {
            try{
                $qtd_produtos = $request->getPost("qtd_produtos");
                
                $id = $request->getPost("id");
                $data = $request->getPost("data");
                $clienteId = $request->getPost("cliente");
                $forma_pagamento = $request->getPost("forma_pagamento");
                $data_entrega = $request->getPost("data_entrega");
                $envio = $request->getPost("envio") == "Entregar";

                $cliente = $em->find("Application\Model\Cliente", $clienteId);
                
                if($idVenda > 0)
                {
                    $venda = $em->find("Application\Model\Venda", $idVenda);
                }else
                {
                    $venda = new \Application\Model\Venda();
                }

                $db = $em->createQuery('select c from Application\Model\Custo c where c.id in (select max(c2.id) FROM Application\Model\Custo c2 GROUP BY c2.produto, c2.cor)');
                $custos = $db->getArrayResult();

                $venda->setEnvio($envio);        
                $venda->setData($data);
                $venda->setData_entrega($data_entrega);
                $venda->setCliente($cliente);
                $venda->setForma_pagamento((int)$forma_pagamento);
                
                $em->persist($venda);
                $em->flush();

                for($i=1; $i<=$qtd_produtos;$i++){                   

                    if($request->getPost("modelo_".$i) != null){
                        
                        if($idVenda > 0 &&  $request->getPost("id_produto_".$i) > 0)
                        {
                            $produto = $em->find("Application\Model\Produto", $request->getPost("id_produto_".$i));
                        }else
                        {
                            $produto = new \Application\Model\Produto();
                        }
                        
                        $produto->setVenda($venda);
                        $produto->setData($request->getPost("data_produto_".$i));
                        $produto->setModelo($request->getPost("modelo_".$i));
                        $produto->setCor($request->getPost("cor_".$i));
                        $produto->setquantidade($request->getPost("quantidade_".$i));
                        $produto->setValor($request->getPost("valor_".$i));

                        //if(!($idVenda > 0 &&  $request->getPost("id_produto_".$i) > 0))
                        //{
                            $valorCusto=0.0;
                            foreach ($custos as $custo){ 
                                if($custo['produto'] == $produto->getModelo() && $custo['cor'] == $produto->getCor()){
                                    $valorCusto = (is_array($venda) ? $v['forma_pagamento'] : $venda->getForma_pagamento()) ==1? $custo['avista']:$custo['aprazo'];
                                    break;
                                }
                            }
                            $produto->custo = $valorCusto;
                        //}
                        $em->persist($produto);
                        $em->flush();
                    }
                }
                
                $result["resp"] = "Salvo com sucesso!";
                $result["tipo_mens"] = 'success';
            }  catch (ExceptionEvent $e){
            }
        }
        if($idVenda > 0)
        {
            $produtos_lista = $em->getRepository("Application\Model\Produto")->findBy(array('id_venda'=> $idVenda));
            
            $venda = $em->getRepository("Application\Model\Venda")->find($idVenda);
            return new ViewModel(array('venda' => $venda, 'result'=> $result, 'clientes' => $clientes, 'produtos' => $produtos_lista));
        }
        
        return new ViewModel(array('result' => $result, 'clientes' => $clientes));
    }
    
    public function excluirAction()
    {
        session_start();
        if (!isset($_SESSION['usuarioNome'])) {
            return $this->redirect()->toRoute('login');
        }
        $id = $this->params()->fromRoute("id", 0);
        $em = $this->getServiceLocator()->get("Doctrine\ORM\EntityManager");
        $venda = $em->getRepository("Application\Model\Venda")->find($id);
        $em->remove($venda);
        $em->flush();
    }
    
    public function cadastrarclienteAction()
    {
        session_start();
        if (!isset($_SESSION['usuarioNome'])) {
            return $this->redirect()->toRoute('login');
        }
        $em = $this->getServiceLocator()->get("Doctrine\ORM\EntityManager");
        
        $request = $this->getRequest();
        
        if($request->isPost())
        {
            $nome = $request->getPost("nome");
            //\Zend\Debug\Debug::dump(trim($nome));
            $cliente = new \Application\Model\Cliente();
            
            $cliente->setNome(trim($nome));
            
            $em->persist($cliente);
            $em->flush();
            
            $view = new ViewModel(array('id' => $cliente->getId()));
            $view->setTerminal(true);
            return $view;
        }
    }
    
    public function custoAction()
    {
        session_start();
        if (!isset($_SESSION['usuarioNome'])) {
            return $this->redirect()->toRoute('login');
        }
        $id = $this->params()->fromRoute("id", 0);

        $em = $this->getServiceLocator()->get("Doctrine\ORM\EntityManager");
        
        $request = $this->getRequest();
        
        $result = array();
        if($request->isPost())
        {
            try{
                $produto = $request->getPost("produto");

                $cor = $request->getPost("cor");
                $vista = $request->getPost("vista");
                $prazo = $request->getPost("prazo");

                if($id > 0)
                {
                    $custo = $em->find("Application\Model\Custo", $id);
                    if($custo == null){
                        $this->redirect()->toRoute('custo');
                    }
                }else
                {
                    $custo = new \Application\Model\Custo();
                }
                $custo->setData(date('d/m/Y'));
                $custo->setProduto($produto);
                $custo->setCor($cor);
                $custo->setAvista($vista);
                $custo->setAprazo($prazo);


                $em->persist($custo);
                $em->flush();
                
                $result["resp"] = "Salvo com sucesso!";
                $result["tipo_mens"] = 'success';
            }  catch (ExceptionEvent $e){
            }
        }
        $lista = $em->getRepository("Application\Model\Custo")->findBy(
                   array(),        // $where 
                   array('datacadastro' => 'ASC'),    // $orderBy
                   1000,                        // $limit
                   0                          // $offset
        );

        if($id > 0)
        {
            $custo = $em->getRepository("Application\Model\Custo")->find($id);
            return new ViewModel(array('lista' => $lista, 'custo' => $custo));
        }
        
        return new ViewModel(array('lista' => $lista));
    }
    
    public function carregarclientesAction()
    {
        session_start();
        if (!isset($_SESSION['usuarioNome'])) {
            return $this->redirect()->toRoute('login');
        }
        $em = $this->getServiceLocator()->get("Doctrine\ORM\EntityManager");
        $lista = $em->getRepository("Application\Model\Cliente")->findAll();
        $view = new ViewModel(array('lista' => $lista));
        $view->setTerminal(true);
        return $view;
    }
    
    public function excluircustoAction()
    {
        session_start();
        if (!isset($_SESSION['usuarioNome'])) {
            return $this->redirect()->toRoute('login');
        }
        $id = $this->params()->fromRoute("id", 0);
        
        $em = $this->getServiceLocator()->get("Doctrine\ORM\EntityManager");
        $custo = $em->getRepository("Application\Model\Custo")->find($id);
        $em->remove($custo);
        $em->flush();
        $view = new ViewModel();
        $view->setTerminal(true);
        return $view;
    }

    public function excluirclienteAction()
    {
        session_start();
        if (!isset($_SESSION['usuarioNome'])) {
            return $this->redirect()->toRoute('login');
        }
        $id = $this->params()->fromRoute("id", 0);
        
        $em = $this->getServiceLocator()->get("Doctrine\ORM\EntityManager");
        $cliente = $em->getRepository("Application\Model\Cliente")->find($id);
        $em->remove($cliente);
        $em->flush();
        $view = new ViewModel();
        $view->setTerminal(true);
        return $view;
    }

    public function excluirprodutoAction()
    {
        session_start();
        if (!isset($_SESSION['usuarioNome'])) {
            return $this->redirect()->toRoute('login');
        }
        $id = $this->params()->fromRoute("id", 0);
        
        $em = $this->getServiceLocator()->get("Doctrine\ORM\EntityManager");
        $prod = $em->getRepository("Application\Model\Produto")->find($id);
        $em->remove($prod);
        $em->flush();
        $view = new ViewModel();
        $view->setTerminal(true);
        return $view;
    }
    
    public function visualizarAction()
    {
        session_start();
        if (!isset($_SESSION['usuarioNome'])) {
            return $this->redirect()->toRoute('login');
        }
        $id = $this->params()->fromRoute("id", 0);
        
        $em = $this->getServiceLocator()->get("Doctrine\ORM\EntityManager");
        $venda = $em->getRepository("Application\Model\Venda")->find($id);
        //         \Zend\Debug\Debug::dump($cliente);
        $view = new ViewModel(array('venda' => $venda));
        return $view;
    }
    
    public function relatorioAction()
    {
        session_start();
        if (!isset($_SESSION['usuarioNome'])) {
            return $this->redirect()->toRoute('login');
        }
        $em = $this->getServiceLocator()->get("Doctrine\ORM\EntityManager");
        $request = $this->getRequest();

        $filtro = array();
        if($request->isPost())
        {
            $cliente = $request->getPost("cliente");
            $data_de = $request->getPost("data_de");
            $data_a = $request->getPost("data_a");

            $limite = $request->getPost("limite");
            $offSet = $this->params()->fromRoute("id", 0);

            $filtro = array('data_de' => $data_de, 'data_a' =>$data_a, 'cliente' => $cliente, 'limite' => $limite, 'next' => $offSet + $limite, 'preview' => $offSet - $limite);

            $where = 'where 1 = 1';
            if($cliente > 0){
                $where .= " and v.id_cliente = $cliente";
            }

            $order = " order By v.data desc";

            if($data_de != null){
                $ano= substr($data_de, 6);
                $mes= substr($data_de, 3,-5);
                $dia= substr($data_de, 0,-8);
                $data_de = $ano."-".$mes."-".$dia;
                
                $where .= " and v.data >= '".$data_de."'";
                $order = " order By v.data ASC";
            }
            if($data_a != null){
                $ano= substr($data_a, 6);
                $mes= substr($data_a, 3,-5);
                $dia= substr($data_a, 0,-8);
                $data_a = $ano."-".$mes."-".$dia;
                
                $where .= " and v.data <= '".$data_a."'";
                $order = " order By v.data ASC";
            }
            $where .= $order;

            $db = $em->createQuery('select v from Application\Model\Venda v '.$where);
            if($limite > 0){
                $db->setFirstResult($offSet);
                $db->setMaxResults( $limite );
            }
            
            $vendas = $db->getArrayResult();
        }else{
            
            $db = $em->createQuery('select v from Application\Model\Venda v order By v.data desc')
            ->setMaxResults( 10 );
            $vendas = $db->getArrayResult();

            $filtro = array('next' =>  10, 'preview' => -10 );
        }
        $produtos = $em->getRepository("Application\Model\Produto")->findAll();
        $clientes = $em->getRepository("Application\Model\Cliente")->findAll();

        return new ViewModel(array('vendas' => $vendas, 'produtos' => $produtos, 'clientes' => $clientes, 'filtro' => $filtro));
    }
}