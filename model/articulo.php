<?php
/*
 * receta_articulo.php
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
 *
 */

require_once 'plugins/facturacion_base/model/core/articulo.php';
/**
 * [articulo description]
 * Clase que extiende la clase articulo de factura base.
 */
class articulo extends FacturaScripts\model\articulo {

  /**
   * buscarArticulo function
   * Metodo que permite buscar un articulo especifico
   * @param [type] $ref
   * @return  $art
   */
  public function buscaArticulo($ref)
  {
    $this->referencia = $ref;
    $art = $this->db->select("SELECT * FROM $this->table_name WHERE referencia = " . $this->var2str($this->referencia) . ";");
    if ($art){
        return new \articulo($art[0]);
    }
  }
  /**
   * Metodo Localizar articulos
   * @return $st_act stock fisico articulo de la tabla articulo de plugin factura base
   */
     public function obtenerStock($idar) {

      $this->referencia = $idar;
      $artc = $this->db->select("SELECT * FROM $this->table_name WHERE referencia = " . $this->var2str($this->referencia) . ";");

      if ($artc){
        $st_art = $art[stockfis];
        return $st_art;
      }
  }
  /**
   *  Metodo actuliza stock fisico del articulo compuesto resultante
   */

  public function actualizaStock($stock_f){

      $sql = "UPDATE $this->table_name SET stockfis = " . $stock_f . "  WHERE referencia = " . $this->var2str($this->referencia) . ";";
      if ($this->db->exec($sql)) {
          return TRUE;
      }
      return FALSE;
  }
  /**
   *  Metodo recupera la referencia del articulo
   */

  public function getReferencia()
  {
    return $this->referencia;
  }
}
