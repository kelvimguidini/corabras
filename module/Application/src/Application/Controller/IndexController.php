<?php

/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Zend\Cache\Storage\ExceptionEvent;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\Controller\Plugin\FlashMessenger;
use Zend\View\Model\ViewModel;
use Zend\Session\Storage\SessionStorage;
use Zend\Session\SessionManager;
use Zend\Session\Container;
use Dompdf\Dompdf;


class IndexController extends AbstractActionController
{

  public function indexAction()
  {
    session_start();

    $encoding = mb_internal_encoding();

    $em = $this->getServiceLocator()->get("Doctrine\ORM\EntityManager");

    $request = $this->getRequest();

    $idVenda = $this->params()->fromRoute("id", 0);
    $produtos_lista = null;
    if ($idVenda > 0) {
      $produtos_lista = $em->getRepository("Application\Model\Produto")->findBy(array('venda' => $idVenda));
      $venda = $em->getRepository("Application\Model\Venda")->find($idVenda);
    } else {
      $venda = new \Application\Model\Venda();
    }

    $result = array();
    if ($request->isPost()) {
      try {
        $qtd_produtos = $request->getPost("qtd_produtos");

        $nome = mb_strtoupper($request->getPost("nome"), $encoding);
        $cpfcnpj = $request->getPost("cpfcnpj");
        $endereco = mb_strtoupper($request->getPost("endereco"), $encoding);
        $cidade = $request->getPost("cidade");
        $data_entrega = $request->getPost("data_entrega");
        $nota_fiscal = $request->getPost("nota_fiscal") == "Sim";
        $tipo_nota_fiscal = $request->getPost("tipo_nota_fiscal");
        $pagamento = $request->getPost("pagamento");
        $forma_pagamento = $request->getPost("forma_pagamento");
        $observacao = $request->getPost("observacao");
        $urgente = $request->getPost("urgente");
        $telefone = $request->getPost("telefone");
        $contato = mb_strtoupper($request->getPost("contato"), $encoding);

        $envio = $request->getPost("envio") == "Entregar";
        $outroendereco = $request->getPost("outroendereco") == "Sim";
        $endereco_entrega = mb_strtoupper($request->getPost("endereco_entrega"), $encoding);
        $obs = $request->getPost("obs");

        $vendedor = mb_strtoupper($request->getPost("vendedor"), $encoding);

        $venda->setNome_vendedor($vendedor);

        $venda->setNome($nome);
        $venda->setCpfcnpj($cpfcnpj);
        $venda->setEndereco($endereco);
        $venda->setCidade($cidade);
        $venda->setData_cadastro(date("d/m/Y"));
        $venda->setData_entrega($data_entrega);
        $venda->setNota_fiscal($nota_fiscal);
        $venda->setTipo_nf($tipo_nota_fiscal);
        $venda->setPagamento($pagamento);
        $venda->setForma_pagamento($forma_pagamento);
        $venda->setOutra($observacao);
        $venda->setUrgente($urgente);
        $venda->setTelefone($telefone);
        $venda->setAberto(false);

        $venda->setEnvio($envio);
        $venda->setLocalEntrega($outroendereco);
        $venda->setEnderecoEntrega($endereco_entrega);
        $venda->setObs($obs);
        $venda->setContato($contato);

        $cesta = array();

        $em->persist($venda);

        $em->flush();

        if ($idVenda > 0) {
          foreach ($produtos_lista as $pro) {
            $p = $em->find("Application\Model\Produto", $pro->getId());
            $em->remove($p);
            $em->flush();
          }
        }

        foreach ($_POST['id_produto_'] as  $i) {
          if ($request->getPost("modelo_" . $i) != null) {

            $produto = new \Application\Model\Produto();

            $produto->setModelo($request->getPost("modelo_" . $i));
            $produto->setCor($request->getPost("cor_" . $i));
            $produto->setquantidade($request->getPost("quantidade_" . $i));
            $produto->setValor($request->getPost("valor_" . $i));
            $produto->setVenda($venda);

            array_push($cesta, $produto);

            $em->persist($produto);
            $em->flush();
          }
        }




        if ($idVenda > 0) {
          return $this->redirect()->toRoute('pedidos');
        }

        $situ = new \Application\Model\Situacao();

        $situ->setData(date("d/m/Y"));
        $situ->setSituacao("Recebido");
        $situ->setVenda($venda);

        $em->persist($situ);
        $em->flush();

        $result["html"] = $this->gerarPdfComprovante($cesta);
      } catch (ExceptionEvent $e) {
        $result["resp"] = "Erro ao salvar! Por favor tente novamente.";
        $result["tipo_mens"] = 'danger';
      }
    }

    $lista = $em->getRepository("Application\Model\Cidade")->findBy(
      array(),
      array('nome' => 'ASC')
    );

    return new ViewModel(array('result' => $result, 'cidades' => $lista, 'venda' => $venda, 'produtos' => $produtos_lista));
  }

  public function loginAction()
  {
    session_start();
    if (isset($_SESSION['usuarioNome'])) {
      return $this->redirect()->toRoute('pedidos');
    }
    $request = $this->getRequest();
    if ($request->isPost()) {
      $usuario = $request->getPost("usuario");
      $senha = $request->getPost("senha");

      if (strtoupper($usuario) == "ALESSANDRO" && strtoupper($senha) == "TOC102030" || strtoupper($usuario) == "ENTREGA" && strtoupper($senha) == "CORAL102030") {

        $_SESSION['usuarioNome'] = $usuario;


        return $this->redirect()->toRoute('pedidos');
      }
    }
    return new ViewModel();
  }

