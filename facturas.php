<?php
/*
Plugin Name: Facturas
Plugin URI: http://zenda.mx
Description: Carga de facturas para Metals and Supplies
Author: Zenda C.G
Version: 1.3
Author URI: 
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
} // end if


/** PHPExcel_IOFactory */
include_once  ABSPATH . 'wp-content/plugins/excel_reader/PHPExcel/IOFactory.php';
wp_register_style( 'facturas', plugins_url( 'css/style.css', __FILE__ ) );

include_once(ABSPATH.'/wp-config.php');
#el domino del host
$_SERVER[ 'HTTP_HOST' ] = DB_HOST;
#ubicacion del wp-load.php para accesar a la base de datos con el $wpdb
$wp_load_loc = ABSPATH.'/wp-load.php';
#lo agrega como libreria
require_once($wp_load_loc);


register_activation_hook(__FILE__, 'prowp_facturas_install');
register_deactivation_hook(__FILE__, 'prowp_facturas_deactivate()');



function prowp_facturas_install(){}

function prowp_facturas_deactivate(){}

function prowp_facturas_menu(){
	add_menu_page('Pagina de Carga de Facturas', 'Carga de Facturas', 'manage_options', 'facturas_menu', 'facturas_page'
		,plugins_url('images/menu.png', __FILE__));

	//add_submenu_page('prowp_main_menu', 'Subir Archivo', 'Subir Archivo', 'manage_options', 'subir_archivo_inventario', 'prowp_upload_file');
	//add_action('admin_init', 'prowp_register_settings');
}
//
add_action( 'init', 'create_tipo_post_facturas' );	
function create_tipo_post_facturas() {
    register_post_type( 'facturas',
        array(
            'labels' => array(
             'name' => 'Facturas',
                'singular_name' => 'Factura',
                'add_new' => 'Agregar Nueva',
                'add_new_item' => 'Agregar Nueva Factura',
                'edit' => 'Editar',
                'edit_item' => 'Editar Factura',
                'new_item' => 'Nueva Factura',
                'view' => 'Ver',
                'view_item' => 'Ver Factura',
                'search_items' => 'Buscar Facturas',
                'not_found' => 'No se encontraton facturas',
                'not_found_in_trash' => 'No hay facturas en la papelera.',
                'parent' => 'Factura padre'
            ),
			/*'capabilities' => array(
			    'create_posts' => false, // Removes support for the "Add New" function
			  ),*/
            'public' => true,
            'menu_position' => 15,
            'supports' => array( 'title', 'author' ),
            'taxonomies' => array( '' ),
            'menu_icon' => plugins_url( 'images/facturas.png', __FILE__ ),
            'has_archive' => true,
			'hierarchical'=>false,
			'query_var' => true,
			'rewrite' => array( 'slug' => 'factura', 'with_front' => false ),
        )
    );
}

add_action( 'admin_init', 'metabox_factura' );

function metabox_factura() {
    add_meta_box( 'factura_meta_box',
        'Detalles de Factura',
        'display_factura_meta_box',
        'facturas', 'normal', 'high'
    );
}

