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
 * Description of receta el modelo para productos compuestos
 *
 * @author Raul Mercado
 */
class receta extends fs_model {

  /**
   * Clave primaria. Varchar (6).
   * @var string
   */
   public $idreceta;
   /**
    * Descripción de la receta. Tipo text, sin límite de caracteres.
    * @var string
    */
   public $descripcion;
   /**
    * Código del almacen de ingredientes al que pertenece. En la clase almacen.
    * @var string
    */
   Public $idalmacening;
   /**
    * Código del almacen de articulos resultantes de la receta al que pertenece. En la clase almacen.
    * @var string
    */
   Public $idalmacenres;
   /**
    * observaciones de la receta. Tipo text, sin límite de caracteres.
    * @var string
    */
   Public $observaciones;
   /**
    *  Nombre del articulo Resultante al utilizar la receta. Tipo text, sin límite de caracteres.
    * @var string
    */
   public $producto_res;
   /**
    *  Referencia de articulo resultante. Clave foranea tabla de articulos modelo articulo.
    * @var string
    */
   public $idarticulo;
     /**
    * Cantidad de articulos a producir por la receta.
    * @var double
    */
   public $produccion;

   private static $column_list;
   private $exists;

   public function __construct($data = FALSE) {
    SELF::$column_list ='idreceta,descripcion, idalmacening,idalmacenres,observaciones,articulo_res,idarticulo,produccion';
    parent::__construct('receta');

      if ($data) {
         $this->load_from_data($data);
      } else {
         $this->clear();
      }
   }

   public function clear() {
      $this->idreceta = '';
      $this->descripcion = '';
      $this->idalmacening = '';
      $this->idalmacenres = '';
      $this->observaciones = '';
      $this->producto_res = '';
      $this->idarticulo = '';
      $this->produccion = 0.0;
   }

   public function load_from_data($data) {
      $this->idreceta = $data['idreceta'];
      $this->descripcion = $data['descripcion'];
      $this->idalmacening = $data['idalmacening'];
      $this->idalmacenres = $data['idalmacenres'];
      $this->observaciones = $data['observaciones'];
      $this->producto_res = $data['producto_res'];
      $this->idarticulo = $data['$idarticulo'];
      $this->produccion = $data['produccion'];
   }

   public function install() {
      return '';
   }
   /**
    * Verifica la exitencia de la receta
    * @return boolean
    */
   public function exists() {
     if (!$this->exists) {
         if ($this->db->select("SELECT idreceta FROM " . $this->table_name . " WHERE idreceta = " . $this->var2str($this->idreceta) . ";")) {
             $this->exists = TRUE;
         }
     }

     return $this->exists;
   }
   /**
    * Devuelve una receta a partir de su referencia
    * @param string $ref
    * @return boolean|\receta
    */
   public function get($ref)
   {
       $data = $this->db->select("SELECT " . self::$column_list . " FROM " . $this->table_name . " WHERE referencia = " . $this->var2str($ref) . ";");
       if ($data) {
           return new \receta($data[0]);
       }

       return FALSE;
   }
   /**
    * Devuelve el almacen de la clase alamcen.
    * @return \almacen|false
    */
   public function get_almacen($ref)
   {
       if (is_null($this->var2str($ref))) {
           return FALSE;
       }

       $almac = new \almacen();
       return $almac;
   }

   /**
    * Devuelve TRUE  si los datos del artículo son correctos.
    * @return boolean
    */
   protected function test() {

      $status = FALSE;

      $this->descripcion = $this->no_html($this->descripcion);
      $this->observaciones = $this->no_html($this->observaciones);
      $this->producto_res = $this->no_html($this->producto_res);

    if (is_null($this->idreceta) || strlen($this->idreceta) < 1 || strlen($this->idreceta) > 6) {
          $this->new_error_msg("Referencia de Receta no válida: " . $this->idreceta . ". Debe tener entre 1 y 6 caracteres.");
      }else {
          $status = TRUE;
      }

      return $status;
   }

