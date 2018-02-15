<?php

/*
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
 * Description of receta_produccion.php
 *
 * @author Raul Mercado, rgmercado@gmai.com
 */
class receta_produccion extends fs_model {

   public $idrecetar;
   public $producidos;
   public $fecha;
   private $exists;
   private static $column_list;

   public function __construct($data = FALSE) {
      parent::__construct('receta_produccion');
      $this->$column_list ='idrecetar,producido,fecha';
      $this->add_keyfield('id');

      if ($data) {
         $this->load_from_data($data);
      } else {
         $this->clear();
      }
   }

   /**
    * Verifica la exitencia de la receta
    * @return boolean
    */
   public function exists() {
     if (!$this->exists) {
         if ($this->db->select("SELECT id FROM " . $this->table_name . " WHERE id = " . $this->var2str($this->idreceta) . ";")) {
             $this->exists = TRUE;
         }
     }

     return $this->exists;
   }

   public function clear() {
      $this->id = '';
      $this->idrecetar = '';
      $this->producidos = '';
      $this->fecha = '';
   }

   public function load_from_data($data) {
      $this->id = $data['id'];
      $this->idrecetar = $data['idrecetar'];
      $this->producidos = $data['producidos'];
      $this->fecha = $data['fecha'];
   }

   public function install() {
      return '';
   }

   protected function test() {
      /*
        PUT HERE MODEL DATA VALIDATIONS
        EXAMPLE:
        if($this->field_Numeric == 0) {
        $this->new_error_msg('Must be inform a code value');
        return FALSE;
        }
        return TRUE;
       */
      return parent::test();
   }

   /**
    * Actualiza  en la base de datos los datos la Receta
    * @return boolean
    */
   protected function updateProduccion() {
     $sql = "UPDATE " . $this->table_name . " SET
            producido = " . $this->producidos .
         ", idalmacening = " . $this->fecha .
         "  WHERE id = " . $this->var2str($this->id) . ";";

      if ($this->db->exec($sql)) {
             $this->exists = TRUE;
             return TRUE;
      }
    return FALSE;
   }
   /**
    * Inserta en la base de datos los datos la Receta
    * @return boolean
    */
   protected function insertProduccion(){

    $sql = "INSERT INTO " . $this->table_name . " (" . $this->$column_list . ") VALUES (" .
            $this->var2str($this->idrecetar) . "," .
            $this->producidos . "," .
            $this->fecha . ");";

    if ($this->db->exec($sql)) {
       $this->exists = TRUE;
       return TRUE;
    }
    return FALSE;
   }
   /**
    * Guarda en la base de datos los datos de la produccion a partir de la Receta
    * @return boolean
    */
   public function save() {
       if ($this->test()) {
         if ($this->exists()) {
            if ($this->updateProduccion()){
              $this->new_message("produccion Receta actualizada correctamente....");
              return TRUE;
            }
          } else {
              if ($this->insertProduccion()){
                return TRUE;
              }
           }
        }
       return FALSE;
   }

   public function delete()
   {
       $sql = "DELETE FROM " . $this->table_name . " WHERE id = " . $this->var2str($this->$id) . ";";
       if ($this->db->exec($sql)) {
         $this->exists = FALSE;
         return TRUE;
       }else {
          $this->new_error_msg("No se ha podido eliminar" . $this->id);
          return FALSE;
       }
    }
      public function deleteEspProduccion()
   {
       $sql = "DELETE FROM " . $this->table_name . " WHERE id = " . $this->var2str($this->$id) . "AND" . "fecha = " . $this->fecha . ";";
       if ($this->db->exec($sql)) {
         $this->exists = FALSE;
         return TRUE;
       }else {
          $this->new_error_msg("No se ha podido eliminar" . $this->id);
          return FALSE;
       }
   }
	}