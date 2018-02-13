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
class articulo extends FacturaScripts\model\articulo {
  /**
   * Metodo Localizar articulos
   * @return $st_act stock fisico articulo de la l
   */
  private function seekArticulo($idar) {

      $this->referencia = $idar;
      $artc = $this->db->select("SELECT * FROM articulo WHERE referencia = " . $this->var2str($this->referencia) . ";");

      if ($artc){
        $st_art = $art[stockfis];
        return $st_art;
      }
  }
  /**
   *  Metodo actuliza stock fisico del articulo compuesto resultante
   */

  private function actualizaStock($stock_f){

    $sql = "UPDATE articulo SET stockfis = " . $st_act . "  WHERE referencia = " . $this->var2str($art1->referencia) . ";";
    if ($this->db->exec($sql)) {
        $this->exists = TRUE;
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
