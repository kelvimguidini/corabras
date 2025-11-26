<?php

/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Laminas\Mvc\MvcEvent;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Dompdf\Dompdf;
use Doctrine\ORM\EntityManager;

class IndexController extends AbstractActionController
{
  private EntityManager $em;
  protected $baseUrl = 'http://sh00094.teste.website/~fddd5815';

  // public function onDispatch(MvcEvent $e)
  // {
  //   $request = $this->getRequest();
  //   $uri = $request->getUri();

  //   $scheme = $uri->getScheme();      // http | https
  //   $host   = $uri->getHost();        // domínio/hostname
  //   $port   = $uri->getPort();        // porta (80, 443 ou outra)
  //   $path   = $request->getBasePath(); // normalmente "/public"

  //   // 🔧 Monta a porta corretamente:
  //   // - só adiciona se não for padrão
  //   $portString = '';
  //   if ($port && !in_array($port, [80, 443])) {
  //     $portString = ':' . $port;
  //   }

  //   // 🔧 Base URL final (com pasta public)
  //   $this->baseUrl  = sprintf(
  //     '%s://%s%s%s',
  //     $scheme,
  //     $host,
  //     $portString,
  //     $path
  //   );

  //   return parent::onDispatch($e);
  // }

  public function __construct(EntityManager $em)
  {
    if ($em) {
      $this->em = $em;
    }
  }


  public function indexAction()
  {
    // session_start();

    $encoding = mb_internal_encoding();
    $request = $this->getRequest();


    $idVenda = $this->params()->fromRoute("id", 0);
    $produtos_lista = null;
    if ($idVenda > 0) {
      $produtos_lista = $this->em->getRepository("Application\Model\Produto")->findBy(array('venda' => $idVenda));
      $venda = $this->em->getRepository("Application\Model\Venda")->find($idVenda);
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

        $venda->setSituacao("Recebido");

        $cesta = array();

        $this->em->persist($venda);

        $this->em->flush();

        if ($idVenda > 0) {
          foreach ($produtos_lista as $pro) {
            $p = $this->em->find("Application\Model\Produto", $pro->getId());
            $this->em->remove($p);
            $this->em->flush();
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

            $this->em->persist($produto);
            $this->em->flush();
          }
        }




        if ($idVenda > 0) {
          return $this->redirect()->toRoute('pedidos');
        }

        // $situ = new \Application\Model\Situacao();

        // $situ->setData(date("d/m/Y"));
        // $situ->setSituacao("Recebido");
        // $situ->setVenda($venda);

        // $this->em->persist($situ);
        // $this->em->flush();

        $result["html"] = $this->gerarPdfComprovante($cesta);
      } catch (\Exception $e) {
        $result["resp"] = "Erro ao salvar! Por favor tente novamente.";
        $result["tipo_mens"] = 'danger';
      }
    }

