/*
 * This file is part of facturacion_base ha sido modificado para el plugin acompuesto
 * Copyright (C) 2014-2017  Carlos Garcia Gomez  neorazorx@gmail.com
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

var fs_nf0 = 2;
var fs_nf0_art = 2;
var all_direcciones = [];
var all_impuestos = [];
var default_impuesto = '';
var all_series = [];
var cliente = false;
var nueva_venta_url = '';
var fin_busqueda1 = true;
var fin_busqueda2 = true;
var siniva = false;
var irpf = 0;
var dtosl = false;
var dtost = false;
var solo_con_stock = true;
var numlineas = 0;
 /**
   * actualizaNlinea
   * Este metodo permite actulalizar el numero de ingredientes o numero de nodos <tr>
   * @return void
   */
function actualizaNlinea() {
    var anlinea = $("#lineas_doc tr").length;
    document.f_new_receta.nlinea.value = anlinea;
}
 /**
   * add_articulo
   * Este metodo permite adicionar lineas de articulos para los ingrediente
   * @param ref referencia articulo
   * @param desc descripcion del articulo
   * @param coste coste del articulo
   * @param stock valor del inventario del articulo
   * @param necesarios cantidad de articulos para la receta
   * @return void
   */
function add_articulo(ref, desc, coste, stock, necesarios){
    $("#lineas_doc").append("<tr id=\"linea_" + numlineas + "\" data-ref=\"" + ref + "\">\n\
      <td><input type=\"hidden\" name=\"idlinea_" + numlineas + "\" value=\"-1\"/>\n\
          <input type=\"hidden\" name=\"referencia_" + numlineas + "\" value=\"" + ref + "\"/>\n\
         <div class=\"form-control\"><small><a target=\"_blank\" href=\"index.php?page=ventas_articulo&ref=" + ref + "\">" + ref + "</a></small></div></td>\n\
        <td><textarea class=\"form-control\" id=\"desc_" + numlineas + "\" name=\"desc_" + numlineas + "\" rows=\"1\">" + desc + "</textarea></td>\n\
      <td><input type=\"" + input_number + "\" step=\"any\" id=\"coste_" + numlineas + "\" class=\"form-control text-left\" name=\"coste_" + numlineas +
            "\" autocomplete=\"off\" value=\"" + coste + "\"/></td>\n\
	  <td><input type=\"" + input_number + "\" step=\"any\" id=\"stock_" + numlineas + "\" class=\"form-control text-left\" name=\"stock_" + numlineas +
            "\" autocomplete=\"off\" value=\"" + stock + "\"/></td>\n\
      <td><input type=\"" + input_number + "\" step=\"any\" id=\"neces_" + numlineas + "\" class=\"form-control text-left\" name=\"neces_" + numlineas +
            "\" autocomplete=\"off\" value=\"" + necesarios + "\"/></td>\n\
      <td><button class=\"btn btn-sm btn-danger\" type=\"button\" onclick=\"$('#linea_" + numlineas + "').remove();actualizaNlinea();\">\n\
         <span class=\"glyphicon glyphicon-trash\"></span></button></td>\n\</tr>");
    numlineas += 1;
    $("#modal_articulos").modal('hide');
    $("#desc_" + (numlineas - 1)).select();
    actualizaNlinea();
    //document.f_new_receta.nlinea.value = numlineas;
    return false;
}
 /**
   * add_linea_libre
   * Este metodo permite adicionar lineas de articulos vacia
   * @return void
   */
function add_linea_libre(){
    $("#lineas_doc").append("<tr id=\"linea_" + numlineas + "\" data-ref=\"\" >\n\
      <td><input type=\"hidden\" name=\"idlinea_" + numlineas + "\" value=\"-1\"/>\n\
         <input type=\"hidden\" name=\"referencia_" + numlineas + "\"/>\n\
         <div class=\"form-control\"></div></td>\n\
      <td><textarea class=\"form-control\" id=\"desc_" + numlineas + "\" name=\"desc_" + numlineas + "\" rows=\"1\"> </textarea></td>\n\
      <td><input type=\"" + input_number + "\" step=\"any\" id=\"coste_" + numlineas + "\" class=\"form-control text-left\" name=\"coste_" + numlineas +
            "\" autocomplete=\"off\" value=\"\"/></td>\n\
    <td><input type=\"" + input_number + "\" step=\"any\" id=\"stock_" + numlineas + "\" class=\"form-control text-left\" name=\"stock_" + numlineas +
            "\" autocomplete=\"off\" value=\"\"/></td>\n\
      <td><input type=\"" + input_number + "\" step=\"any\" id=\"neces_" + numlineas + "\" class=\"form-control text-left\" name=\"neces_" + numlineas +
            "\" autocomplete=\"off\" value=\"\"/></td>\n\
      <td><button class=\"btn btn-sm btn-danger\" type=\"button\" onclick=\"$('#linea_" + numlineas + "').remove();\">\n\
         <span class=\"glyphicon glyphicon-trash\"></span></button></td>\n\</tr>");

    numlineas += 1;
    $("#numlineas").val(numlineas);
    $("#desc_" + (numlineas - 1)).select();
    return false;
}
 /**
   * buscar_articulos
   * Este metodo permite buscar articulos y poder seleccionarlos como ingredientes
   * @return void
   */
