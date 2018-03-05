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
 * Plugin facturascripts para producir productos Compuestos por medio de Recetas
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
  public $m_receta;
  public $resultado = array();
  public $resul_ingre = array();


  public function __construct() {
        parent::__construct(__CLASS__, 'Nueva Receta', 'ventas', FALSE, FALSE, TRUE);
   }

  /**
   * private_core
   * Metodo de entrada del controlador a la clase nueva_receta
   * @return void
   */
  protected function private_core() {
      // configure delete action
      $this->allow_delete = $this->user->allow_delete_on(__CLASS__);
      /*Load data with estructure data*/
      parent::private_core();

    if (isset($_POST['referencia'])) {
			$this->entradaReceta();
		}elseif ($this->query != '') {
            $this->new_search();
		}elseif ((isset($_GET['n_receta'])) && ($_GET['n_receta']=='nc_insert') && ($_GET['gm_receta']=='')){
			$this->newReceta();
		}elseif (isset($_GET['prod']) && ($_GET['prod']=='producir')){
            $this->producir();
        }elseif (isset($_GET['m_receta']) && ($_GET['m_receta']=='TRUE') && ($_GET['gm_receta']=='')) {
            $ref = $_GET['idr'];
            $this->modificaReceta($ref);
        }elseif (isset($_GET['gm_receta']) && ($_GET['gm_receta']=='TRUE')) {
            $this->guardarModificarReceta();
        }
}

	/**
	 * entradaReceta
	 * Abre la vista para elaborar la nueva receta o producto compuesto
     * Crea una instacia de la clase receta y otra con los almacenes
	 * @return void
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
                            'fechap'        => '',
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

  /**
   * newReceta
   * Este metodo permite guarar la nueva receta con sus ingredientes
   * @return void
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
        $rect->produccion  =   0.0;
        $rect->fechap   = "NULL";
        //$rect->necesarios = $_POST['nr_cantidad'];
        if ($rect->save()) {
            $this->nreceta = TRUE;
            $this->new_message("Receta creada correctamente....");
            header("Location: index.php?page=acompuesto");
        }else{
            $this->new_error_msg("La Receta no se pudo crear, verifique....");
            $this->new_message("Limpiando datos Receta....");
            /**Elimino la receta tabla recteta y los ingredientes que
             * se habian creado tabla receta_ingredientes */
            $art = new articulo();
            $art->referencia = $rect->idarticulo;
            $art->delete();
            $ring = new receta_ingrediente();
            $ring->idrecetar = $rect->idreceta;
            $ring->delete();
        }

      }
   }

   /* crearArticulo
   *
   * @param mixed $ref
   * @param mixed $des_nr
   * @return $art0->referencia codigo de referencia del articulo
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
   * producir
   * Metodo para generar produccion, la cantidad de producto a producir
   * @return void
   */
  private function producir(){
    /*..Recojo los valores de la forma producir..*/
    $p_idrec = $_POST['fp_idreceta'];
    $p_idart = $_POST['fp_idarticulo'];
    $p_cant = $_POST['fp_cantidad'];
    if (($p_idrec != '') && ($p_idart != '')) {
        $receta = new receta();
        $receta->idreceta = $p_idrec;
        if ($receta->exists()) {
            $campo = 'produccion';
            if ($receta->actualizaCampo($campo,$p_cant)){
                $fecha = Date("Y-m-d H:i:s");
                $campo0 = 'fechap';
              if (!$receta->actualizaCampo($campo0,$fecha)) {
                  $this->new_error_msg("no actualizo fecha");
              }
                 // Actualiza la ultima cantidad producida en la table Receta
                $this->registraProduccion($p_idrec,$p_cant); // Registro produccion en tabla receta_produccion
                $this->actualizarStockArt($p_idrec,$p_cant); //Actualiza el inventario en el stock para cada articulo ingrediente
            }else{
                $this->new_error_msg("No pudo actializar Producción en la Receta");
            }
        } else {
            $this->new_error_msg("La Receta o el Articulo Resultante no existe....");
        }
    }
    header("Location: index.php?page=acompuesto");
  }

    /**
     * registraProduccion
     * Metodo para registrar la produccion en la tabla receta_produccion
     * @param mixed $rpidrec
     * @param mixed $rpcant
     * @return void
     */
    private function registraProduccion($idr,$cant){
        $rec_pro = new receta_produccion();
        $rec_pro->idrecetar = $idr;
        $rec_pro->producidos = $cant;
        $rec_pro->fecha = date("Y-m-d H:i:s");
        if ($rec_pro->save()) {
            $this->new_message("Produccion registrada correctamente....");
            return TRUE;
        }else{
            $this->new_error_msg("No se pudo registrar la produccion, verifique....");
            return FALSE;
        }
    }

    /**
     * actualizarStockArt
     * Metodo para actualizar Stock del Articulo resultante de la Receta
     * @param mixed $idr
     * @param mixed $cant
     * @return void
     */
    private function actualizarStockArt($idr,$cant) {
        $ing = array();
        $receta_i = new receta_ingrediente();
        $receta_i->idrecetar = $idr;
        $ing = $receta_i->buscarIngredientes($idr);
        foreach ($ing as $ingre0) {
            $art = new \articulo;
            $art0 = $art->buscaArticulo($ingre0->idarticulor);
            $nece = (float) $ingre0->necesarios;
            $stock = (float) $art0->stockfis;
            $cantc = (float) $cant;
            if (($nece*$cantc) <= $stock){
                $resta = ($stock - $nece*$cantc);
                $resta0 = round($resta, 0, PHP_ROUND_HALF_DOWN);
                $art0->actualizaStock($resta0);
            } else {
                $this->new_error_msg("No hay stock Suficiente...");
            }
            unset($art);
        }
    }

    /**
     * modificaReceta funcion
     * Permite modificar la receta
     * @param  [type varchar] $ref [identificador de la receta]
     * @return [void]
     */
    private function modificaReceta($ref)
    {
    $almacen = new \almacen();
    $this->familia = new \familia();
    $this->fabricante = new \fabricante();
    $this->almacenes = $almacen->all();
    $this->m_receta = "TRUE"; //Variable de control de la vista
    $this->msg_error='FALSE';
    $nreceta = new receta();
    $nreceta->idreceta = $ref;
    $this->receta = $nreceta->buscarUnaReceta($nreceta->idreceta);
    $this->receta->produccion  =   0;
    $ingred = new receta_ingrediente();
    $this->i_resul = $ingred->buscarIngredientes($nreceta->idreceta);
    foreach ($this->i_resul as $ingre0) {
        $art = new \articulo;
        $art0 = $art->buscaArticulo($ingre0->idarticulor);
        $this->resultado[] = array('id'           => $ingre0->id,
                                    'idrecetar'   => $ingre0->idrecetar,
                                    'idarticulor' => $ingre0->idarticulor,
                                    'necesarios'  => $ingre0->necesarios,
                                    'idlinea'     => $ingre0->idlinea,
                                    'descripcion' => $art0->descripcion,
                                    'preciocoste' => $art0->preciocoste,
                                    'stockfis'    => $art0->stockfis,
                                    );
        unset($art);
        }
    }
  /**
   * [Permite guardar las modificaciones de una receta]
   * @method guardarModificarReceta
   * @return [void] [description]
   */

private function guardarModificarReceta(){
    $this->msg_error='FALSE';
    if ($_POST['nr_idreceta'] != '') {
      $this->cod_art = $this->crearArticulo($_POST['nr_idreceta'],$_POST['nr_articulo_res']); // Creamos el articulo resultante
      /*Elimino los ingredientes existentes*/
      $ring = new receta_ingrediente();
      $ring->idrecetar = $_POST['nr_idreceta'];
      $ring->delete();
      unset($ring);
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
      $rect->fechap = 'NULL';
      if ($rect->save()) {
          $this->nreceta = TRUE;
          $this->new_message("Receta creada correctamente....");
          header("Location: index.php?page=acompuesto");
      }else{
          $this->new_error_msg("La Receta no se pudo crear, verifique....");
          $this->new_message("Limpiando datos Receta....");
          /**Elimino la receta tabla recteta y los ingredientes que
           * se habian creado tabla receta_ingredientes */
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
   * new_search
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
