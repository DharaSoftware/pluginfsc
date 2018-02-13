<?php
/*
 * This file is part of plugin Acompuesto
 * nueva_receta.php
 * nueva_receta.php controller de la clase Receta
 * Copyright (C) 2013-2017  Carlos Garcia Gomez  neorazorx@gmail.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Description of Entrada al plugin para producir productos Compuestos por medio de Recetas
 * @author Raul Mercado  rgmercado@gmail.com
 */

require_model('receta.php');
require_model('receta_ingrediente');
require_model('receta_produccion');
require_model('articulo');
require_model('almacen');

class nueva_receta  extends fs_controller {
  public $almacenes;
  public $fabricante;
  public $familia;
  public $impuesto;
  public $receta;
  public $msg_error;
  public $multi_almacen;
  public $nreceta = FALSE; //Variable para control de resultado de crear una receta
  public $ref_ca;
  public $cod_art;

  public function __construct() {
        parent::__construct(__CLASS__, 'Nueva Receta', 'ventas', FALSE, FALSE, TRUE);
   }

  protected function private_core() {
      // configure delete action
      $this->allow_delete = $this->user->allow_delete_on(__CLASS__);
      /*Load data with estructure data*/
      parent::private_core();

    if (isset($_POST['referencia'])) {
			$this->entradaReceta();
		}elseif ($this->query != '') {
      $this->new_search();
		}elseif ((isset($_GET['n_receta'])) && ($_GET['n_receta']=='nc_insert')){
			$this->newReceta();
		}elseif (isset($_GET['prod']) && ($_GET['prod']=='producir')){
      $this->producir();
    }
  }
  /**
   * Abre la vista para elaborar la nueva receta o producto compuesto
   * Crea una instacia de la clase receta y otra con los almacenes
   */
	private function entradaReceta() {
		$almacen = new \almacen();
    $this->familia = new \familia();
    $this->fabricante = new \fabricante();
    $this->almacenes = $almacen->all();
    if(!is_object($this->receta)){
		  $data = array('idreceta'      => $_POST['referencia'],
                    'descripcion'   => $_POST['descripcion'],
                    'producto_res'  => $_POST['articulo_res'],
						'produccion'    =>  0.0,
						'idalmacening'  => '',
						'idalmacenres'  => '',
						'observaciones' => '',
						'idarticulo'   => '');
		  $this->receta = new \receta($data);
		  $this->msg_error='FALSE';
    }
    if ($this->receta->exists($_POST['referencia'])){
        $this->new_error_msg("Identificador de Receta ya existe....");
        $this->msg_error='TRUE';
    }
	}
/*
 * Nueva receta este metodo permite guarar la nueva receta con sus ingredientes
 */
  private function newReceta() {

      if ($_POST['nr_idreceta'] != '') {
        $this->cod_art = $this->crearArticulo($_POST['nr_idreceta'],$_POST['nr_articulo_res']); // Creamos el articulo resultante
      /* Guardamos los ingredientes del articulo compuesto Receta */
        for ($i=0; $i < $_POST['nlinea']; $i++) {
            $objri = new receta_ingrediente();
            $objri->id = '';
            $objri->idrecetar = $_POST['nr_idreceta'];
            $objri->idarticulor = $_POST["referencia_$i"];
            $objri->necesarios = $_POST["neces_$i"];
            $objri->idlinea = $i;
            if ($objri->save()) {
                $this->new_message("Componentes linea: $i guadados satisfactoriamente....");
                unset($objri);
            } else {
                $this->new_error_msg("Fallo el grabar los Componentes de la Receta linea: $i....");
            }
        }
        /*Guardamos los datos de la Receta*/
        $rect = new receta();
        $rect->idreceta = $_POST['nr_idreceta'];
        $rect->descripcion = $_POST['nr_descripcion'];
        $rect->idalmacening = $_POST['nr_almacening'];
        $rect->idalmacenres = $_POST['nr_almacenres'];
        $rect->producto_res = $_POST['nr_articulo_res'];
        $rect->observaciones = $_POST['nr_observaciones'];
        $rect->idarticulo =  $this->cod_art;
        $rect->produccion  =   0;
        //$rect->necesarios = $_POST['nr_cantidad'];
        if ($rect->save()) {
            $this->nreceta = TRUE;
            $this->new_message("Receta creada correctamente....");
        }else{
            $this->new_error_msg("La Receta no se pudo crear, verifique....");
            $this->new_message("Limpiando datos Receta....");
            $art = new articulo();
            $art->referencia = $rect->idarticulo;
            $art->delete();
            $ring = new receta_ingrediente();
            $ring->idrecetar = $rect->idreceta;
            $ring->delete();
        }

      }
  }
  /**
   *  Metodo para crear el articulo compuesto resultante de la receta en la tabla articulo
   *  @return  $art0->referencia codigo de referencia del articulo
   */
  private function crearArticulo($ref,$des_nr) {

    $this->ref_ca = $ref;
    $art0 = new articulo();
    $art0->referencia = $ref;
    if (!$art0->exists()) {
      $art0->referencia = $art0->get_new_referencia();
      $art0->descripcion = $des_nr;
      $art0->tipo = NULL;
      $art0->codfamilia = NULL;
      $art0->codfabricante = NULL;
      $art0->pvp = 0.0;
      $art0->factualizado = Date('d-m-Y');
      $art0->costemedio = 0.0;
      $art0->preciocoste = 0.0;
      $art0->codimpuesto = NULL;
      $art0->stockfis = 0.0;
      $art0->stockmin = 0.0;
      $art0->stockmax = 0.0;
      $art0->controlstock = (bool) FS_VENTAS_SIN_STOCK;
      $art0->nostock = FALSE;
      $art0->bloqueado = FALSE;
      $art0->secompra = TRUE;
      $art0->sevende = TRUE;
      $art0->publico = FALSE;
      $art0->equivalencia = NULL;
      $art0->partnumber = NULL;
      $art0->codbarras = '';
      $art0->observaciones = '';
      $art0->codsubcuentacom = NULL;
      $art0->codsubcuentairpfcom = NULL;
      $art0->trazabilidad = FALSE;
      $art0->imagen = NULL;
      $art0->exists = FALSE;
      if ($art0->save()) {
        $this->new_message("articulo creado correctamente....");
        $this->ref_ca = $art0->getReferencia();
      }else{
        $this->new_error_msg("No se pudo crear el articulo, verifique....");
      }
    }
    return $this->ref_ca;
  }
  /**
   * Metodo para generar produccion, la cantidad de producto a producir
   */
  private function producir (){
    /*..Recojo los valores de la forma proucir..*/
    $p_idrec = $_POST['fp_idreceta'];
    $p_idart = $_POST['fp_idarticulo'];
    $p_cant = $_POST['fp_cantidad'];
    if (($p_idrec != '') && ($p_idart != '')) {
      $receta = new receta();
      if ($receta->exists()) {
            $this->registraProduccion($p_idrec,$p_cant); // Registro produccion en tabla receta_produccion
            $campo = 'produccion';
            $receta->actualizacampo($campo,$p_cant); // Actualiza la ultima cantidad producida en la table Receta
            $this->actualizarStockArt($p_idrec); //Actualiza el inventario en el stock para cada articulo ingrediente

      }else {
        $this->new_error_msg("La Receta no existe....");
      }
    }
  }
/**
 * Metodo para registrar la produccion en la tabla receta_produccion
 */
private function registraProduccion($rpidrec,$rpcant){
  $rec_pro = new receta_produccion();
  $rec_pro->idrecetar = $rpidrec;
  $rec_pro->producidos = $rpcant;
  $rec_pro->fecha = date('Y-m-d H:i:s');
  if ($rec_pro->save()) {
    $this->new_message("Produccion registrada correctamente....");
    return TRUE;
  }else{
    $this->new_error_msg("No se pudo registrar la produccion, verifique....");
    return FALSE;
  }
}

/**
 *Metodo para actualizar Stock del Artculo resultante de la Receta
 */
private function actualizarStockArt($idr){
    $array_ing = array();
    $ing_stk = new receta_ingrediente();
    $array_ing = $ing_stk->buscarIngredientes($idr, $offset = 0);

    for ($i=0; $i < count($array_ing); $i++) {
      $this->seekArticulo($array_ing[$i]->idarticulo,$array_ing[$i]->necesarios);
    }
}

/**
   * Metodo tomado de facturacion_base, para la busqueda de articulos
   * @return JSON array rest
   */
   private function new_search()
    {
        /// desactivamos la plantilla HTML
        $this->template = FALSE;

        $articulo = new articulo();
        $codfamilia = '';
        if (isset($_REQUEST['codfamilia'])) {
            $codfamilia = $_REQUEST['codfamilia'];
        }
        $codfabricante = '';
        if (isset($_REQUEST['codfabricante'])) {
            $codfabricante = $_REQUEST['codfabricante'];
        }
        $con_stock = isset($_REQUEST['con_stock']);
        $this->results = $articulo->search($this->query, 0, $codfamilia, $con_stock, $codfabricante);

        /// añadimos la busqueda, el descuento, la cantidad, etc...
        $stock = new stock();
        foreach ($this->results as $i => $value) {
            $this->results[$i]->query = $this->query;
            $this->results[$i]->dtopor = 0;
            $this->results[$i]->cantidad = 1;
            $this->results[$i]->coddivisa = $this->empresa->coddivisa;

            /// añadimos el stock del almacén y el general
            $this->results[$i]->stockalm = $this->results[$i]->stockfis;
            if ($this->multi_almacen && isset($_REQUEST['codalmacen'])) {
                $this->results[$i]->stockalm = $stock->total_from_articulo($this->results[$i]->referencia, $_REQUEST['codalmacen']);
            }
        }

        /// ejecutamos las funciones de las extensiones
        foreach ($this->extensions as $ext) {
            if ($ext->type == 'function' && $ext->params == 'new_search') {
                $name = $ext->text;
                $name($this->db, $this->results);
            }
        }

        /// buscamos el grupo de clientes y la tarifa
        if (isset($_REQUEST['codcliente'])) {
            $cliente = $this->cliente->get($_REQUEST['codcliente']);
            $tarifa0 = new tarifa();

            if ($cliente && $cliente->codtarifa) {
                $tarifa = $tarifa0->get($cliente->codtarifa);
                if ($tarifa) {
                    $tarifa->set_precios($this->results);
                }
            } else if ($cliente && $cliente->codgrupo) {
                $grupo0 = new grupo_clientes();

                $grupo = $grupo0->get($cliente->codgrupo);
                if ($grupo) {
                    $tarifa = $tarifa0->get($grupo->codtarifa);
                    if ($tarifa) {
                        $tarifa->set_precios($this->results);
                    }
                }
            }
        }

        /// convertimos la divisa
        if (isset($_REQUEST['coddivisa']) && $_REQUEST['coddivisa'] != $this->empresa->coddivisa) {
            foreach ($this->results as $i => $value) {
                $this->results[$i]->coddivisa = $_REQUEST['coddivisa'];
                $this->results[$i]->pvp = $this->divisa_convert($value->pvp, $this->empresa->coddivisa, $_REQUEST['coddivisa']);
            }
        }

        header('Content-Type: application/json');
        echo json_encode($this->results);
    }


}