function buscar_articulos() {
    document.f_nuevo_articulo.referencia.value = document.f_buscar_articulos.query.value;
    if (document.f_buscar_articulos.query.value === '') {
        $("#nav_articulos").hide();
        $("#search_results").html('');
        $("#nuevo_articulo").hide();
        fin_busqueda1 = true;
        fin_busqueda2 = true;
    } else {
        $("#nav_articulos").show();
        if (nueva_venta_url !== '') {
            fin_busqueda1 = false;
            $.getJSON(nueva_venta_url, $("form[name=f_buscar_articulos]").serialize(), function(json) {
                var items = [];
                var insertar = false;
                var necesarios = 0;
                $.each(json, function(key, val) {
                    var stock = val.stockalm;
                    if (val.nostock) {
                        stock = '-';
                    } else if (val.stockalm != val.stockfis) {
                        stock += ' <span title="stock general">(' + val.stockfis + ')</span>';
                    }
                    var descripcion = Base64.encode(val.descripcion);
                    var descripcion_visible = val.descripcion;
                    var tr_aux = '<tr>';
                    if (val.bloqueado || (val.stockalm < 1 && !val.controlstock)) {
                        tr_aux = "<tr class=\"danger\">";
                    } else if (val.stockfis < val.stockmin) {
                        tr_aux = "<tr class=\"warning\">";
                    } else if (val.stockalm > 0) {
                        tr_aux = "<tr class=\"success\">";
                    }
                    if (val.sevende) {
                        var funcion = "add_articulo('" + val.referencia + "','" + descripcion_visible + "','" + val.preciocoste + "','" +
                            stock + "','" + necesarios + "')";

                        if (val.tipo) {
                            funcion = "add_articulo_" + val.tipo + "('" + val.referencia + "','" + descripcion_visible + "','" +
                                val.preciocoste + "','" + stock + "','" + necesarios + "')";
                        }

                        items.push(tr_aux + "<td><a href=\"#\" onclick=\"get_precios('" + val.referencia + "')\" title=\"más detalles\">\n\
                         <span class=\"glyphicon glyphicon-eye-open\"></span></a>\n\
                         &nbsp; <a href=\"#\" onclick=\"return " + funcion + "\">" + val.referencia + '</a> ' + descripcion_visible + "</td>\n\
                         <td class=\"text-right\"><a href=\"#\" onclick=\"return " + funcion + "\" title=\"actualizado el " + val.factualizado +
                                "\">" + show_precio(val.pvp * (100 - val.dtopor) / 100, val.coddivisa) + "</a></td>\n\
                         <td class=\"text-right\"><a href=\"#\" onclick=\"return " + funcion + "\" title=\"actualizado el " + val.factualizado +
                                "\">" + show_pvp_iva(val.pvp * (100 - val.dtopor) / 100, val.codimpuesto, val.coddivisa) + "</a></td>\n\
                         <td class=\"text-right\">" + stock + "</td></tr>");
                    }
                    if (val.query == document.f_buscar_articulos.query.value) {
                        insertar = true;
                        fin_busqueda1 = true;
                        nlinea = nlinea + 1;
                    }
                });
                if (items.length == 0 && !fin_busqueda1) {
                    items.push("<tr><td colspan=\"4\" class=\"warning\">Sin resultados. Usa la pestaña\n\
                              <b>Nuevo</b> para crear uno.</td></tr>");
                    insertar = true;
                }
                if (insertar) {
                    $("#search_results").html("<div class=\"table-responsive\"><table class=\"table table-hover\"><thead><tr>\n\
                  <th class=\"text-left\">Referencia + descripción</th>\n\
                  <th class=\"text-right\" width=\"80\">Precio</th>\n\
                  <th class=\"text-right\" width=\"80\">Precio+IVA</th>\n\
                  <th class=\"text-right\" width=\"80\">Stock</th>\n\
                  </tr></thead>" + items.join('') + "</table></div>");
                }
            });
        }
    }
}
function get_precios(ref) {
    if (nueva_venta_url !== '') {
        $.ajax({
            type: 'POST',
            url: nueva_venta_url,
            dataType: 'html',
            data: "referencia4precios=" + ref + "&codcliente=" + cliente.codcliente,
            success: function(datos) {
                $("#nav_articulos").hide();
                $("#search_results").html(datos);
            },
            error: function() {
                bootbox.alert({
                    message: 'Se ha producido un error al obtener los precios.',
                    title: "<b>Atención</b>"
                });
            }
        });
    }
}