  public function abrirAction()
  {
    session_start();
    if (!isset($_SESSION['usuarioNome'])) {
      return $this->redirect()->toRoute('login');
    }

    $request = $this->getRequest();
    $em = $this->getServiceLocator()->get("Doctrine\ORM\EntityManager");
    $id = $this->params()->fromRoute("id", 0);

    $db = $em->createQuery('select v from Application\Model\Venda v where v.id = ' . $id)
      ->setMaxResults(1);

    $venda = $db->getSingleResult();
    $venda->setAberto(true);
    $em->persist($venda);
    $em->flush();
    $view->setTerminal(true);

    return new ViewModel();
  }

  public function tramitarAction()
  {
    session_start();
    if (!isset($_SESSION['usuarioNome'])) {
      return $this->redirect()->toRoute('login');
    }

    $request = $this->getRequest();
    $situacao = "Recebido";

    $cargaTemp = null;
    if ($request->isPost()) {
      $situacao = $request->getPost("situacao");
      $idcarga = $request->getPost("carga");

      if (isset($_POST['item_tramitar'])) {
        $array_tramitar = $_POST['item_tramitar'];
      }


      $em = $this->getServiceLocator()->get("Doctrine\ORM\EntityManager");

      $array_tramitar_carga = isset($_POST['carga_tramitar']) ? $_POST['carga_tramitar'] : array();

      if (($situacao == "Entrega" || $situacao == "Finalizados") && count($array_tramitar_carga) > 0) {

        foreach ($array_tramitar_carga as $item_tramitar) {

          $carga = $em->getRepository("Application\Model\Carga")->find($item_tramitar);
          $carga->setSituacao($situacao);

          $db = $em->createQuery('select v from Application\Model\Venda v where IDENTITY(v.carga) = ' . $item_tramitar);
          $vendas = $db->getArrayResult();

          $em->persist($carga);
          $em->flush();
          $array_tramitar = array();

          foreach ($vendas as $venda) {
            $array_tramitar[] = $venda["id"];

            $db = $em->createQuery('select v from Application\Model\Venda v where v.id = ' . $venda["id"])
              ->setMaxResults(1);

            $venda = $db->getSingleResult();


            $venda->setCarga($carga);

            $em->persist($venda);
            $em->flush();
          }
        }
      }

      if (($situacao == 'Carregamento' || $situacao == 'Entrega') && $idcarga != null) {

        $db = $em->createQuery('select c from Application\Model\Carga c where c.id = ' . $idcarga)
          ->setMaxResults(1);

        $carga = $db->getSingleResult();
      }
      //\Zend\Debug\Debug::dump($carga);
      foreach ($array_tramitar as $item_tramitar) {

        $db = $em->createQuery('select v from Application\Model\Venda v where v.id = ' . $item_tramitar)
          ->setMaxResults(1);

        $venda = $db->getSingleResult();


        if (($situacao == 'Carregamento' || $situacao == 'Entrega') && $idcarga != null) {
          $venda->setCarga($carga);

          $em->persist($venda);
          $em->flush();
        }
        if ($situacao == 'Recebido') {
          $cargaTemp = $venda->getCarga()->getId();
          $venda->setCarga(null);

          $em->persist($venda);
          $em->flush();
        }
        //\Zend\Debug\Debug::dump($cargaTemp );
        if (($venda->getCarga() != null && $venda->getCarga()->getId() != null) || $cargaTemp != null) {

          $idCargaTemp = $cargaTemp != null ? $cargaTemp : $venda->getCarga()->getId();

          $db = $em->createQuery('select v.id from Application\Model\Venda v where IDENTITY(v.carga) = ' . $idCargaTemp);
          $cargas = $db->getArrayResult();


          if (count($cargas) == 0) {
            $c = $em->getRepository("Application\Model\Carga")->find($idCargaTemp);
            $em->remove($c);
            $em->flush();
          } else {
            $db2 = $em->createQuery('select v.id from Application\Model\Venda v where IDENTITY(v.carga) = ' . $idCargaTemp . ' and v.id in (select IDENTITY(s.venda) from Application\Model\Situacao s where s.id = (select max(s1.id) from Application\Model\Situacao s1 where IDENTITY(s1.venda) = v.id ) and s.situacao = \'Carregamento\' or s.situacao = \'Entrega\')');
            $carga2 = $db2->getArrayResult();

            //\Zend\Debug\Debug::dump($carga2);
            if (count($carga2) == 0) {
              $c = $em->getRepository("Application\Model\Carga")->find($idCargaTemp);
              $c->setSituacao($situacao);

              $em->persist($c);
              $em->flush();
            }
          }
        }


        $s = $em->createQuery('select s from Application\Model\Situacao s where s.venda = ' . $item_tramitar)->setMaxResults(1)->getSingleResult();
        $em->remove($s);
        $em->flush();

        $situ = new \Application\Model\Situacao();


        $situ->setData(date("d/m/Y"));
        $situ->setSituacao($situacao);
        $situ->setVenda($venda);

        $em->persist($situ);
        $em->flush();
      }

      $result["resp"] = "Tramitado com sucesso!";
      $result["tipo_mens"] = 'success';
    }
    return $this->redirect()->toRoute('pedidos', array('situacao' => $situacao));
  }