function display_factura_meta_box( $factura ) {
    // Retrieve current name of the Director and Movie Rating based on review ID
    $fecha_de_expedicion = esc_html( get_post_meta( $factura->ID, 'factura_fecha_de_expedicion', true ) );
    $estado = esc_html( get_post_meta( $factura->ID, 'factura_estado', true ) );
    $neto = get_post_meta( $factura->ID, 'factura_neto', true );
    $descuentos = get_post_meta( $factura->ID, 'factura_descuentos', true );
    $impuestos = get_post_meta( $factura->ID, 'factura_impuestos', true );
    $total = get_post_meta( $factura->ID, 'factura_total', true );	
	$descargas = get_post_meta( $factura->ID, 'factura_descargas', true );	
	$idCliente = get_post_meta( $factura->ID, 'factura_idCliente', true );
	//wp_nonce_field(plugin_basename(__FILE__), 'display_factura_meta_box_nonce');
	
	//http://code.tutsplus.com/tutorials/a-guide-to-wordpress-custom-post-types-creation-display-and-meta-boxes--wp-27645
	//http://code.tutsplus.com/articles/attaching-files-to-your-posts-using-wordpress-custom-meta-boxes-part-1--wp-22291
	//Falta el guardado de los metas y el archivo
	?>
	<table> 
		<tr>
		<th> </th>  <th> </th></tr>
		<tr>
			<td><b>Fecha de Expedición</b></td> 
			<td><input type="text" size="20" name="factura_fecha_de_expedicion" value="<?php echo $fecha_de_expedicion; ?>" /></td>
		</tr>
		<tr>
			<td><b>Estado</b> </td>
			<td><input type="text" size="20" name="factura_estado" value="<?php echo $estado; ?>" /></td>
		</tr>
		<tr>
			<td><b>Neto</b> </td> 
			<td><input type="text" size="20" name="factura_neto" value="<?php echo $neto; ?>" /></td>
		</tr>
		<tr>
			<td><b>Descuentos</b> </td> 
			<td><input type="text" size="20" name="factura_descuentos" value="<?php echo $descuentos; ?>" /></td>
		</tr>
		<tr>
			<td><b>Impuestos</b> </td>
			<td><input type="text" size="20" name="factura_impuestos" value="<?php echo $impuestos; ?>" /></td>
		</tr>
		<tr>
			<td><b>Total</b> </td>
			<td><input type="text" size="20" name="factura_total" value="<?php echo $total; ?>" /></td>
		</tr>
		<tr>
			<td><b>Url facturas (Separar por comas si son varios archivos)</b> </td>
			<td><input type="text" size="80" name="factura_descargas" value="<?php echo $descargas; ?>" /></td>
		</tr>
		<tr>
			<td><b>Cliente</b> </td> 
			<td><input type="text" size="20" name="factura_idCliente" value="<?php echo $idCliente; ?>" /></td>
		</tr>
	</table>

    <?php
}

add_action( 'save_post', 'add_factura_fields', 10, 2 );

function add_factura_fields( $factura_id, $factura ) {
    // Check post type for movie reviews
    if ( $factura->post_type == 'facturas' ) {
        // Store data in post meta table if present in post data
        if ( isset( $_POST['factura_fecha_de_expedicion'] ) && $_POST['factura_fecha_de_expedicion'] != '' ) {
            update_post_meta( $factura_id, 'factura_fecha_de_expedicion', $_POST['factura_fecha_de_expedicion'] );
        }

        if ( isset( $_POST['factura_estado'] ) && $_POST['factura_estado'] != '' ) {
            update_post_meta( $factura_id, 'factura_estado', $_POST['factura_estado'] );
        }
		
        if ( isset( $_POST['factura_neto'] ) && $_POST['factura_neto'] != '' ) {
            update_post_meta( $factura_id, 'factura_neto', $_POST['factura_neto'] );
        }
		
        if ( isset( $_POST['factura_descuentos'] ) && $_POST['factura_descuentos'] != '' ) {
            update_post_meta( $factura_id, 'factura_descuentos', $_POST['factura_descuentos'] );
        }
		
        if ( isset( $_POST['factura_impuestos'] ) && $_POST['factura_impuestos'] != '' ) {
            update_post_meta( $factura_id, 'factura_impuestos', $_POST['factura_impuestos'] );
        }
		
        if ( isset( $_POST['factura_total'] ) && $_POST['factura_total'] != '' ) {
            update_post_meta( $factura_id, 'factura_total', $_POST['factura_total'] );
        }
		
        if ( isset( $_POST['factura_descargas'] ) && $_POST['factura_descargas'] != '' ) {
            update_post_meta( $factura_id, 'factura_descargas', $_POST['factura_descargas'] );
        }
		
        if ( isset( $_POST['factura_idCliente'] ) && $_POST['factura_idCliente'] != '' ) {
            update_post_meta( $factura_id, 'factura_idCliente', $_POST['factura_idCliente'] );
        }
    }
}


function include_facturas_template( $template_path ) {
 
   	return plugin_dir_path( __FILE__ ) . 'page-facturas.php';

}
//add_filter( 'template_include', 'include_facturas_template', 1);

/*
function wpse114181_template_include( $template ) {
    return ( '' != get_query_var( 'plugin_key' ) ? plugin_dir_path( __FILE__ ) . 'template-plugin.php' : $template );
}
add_filter( 'template_include', 'wpse114181_template_include' );
*/

