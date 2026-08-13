<?php

/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Dompdf\Dompdf;
use Doctrine\ORM\EntityManager;


class IndexController extends AbstractActionController
{
  private ?EntityManager $em = null;

  public function __construct(EntityManager $em)
  {
    $this->em = $em;
  }

  public function indexAction()
  {
    session_start();

    $encoding = mb_internal_encoding();

    $em = $this->em;

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

        $venda->setSituacao("Recebido");

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

        // $situ = new \Application\Model\Situacao();

        // $situ->setData(date("d/m/Y"));
        // $situ->setSituacao("Recebido");
        // $situ->setVenda($venda);

        // $em->persist($situ);
        // $em->flush();

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
    if (session_status() === PHP_SESSION_NONE) {
      session_start();
    }
    if (!isset($_SESSION['usuarioNome'])) {
      $response = $this->getResponse();
      $response->setStatusCode(401);
      $response->setContent('Não autorizado');
      return $response;
    }

    $id = (int)$this->params()->fromRoute("id", 0);

    if ($id > 0) {
      try {
        $venda = $this->em->getRepository("Application\Model\Venda")->find($id);
        if ($venda) {
          $venda->setAberto(true);
          $venda->ja_aberto = true;
          $this->em->persist($venda);
          $this->em->flush();
        }
        $response = $this->getResponse();
        $response->setContent('ok');
        return $response;
      } catch (\Exception $e) {
        $response = $this->getResponse();
        $response->setStatusCode(500);
        $response->setContent('Erro: ' . $e->getMessage());
        return $response;
      }
    }

    $response = $this->getResponse();
    $response->setStatusCode(400);
    $response->setContent('ID inválido');
    return $response;
  }