  public function desmembrarAction()
  {
    session_start();
    if (!isset($_SESSION['usuarioNome'])) {
      return $this->redirect()->toRoute('login');
    }

    $request = $this->getRequest();

    if ($request->isPost()) {
      $idvenda = $request->getPost("idvenda");

      $em = $this->getServiceLocator()->get("Doctrine\ORM\EntityManager");

      $vendaOld = $em->getRepository("Application\Model\Venda")->find($idvenda);
      $produtos = json_decode(stripslashes($_POST['prods']));

      $venda = new \Application\Model\Venda();

      $venda->setNome_vendedor($vendaOld->getNome_vendedor());

      $venda->setNome($vendaOld->getNome());
      $venda->setCpfcnpj($vendaOld->getCpfcnpj());
      $venda->setEndereco($vendaOld->getEndereco());
      $venda->setCidade($vendaOld->getCidade());
      $venda->setData_cadastro($vendaOld->getData_cadastro());
      $venda->setData_entrega($vendaOld->getData_entrega());
      $venda->setNota_fiscal($vendaOld->getNota_fiscal());
      $venda->setTipo_nf($vendaOld->getTipo_nf());
      $venda->setPagamento($vendaOld->getPagamento());
      $venda->setForma_pagamento($vendaOld->getForma_pagamento());
      $venda->setOutra($vendaOld->getOutra());
      $venda->setUrgente($vendaOld->getUrgente());
      $venda->setTelefone($vendaOld->getTelefone());
      $venda->setContato($vendaOld->getContato());
      $venda->setAberto(true);

      $venda->setEnvio($vendaOld->getEnvio());
      $venda->setLocalEntrega($vendaOld->getLocalEntrega());
      $venda->setEnderecoEntrega($vendaOld->getEnderecoEntrega());
      $venda->setObs($vendaOld->getObs());

      $em->persist($venda);
      $em->flush();

      $situ = new \Application\Model\Situacao();

      $situ->setData(date("d/m/Y"));
      $situ->setSituacao("Recebido");
      $situ->setVenda($venda);

      $em->persist($situ);
      $em->flush();

      $i = 0;
      foreach ($produtos as $prod) {

        if (!is_null($prod)) {

          $prodOld = $em->getRepository("Application\Model\Produto")->find($i);

          $produto = new \Application\Model\Produto();

          $produto->setModelo($prodOld->getModelo());
          $produto->setCor($prodOld->getCor());
          $produto->setQuantidade($prod);
          $produto->setValor($prodOld->getValor());
          $produto->setVenda($venda);

          $em->persist($produto);
          $prodOld->setquantidade($prodOld->getQuantidade() - $prod);

          $em->persist($prodOld);
          $em->flush();
        }
        $i++;
      }
    }
    return new ViewModel(array('resp' => 'Tramitado com sucesso!'));
  }

  public function pedidosAction()
  {
    session_start();
    if (!isset($_SESSION['usuarioNome'])) {
      return $this->redirect()->toRoute('login');
    }
    $encoding = mb_internal_encoding();

    $em = $this->getServiceLocator()->get("Doctrine\ORM\EntityManager");
    $request = $this->getRequest();

    $offSet = $this->params()->fromRoute("offset", 0);
    $situ = $this->params()->fromRoute("situacao", "Recebido");

    $dbSub = $em->createQuery("select st.situacao from Application\Model\Situacao st where st.situacao = '$situ' ORDER BY st.data ASC")
      ->setMaxResults(1);
    $dbSubResult = $dbSub->getArrayResult()[0]['situacao'];

    $where = "where '$situ' = '$dbSubResult' ";

    $filtro = array();
    if ($request->isPost()) {
      $cliente = mb_strtoupper($request->getPost("nome"), $encoding);
      $cpfCnpj = $request->getPost("cpfcnpj");
      $cidade = $request->getPost("cidade");
      $dataEnrega = $request->getPost("data_entrega");
      $vendedor = mb_strtoupper($request->getPost("vendedor"), $encoding);
      $modelo = $request->getPost("modelo");

      $entregar = isset($_POST['envio']) && in_array('Entregar', $_POST['envio']);
      $retirar = isset($_POST['envio']) && in_array('Retirar', $_POST['envio']);

      $urgente = isset($_POST['urgente']) &&  in_array('Sim', $_POST['urgente']);
      $naoUrgente = isset($_POST['urgente']) &&  in_array('Não', $_POST['urgente']);

      $limite = $request->getPost("limite");

      $filtro = array(
        'nome' => $cliente,
        'cpfcnpj' => $cpfCnpj,
        'cidade' => $cidade,
        'data_entrega' => $dataEnrega,
        'vendedor' => $vendedor,
        'modelo' => $modelo,
        'Entregar' => $entregar,
        'Retirar' => $retirar,
        'urgente' => $urgente,
        'naoUrgente' => $naoUrgente,

        'limite' => $limite,
        'next' => $offSet + $limite,
        'preview' => $offSet - $limite,
        'situacao' => $situ
      );


      if ($cliente != "") {
        $where .= " and v.nome like '%$cliente%'";
      }
      if ($cpfCnpj != "") {
        $where .= " and v.cpfcnpj = '$cpfCnpj'";
      }
      if ($cidade != "") {
        $where .= " and v.cidade = '$cidade'";
      }
      if ($vendedor != "") {
        $where .= " and v.nome_vendedor like '%$vendedor%'";
      }
      if ($modelo != "") {
        $where .= " and v.id in (select IDENTITY(p.venda) from Application\Model\Produto p where p.modelo = '$modelo')";
      }
      if (!$urgente) {
        $where .= " and v.urgente = 'Não'";
      }
      if (!$naoUrgente) {
        $where .= " and v.urgente = 'Sim'";
      }
      if (!$entregar) {
        $where .= " and v.envio = false";
      }
      if (!$retirar) {
        $where .= " and v.envio = 1";
      }
      if ($dataEnrega != null) {
        $ano = substr($dataEnrega, 6);
        $mes = substr($dataEnrega, 3, -5);
        $dia = substr($dataEnrega, 0, -8);
        $dataEnrega = $ano . "-" . $mes . "-" . $dia;

        $where .= " and v.data_para_entrega = '" . $dataEnrega . "'";
      }
      //\Zend\Debug\Debug::dump($where);
      $db = $em->createQuery('select v, c from Application\Model\Venda v LEFT JOIN v.carga c ' . $where . ' order By v.data_cadastro DESC, v.id DESC');
      if ($limite > 0) {
        $db->setFirstResult($offSet);
        $db->setMaxResults($limite);
      }

      $vendas = $db->getArrayResult();
    } else {

      $db = $em->createQuery('select v, c from Application\Model\Venda v LEFT JOIN v.carga c ' . $where . ' order By v.data_cadastro DESC, v.id DESC')
        ->setMaxResults(100);
      $vendas = $db->getArrayResult();

      $filtro = array('next' =>  100, 'preview' => -100, 'situacao' => $situ);
    }

    $vendasId = array();
    foreach ($vendas as $key => $value) {
      array_push($vendasId, $value["id"]);
    }

    $db = $em->createQuery('select p FROM Application\Model\Produto p WHERE p.venda IN (:v)');
    $db->setParameter('v', $vendasId);

    $produtos = $db->getResult();
    //\Zend\Debug\Debug::dump($produtos);

    $cidades = $em->getRepository("Application\Model\Cidade")->findBy(
      array(),
      array('nome' => 'ASC')
    );

    $data_atual = date("Y/m/d");

    $query = <<<END
      select c from Application\Model\Carga c
      where (c.situacao = 'Carregamento' or c.situacao = 'Entrega')
        and c.id in(
          select max(v.carga)
          from Application\Model\Venda v
          where
              v.carga is not null
              and v.id not in(
                  select
                      max(s.venda)
                  from Application\Model\Situacao s
                  where s.id = (
                      select max(s1.id)
                      from Application\Model\Situacao s1
                      where s1.venda = (
                          select max(v2.id) from Application\Model\Venda v2 where v2.carga is not null
                      )
                  )
              and v.id not in(
                  select
                      max(s3.venda)
                  from Application\Model\Situacao s3
                  where s3.venda = (
                      select max(v3.id) from Application\Model\Venda v3 where v3.carga is not null
                  )
                  and s3.situacao = 'Carregamento'
              )
          )
      )
    END;

    $_cargas_combo = $em->createQuery($query);
    $cargas_combo = $_cargas_combo->getArrayResult();

    $_cargas = $em->createQuery('select c from Application\Model\Carga c ');
    $cargas = $_cargas->getArrayResult();

    return new ViewModel(array('vendas' => $vendas, 'filtro' => $filtro, 'produtos' => $produtos, 'cargas' => $cargas, 'cargas_combo' => $cargas_combo, 'cidades' => $cidades));
  }