    $lista = $this->em->getRepository("Application\Model\Cidade")->findBy(
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
    $id = $this->params()->fromRoute("id", 0);

    $db = $this->em->createQuery('select v from Application\Model\Venda v where v.id = ' . $id)
      ->setMaxResults(1);

    $venda = $db->getSingleResult();
    $venda->setAberto(true);
    $this->em->persist($venda);
    $this->em->flush();
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

      $array_tramitar_carga = isset($_POST['carga_tramitar']) ? $_POST['carga_tramitar'] : array();

      if (($situacao == "Entrega" || $situacao == "Finalizados") && count($array_tramitar_carga) > 0) {

        foreach ($array_tramitar_carga as $item_tramitar) {

          $carga = $this->em->getRepository("Application\Model\Carga")->find($item_tramitar);
          $carga->setSituacao($situacao);

          $db = $this->em->createQuery('select v from Application\Model\Venda v where IDENTITY(v.carga) = ' . $item_tramitar);
          $vendas = $db->getArrayResult();

          $this->em->persist($carga);
          $this->em->flush();
          $array_tramitar = array();

          foreach ($vendas as $venda) {
            $array_tramitar[] = $venda["id"];

            $db = $this->em->createQuery('select v from Application\Model\Venda v where v.id = ' . $venda["id"])
              ->setMaxResults(1);

            $venda = $db->getSingleResult();


            $venda->setCarga($carga);

            $this->em->persist($venda);
            $this->em->flush();
          }
        }
      }

      if (($situacao == 'Carregamento' || $situacao == 'Entrega') && $idcarga != null) {

        $db = $this->em->createQuery('select c from Application\Model\Carga c where c.id = ' . $idcarga)
          ->setMaxResults(1);

        $carga = $db->getSingleResult();
      }
      //\Laminas\Debug\Debug::dump($carga);
      foreach ($array_tramitar as $item_tramitar) {

        $db = $this->em->createQuery('select v from Application\Model\Venda v where v.id = ' . $item_tramitar)
          ->setMaxResults(1);

        $venda = $db->getSingleResult();

        $venda->setSituacao($situacao);

        if (($situacao == 'Carregamento' || $situacao == 'Entrega') && $idcarga != null) {
          $venda->setCarga($carga);

          $this->em->persist($venda);
          $this->em->flush();
        } else if ($situacao == 'Recebido') {
          $cargaTemp = $venda->getCarga()->getId();
          $venda->setCarga(null);

          $this->em->persist($venda);
          $this->em->flush();
        } else {
          $this->em->persist($venda);
          $this->em->flush();
        }
        //\Laminas\Debug\Debug::dump($cargaTemp );
        if (($venda->getCarga() != null && $venda->getCarga()->getId() != null) || $cargaTemp != null) {

          $idCargaTemp = $cargaTemp != null ? $cargaTemp : $venda->getCarga()->getId();

          $db = $this->em->createQuery('select v.id from Application\Model\Venda v where IDENTITY(v.carga) = ' . $idCargaTemp);
          $cargas = $db->getArrayResult();


          if (count($cargas) == 0) {
            $c = $this->em->getRepository("Application\Model\Carga")->find($idCargaTemp);
            $this->em->remove($c);
            $this->em->flush();
          } else {
            $db2 = $this->em->createQuery('select v.id from Application\Model\Venda v where IDENTITY(v.carga) = ' . $idCargaTemp . ' and v.id in (select IDENTITY(s.venda) from Application\Model\Situacao s where s.id = (select max(s1.id) from Application\Model\Situacao s1 where IDENTITY(s1.venda) = v.id ) and s.situacao = \'Carregamento\' or s.situacao = \'Entrega\')');
            $carga2 = $db2->getArrayResult();

            //\Laminas\Debug\Debug::dump($carga2);
            if (count($carga2) == 0) {
              $c = $this->em->getRepository("Application\Model\Carga")->find($idCargaTemp);
              $c->setSituacao($situacao);

              $this->em->persist($c);
              $this->em->flush();
            }
          }
        }


        // $s = $this->em->createQuery('select s from Application\Model\Situacao s where s.venda = ' . $item_tramitar)->setMaxResults(1)->getSingleResult();
        // $this->em->remove($s);
        // $this->em->flush();

        // $situ = new \Application\Model\Situacao();


        // $situ->setData(date("d/m/Y"));
        // $situ->setSituacao($situacao);
        // $situ->setVenda($venda);

        // $this->em->persist($situ);
        // $this->em->flush();
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

      $vendaOld = $this->em->getRepository("Application\Model\Venda")->find($idvenda);
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
      $venda->setSituacao("Recebido");

      $venda->setEnvio($vendaOld->getEnvio());
      $venda->setLocalEntrega($vendaOld->getLocalEntrega());
      $venda->setEnderecoEntrega($vendaOld->getEnderecoEntrega());
      $venda->setObs($vendaOld->getObs());

      $this->em->persist($venda);
      $this->em->flush();


      // $situ = new \Application\Model\Situacao();

      // $situ->setData(date("d/m/Y"));
      // $situ->setSituacao();
      // $situ->setVenda($venda);

      // $this->em->persist($situ);
      // $this->em->flush();

      foreach ($produtos as $idProduto => $qtd) {
        if (!is_null($qtd) && $qtd !== '') {
          $prodOld = $this->em->getRepository("Application\Model\Produto")->find($idProduto);


          $produto = new \Application\Model\Produto();

          $produto->setModelo($prodOld->getModelo());
          $produto->setCor($prodOld->getCor());
          $produto->setQuantidade($qtd);
          $produto->setValor($prodOld->getValor());
          $produto->setVenda($venda);

          $this->em->persist($produto);
          $prodOld->setquantidade($prodOld->getQuantidade() - $qtd);

          $this->em->persist($prodOld);
          $this->em->flush();
        }
      }
    } else {
      return new ViewModel(array('resp' => 'Erro ao desmembrar! Por favor tente novamente.'));
    }
    return new ViewModel(array('resp' => 'Tramitado com sucesso!'));
  }
  public function pedidosAction()
  {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    session_start();
    if (!isset($_SESSION['usuarioNome'])) {
      return $this->redirect()->toRoute('login');
    }
    try {
      $encoding = mb_internal_encoding();
      $request = $this->getRequest();

      $offSet = $this->params()->fromRoute("offset", 0);
      $situ = $this->params()->fromRoute("situacao", "Recebido");

      $limitePadrao = 50;
      $limite = $request->isPost() ? (int) $request->getPost("limite", $limitePadrao) : $limitePadrao;

      // Define a ordenação conforme a situação
      $direcao = in_array($situ, ["Recebido", "Entrega"]) ? "v.data_para_entrega" : "v.data_cadastro";

      $qb = $this->em->createQueryBuilder();
      $qb->select('v', 'c')
        ->from('Application\Model\Venda', 'v')
        ->leftJoin('v.carga', 'c')
        ->where('v.situacao = :situacao')
        ->setParameter('situacao', $situ)
        ->orderBy('v.ja_aberto', 'ASC')
        ->addOrderBy($direcao, 'ASC')
        ->setFirstResult($offSet)
        ->setMaxResults($limite);

      $filtro = [
        'next' => $offSet + $limite,
        'preview' => $offSet - $limite,
        'situacao' => $situ,
        'limite' => $limite,
      ];

      // -------------------- FILTROS (POST) --------------------
      if ($request->isPost()) {

        $cliente   = mb_strtoupper($request->getPost("nome"), $encoding);
        $cpfCnpj   = $request->getPost("cpfcnpj");
        $cidade    = $request->getPost("cidade");
        $dataEnt   = $request->getPost("data_entrega");
        $vendedor  = mb_strtoupper($request->getPost("vendedor"), $encoding);
        $modelo    = $request->getPost("modelo");

        $entregar     = isset($_POST['envio']) && in_array('Entregar', $_POST['envio']);
        $retirar      = isset($_POST['envio']) && in_array('Retirar', $_POST['envio']);
        $urgente      = isset($_POST['urgente']) && in_array('Sim', $_POST['urgente']);
        $naoUrgente   = isset($_POST['urgente']) && in_array('Não', $_POST['urgente']);

        $filtro += [
          'nome' => $cliente,
          'cpfcnpj' => $cpfCnpj,
          'cidade' => $cidade,
          'data_entrega' => $dataEnt,
          'vendedor' => $vendedor,
          'modelo' => $modelo,
          'Entregar' => $entregar,
          'Retirar' => $retirar,
          'urgente' => $urgente,
          'naoUrgente' => $naoUrgente,
        ];

        if ($cliente) {
          $qb->andWhere('UPPER(v.nome) LIKE :cliente')->setParameter('cliente', "%$cliente%");
        }
        if ($cpfCnpj) {
          $qb->andWhere('v.cpfcnpj = :cpfCnpj')->setParameter('cpfCnpj', $cpfCnpj);
        }
        if ($cidade) {
          $qb->andWhere('v.cidade = :cidade')->setParameter('cidade', $cidade);
        }
        if ($vendedor) {
          $qb->andWhere('UPPER(v.nome_vendedor) LIKE :vendedor')->setParameter('vendedor', "%$vendedor%");
        }
        if ($modelo) {
          $qb->join('v.produtos', 'p')
            ->andWhere('p.modelo = :modelo')
            ->setParameter('modelo', $modelo);
        }

        // Urgência
        if ($urgente xor $naoUrgente) {
          $qb->andWhere('v.urgente = :urgente')
            ->setParameter('urgente', $urgente ? 'Sim' : 'Não');
        }

        // Tipo de envio
        if ($entregar xor $retirar) {
          $qb->andWhere('v.envio = :envio')
            ->setParameter('envio', $entregar ? true : false);
        }

        // Data de entrega
        if ($dataEnt) {
          $dataObj = \DateTime::createFromFormat('d/m/Y', $dataEnt);
          if ($dataObj) {
            $qb->andWhere('v.data_para_entrega = :dataEntrega')
              ->setParameter('dataEntrega', $dataObj->format('Y-m-d'));
          }
        }
      }

      // -------------------- EXECUÇÃO --------------------
      $vendas = $qb->getQuery()->getArrayResult();

      // -------------------- PRODUTOS RELACIONADOS --------------------
      $produtos = [];
      if (!empty($vendas)) {
        $vendasId = array_column($vendas, 'id');
        $qbProd = $this->em->createQueryBuilder();
        $qbProd->select('p')
          ->from('Application\Model\Produto', 'p')
          ->where('p.venda IN (:vendas)')
          ->setParameter('vendas', $vendasId);

        // Retorna objetos Produto
        $produtos = $qbProd->getQuery()->getResult();
      }

      // -------------------- OUTRAS CONSULTAS (pode cachear) --------------------
      $cidades = $this->em->getRepository("Application\Model\Cidade")
        ->findBy([], ['nome' => 'ASC']);

      $queryCargasCombo = $this->em->createQuery(
        "SELECT c FROM Application\Model\Carga c 
         WHERE (c.situacao IN ('Carregamento','Entrega'))
         AND c.id IN (
             SELECT DISTINCT IDENTITY(v.carga)
             FROM Application\Model\Venda v
             WHERE v.carga IS NOT NULL
             AND v.situacao <> 'Excluidos'
         )"
      );
      $cargas_combo = $queryCargasCombo->getArrayResult();

      $cargas = $this->em->createQuery('SELECT c FROM Application\Model\Carga c')
        ->getArrayResult();

      $data_atual = date("Y/m/d");

      return new ViewModel([
        'vendas' => $vendas,
        'filtro' => $filtro,
        'produtos' => $produtos,
        'cargas' => $cargas,
        'cargas_combo' => $cargas_combo,
        'cidades' => $cidades,
        'data_atual' => $data_atual,
      ]);
    } catch (\Exception $e) {
      \Laminas\Debug\Debug::dump($e->getMessage());
      \Laminas\Debug\Debug::dump($e->getTraceAsString());
      exit;
    }
  }