add_filter( 'manage_edit-facturas_columns', 'facturas_columns' );

function facturas_columns( $columns ) {
    $columns['factura_fecha_de_expedicion'] = 'Expedida';
    $columns['factura_estado'] = 'Estado';
 	$columns['factura_total'] = 'Total';
 	$columns['factura_idCliente'] = 'Folio Cliente';
	unset( $columns['author'] );	
    return $columns;
}

add_action( 'manage_posts_custom_column', 'populate_facturas_columns' );

function populate_facturas_columns( $column ) {
    if ( 'factura_fecha_de_expedicion' == $column ) {
        $factura_fecha_de_expedicion = esc_html( get_post_meta( get_the_ID(), 'factura_fecha_de_expedicion', true ) );
        echo $factura_fecha_de_expedicion;
    }
    elseif ( 'factura_estado' == $column ) {
        $factura_estado = get_post_meta( get_the_ID(), 'factura_estado', true );
        echo $factura_estado;
    }
	elseif('factura_total' == $column){
        $factura_total = get_post_meta( get_the_ID(), 'factura_total', true );
        echo  '$'.number_format((float)$factura_total, 2, '.', '');
	}
	elseif('factura_idCliente' == $column){
        $factura_idCliente = get_post_meta( get_the_ID(), 'factura_idCliente', true );
        echo $factura_idCliente;
	}
}



add_action('admin_menu', 'prowp_facturas_menu');
	
/*function prowp_upload_file(){
	echo "<h1>LOL</h1>";
}*/

function facturas_page(){
	echo "<h1>Subir Facturas</h1>";
	
	if(isset($_FILES) && !empty($_FILES)){
  		$allowedExts = array("zip");
  		$temp = explode(".", $_FILES["file"]["name"]);
  		$extension = end($temp);
		
		
		if ( ( ($_FILES["file"]["type"] == "application/zip") || ($_FILES["file"]["type"] =="application/octet-stream")) && in_array($extension, $allowedExts)){
			
		  if ($_FILES["file"]["error"] > 0){
		    echo "Error: " . $_FILES["file"]["error"] . "<br>";
		    
			}else{
				$new_filename=date('d_m_Y_H_i_s', strtotime('now'));
				
		  
		    	if(file_exists("upload/{$new_filename}")){
		    		echo $new_filename . " already exists. ";
				}else{
				
					$uploadfile = plugin_dir_path( __FILE__ ).'upload/'.$new_filename.'.zip'; // 
				
				  	if(move_uploaded_file($_FILES["file"]["tmp_name"], $uploadfile )){
						//Descompresión de archivo zip, recibimos el master 
						$dispersion = unzip_facturas($uploadfile);
					
						
						 if($dispersion){
							 if (!empty($dispersion['master']) && file_exists($dispersion['master'])){
								 $master_header = verify_master_file($dispersion['master']);
							 }else{
								 echo "No se tiene o no existe una ruta para el master.xls / master.xlsx";
								 echo "<pre>";
								var_dump($dispersion, file_exists($dispersion['master']));
								 echo "</pre>";
							 }
							// array('master'=>$master_file,'contenedora'=>$contenedora );
							if((count($master_header['header']) > 0) && (count($master_header['data']) > 0)){
								
								create_all(	$master_header, $dispersion['contenedora']);
							}else{
								 echo "Sin datos de encabezado ni detalles de relaciones";
							}
							 
						 }else
							 echo "No se encontró el archivo maestro";
						 
					  
				  }else{
					  echo "<pre>";
					  echo "Error: No se ha podido subir el archivo, por favor inténte nuevamente.";
					  echo "</pre>";
				  }
				  
		    }
			  
		    }
		  
		  }else{
			  echo "El formato para subir las Facturas debe ser en .zip <br/>";
			  //print_r($allowedExts);
		  }
		  
		  
	
	}else{
		
		echo '<form action="'.admin_url().'admin.php?page=facturas_menu" method="post"	enctype="multipart/form-data">';
		
		echo "<div class='row' style='width:60%; box-shadow: 1px 1px 1px #888888;'>";
			
			echo "<div style='width:50%; display:inline-block; '>"; echo '<input type="file" name="file" id="file">'; echo "</div>";
			echo "<div style='width:50%; display:inline-block;'>"; echo '<input type="submit" name="submit" class="button" value="Subir archivo">'; echo "</div>";
		echo "</div>";
		
		echo '</form>';
		
	}
}