  public function carregamentoAction()
  {
    session_start();

    $em = $this->getServiceLocator()->get("Doctrine\ORM\EntityManager");
    $db = $em->createQuery('select c, v, p from Application\Model\Carga c LEFT JOIN c.vendas v LEFT JOIN v.produtos p');
    $db->setMaxResults(10);
    $cargas = $db->getArrayResult();

    //\Zend\Debug\Debug::dump($cargas);
    return new ViewModel(array('carregamentos' => $cargas));
  }

  public function cadastrarcargaAction()
  {
    session_start();
    if (!isset($_SESSION['usuarioNome'])) {
      return $this->redirect()->toRoute('login');
    }
    $em = $this->getServiceLocator()->get("Doctrine\ORM\EntityManager");

    $request = $this->getRequest();

    if ($request->isPost()) {
      $motorista = $request->getPost("motorista");
      $data = $request->getPost("saida");
      $saida = $request->getPost("saidah");
      $retorno = $request->getPost("retornoh");
      $situacao = $request->getPost("situacao") == "true" ? "Entrega" : "Carregamento";

      $carga = new \Application\Model\Carga();

      $carga->setMotorista(trim($motorista));
      $carga->setData(trim($data));
      $carga->setSaida(trim($saida));
      $carga->setRetorno(trim($retorno));
      $carga->setSituacao($situacao);

      $em->persist($carga);
      $em->flush();

      $view = new ViewModel(array('id' => $carga->getId()));
      $view->setTerminal(true);
      return $view;
    }
  }

  public function carregarcargasAction()
  {
    session_start();
    if (!isset($_SESSION['usuarioNome'])) {
      return $this->redirect()->toRoute('login');
    }
    $em = $this->getServiceLocator()->get("Doctrine\ORM\EntityManager");
    $lista = $em->getRepository("Application\Model\Carga")->findAll();
    $view = new ViewModel(array('lista' => $lista));
    $view->setTerminal(true);
    return $view;
  }

  public function carregardadoscargaAction()
  {
    session_start();
    if (!isset($_SESSION['usuarioNome'])) {
      return $this->redirect()->toRoute('login');
    }
    $em = $this->getServiceLocator()->get("Doctrine\ORM\EntityManager");

    $id = $this->params()->fromRoute("id", 0);

    $db = $em->createQuery('select c, v, p from Application\Model\Carga c LEFT JOIN c.vendas v LEFT JOIN v.produtos p where c.id = ' . $id);
    $cargas = $db->getArrayResult();


    $view = new ViewModel(array('lista' => $cargas));
    $view->setTerminal(true);
    return $view;
  }