  public function carregamentoAction()
  {
    session_start();

    $db = $this->em->createQuery('select c from Application\Model\Carga c order By c.id DESC');
    $db->setMaxResults(10);
    $cargas = $db->getArrayResult();

    //\Laminas\Debug\Debug::dump($cargas);
    return new ViewModel(array('carregamentos' => $cargas));
  }

  public function cadastrarcargaAction()
  {
    session_start();
    if (!isset($_SESSION['usuarioNome'])) {
      return $this->redirect()->toRoute('login');
    }

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

      $this->em->persist($carga);
      $this->em->flush();

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

    $lista = $this->em->getRepository("Application\Model\Carga")->findAll();
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

    $id = $this->params()->fromRoute("id", 0);

    $db = $this->em->createQuery('select c, v, p from Application\Model\Carga c LEFT JOIN c.vendas v LEFT JOIN v.produtos p where c.id = ' . $id);
    $cargas = $db->getArrayResult();


    $view = new ViewModel(array('lista' => $cargas));
    $view->setTerminal(true);
    return $view;
  }

  public function gerarPdfComprovante($produtos)
  {

    if (isset($produtos[0]->venda)) {

      $imgLogo1 = $this->baseUrl . '/img/logo-1.jpg';
      $imgLogo2 = $this->baseUrl . '/img/Corabras_Selo-1.jpg';
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
                    <td width='170px' style='text-align:left;vertical-align:middle'><img src=\"$imgLogo1\" alt=\"Image\" width=\"150\" height=\"60\"></td>
                    <td style='text-align:center;vertical-align:middle'><span style=\"font-weight:bold; font-size:19px\">CORABRAS TELHAS DE CONCRETO</span><br><br><span style=\"font-weight:bold\">CNPJ </span> 82.888.702/0001-18<br>Lote 02 - Gleba 02 - ICAGE Alexandre Gusmão - Brazlândia-DF<br><span style=\"color:#3166FF; font-size:18px\">www.corabras.com.br</span></td>
                    <td width='150px' style='text-align:left;vertical-align:middle'><img src=\"$imgLogo2\" width=\"150\" height=\"112\"></td>
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
          </tr>";
      // $html .=     "<tr>
      //       <th>Tipo Nota Fiscal:</th>
      //       <td colspan=\"2\">" . $produtos[0]->venda->tipo_nf . "</td>

      //       <th>Nota Fiscal:</th>
      //       <td>";
      // $html .= $produtos[0]->venda->nota_fiscal ? "Sim" : "Não";
      // $html .= "</td>
      //     </tr>";
      $html .= "<tr>
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


    $db = $this->em->createQuery('select v, p, c from Application\Model\Venda v LEFT JOIN v.produtos p LEFT JOIN v.carga c where v.id = ' . $idVenda);
    $pedido = $db->getArrayResult()[0];
    //\Laminas\Debug\Debug::dump($pedido);
    $renderer = $this->getEvent()->getApplication()->getServiceManager()->get('Laminas\View\Renderer\RendererInterface');

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


    $db = $this->em->createQuery('select v, p, c from Application\Model\Venda v LEFT JOIN v.produtos p LEFT JOIN v.carga c where v.id = ' . $idVenda);
    $pedido = $db->getArrayResult()[0];
    $renderer = $this->getEvent()->getApplication()->getServiceManager()->get('Laminas\View\Renderer\RendererInterface');


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

    $request = $this->getRequest();

    if ($request->isPost()) {
      $nome = $request->getPost("cliente_nome");
      $cliente = new \Application\Model\Cidade();

      $cliente->setNome(mb_strtoupper(trim($nome)), $encoding);

      $this->em->persist($cliente);
      $this->em->flush();

      $result["resp"] = "Salvo com sucesso!";
      $result["tipo_mens"] = 'success';
    }

    $lista = $this->em->getRepository("Application\Model\Cidade")->findBy(
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

    $cidade = $this->em->getRepository("Application\Model\Cidade")->find($id);
    $this->em->remove($cidade);
    $this->em->flush();

    $result["resp"] = "Salvo com sucesso!";
    $result["tipo_mens"] = 'success';

    return $this->redirect()->toRoute('cadastrarcidade');
  }

  public function gerarPdf($html, $filename)
  {

    // instantiate and use the dompdf class
    $dompdf = new \Dompdf\Dompdf([
      'isRemoteEnabled' => true,
      'isHtml5ParserEnabled' => true,
    ]);

    $dompdf->set_option('isRemoteEnabled', true);

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