  public function tramitarAction()
  {
    if (session_status() === PHP_SESSION_NONE) {
      session_start();
    }
    if (!isset($_SESSION['usuarioNome'])) {
      return $this->redirect()->toRoute('login');
    }

    $request = $this->getRequest();
    $situacao = "Recebido";

    if ($request->isPost()) {
      $situacao = $request->getPost("situacao") ?: "Recebido";
      $idcarga = $request->getPost("carga");

      $array_tramitar = isset($_POST['item_tramitar']) ? (array)$_POST['item_tramitar'] : [];
      $array_tramitar_carga = isset($_POST['carga_tramitar']) ? (array)$_POST['carga_tramitar'] : [];

      $em = $this->em;

      // Se tramitou cargas inteiras
      if (count($array_tramitar_carga) > 0) {
        foreach ($array_tramitar_carga as $item_tramitar_carga) {
          $cargaObj = $em->getRepository("Application\Model\Carga")->find($item_tramitar_carga);
          if ($cargaObj) {
            $vendasCarga = $em->getRepository("Application\Model\Venda")->findBy(['carga' => $cargaObj]);
            if ($situacao == 'Recebido') {
              foreach ($vendasCarga as $vendaC) {
                $vendaC->setSituacao('Recebido');
                $vendaC->setCarga(null);
                $em->persist($vendaC);
              }
              $em->remove($cargaObj);
            } else {
              $cargaObj->setSituacao($situacao);
              $em->persist($cargaObj);
              foreach ($vendasCarga as $vendaC) {
                $vendaC->setSituacao($situacao);
                $em->persist($vendaC);
              }
            }
            $em->flush();
          }
        }
      }

      // Se selecionou uma carga específica para vincular
      $carga = null;
      if (($situacao == 'Carregamento' || $situacao == 'Entrega') && !empty($idcarga)) {
        $carga = $em->getRepository("Application\Model\Carga")->find($idcarga);
      }

      // Tramita os itens/vendas selecionados individualmente
      foreach ($array_tramitar as $item_tramitar) {
        $venda = $em->getRepository("Application\Model\Venda")->find($item_tramitar);
        if (!$venda) {
          continue;
        }

        $cargaAnterior = $venda->getCarga();
        $idCargaAnterior = $cargaAnterior ? $cargaAnterior->getId() : null;

        $venda->setSituacao($situacao);

        if (($situacao == 'Carregamento' || $situacao == 'Entrega') && $carga != null) {
          $venda->setCarga($carga);
        } else if ($situacao == 'Recebido') {
          $venda->setCarga(null);
        }

        $em->persist($venda);
        $em->flush();

        // Se a venda tinha uma carga anterior ou atual, verifica o status dessa carga
        $idCargaChecar = $idCargaAnterior ?: ($venda->getCarga() ? $venda->getCarga()->getId() : null);

        if ($idCargaChecar) {
          $vendasRestantes = $em->createQuery(
            'SELECT v.id FROM Application\Model\Venda v WHERE IDENTITY(v.carga) = :idCarga'
          )->setParameter('idCarga', $idCargaChecar)->getArrayResult();

          if (count($vendasRestantes) == 0) {
            $c = $em->getRepository("Application\Model\Carga")->find($idCargaChecar);
            if ($c) {
              $em->remove($c);
              $em->flush();
            }
          } else {
            $vendasAtivas = $em->createQuery(
              "SELECT v.id FROM Application\Model\Venda v WHERE IDENTITY(v.carga) = :idCarga AND v.situacao IN ('Carregamento', 'Entrega')"
            )->setParameter('idCarga', $idCargaChecar)->getArrayResult();

            if (count($vendasAtivas) == 0) {
              $c = $em->getRepository("Application\Model\Carga")->find($idCargaChecar);
              if ($c) {
                $c->setSituacao($situacao);
                $em->persist($c);
                $em->flush();
              }
            }
          }
        }
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

      $em = $this->em;

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
      $venda->setSituacao("Recebido");

      $venda->setEnvio($vendaOld->getEnvio());
      $venda->setLocalEntrega($vendaOld->getLocalEntrega());
      $venda->setEnderecoEntrega($vendaOld->getEnderecoEntrega());
      $venda->setObs($vendaOld->getObs());

      $em->persist($venda);
      $em->flush();


      // $situ = new \Application\Model\Situacao();

      // $situ->setData(date("d/m/Y"));
      // $situ->setSituacao();
      // $situ->setVenda($venda);

      // $em->persist($situ);
      // $em->flush();

      foreach ($produtos as $idProduto => $qtd) {
        if (!is_null($qtd) && $qtd !== '') {
          $prodOld = $em->getRepository("Application\Model\Produto")->find($idProduto);


          $produto = new \Application\Model\Produto();

          $produto->setModelo($prodOld->getModelo());
          $produto->setCor($prodOld->getCor());
          $produto->setQuantidade($qtd);
          $produto->setValor($prodOld->getValor());
          $produto->setVenda($venda);

          $em->persist($produto);
          $prodOld->setquantidade($prodOld->getQuantidade() - $qtd);

          $em->persist($prodOld);
          $em->flush();
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
    if (session_status() === PHP_SESSION_NONE) {
      session_start();
    }
    if (!isset($_SESSION['usuarioNome'])) {
      return $this->redirect()->toRoute('login');
    }
    try {
      $encoding = mb_internal_encoding();
      $em = $this->em;
      $request = $this->getRequest();

      $offSet = (int)$this->params()->fromRoute("offset", 0);
      $situ = $this->params()->fromRoute("situacao", "Recebido");

      // If situation is 'Recebido' show all records (no limit)
      $limitePadrao = ($situ === 'Recebido') ? 0 : 100;
      $limite = $request->isPost() ? (int) $request->getPost("limite", $limitePadrao) : $limitePadrao;
      $filtro = [];

      // Define a ordenação conforme a situação
      $direcao = in_array($situ, ["Recebido", "Entrega"]) ? "v.data_para_entrega" : "v.data_cadastro";

      $qb = $em->createQueryBuilder();
      $qb->select('v', 'c')
        ->from('Application\Model\Venda', 'v')
        ->leftJoin('v.carga', 'c')
        ->where('v.situacao = :situacao')
        ->setParameter('situacao', $situ)
        ->orderBy('v.ja_aberto', 'ASC')
        ->addOrderBy($direcao, 'ASC');

      // -------------------- FILTROS (POST) --------------------
      if ($request->isPost()) {

        $cliente   = mb_strtoupper($request->getPost("nome"), $encoding);
        $cpfCnpj   = $request->getPost("cpfcnpj");
        $cidade    = (array) $request->getPost("cidade", []);
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
        if (!empty($cidade)) {
          $qb->andWhere('v.cidade IN (:cidades)')->setParameter('cidades', $cidade);
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
      // Compute total number of matching records (without pagination)
      $qbCount = clone $qb;
      $qbCount->select('COUNT(DISTINCT v.id)');
      $qbCount->resetDQLPart('orderBy');
      $qbCount->setFirstResult(null);
      $qbCount->setMaxResults(null);
      $total = (int) $qbCount->getQuery()->getSingleScalarResult();

      // Apply pagination only when $limite > 0. A $limite of 0 means "no limit" (return all).
      if ($limite > 0) {
        $qb->setFirstResult($offSet)
          ->setMaxResults($limite);
        $next = $offSet + $limite;
        $preview = max(0, $offSet - $limite);
      } else {
        $next = 0;
        $preview = 0;
      }

      $filtro += [
        'next' => $next,
        'preview' => $preview,
        'situacao' => $situ,
        'limite' => $limite,
        'offset' => $offSet,
        'total'  => $total,
      ];

      $vendas = $qb->getQuery()->getArrayResult();

      // -------------------- PRODUTOS RELACIONADOS --------------------
      $produtos = [];
      if (!empty($vendas)) {
        $vendasId = array_column($vendas, 'id');
        $qbProd = $em->createQueryBuilder();
        $qbProd->select('p')
          ->from('Application\Model\Produto', 'p')
          ->where('p.venda IN (:vendas)')
          ->setParameter('vendas', $vendasId);

        // Retorna objetos Produto
        $produtos = $qbProd->getQuery()->getResult();
      }

      // -------------------- OUTRAS CONSULTAS (pode cachear) --------------------
      $cidades = $em->getRepository("Application\Model\Cidade")
        ->findBy([], ['nome' => 'ASC']);

      $hoje = date("Y-m-d");
      $queryCargasCombo = $em->createQuery(
        "SELECT c FROM Application\Model\Carga c 
         WHERE c.situacao IN ('Carregamento','Entrega')
         AND c.data >= :hoje
         ORDER BY c.id DESC"
      )->setParameter('hoje', $hoje);
      $cargas_combo = $queryCargasCombo->getArrayResult();

      $cargas = $em->createQuery('SELECT c FROM Application\Model\Carga c ORDER BY c.id DESC')
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
      error_log($e->getMessage());
      error_log($e->getTraceAsString());
      throw $e;
    }
  }

  public function carregamentoAction()
  {
    if (session_status() === PHP_SESSION_NONE) {
      session_start();
    }
    if (!isset($_SESSION['usuarioNome'])) {
      return $this->redirect()->toRoute('login');
    }

    $em = $this->em;
    $db = $em->createQuery('select c from Application\Model\Carga c order By c.id DESC');
    $cargas = $db->getArrayResult();

    return new ViewModel(array('carregamentos' => $cargas));
  }

  public function cadastrarcargaAction()
  {
    if (session_status() === PHP_SESSION_NONE) {
      session_start();
    }
    if (!isset($_SESSION['usuarioNome'])) {
      $response = $this->getResponse();
      $response->setStatusCode(401);
      $response->setContent('Não autorizado');
      return $response;
    }

    $em = $this->em;
    $request = $this->getRequest();

    if ($request->isPost()) {
      try {
        $motorista = trim((string)$request->getPost("motorista"));
        $data = trim((string)$request->getPost("saida"));
        $saida = trim((string)$request->getPost("saidah"));
        $retorno = trim((string)$request->getPost("retornoh"));
        $situacaoPost = $request->getPost("situacao");
        $situacao = ($situacaoPost === true || $situacaoPost === 'true' || $situacaoPost === 'Entrega') ? "Entrega" : "Carregamento";

        if (empty($motorista) || empty($data) || empty($saida)) {
          $response = $this->getResponse();
          $response->setStatusCode(400);
          $response->setContent('Campos obrigatórios ausentes');
          return $response;
        }

        $carga = new \Application\Model\Carga();

        $carga->setMotorista($motorista);
        $carga->setData($data);
        $carga->setSaida($saida);
        if (!empty($retorno)) {
          $carga->setRetorno($retorno);
        }
        $carga->setSituacao($situacao);

        $em->persist($carga);
        $em->flush();

        $response = $this->getResponse();
        $response->setContent((string)$carga->getId());
        return $response;
      } catch (\Exception $e) {
        $response = $this->getResponse();
        $response->setStatusCode(500);
        $response->setContent('Erro ao salvar carga: ' . $e->getMessage());
        return $response;
      }
    }

    $response = $this->getResponse();
    $response->setStatusCode(400);
    $response->setContent('Requisição inválida');
    return $response;
  }

  public function carregarcargasAction()
  {
    if (session_status() === PHP_SESSION_NONE) {
      session_start();
    }
    if (!isset($_SESSION['usuarioNome'])) {
      return $this->redirect()->toRoute('login');
    }
    $em = $this->em;
    $lista = $em->getRepository("Application\Model\Carga")->findBy([], ['id' => 'DESC']);
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
    $em = $this->em;

    $id = $this->params()->fromRoute("id", 0);

    $db = $em->createQuery('select c, v, p from Application\Model\Carga c LEFT JOIN c.vendas v LEFT JOIN v.produtos p where c.id = ' . $id);
    $cargas = $db->getArrayResult();


    $view = new ViewModel(array('lista' => $cargas));
    $view->setTerminal(true);
    return $view;
  }

  public function gerarPdfComprovante($produtos)
  {
    $renderer = $this->getEvent()->getApplication()->getServiceManager()->get('Laminas\View\Renderer\RendererInterface');

    if (isset($produtos[0]->venda)) {
      $imgLogo1 = $this->getImageBase64('/img/logo-1.png');
      $imgLogo2 = $this->getImageBase64('/img/Corabras_Selo-1.png');

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
                    <td width='170px' style='text-align:left;vertical-align:middle'><img src=\"" . $imgLogo1 . "\" alt=\"Logo\" width=\"150\" height=\"60\"></td>
                    <td style='text-align:center;vertical-align:middle'><span style=\"font-weight:bold; font-size:19px\">CORABRAS TELHAS DE CONCRETO</span><br><br><span style=\"font-weight:bold\">CNPJ </span> 82.888.702/0001-18<br>Lote 02 - Gleba 02 - ICAGE Alexandre Gusmão - Brazlândia-DF<br><span style=\"color:#3166FF; font-size:18px\">www.corabras.com.br</span></td>
                    <td width='150px' style='text-align:left;vertical-align:middle'><img src=\"" . $imgLogo2 . "\" alt=\"Selo\" width=\"150\" height=\"112\"></td>
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
    if (session_status() === PHP_SESSION_NONE) {
      session_start();
    }
    if (!isset($_SESSION['usuarioNome'])) {
      return $this->redirect()->toRoute('login');
    }
    mb_internal_encoding("UTF-8");

    $idVenda = $this->params()->fromRoute("id", 0);
    $filename = "Declaracao_entrega_" . $idVenda;

    $em = $this->em;

    $db = $em->createQuery('select v, p, c from Application\Model\Venda v LEFT JOIN v.produtos p LEFT JOIN v.carga c where v.id = ' . $idVenda);
    $pedido = $db->getArrayResult()[0];

    $view = new \Laminas\View\Model\ViewModel([
      'pedido' => $pedido,
      'dataAtual' => date("d/m/Y"),
      'logo' => $this->getImageBase64('/img/corabras.png'),
    ]);
    $view->setTemplate('application/index/imprimir');

    /** @var \Laminas\View\Renderer\PhpRenderer $renderer */
    $renderer = $this->getEvent()->getApplication()->getServiceManager()->get('ViewRenderer');
    $html = $renderer->render($view);

    return $this->gerarPdf($html, $filename);
  }

  public function reciboAction()
  {
    if (session_status() === PHP_SESSION_NONE) {
      session_start();
    }
    if (!isset($_SESSION['usuarioNome'])) {
      return $this->redirect()->toRoute('login');
    }
    $idVenda = $this->params()->fromRoute("id", 0);
    $filename = "Recibo_entrega_" . $idVenda;

    $em = $this->em;

    $db = $em->createQuery('select v, p, c from Application\Model\Venda v LEFT JOIN v.produtos p LEFT JOIN v.carga c where v.id = ' . $idVenda);
    $pedido = $db->getArrayResult()[0];

    $qtd_total = 0;
    $valor_total = 0;
    foreach ($pedido['produtos'] as $prod) {
      $qtd_total += $prod['quantidade'];
      $valor_total += floatval(str_replace(',', '.', $prod['valor'])) * $prod['quantidade'];
    }

    $valor_total_g = number_format($valor_total, 2, ',', '.');
    $valor_extenso = $this->valorExtenso($valor_total);

    /** @var \Laminas\View\Renderer\PhpRenderer $renderer */
    $renderer = $this->getEvent()->getApplication()->getServiceManager()->get('ViewRenderer');

    $view = new \Laminas\View\Model\ViewModel([
      'pedido'         => $pedido,
      'qtd_total'      => $qtd_total,
      'valor_total_g'  => $valor_total_g,
      'valor_extenso'  => $valor_extenso,
      'logo'           => $this->getImageBase64('/img/corabras.png'),
    ]);
    $view->setTemplate('application/index/recibo');

    $html = $renderer->render($view);

    return $this->gerarPdf($html, $filename);
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
    $em = $this->em;

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

    $em = $this->em;
    $cidade = $em->getRepository("Application\Model\Cidade")->find($id);
    $em->remove($cidade);
    $em->flush();

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

    $dompdf->set_option('isPdfObjectStream', false);
    $dompdf->set_option('isPdfCompressionEnabled', false);
    $dompdf->set_option("pdf_version", "1.4");
    $dompdf->set_option('isRemoteEnabled', true);

    if (strpos($html, 'charset') === false) {
      $html = '<!DOCTYPE html><html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"/></head><body>' . $html . '</body></html>';
    }

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

  private function getImageBase64(string $imagePath): string
  {
    $possiblePaths = [
      $imagePath,
      __DIR__ . '/../../../../../public' . $imagePath,
      __DIR__ . '/../../../../public' . $imagePath,
      __DIR__ . '/../../../public' . $imagePath,
      getcwd() . '/public' . $imagePath,
      '/var/www/html/public' . $imagePath,
    ];

    foreach ($possiblePaths as $path) {
      if (file_exists($path) && is_file($path) && filesize($path) > 0) {
        $type = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $mime = $type === 'svg' ? 'svg+xml' : ($type === 'jpg' ? 'jpeg' : $type);
        $data = file_get_contents($path);
        return 'data:image/' . $mime . ';base64,' . base64_encode($data);
      }
    }

    return '';
  }
}
