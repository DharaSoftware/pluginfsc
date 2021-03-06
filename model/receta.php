<?php
/*
 * This file del plugin acompuestos (productos compuestos)
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
  * @author Raul Mercado @mail rgmercado@gmail.com
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
    * @var float
    */
   public $produccion;
   /**
    * Fecha de la ultima Produccion
    * @var type datetime
    */
   public $fechap;
/**
 * Variables generales utilizadas en el modelo receta
 */
   private static $column_list;
   private $exists;
   public $r_rec;
/**
 * Funcion constructora de la instancia receta, permite crear una instancia de la clase receta, vacia,
 * o con datos que se pasan por parametros.
 * @param array $data
 */
   public function __construct($data = FALSE) {
    SELF::$column_list ='idreceta,descripcion,idalmacening,idalmacenres,observaciones,producto_res,idarticulo,produccion,fechap';
    parent::__construct('receta');

      if ($data) {
         $this->load_from_data($data);
      } else {
         $this->clear();
      }
   }

   public function clear() {
      $this->idreceta       = '';
      $this->descripcion    = '';
      $this->idalmacening   = '';
      $this->idalmacenres   = '';
      $this->observaciones  = '';
      $this->producto_res   = '';
      $this->idarticulo     = '';
      $this->produccion     = 0;
      $this->fechap        = 'NULL';
   }
   public function load_from_data($data) {
       if (!empty($data)) {
			foreach ($data as $property => $argument) {
				$this->{$property} = $argument;
			}
		}
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
       $data = $this->db->select("SELECT " . self::$column_list . " FROM " . $this->table_name . " WHERE idreceta = " . $this->var2str($ref) . ";");
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
    * Guarda en la base de datos los datos de la Receta
    * @return boolean
    */
   public function save()
   {
       if ($this->test()) {
         if ($this->exists()) {
            if ($this->updateReceta()){
              $this->new_message("Receta actualizada correctamente....");
              return TRUE;
            }
          } else {
              if ($this->insertReceta()){
                return TRUE;
              }
           }
        }
       return FALSE;
   }
   /**
    * Actualiza  en la base de datos los datos la Receta
    * @return boolean
    */
   protected function updateReceta() {
     $sql = "UPDATE " . $this->table_name . " SET
            descripcion = " . $this->var2str($this->descripcion) .
         ", idalmacening = " . $this->var2str($this->idalmacening) .
         ", idalmacenres = " . $this->var2str($this->idalmacenres) .
         ", observaciones = " . $this->var2str($this->observaciones) .
         ", producto_res = " . $this->var2str($this->producto_res) .
         ", idarticulo = " . $this->var2str($this->idarticulo) .
         ", produccion = " . 0 .
         ", fechap = " . $this->fechap .
         "  WHERE idreceta = " . $this->var2str($this->idreceta) . ";";
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
  protected function insertReceta(){
    $this->produccion = 0;
    $this->fechap = 'NULL';
    $sql = "INSERT INTO " . $this->table_name . " (" . self::$column_list . ") VALUES (" .
            $this->var2str($this->idreceta) . "," .
            $this->var2str($this->descripcion) . "," .
            $this->var2str($this->idalmacening) . "," .
            $this->var2str($this->idalmacenres) . "," .
            $this->var2str($this->observaciones) . "," .
            $this->var2str($this->producto_res) . "," .
            $this->var2str($this->idarticulo) . "," .
            $this->produccion . "," .
            $this->fechap . ");";

    if ($this->db->exec($sql)) {
       $this->exists = TRUE;
       return TRUE;
    }
    return FALSE;
  }
   /**
    * Elimina el artículo de la base de datos.
    * @return boolean
    */
   public function delete()
   {
      $sql = "DELETE FROM " . $this->table_name . " WHERE idreceta = " . $this->var2str($this->idreceta) . ";";
      if ($this->db->exec($sql)) {
         $this->exists = FALSE;
         return TRUE;
      }else {
          $this->new_error_msg("No se ha podido eliminar" . $this->idreceta);
          return FALSE;
      }
   }
   /*
    * Metodo para actualizar un campo especifico de la tabla de productos Compuestos.
    * @return boolean
    */
    public function actualizaCampo($idcampo,$dato){
        $sql = "UPDATE " . $this->table_name . " SET " . $idcampo . " = " . "\" $dato \"" .
               "  WHERE idreceta = " . $this->var2str($this->idreceta) . ";";
        if ($this->db->exec($sql)) {
            $this->exists = TRUE;
            return TRUE;
        }
        return FALSE;
   }
   /*
    * Devuelve un arreglo de instancias de objetos de la clase de receta
    * @return array object \receta
    */
   private function all_from($sql, $offset = 0, $limit = FS_ITEM_LIMIT)
   {
       $reclist = array();
       $data = $this->db->select_limit($sql, $limit, $offset);
       if ($data) {
           foreach ($data as $a) {
               $reclist[] = new receta($a);
           }
       }

       return $reclist;
   }
   /**
    * Devuelve un array con los artículos encontrados en base a la búsqueda.
    * @param string $query
    * @param integer $offset
    * @return object \receta
    */
  public function buscarRecetas($query = '', $offset = 0)
   {
      $sql = "SELECT * FROM " . $this->table_name;
      $inglist = $this->all_from($sql, $offset);
      return $inglist;
  }
  /**
   * Devuelve un array con los artículos encontrados en base a la búsqueda.
   * @param string $ref
   * @param integer $offset
   * @return object \receta
   */
 public function buscarUnaReceta($ref = '', $offset = 0, $limit = FS_ITEM_LIMIT)
  {
    $sql = "SELECT " . self::$column_list . " FROM " . $this->table_name;
    $separador = ' WHERE';
    $sql .= $separador . " (idreceta = " . $this->var2str($ref) . ")";
    $data = $this->db->select_limit($sql, $limit, $offset);

    if ($data){
      return new \receta($data[0]);
    }
 }
}