  public function gerarPdfComprovante($produtos)
  {
    $renderer = $this->serviceLocator->get('Zend\View\Renderer\RendererInterface');

    if (isset($produtos[0]->venda)) {

      $html = "
      <style type=\"text/css\">
.tg  {border-collapse:collapse;border-spacing:0;}
.tg td{border-color:black;border-style:solid;border-width:1px;font-family:Arial, sans-serif;font-size:14px;
  overflow:hidden;padding:10px 5px;word-break:normal;}
.tg th{border-color:black;border-style:solid;border-width:1px;font-family:Arial, sans-serif;font-size:14px;
  font-weight:normal;overflow:hidden;padding:10px 5px;word-break:normal;}
.tg .tg-0pky{border-color:inherit;text-align:left;vertical-align:top}
</style>
      <table border=\"1\" width='100%' style='border-collapse: collapse;'>
        <thead>
          <tr>
            <th colspan=\"5\"><table width='100%'>
                <tbody>
                  <tr>
                    <td width='170px' style='text-align:left;vertical-align:middle'><img src=\"" . __DIR__ . "/../../../../../public" . $renderer->basePath('/img/logo-1.png') . "\" alt=\"Image\" width=\"150\" height=\"60\"></td>
                    <td style='text-align:center;vertical-align:middle'><span style=\"font-weight:bold; font-size:19px\">CORABRAS TELHAS DE CONCRETO</span><br><br><span style=\"font-weight:bold\">CNPJ </span> 82.888.702/0001-18<br>Lote 02 - Gleba 02 - ICAGE Alexandre Gusmão - Brazlândia-DF<br><span style=\"color:#3166FF; font-size:18px\">www.corabras.com.br</span></td>
                    <td width='150px' style='text-align:left;vertical-align:middle'><img src=\"" . __DIR__ . "/../../../../../public" . $renderer->basePath('/img/Corabras_Selo-1.png') . "\" width=\"150\" height=\"112\"></td>
                  </tr>
                </tbody>
                </table>
               </th>
          </tr>
          <tr>
            <th>Vendedor:</th>
            <td colspan=\"2\">" . $produtos[0]->venda->nome_vendedor . "</td>
            <th>Data Cadastro:</th>
            <td>" . $produtos[0]->venda->getData_cadastro() . "</td>
          </tr>
          <tr>
            <th>Nome Cliente:</th>
            <td colspan=\"2\">" . $produtos[0]->venda->nome . "</td>
            <th>Documento:</th>
            <td>" . $produtos[0]->venda->cpfcnpj . "</td>
          </tr>
          <tr>
            <th>Telefone do Cliente:</th>
            <td colspan=\"2\">" . $produtos[0]->venda->telefone . "</td>
                        <th>Contato:</th>
            <td>" . $produtos[0]->venda->contato . "</td>
          </tr>
          <tr>
            <th>Endereço:</th>
            <td colspan=\"2\">" . $produtos[0]->venda->endereco . "</td>

            <th>Cidade:</th>
            <td>" . $produtos[0]->venda->cidade . "</td>
          </tr>
          <tr>
            <th>Data Entrega:</th>
            <td colspan=\"2\">" . $produtos[0]->venda->getData_entrega() . "</td>
            <th>Urgente:</th>
            <td>";
      $html .= $produtos[0]->venda->urgente;
      $html .= "</td>
          </tr>
          <tr>
            <th>Tipo Nota Fiscal:</th>
            <td colspan=\"2\">" . $produtos[0]->venda->tipo_nf . "</td>

            <th>Nota Fiscal:</th>
            <td>";
      $html .= $produtos[0]->venda->nota_fiscal ? "Sim" : "Não";
      $html .= "</td>
          </tr>
          <tr>
            <th>Forma de Pagamento:</th>
            <td colspan=\"2\">";

      switch ($produtos[0]->venda->forma_pagamento) {
        case "AV":
          $html .= "À Vista";
          break;
        case "CH":
          $html .= "Cheque";
          break;
        case "TR":
          $html .= "Transferência";
          break;
        case "OU":
          $html .= $produtos[0]->venda->descricao_outra_forma_pagamento;
          break;
      }

      $html .= "</td>
            <th>Pagamento:</th>
            <td>";

      switch ($produtos[0]->venda->pagamento) {
        case "EN":
          $html .= "Na Entrega";
          break;
        case "AN":
          $html .= "Antecipado";
          break;
      }

      $html .= "</td>
          </tr>
          <tr>
            <th>Endereço Entrega:</th>
            <td colspan=\"4\">";
      if ($produtos[0]->venda->envio) {
        if ($produtos[0]->venda->local_entrega) {
          $html .= $produtos[0]->venda->endereco;
        } else {
          $html .= $produtos[0]->venda->endereco_entrega;
        }
      } else {
        $html .= "Retirar";
      }
      $html .= "</td>
          </tr>

          <tr>
            <th >Observação:</th>
            <td colspan=\"4\">" . $produtos[0]->venda->obs . "</td>
          </tr>
      <thead>

      <tbody>
        <tr>
          <th colspan=\"5\">&nbsp;</th>
                </tr>
        <tr style=\"background-color: #f4f2f7;\">
          <th>Modelo</th>
          <th>Cor</th>
          <th>Quantidade</th>
          <th>Valor</th>
          <th>Total</th>
                </tr>";



      $totalGeral = 0;
      foreach ($produtos as $produto) {
        $total = floatval(str_replace(',', '.', $produto->valor)) * $produto->quantidade;
        $totalGeral += $total;

        $valor = number_format($produto->valor, 2, ',', '.');
        $valorTotal = number_format($total, 2, ',', '.');

        $html .= "<tr>
          <td>" . $produto->modelo . "</td>
          <td>" . $produto->cor . "</td>
          <td>" . $produto->quantidade . "</td>
          <th>R$ " . $valor . "</td>
          <td>R$ " . $valorTotal . "</td>
                </tr>";
      }
      $valorTotalGeral = number_format($totalGeral, 2, ',', '.');
      $html .= "<tr>
          <th colspan=\"4\">Total</th>
          <td>R$ " . $valorTotalGeral . "</td>
                </tr>
                <tr>
            <th colspan=\"5\">
                <table width='100%' class=\"tg\">
                    <tbody>
                        <tr>
                            <th class=\"tg-0pky\"><span style=\"font-weight:bold\">Banco Brasil</span><br>Agencia: <span style=\"font-weight:bold\">1840-6</span><br>C/C: <span style=\"font-weight:bold\">134 538-9</span><br>Coral &amp; Coral Ltda. <br>CNPJ 82.888.702/0001-18</th>
                            <th class=\"tg-0pky\"><span style=\"font-weight:bold\">Itaú </span><br>Agencia: <span style=\"font-weight:bold\">4336  </span><br>C/C: <span style=\"font-weight:bold\">22492-0</span><br>Coral &amp; Coral Ltda.<br>CNPJ 82.888.702/0001-18</th>
                            <th class=\"tg-0pky\"><span style=\"font-weight:bold\">Caixa</span><br>Agência: <span style=\"font-weight:bold\">2407 </span><br>Operação: <span style=\"font-weight:bold\">003</span><br>C/C: <span style=\"font-weight:bold\">3924-4</span><br>Coral &amp; Coral Ltda.  <br>CNPJ 82.888.702/0001-18</th>
                          </tr>
                    </tbody>
                </table>
               </th>
          </tr>
      </tbody>
    </table>";


      $filename = "Comprovante_cadastro";

      $this->gerarPdf($html, $filename);
    }
  }

