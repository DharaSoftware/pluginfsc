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
if (file_exists("facturascripts/plugins/factura_base"))
{
  require_model('facturascripts/plugins/factura_base/model/articulo.php');
  require_model('facturascripts/plugins/factura_base/model/almacen.php');
}else {
  echo "requiere que el plugin fatura Base este instalado";
}

class nueva_receta  extends fs_controller {

    public $n_idreceta;
    public $n_descripcion;
    public $n_articulo_res;

   public function __construct() {

        parent::__construct(__CLASS__, 'Nueva Receta', 'ventas', FALSE, FALSE, TRUE);
   }

   protected function private_core() {
      // configure delete action
      $this->allow_delete = $this->user->allow_delete_on(__CLASS__);
      /*Load data with estructure data*/
      parent::private_core();

      $receta = new receta();
      $alamcen = new almacen();


      if (isset($_POST['referencia'])) {
        if ($receta->exists($_POST['referencia']))
        {
          $this->new_error_msg("Identificado de Receta ya existe");
        }else {
          $this->receta->idreceta = $_POST['referencia'];
          $this->receta->descripcion = $_POST['descripcion'];
          $this->receta->producto_res = $_POST['articulo_res'];
        }
      }



   }

}