//descomprime y regresa el path del archivo maestro.

	function unzip_facturas($path){
		$array_files= array();
		$master_file = null;
		$header = null;
		$header_data = null;
		$num_rows = 0;
		$info_values = null;
		
		// Crear folder para guardar las facturas de forma temporal
		$extract_to = str_replace ('.zip' , '', $path);
		
		if(!mkdir($extract_to, 0777, true)){
			echo 'Falló al crear la carpeta para extraer los archivos...'; 
		}else{
			chmod($extract_to, 0777);
			$zip = new ZipArchive;
			
			if ($zip->open($path) === TRUE) {
			    
				if($zip->extractTo($extract_to)){
					chmod($extract_to, 0777);
					$zip->close();
				    
					
					$objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($extract_to), RecursiveIteratorIterator::SELF_FIRST);
					foreach($objects as $name => $object){
					    $low_case_name=strtolower($name);
						$file_name_ = end(explode("/",$low_case_name));
						
						if( $file_name_== 'master.xls') 
							$master_file_path= $object->getPath().'/'.end(explode("/",$name));
						
						if ($file_name_== 'master.xlsx')
							$master_file_path= $object->getPath().'/'.end(explode("/",$name));
						
					}
					unlink($path);
					return (array('master'=>$master_file_path,'contenedora'=>$extract_to));
					
				}else{
					echo "<br/>No fué posible descomprimir el archivo, inténte de nuevo.";
				}			    
	
			} else {
			    echo '<br/>No se ha podido leer el archivo .zip.';
			}
			
		}
		
		return false;
	}
	
	function create_all($relacion, $path){
		
		$url_http= substr($path, strpos ( $path , '/upload/'));
		$http_link =plugins_url($url_http, __FILE__);
		$home =  home_url();
		$counter=1;
		
		$header=$relacion['header'];
		$header=array_map('strtoupper', $header);
		
		$index_fecha =array_search( 'FECHA' , $header); $index_serie =array_search( 'SERIE' , $header);
		$index_folio =array_search( 'FOLIO' , $header);	$index_concepto =array_search( 'CONCEPTO' , $header);
		$index_cliente =array_search( 'CLIENTE' , $header); $index_cantidad =array_search( 'CANTIDAD' , $header);
		$index_neto =array_search( 'NETO' , $header); $index_descuento =array_search( 'DESCUENTO' , $header);
		$index_impuesto =array_search( 'IMPUESTO' , $header); $index_total =array_search( 'TOTAL' , $header);
		$index_estado =array_search( 'ESTADO' , $header);
		
		
		//echo "<br/>Buscando los archivos necesarios:<br/>";
		echo "<div class='row' style='width:60%; box-shadow: 1px 1px 1px #888888;'>";
		echo "<table style ='width:100%;'>";
			echo "<tr>";
				echo "<th>No.</th>";
				echo "<th>Llave</th>";
				echo "<th>Cliente</th>";
				echo "<th>Registrado</th>";
				echo "<th>PDF / XLS </th>";
				echo "<th>Estatus</th>";
			echo "</tr>";
			
		foreach($relacion['data'] as $factura){
			$num_cliente= str_replace(",", "", $factura[$index_cliente]);
			$num_folio= str_replace(",", "", $factura[$index_folio]);
			$serie= str_replace(",", "", $factura[$index_serie]);
			$zip='no';
			
			
			
			$factura['key_files']='F'.$serie.str_pad(preg_replace('/[^0-9]/', '', $num_folio ), 10, "0", STR_PAD_LEFT);
			
			if($factura['key_files'] =='F0000000000')
				continue;
			
			$objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path), RecursiveIteratorIterator::SELF_FIRST);
			
			foreach($objects as $name => $object){
			    $file_name_ = end(explode("/",$name));
				if( $file_name_== $factura['key_files'].'.zip'){ 
					$path_zip=  $name;
					$zip='ok';
				}
			}
			
			
			echo "<tr>";
				echo "<td style = 'text-align:center; '>".$counter."</td>";
				echo "<td style = 'text-align:center; '>".$factura['key_files']."</td>";
				echo "<td style = 'text-align:left; '>".$num_cliente."</td>";
				echo "<td>";
				
				$user = get_user_by( 'login', trim($num_cliente) );
		
				if($user){
					echo "<img src='".plugins_url('images/ok.png', __FILE__)."' />";
				}else
					echo "<img src='".plugins_url('images/no.png', __FILE__)."' />";
				
				echo "</td>";
				echo "<td style = 'text-align:center; '> <img alt='Ok' src='".plugins_url('images/'.$zip.'.png', __FILE__)."'/></td>";
				
				$factura['cliente']=$num_cliente;
				$factura['fecha']=$factura[$index_fecha];
				$factura['estado']=$factura[$index_estado];
				$factura['neto']=$factura[$index_neto];
				$factura['descuentos']=$factura[$index_descuento];
				$factura['impuestos']=$factura[$index_impuesto];
				$factura['total']=$factura[$index_total];
				
				
				$zip_path_exploded = explode("/", $path_zip);
				$index_wp_content= array_search('wp-content', $zip_path_exploded);
				$link__=$home.'/'.implode("/", array_slice($zip_path_exploded,$index_wp_content ));
				
				$idPost = ($zip =='ok' && $user) ? create_post($factura, $zip=='ok' ? true:null , $link__) : false;
				
				if(true){
					$estatus_img = " <a href='{$home}?p={$idPost}'><img src='".plugins_url('images/ver.png', __FILE__)."' /></a> ";
					//permisos_factura($idPost, $user->data->ID);
				}else
					$estatus_img = "<img src='".plugins_url('images/no.png', __FILE__)."' />";
				
				echo "<td style = 'text-align:center; '>{$estatus_img}</td>";
				echo "</tr>";
			
			$counter++;
		}
		echo "</table>";
		echo "</div>";
	}
	
	/**
	Función para crear los post con las facturas para descargar
	**/
	
	function create_post($factura, $zip=null, $http_link){
		global $current_user;
		get_currentuserinfo();
		
		
		if($factura['cliente']){

			
			/*if(!is_null($zip)){
				$links.="<a href = '{$http_link}' ><img alt='Descargar' src='".plugins_url('images/zip.png', __FILE__)."'/> </a>";
			}*/
			
			/*$texto = '<div style="text-align:left; "><table style="width:100%;"> <tr><th> </th>  <th> </th></tr>';
			$texto.='<tr><td><b>Fecha de Expedición</b></td> <td>'.$factura['fecha'].'</td></tr>';
			$texto.='<tr><td><b>Estado</b> </td> <td>'.$factura['estado'].'</td></tr>';
			$texto.='<tr><td><b>Neto</b> </td> <td>'.$factura['neto'].'</td></tr>';
			$texto.='<tr><td><b>Descuentos</b> </td> <td>'.$factura['descuentos'].'</td></tr>';
			$texto.='<tr><td><b>Impuestos</b> </td> <td>'.$factura['impuestos'].'</td></tr>';
			$texto.='<tr><td><b>Total</b> </td> <td>'.$factura['total'].'</td></tr>';									
			$texto.='<tr><td><b>Descargas</b> </td> <td>'.$links.'</td> </tr>';
			$texto.='</table> </div>';*/
			
			$my_post = array(
			  'post_title'    => 'Factura '.$factura['key_files'].' '.$factura['fecha'],
  			  'post_type'     => 'facturas',
			  'comment_status'=> 'closed',
			  'post_parent'   => 0,
			  'post_content'  => '',
			  'post_status'   => 'publish',
			  'post_author'   => $current_user->ID,
			  'ping_status'   => get_option('default_ping_status'),
			 
			);
			//echo $rtrn;
			if ($post=wp_insert_post( $my_post )){
		        
				if ( isset( $factura['fecha'] ) && $factura['fecha'] != '' ) {
		            update_post_meta( $post, 'factura_fecha_de_expedicion', $factura['fecha'] );
		        }

		        if ( isset( $factura['estado'] ) && $factura['estado'] != '' ) {
		            update_post_meta( $post, 'factura_estado', $factura['estado'] );
		        }
		
		        if ( isset( $factura['neto'] ) && $factura['neto'] != '' ) {
		            update_post_meta( $post, 'factura_neto', $factura['neto'] );
		        }
		
		        if ( isset( $factura['descuentos'] ) && $factura['descuentos'] != '' ) {
		            update_post_meta( $post, 'factura_descuentos', $factura['descuentos'] );
		        }
		
		        if ( isset( $factura['impuestos'] ) && $factura['impuestos'] != '' ) {
		            update_post_meta( $post, 'factura_impuestos', $factura['impuestos'] );
		        }
		
		        if ( isset( $factura['total'] ) && $factura['total'] != '' ) {
		            update_post_meta( $post, 'factura_total', $factura['total'] );
		        }
		
	            update_post_meta( $post, 'factura_descargas', $http_link );
		
		        if ( isset( $factura['cliente'] ) && $factura['cliente'] != '' ) {
		            update_post_meta( $post, 'factura_idCliente', $factura['cliente'] );
		        }
				
				return $factura;
			}
		}
		
		return false;
	}
	
	/**
	Verifica que exista el encabezado esperado en el maestro
	**/
	
	function verify_master_file($master_file){
		$header_data = null;
			
		$objPHPExcel = PHPExcel_IOFactory::load($master_file);
		if($objPHPExcel){
			
			$sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
			$num_rows = count($sheetData);
			if($num_rows){
				foreach($sheetData as $key=>$r){
					$values= array_values($r);
					$upper_arr= array_map('strtoupper', $values);
					$haystack = array('FOLIO', 'CLIENTE', 'SERIE');

					if(count(array_intersect($haystack, $upper_arr)) > 2){
						$header=$key;
						$header_data = $r;
						break;
					}
								
				}//end foreach

				return array('header'=>$header_data, 'data'=>array_slice($sheetData, $header) );
		
			}else{
				echo "<pre> No se han podido extraer los datos del archivo maestro {$master_file}</pre>";
				return array('header'=>false, 'data'=>false );
			}
		}else
			echo "<pre> No se pudo leer el archivo {$master_file}</pre>";
		
		return array('header'=>false, 'data'=>false );
	}
	
	
	
	function func_facturas_shortcode( $atts ) {
	      $atts = shortcode_atts( array(
	 	      'foo' => 'no foo',
	 	      'baz' => 'default baz'
	      ), $atts );
		  wp_enqueue_style('facturas');
		  if(is_user_logged_in() ){
			 global $current_user;
	  		$user_roles = $current_user->roles;
	  		$user_role = array_shift($user_roles);
					
					
				if($user_role=='administrator'){
					$args = array(
						'post_type' => 'facturas',
						'post_status'=>'published',
						'posts_per_page' => 20,
						'paged' => get_query_var( 'paged' )
					);
				}else{
					
					$args = array(
						'post_type' => 'facturas',
						'post_status'=>'published',
						'posts_per_page' => 20,
						'paged' => get_query_var( 'paged' ),
						'meta_query' => array(
								array(
									'key'     => 'factura_idCliente',
									'value'   => $current_user->user_login,
									//'compare' => 'NOT LIKE',
								),
							),
					);
					
				}
					//      var_dump($args);
				 $the_query = new WP_Query($args );
			 
				 if ( $the_query->have_posts() ) :
					 while ( $the_query->have_posts() ) : $the_query->the_post();
						 echo "<article class='factura'>";
							$meta = get_post_meta( get_the_ID() );
							//var_dump($meta);
							?>
							<table class="facturas">
								<tr class="title_factura"><th colspan=3><?php the_title(); ?></th></tr>
								<tr><td>Fecha de Expedición</td>
									<td><?php echo isset($meta['factura_fecha_de_expedicion'][0]) ? $meta['factura_fecha_de_expedicion'][0]:'No agregado'; ?> </td>
									<td class="downloads" rowspan="6">
										<?php
										$urls=	$meta['factura_descargas'][0];
										$urls= explode(',', $urls);
										
										foreach($urls as $link){
											$link_tokens = explode("/", $link);
											$filename = $link_tokens[count($link_tokens)-1];
											$filename_tokens = explode(".", $filename);
											$extension = $filename_tokens[count($filename_tokens)-1];
											
											if(strtolower($extension) == 'zip'){
												$img = "<img src='".plugins_url('images/zip.png', __FILE__)."' alt='Descargar ZIP' />";
											}
											
											if(strtolower($extension) == 'pdf'){
												$img = "<img src='".plugins_url('images/pdf.png', __FILE__)."' alt='Descargar PDF' />";
											}
											
											if(strtolower($extension) == 'xml'){
												$img = "<img src='".plugins_url('images/xml.png', __FILE__)."' alt='Descargar XML' />";
											}
											
											
											echo "<a href='$link'>".$img."</a>";
										}
										
										?>
									</td></tr>
								<tr><td>Estado</td>
									<td>
										<?php echo isset($meta['factura_estado'][0]) ? $meta['factura_estado'][0]:'No agregado'; ?>
									</td>
								</tr>
								<tr><td>Neto</td>
									<td>
									<?php echo isset($meta['factura_neto'][0]) ? "$ ".number_format((float)$meta['factura_neto'][0], 2, '.', ''):'No agregado'; ?>
									</td>
								</tr>
								<tr><td>Descuentos</td>
									<td>
										<?php echo isset($meta['factura_descuentos'][0]) ? "$ ".number_format((float)$meta['factura_descuentos'][0], 2, '.', ''):'No agregado'; ?>
									</td>
								</tr>
								<tr><td>Impuestos</td>
									<td>
										<?php echo isset($meta['factura_impuestos'][0]) ? "$ ".number_format((float)$meta['factura_impuestos'][0], 2, '.', ''):'No agregado'; ?>
									</td>
								</tr>
								<tr><td>Total</td>
									<td>
									<?php echo isset($meta['factura_total'][0]) ? "$ ".number_format((float)$meta['factura_total'][0], 2, '.', ''):'No agregado'; ?>
								</td>
								</tr>
									
							</table>
							<?php
						 echo "</article>";
					 endwhile;

			                                                                 
					next_posts_link( '<-Facturas Anteriores', $the_query->max_num_pages );
					previous_posts_link( ' :: Facturas Más Recientes->' );
                	wp_reset_postdata();

				else:
			 		_e( 'No hemos encontrado facturas para ti.' );
					echo "</br>";
				endif;
			 
	  		}else{
	  			echo do_shortcode( '[alert type="error"]' . "Debe iniciar sesión para descargar sus facturas" . '[/alert]' );
	  		}
	      //return "foo = {$atts['foo']}";
	}
	add_shortcode( 'facturas', 'func_facturas_shortcode' );
	
	#funcion para permisos mike mike
	/*function permisos_factura($post_id, $user_id){ 
		global $wpdb;
		
		$wpdb->insert( 'wp_role_scope_rs', array( "role_name" => "post_author",
		"topic" => "object", "src_or_tx_name" => "post","obj_or_term_id" => $post_id,"max_scope" => "object"),
		 array( '%s', '%s', '%s','%d') );
		 
		 $wpdb->insert( 'wp_role_scope_rs', array( "role_name" => "post_contributor",
		"topic" => "object", "src_or_tx_name" => "post","obj_or_term_id" => $post_id,"max_scope" => "object"),
		 array( '%s', '%s', '%s','%d','%s') );
		 
		 $wpdb->insert( 'wp_role_scope_rs', array( "role_name" => "post_editor",
		"topic" => "object", "src_or_tx_name" => "post","obj_or_term_id" => $post_id,"max_scope" => "object"),
		 array( '%s', '%s', '%s','%d') );
		 
		 $wpdb->insert( 'wp_role_scope_rs', array( "role_name" => "post_reader",
		"topic" => "object", "src_or_tx_name" => "post","obj_or_term_id" => $post_id,"max_scope" => "object"),
		 array( '%s', '%s', '%s','%d') );
		 
		 $wpdb->insert( 'wp_role_scope_rs', array( "role_name" => "private_post_reader",
		"topic" => "object", "src_or_tx_name" => "post","obj_or_term_id" => $post_id,"max_scope" => "object"),
		 array( '%s', '%s', '%s','%d') );
		 // if using a custom function, you need this
		$wpdb->insert( 'wp_user2role2object_rs', array( "user_id" => $user_id, "role_name" => "private_post_reader",
		"obj_or_term_id" => $post_id,"scope" => "object","src_or_tx_name" => "post","assigner_id" => 1),
		 array( '%d','%s','%d','%s','%s','%d' ) );
	}*/
?>