function new_articulo() {
    if (nueva_venta_url !== '') {
        $.ajax({
            type: 'POST',
            url: nueva_venta_url + '&new_articulo=TRUE',
            dataType: 'json',
            data: $("form[name=f_nuevo_articulo]").serialize(),
            success: function(datos) {
                if (typeof datos[0] == 'undefined') {
                    bootbox.alert({
                        message: 'Se ha producido un error al crear el artículo.',
                        title: "<b>Atención</b>"
                    });
                } else {
                    document.f_buscar_articulos.query.value = document.f_nuevo_articulo.referencia.value;
                    $("#nav_articulos li").each(function() {
                        $(this).removeClass("active");
                    });
                    $("#li_mis_articulos").addClass('active');
                    $("#search_results").show();
                    $("#nuevo_articulo").hide();

                    add_articulo(datos[0].referencia, Base64.encode(datos[0].descripcion), datos[0].pvp, 0, datos[0].codimpuesto, 1);
                }
            },
            error: function() {
                bootbox.alert({
                    message: 'Se ha producido un error al crear el artículo.',
                    title: "<b>Atención</b>"
                });
            }
        });
    }
}
function show_pvp_iva(pvp, codimpuesto, coddivisa) {
    var iva = 0;
    if (cliente.regimeniva != 'Exento' && !siniva) {
        for (var i = 0; i < all_impuestos.length; i++) {
            if (all_impuestos[i].codimpuesto == codimpuesto) {
                iva = all_impuestos[i].iva;
                break;
            }
        }
    }

    return show_precio(pvp + pvp * iva / 100, coddivisa);
}

/**
 * Funcion metodo ready jquery.
 */
$(document).ready(function() {
    /**
     * Renombramos el id "lineas_albaran" a "lineas_doc", para asegurar que no deja de funcionar
     * hasta que todos los plugins gratuitos y de pago hayan aplicado el cambio.
     */
    $("#lineas_albaran").attr('id', 'lineas_doc');
    show = false;
    if (!show) {
        dtosl = false;
        $('.dtosl').hide();
    } else {
        dtosl = true;
        $('.dtosl').show();
    }
    $("#i_new_line").click(function() {
        $("#i_new_line").val("");
        $("#nav_articulos li").each(function() {
            $(this).removeClass("active");
        });
        $("#li_mis_articulos").addClass('active');
        $("#search_results").show();
        $("#nuevo_articulo").hide();
        $("#modal_articulos").modal('show');
        document.f_buscar_articulos.query.select();
    });
    $("#i_new_line").keyup(function() {
        document.f_buscar_articulos.query.value = $("#i_new_line").val();
        $("#i_new_line").val('');
        $("#nav_articulos li").each(function() {
            $(this).removeClass("active");
        });
        $("#li_mis_articulos").addClass('active');
        $("#search_results").html('');
        $("#search_results").show();
        $("#nuevo_articulo").hide();
        $("#modal_articulos").modal('show');
        document.f_buscar_articulos.query.select();
        buscar_articulos();
    });
    $("#f_buscar_articulos").keyup(function() {
        buscar_articulos();
    });
    $("#f_buscar_articulos").submit(function(event) {
        event.preventDefault();
        buscar_articulos();
    });
    $("#b_mis_articulos").click(function(event) {
        event.preventDefault();
        $("#nav_articulos li").each(function() {
            $(this).removeClass("active");
        });
        $("#li_mis_articulos").addClass('active');
        $("#nuevo_articulo").hide();
        $("#search_results").show();
        document.f_buscar_articulos.query.focus();
    });
    $("#b_nuevo_articulo").click(function(event) {
        event.preventDefault();
        $("#nav_articulos li").each(function() {
            $(this).removeClass("active");
        });
        $("#li_nuevo_articulo").addClass('active');
        $("#search_results").hide();
        $("#nuevo_articulo").show();
        document.f_nuevo_articulo.referencia.select();
    });
    /**/
    if ($("#lineas_doc tr").length != 0) {
        numlineas = $("#lineas_doc tr").length;
        document.f_new_receta.nlinea.value = numlineas;
    }
});