  public function imprimirAction()
  {
    session_start();
    if (!isset($_SESSION['usuarioNome'])) {
      return $this->redirect()->toRoute('login');
    }
    $encoding = mb_internal_encoding();

    $idVenda = $this->params()->fromRoute("id", 0);
    $filename = "Declacao_entrega_" . $idVenda;

    $em = $this->getServiceLocator()->get("Doctrine\ORM\EntityManager");

    $db = $em->createQuery('select v, p, c from Application\Model\Venda v LEFT JOIN v.produtos p LEFT JOIN v.carga c where v.id = ' . $idVenda);
    $pedido = $db->getArrayResult()[0];
    //\Zend\Debug\Debug::dump($pedido);
    $renderer = $this->serviceLocator->get('Zend\View\Renderer\RendererInterface');

    $html = "
        <style type=\"text/css\">
            .tg  {border-collapse:collapse;border-spacing:0;margin:0px auto;}
            .tg td{border-color:black;border-style:solid;border-width:1px;font-family:Arial, sans-serif;font-size:14px;
              overflow:hidden;padding:5px 5px;word-break:normal;}
            .tg th{border-color:black;border-style:solid;border-width:1px;font-family:Arial, sans-serif;font-size:14px;
              font-weight:normal;overflow:hidden;padding:5px 5px;word-break:normal;}
            .tg .tg-1wig{font-weight:bold;text-align:left;vertical-align:top}
            .tg .tg-9wq8{border-color:inherit;text-align:center;vertical-align:middle}
            .tg .tg-baqh{text-align:center;vertical-align:top}
            .tg .tg-c3ow{border-color:inherit;vertical-align:top}
            .tg .tg-0pky{border-color:inherit;text-align:left;vertical-align:top}
            .tg .tg-dvpl{border-color:inherit;text-align:right;vertical-align:top}
            .tg .tg-fymr{border-color:inherit;font-weight:bold;text-align:left;vertical-align:top}
            .tg .tg-0lax{text-align:left;vertical-align:top}
            .tg .tg-amwm{font-weight:bold;text-align:center;vertical-align:top}
            </style>
            <table class=\"tg\">
            <tbody>
              <tr>
                <td class=\"tg-9wq8\" colspan=\"2\"><img src=\"" . __DIR__ . "/../../../../../public" . $renderer->basePath('/img/corabras.png') . "\" ></td>
                <td class=\"tg-9wq8\" colspan=\"3\"><span style=\"font-weight:bold\">DECLARAÇÃO</span></td>
              </tr>
              <tr>
                <td class=\"tg-0pky\"><span style=\"font-weight:bold\">CLIENTE</span></td>
                <td class=\"tg-0pky\" colspan=\"4\">" . $pedido['nome'] . "</td>
              </tr>
              <tr>
                <td class=\"tg-c3ow\"><span style=\"font-weight:bold\">Endereço: </span></td>
                <td class=\"tg-0pky\" colspan=\"4\">" . $pedido['endereco'] . " - " . $pedido['cidade'] . "</td>
              </tr>
              <tr>
                <td class=\"tg-0pky\"><span style=\"font-weight:bold\">CONTATO</span></td>
                <td class=\"tg-0pky\" colspan=\"4\">" . $pedido['contato'] . "</td>
              </tr>
              <tr>
                <td class=\"tg-0pky\"><span style=\"font-weight:bold\">TELEFONE</span></td>
                <td class=\"tg-0pky\" colspan=\"4\">" . $pedido['telefone'] . "</td>
              </tr>
              <tr>
                <td class=\"tg-0pky\"><span style=\"font-weight:bold\">VENDEDOR</span></td>
                <td class=\"tg-0pky\" colspan=\"4\">" . $pedido['nome_vendedor'] . "</td>
              </tr>
              <tr>
                <td class=\"tg-0pky\" colspan=\"5\"><span style=\"font-weight:bold\">Declaro ter recebido a mercadoria descrita abaixo:</span></td>
              </tr>";
    $qtd_total = 0;
    foreach ($pedido['produtos'] as $prod) {
      $qtd_total += $prod['quantidade'];
      $html .= "<tr>
              <td class=\"tg-dvpl\"><span style=\"font-weight:bold\">" . $prod['quantidade'] . "</span></td>
              <td class=\"tg-0pky\" colspan=\"4\">PÇS " . mb_strtoupper($prod['modelo'], $encoding) . " " . mb_strtoupper($prod['cor'], $encoding) . "</td>
            </tr>";
    }

    $html .= "<tr>
                <td class=\"tg-dvpl\" colspan=\"5\"><span style=\"font-weight:bold\">BRASÍLIA-DF " . date("d/m/Y") . "</span></td>
              </tr>
              <tr>
                <td class=\"tg-0pky\" colspan=\"5\">QUEBRAS NO CARREGAMENTO: </td>
              </tr>
              <tr>
                <td class=\"tg-0pky\" colspan=\"5\">PEÇAS PARA REPOSIÇÃO:</td>
              </tr>
              <tr>
                <td class=\"tg-0pky\">TOTAL DE TELHAS E/OU ACESSÓRIOS: </td>
                <td class=\"tg-0pky\" colspan=\"4\">" . $qtd_total . " PEÇAS</td>
              </tr>
              <tr>
                <td class=\"tg-0lax\" colspan=\"4\"><br><br></td>
                <td class=\"tg-0lax\"></td>
              </tr>
              <tr>
                <td class=\"tg-baqh\" colspan=\"4\">Responsável pela descarga</td>
                <td class=\"tg-0lax\"></td>
              </tr>
              <tr>
                <td class=\"tg-0lax\"></td>
                <td class=\"tg-0lax\" colspan=\"4\"><br></td>
              </tr>
              <tr>
                <td class=\"tg-0lax\"></td>
                <td class=\"tg-baqh\" colspan=\"4\">Motorista</td>
              </tr>
              <tr>
                <td class=\"tg-0lax\" colspan=\"4\"><br><br></td>
                <td class=\"tg-0lax\"></td>
              </tr>
              <tr>
                <td class=\"tg-baqh\" colspan=\"4\">Nome completo do conferente</td>
                <td class=\"tg-0lax\"></td>
              </tr>
              <tr>
                <td class=\"tg-0lax\"></td>
                <td class=\"tg-baqh\" colspan=\"4\"><br><br></td>
              </tr>
              <tr>
                <td class=\"tg-0lax\"></td>
                <td class=\"tg-amwm\" colspan=\"4\">Assinatura do conferente</td>
              </tr>
              <tr>
                <td class=\"tg-1wig\" colspan=\"5\">Afirmo que conferi o carregamento e o pedido, confirmando as quantidades acima descritas, SENDO ENTÃO RESPONSÁVEL pela entrega nas quantidades exatas me passadas pelo romaneio</td>
              </tr>
            </tbody>
        </table>";

    //echo $html;
    $this->gerarPdf($html, $filename);
  }

