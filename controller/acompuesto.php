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

class acompuesto extends fs_controller {

   public function __construct() {

      parent::__construct(__CLASS__, 'Productos Compuestos', 'ventas');
   }

   public $resultados = array();

   protected function private_core() {
      // configure delete action
      $this->allow_delete = $this->user->allow_delete_on(__CLASS__);
      /*Load data with estructure data*/
      parent::private_core();

      $this->listadoReceta();
   }
   protected function listadoReceta (){
      $this->resultados = $this->db->select("SELECT * FROM receta;");
   }
 }
