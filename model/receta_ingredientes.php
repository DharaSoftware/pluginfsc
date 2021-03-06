<?php
/*
 * receta_ingredientes.php
 *
 * Copyright 2018 Raul G Mercado H <rgmercado@PC-Mint>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301, USA.
 *
 */

class receta_ingrediente extends fs_model {
  /**
   * [public valor autoimcremental como indice idetificador de la receta producto compuesto]
   * @var type integer
   */
   public $id;
   /**
    * [Identificador del codigo de la reeta o producto compuesto]
    * @var type varchar(6)
    */
   public $idrecetar;
   /**
    * public clave foranea de referencia articulo en la tabla articulo de facturacion base
    * @var type varchar(18)
    */
  public $idarticulor;
  /**
   * public la cantidad necesaria de del ingrediente en la receta.
   * @var type integer
   */
  public $necesarios;
  /**
   * public posicion de la linea de ingrediente en la receta
   * @var type integer
   */
   public $idlinea;

   /**
    * variables globales utilizadas en la clase
    * @var type array $column_lis .
    * @var type array $column_ri.
    */
   private static $column_list;
   private static $column_ri;
   private $exists;

   /**
    * Metodo constrcutor de la clase
    * @method __construct
    * @param  boolean  $data arreglo de valores iniciales para instanciar
    */

   public function __construct($data = FALSE) {
      parent::__construct('receta_ingredientes');
      SELF::$column_list = 'idrecetar, idarticulor, necesarios, idlinea';
      //$this->add_keyfield('id');
      if ($data) {
         $this->load_from_data($data);
      } else {
         $this->clear();
      }
   }

   public function exists() {

     if (!$this->exists) {
         if ($this->db->select("SELECT id FROM " . $this->table_name . " WHERE id = " . $this->var2str($this->id) . ";")) {
             $this->exists = TRUE;
         }
     }

     return $this->exists;
   }

   public function clear() {
      $this->id = '';
      $this->idrecetar = '';
      $this->idarticulor = '';
      $this->necesarios = '';
      $this->idlinea = '';
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
    * Actualiza  en la base de datos los datos la Receta
    * @return boolean
    */
   protected function updateIngrediente()
   {
     $sql = "UPDATE " . $this->table_name . " SET
               idrecetar = " . $this->var2str($this->idrecetar) .
            ", idarticulor = " . $this->var2str($this->idarticulor) .
            ", necesarios = " . $this->necesarios .
            ", idlinea = " . $this->idlinea .
            "  WHERE id = " . $this->id . ";";

      if ($this->db->exec($sql)) {
        return TRUE;
      }
    return FALSE;
   }
   /**
    * Inserta en la base de datos los datos la Receta
    * @return boolean
    */
   protected function insertIngrediente()
   {
        $sql = "INSERT INTO " . $this->table_name . " (" . SELF::$column_list . ") VALUES (" .
            $this->var2str($this->idrecetar) . "," .
            $this->var2str($this->idarticulor) . "," .
            $this->necesarios . "," .
            $this->idlinea . ");";

    if ($this->db->exec($sql)) {
       return TRUE;
    }
    return FALSE;
   }
   /**
    * Guarda en la base de datos los datos de la produccion a partir de la Receta
    * @return boolean
    */
   public function save()
   {
         if ($this->exists()) {
            if ($this->updateIngrediente()){
              return TRUE;
            }
          } else {
              if ($this->insertIngrediente()){
                return TRUE;
              }
         }
      return FALSE;
   }
   /**
    * Elimina los ingrediente para una receta
    * @return boolean
    */
   public function delete()
   {
       $sql = "DELETE FROM " . $this->table_name . " WHERE idrecetar = " . $this->var2str($this->idrecetar) . ";";
       if ($this->db->exec($sql)) {
         $this->exists = FALSE;
         return TRUE;
       }else {
          $this->new_error_msg("No se ha podido eliminar" . $this->id);
          return FALSE;
       }
   }
   public function deleteLineaIngrediente()
   {
       $sql = "DELETE FROM " . $this->table_name . " WHERE id = " . $this->var2str($this->id) . "AND" . "idlinea = " . $this->idlinea . ";";
       if ($this->db->exec($sql)) {
         $this->exists = FALSE;
         return TRUE;
       }else {
          $this->new_error_msg("No se ha podido eliminar" . $this->id);
          return FALSE;
       }
   }
   /**
    * Devuelve un array con los ingredientes de la receta
    * @param string $sql
    * @param integer $offset
    * @return array de objetos \receta_ingrediente
    */
   private function all_from($sql, $offset = 0, $limit = FS_ITEM_LIMIT)
   {
       $artilist = array();
       $data = $this->db->select_limit($sql,$limit, $offset);
       if ($data) {
           foreach ($data as $a) {
               $artilist[] = new \receta_ingrediente($a);
           }
       }

       return $artilist;
   }
   /**
    * Devuelve un array con los artículos encontrados en base a la búsqueda.
    * @param string $query
    * @param integer $offset
    * @return \articulo
    */
   public function buscarIngredientes($query = '', $offset = 0, $limit = FS_ITEM_LIMIT)
   {
       $inglist = array();
       $query = $this->no_html(mb_strtolower($query, 'UTF8'));
       if (count($inglist) <= 1) {
          $sql = "SELECT * FROM " . $this->table_name;
          $separador = ' WHERE';
          if ($query == '') {
              $sql .= $separador . " (idrecetar = " . $this->var2str($query)
                   . " OR idrecetar LIKE '%" . $query . "%'" . ")";
              $sql .= " ORDER BY lower(idrecetar) ASC";
           }
        }
      $inglist = $this->all_from($sql, $offset,$limit);
      return $inglist;
   }
}
