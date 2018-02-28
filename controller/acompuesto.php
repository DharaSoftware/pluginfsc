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
 *  acompuesto.php
 *  Description of Entrada al plugin para producir productos Compuestos por medio de Recetas
 *  @author Raul Mercado rgmercado@gmail.com
 */

require_model('receta.php');
require_model('receta_ingrediente');
require_model('receta_produccion');

class acompuesto extends fs_controller {

   public function __construct() {

      parent::__construct(__CLASS__, 'Productos Compuestos', 'ventas');
   }

   public $resultados = array();
   public $ref;

   protected function private_core() {
      // configure delete action
      $this->allow_delete = $this->user->allow_delete_on(__CLASS__);
      /*Load data with estructure data*/
      parent::private_core();

      if ((isset($_GET['eliminar'])) && ($_GET['eliminar'] = 'TRUE')) {
          $idrec = $_POST['refe'];
          $this->eliminarReceta($idrec);
  		}else{
          $this->listadoReceta();
  		}
   }
   public function listadoReceta (){
      $this->resultados = $this->db->select("SELECT * FROM receta;");
      
  }
  protected function eliminarReceta($ref){
    if ($ref != '') {
      $ingred = new receta_ingrediente();
      $ingred->idrecetar = $ref;
      if ($ingred->delete()) {
        $this->new_message("Ingredientes exitosamente eliminados");
        $prod = new receta_produccion();
        $prod->idrecetar = $ref;
        $prod->delete();
        $receta = new receta();
        $receta->idreceta = $ref;
        if ($receta->delete()) {
            $this->new_message("Receta e ingredientes eliminados con exito...");
            $this->listadoReceta();

        }
      }
    }
  }
 }
