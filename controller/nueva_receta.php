<?php
/*
 * This file is part of plugin Acompuesto
 * This file is part of FacturaScripts
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
 *
 * @author Raul Mercado

 * Description of ______________
 *
 * @author ________

 */

require_model('receta.php');
if (file_exists("/var/www/facturascripts/plugins/facturacion_base")){
    require_model('/var/www/facturascripts/plugins/facturacion_base/model/articulo.php');
    require_model('/var/www/facturascripts/model/core/almacen.php');
}

class nueva_receta  extends fs_controller {
  public $almacenes;
  public $fabricante;
  public $familia;
  public $impuesto;
  public $receta = NULL;

  public function __construct() {
        parent::__construct(__CLASS__, 'Nueva Receta', 'ventas', FALSE, FALSE, TRUE);
   }

   protected function private_core() {
          // configure delete action
          $this->allow_delete = $this->user->allow_delete_on(__CLASS__);
          /*Load data with estructure data*/
          parent::private_core();

    	if(!is_object($receta))
    	{
    		$receta = new \receta();
    	}

    /* Bloque de entrada desde la page acompuesto*/
        if (isset($_POST['referencia'])) {
    		if ($receta->exists($_POST['referencia']))
            {
                $this->new_error_msg("Identificador de Receta ya existe....");
            }else {
    			  		  /*Asignación de los valores de la receta*/

        			  $this->familia = new \familia();
        			  $this->fabricante = new \fabricante();
        			  $this->impuesto = new \impuesto();
        			  $almacen = new \almacen();
        			  $this->almacenes = $almacen->all();
        			  			  /*Asignación de los valores de la receta*/
        			  $this->receta->idreceta = $_POST['referencia'];
        			  $this->receta->descripcion = $_POST['descripcion'];
        			  $this->receta->producto_res = $_POST['articulo_res'];
        			  $this->receta->produccion = 0.0;
        		  }
            }
            if ($this->query != '') {
                $this->new_search();
            }

    }

/*
 * Metodos para el manejo de Articulos de facturacion_base *******************
 */
    private function new_articulo()
    {
        /// desactivamos la plantilla HTML
        $this->template = FALSE;

        $art0 = new articulo();
        if ($_REQUEST['referencia'] != '') {
            $art0->referencia = $_REQUEST['referencia'];
        } else {
            $art0->referencia = $art0->get_new_referencia();
        }

        if ($art0->exists()) {
            $this->results[] = $art0->get($art0->referencia);
        } else {
            $art0->descripcion = $_REQUEST['descripcion'];
            $art0->codbarras = $_REQUEST['codbarras'];
            $art0->set_impuesto($_REQUEST['codimpuesto']);
            $art0->set_pvp(floatval($_REQUEST['pvp']));

            $art0->secompra = isset($_POST['secompra']);
            $art0->sevende = isset($_POST['sevende']);
            $art0->nostock = isset($_POST['nostock']);
            $art0->publico = isset($_POST['publico']);

            if ($_REQUEST['codfamilia'] != '') {
                $art0->codfamilia = $_REQUEST['codfamilia'];
            }

            if ($_REQUEST['codfabricante'] != '') {
                $art0->codfabricante = $_REQUEST['codfabricante'];
            }

            if ($art0->save()) {
                $this->results[] = $art0;
            }
        }

        header('Content-Type: application/json');
        echo json_encode($this->results);
    }

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