  public function reciboAction()
  {
    session_start();
    if (!isset($_SESSION['usuarioNome'])) {
      return $this->redirect()->toRoute('login');
    }
    $idVenda = $this->params()->fromRoute("id", 0);
    $filename = "Recibo_entrega_" . $idVenda;

    $em = $this->getServiceLocator()->get("Doctrine\ORM\EntityManager");

    $db = $em->createQuery('select v, p, c from Application\Model\Venda v LEFT JOIN v.produtos p LEFT JOIN v.carga c where v.id = ' . $idVenda);
    $pedido = $db->getArrayResult()[0];
    $renderer = $this->serviceLocator->get('Zend\View\Renderer\RendererInterface');


    $qtd_total = 0;
    $valor_total = 0;
    foreach ($pedido['produtos'] as $prod) {
      $qtd_total += $prod['quantidade'];
      $valor_total += floatval(str_replace(',', '.', $prod['valor'])) * $prod['quantidade'];
    }

    $valor_total_g = number_format($valor_total, 2, ',', '.');
    $valor_extenso = $this->valorExtenso($valor_total);

    setlocale(LC_TIME, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');
    date_default_timezone_set('America/Sao_Paulo');

    $html2 = "<style type=\"text/css\">
                .tg  {border: none;margin-top:25px;}
                .tg td{border-color:black;border-style:solid;border-width:1px;font-family:Arial, sans-serif;font-size:14px;
                  overflow:hidden;padding:5px 5px;word-break:normal;}
                .tg th{border-color:black;border-style:solid;border-width:1px;font-family:Arial, sans-serif;font-size:14px;
                  font-weight:normal;overflow:hidden;padding:5px 5px;word-break:normal;}
                .tg .tg-9wq8{border-color:inherit;text-align:center;vertical-align:middle;}
                .tg .tg-9yu8{border-color:inherit;text-align:center;vertical-align:middle;text-decoration: underline;font-size: 20px;}
                .tg .tg-9wxp{border-color:inherit;text-align:center;vertical-align:middle;font-size:40px;}
                .tg .tg-5wxp{border-color:inherit;text-align:center;vertical-align:middle;width: 25px;}
                .tg .tg-c3ow{border-color:inherit;vertical-align:top}
                .tg .tg-0pky{border-color:inherit;text-align:right;vertical-align:top}
                .tg .tg-0lax{text-align:left;vertical-align:top}
                table, tr, td { border: none !important;}
                </style>";

    for ($i = 0; $i < 2; $i++) {
      $html2 .= "<table class=\"tg\" cellspacing=\"0\" cellpadding=\"0\">
                <tbody>
                    <tr>
                        <td class=\"tg-5wxp\" ><img src=\"" . __DIR__ . "/../../../../../public" . $renderer->basePath('/img/corabras.png') . "\" width=\"100px\" height=\"80px\"; ></td>
                        <td class=\"tg-9wxp\" colspan=\"2\"><span style=\"font-weight:bold\">CORABRAS</span></td>
                    </tr>
                    <tr>
                        <td class=\"tg-0lax\" colspan=\"3\"><br><br></td>
                    </tr>
                    <tr>
                        <td class=\"tg-9yu8\" colspan=\"3\"><span style=\"font-weight:bold\">RECIBO</span></td>
                    </tr>
                    <tr>
                        <td class=\"tg-0lax\" colspan=\"3\"><br><br></td>
                    </tr>
                    <tr>
                        <td class=\"tg-c3ow\" colspan=\"3\">Recebemos de " . $pedido['nome'] . " a quantia supra algarismada de <span style=\"font-weight:bold\">R$ " . $valor_total_g . " (" . $valor_extenso . ") </span> referente ao pagamento de telhas de concreto.</td>
                    </tr>
                    <tr>
                        <td class=\"tg-0lax\" colspan=\"3\"><br><br></td>
                    </tr>
                    <tr>
                        <td class=\"tg-9wq8\" colspan=\"2\"></td>
                        <td class=\"tg-0pky\">" . strftime('%A, %d de %B de %Y', strtotime('today')) . "</td>
                    </tr>
                    <tr>
                        <td class=\"tg-0lax\" colspan=\"2\"><span style=\"font-weight:bold\">" . $qtd_total . " PEÇAS</span></td>
                        <td class=\"tg-0pky\"><br><br><br><br>______________________________________________________________</td>
                    </tr>
                </tbody>
            </table>";
    }


    //echo $html2;
    $this->gerarPdf($html2, $filename);
  }

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

  public function cadastrarcidadeAction()
  {
    session_start();
    if (!isset($_SESSION['usuarioNome'])) {
      return $this->redirect()->toRoute('login');
    }
    $encoding = mb_internal_encoding();
    $em = $this->getServiceLocator()->get("Doctrine\ORM\EntityManager");

    $request = $this->getRequest();

    if ($request->isPost()) {
      $nome = $request->getPost("cliente_nome");
      $cliente = new \Application\Model\Cidade();

      $cliente->setNome(mb_strtoupper(trim($nome)), $encoding);

      $em->persist($cliente);
      $em->flush();

      $result["resp"] = "Salvo com sucesso!";
      $result["tipo_mens"] = 'success';
    }

    $lista = $em->getRepository("Application\Model\Cidade")->findBy(
      array(),
      array('nome' => 'ASC')
    );

    $view = new ViewModel(array('lista' => $lista));
    return $view;
  }

  public function excluircidadeAction()
  {
    session_start();
    if (!isset($_SESSION['usuarioNome'])) {
      return $this->redirect()->toRoute('login');
    }
    $id = $this->params()->fromRoute("id", 0);

    $em = $this->getServiceLocator()->get("Doctrine\ORM\EntityManager");
    $cidade = $em->getRepository("Application\Model\Cidade")->find($id);
    $em->remove($cidade);
    $em->flush();

    $result["resp"] = "Salvo com sucesso!";
    $result["tipo_mens"] = 'success';

    return $this->redirect()->toRoute('cadastrarcidade');
  }

  public function gerarPdf($html, $filename)
  {
    // include autoloader
    require_once  __DIR__ . '/../../../../../vendor/dompdf/autoload.inc.php';

    // instantiate and use the dompdf class
    $dompdf = new Dompdf();

    $dompdf->loadHtml($html);

    // (Optional) Setup the paper size and orientation
    $dompdf->setPaper('A4', 'portrait');

    // Render the HTML as PDF
    $dompdf->render();

    // Output the generated PDF to Browser
    $dompdf->stream($filename);
  }

  public function valorExtenso($valor, $bolExibirMoeda = true, $bolPalavraFeminina = false)
  {
    //$valor = self::removerFormatacaoNumero( $valor );

    $singular = null;
    $plural = null;

    if ($bolExibirMoeda) {
      $singular = array("centavo", "real", "mil", "milhão", "bilhão", "trilhão", "quatrilhão");
      $plural = array("centavos", "reais", "mil", "milhões", "bilhões", "trilhões", "quatrilhões");
    } else {
      $singular = array("", "", "mil", "milhão", "bilhão", "trilhão", "quatrilhão");
      $plural = array("", "", "mil", "milhões", "bilhões", "trilhões", "quatrilhões");
    }

    $c = array("", "cem", "duzentos", "trezentos", "quatrocentos", "quinhentos", "seiscentos", "setecentos", "oitocentos", "novecentos");
    $d = array("", "dez", "vinte", "trinta", "quarenta", "cinquenta", "sessenta", "setenta", "oitenta", "noventa");
    $d10 = array("dez", "onze", "doze", "treze", "quatorze", "quinze", "dezesseis", "dezessete", "dezoito", "dezenove");
    $u = array("", "um", "dois", "três", "quatro", "cinco", "seis", "sete", "oito", "nove");

    if ($bolPalavraFeminina) {

      if ($valor == 1) {
        $u = array("", "uma", "duas", "três", "quatro", "cinco", "seis", "sete", "oito", "nove");
      } else {
        $u = array("", "um", "duas", "três", "quatro", "cinco", "seis", "sete", "oito", "nove");
      }

      $c = array("", "cem", "duzentas", "trezentas", "quatrocentas", "quinhentas", "seiscentas", "setecentas", "oitocentas", "novecentas");
    }

    $z = 0;

    $valor = number_format($valor, 2, ".", ".");
    $inteiro = explode(".", $valor);

    for ($i = 0; $i < count($inteiro); $i++) {
      for ($ii = mb_strlen($inteiro[$i]); $ii < 3; $ii++) {
        $inteiro[$i] = "0" . $inteiro[$i];
      }
    }

    // $fim identifica onde que deve se dar junção de centenas por "e" ou por "," ;)
    $rt = "";
    $fim = count($inteiro) - ($inteiro[count($inteiro) - 1] > 0 ? 1 : 2);
    for ($i = 0; $i < count($inteiro); $i++) {
      $valor = $inteiro[$i];
      $rc = (($valor > 100) && ($valor < 200)) ? "cento" : $c[$valor[0]];
      $rd = ($valor[1] < 2) ? "" : $d[$valor[1]];
      $ru = ($valor > 0) ? (($valor[1] == 1) ? $d10[$valor[2]] : $u[$valor[2]]) : "";

      $r = $rc . (($rc && ($rd || $ru)) ? " e " : "") . $rd . (($rd && $ru) ? " e " : "") . $ru;
      $t = count($inteiro) - 1 - $i;
      $r .= $r ? " " . ($valor > 1 ? $plural[$t] : $singular[$t]) : "";
      if ($valor == "000")
        $z++;
      elseif ($z > 0)
        $z--;

      if (($t == 1) && ($z > 0) && ($inteiro[0] > 0))
        $r .= (($z > 1) ? " de " : "") . $plural[$t];

      if ($r) {
        $rt .= ((($i > 0) && ($i <= $fim) && ($inteiro[0] > 0) && ($z < 1)) ? (($i < $fim) ? ", " : " e ") : " ") . $r;
      }
    }

    $rt = mb_substr($rt, 1);

    return ($rt ? trim($rt) : "zero");
  }
}