   /**
    * Guarda en la base de datos los datos del artículo.
    * @return boolean
    */
   public function save()
   {
       if ($this->test()) {

           if ($this->exists()) {
               $sql = "UPDATE " . $this->table_name . " SET descripcion = " . $this->var2str($this->descripcion) .
                   ", codfamilia = " . $this->var2str($this->codfamilia) .
                   ", codfabricante = " . $this->var2str($this->codfabricante) .
                   ", pvp = " . $this->var2str($this->pvp) .
                   ", factualizado = " . $this->var2str($this->factualizado) .
                   ", costemedio = " . $this->var2str($this->costemedio) .
                   ", preciocoste = " . $this->var2str($this->preciocoste) .
                   ", codimpuesto = " . $this->var2str($this->codimpuesto) .
                   ", stockfis = " . $this->var2str($this->stockfis) .
                   ", stockmin = " . $this->var2str($this->stockmin) .
                   ", stockmax = " . $this->var2str($this->stockmax) .
                   ", controlstock = " . $this->var2str($this->controlstock) .
                   ", nostock = " . $this->var2str($this->nostock) .
                   ", bloqueado = " . $this->var2str($this->bloqueado) .
                   ", sevende = " . $this->var2str($this->sevende) .
                   ", publico = " . $this->var2str($this->publico) .
                   ", secompra = " . $this->var2str($this->secompra) .
                   ", equivalencia = " . $this->var2str($this->equivalencia) .
                   ", partnumber = " . $this->var2str($this->partnumber) .
                   ", codbarras = " . $this->var2str($this->codbarras) .
                   ", observaciones = " . $this->var2str($this->observaciones) .
                   ", tipo = " . $this->var2str($this->tipo) .
                   ", imagen = " . $this->var2str($this->imagen) .
                   ", codsubcuentacom = " . $this->var2str($this->codsubcuentacom) .
                   ", codsubcuentairpfcom = " . $this->var2str($this->codsubcuentairpfcom) .
                   ", trazabilidad = " . $this->var2str($this->trazabilidad) .
                   "  WHERE referencia = " . $this->var2str($this->referencia) . ";";

               if ($this->nostock && $this->stockfis != 0) {
                   $this->stockfis = 0.0;
                   $sql .= "DELETE FROM stocks WHERE referencia = " . $this->var2str($this->referencia) . ";";
                   $sql .= "UPDATE " . $this->table_name . " SET stockfis = " . $this->var2str($this->stockfis) .
                       " WHERE referencia = " . $this->var2str($this->referencia) . ";";
               }
           } else {
               $sql = "INSERT INTO " . $this->table_name . " (" . self::$column_list . ") VALUES (" .
                   $this->var2str($this->referencia) . "," .
                   $this->var2str($this->codfamilia) . "," .
                   $this->var2str($this->codfabricante) . "," .
                   $this->var2str($this->descripcion) . "," .
                   $this->var2str($this->pvp) . "," .
                   $this->var2str($this->factualizado) . "," .
                   $this->var2str($this->costemedio) . "," .
                   $this->var2str($this->preciocoste) . "," .
                   $this->var2str($this->codimpuesto) . "," .
                   $this->var2str($this->stockfis) . "," .
                   $this->var2str($this->stockmin) . "," .
                   $this->var2str($this->stockmax) . "," .
                   $this->var2str($this->controlstock) . "," .
                   $this->var2str($this->nostock) . "," .
                   $this->var2str($this->bloqueado) . "," .
                   $this->var2str($this->secompra) . "," .
                   $this->var2str($this->sevende) . "," .
                   $this->var2str($this->equivalencia) . "," .
                   $this->var2str($this->codbarras) . "," .
                   $this->var2str($this->observaciones) . "," .
                   $this->var2str($this->imagen) . "," .
                   $this->var2str($this->publico) . "," .
                   $this->var2str($this->tipo) . "," .
                   $this->var2str($this->partnumber) . "," .
                   $this->var2str($this->codsubcuentacom) . "," .
                   $this->var2str($this->codsubcuentairpfcom) . "," .
                   $this->var2str($this->trazabilidad) . ");";
           }

           if ($this->db->exec($sql)) {
               $this->exists = TRUE;
               return TRUE;
           }
       }

       return FALSE;
   }

   /**
    * Elimina el artículo de la base de datos.
    * @return boolean
    */
   public function delete()
   {

       /*
       $sql .= "DELETE FROM receta_ingredientes WHERE idrecetar = " . $this->var2str($this->idreceta) . ";";
       $sql .= "DELETE FROM receta_produccion WHERE idrecetar = " . $this->var2str($this->idreceta) . ";";
       */
       $sql = "DELETE FROM " . $this->table_name . " WHERE idreceta = " . $this->var2str($this->$idreceta) . ";";
       if ($this->db->exec($sql)) {
         $this->exists = FALSE;
         return TRUE;
       }else {
          $this->new_error_msg("No se ha podido eliminar" . $this->idreceta);
          return FALSE;
       }
   }
  }
