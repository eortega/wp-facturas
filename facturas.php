<?php
/*
Plugin Name: Facturas
Plugin URI: http://wordpress.org/extend/plugins/update-inventory/
Description: This is plugin upload invoices on bulk using zip format
Author: WSI
Version: 0.1
Author URI: 
*/
?>

<?php

/** PHPExcel_IOFactory */
include plugin_dir_path( __FILE__ ).'/Classes/PHPExcel/IOFactory.php';


register_activation_hook(__FILE__, 'prowp_facturas_install');
register_deactivation_hook(__FILE__, 'prowp_facturas_deactivate()');


function prowp_facturas_install(){
	echo "Facturas Activadas";
}

function prowp_facturas_deactivate(){
	echo "Facturas Desactivadas";
}

function prowp_facturas_menu(){
	add_menu_page('Pagina de Carga de Facturas', 'Carga de Facturas', 'manage_options', 'facturas_menu', 'facturas_page'
		,plugins_url('images/menu.png', __FILE__));

	//add_submenu_page('prowp_main_menu', 'Subir Archivo', 'Subir Archivo', 'manage_options', 'subir_archivo_inventario', 'prowp_upload_file');
	//add_action('admin_init', 'prowp_register_settings');
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
		
		//print_r($_FILES);
		
		if ((($_FILES["file"]["type"] == "application/zip"))&& in_array($extension, $allowedExts)){
			
		  if ($_FILES["file"]["error"] > 0){
		    echo "Error: " . $_FILES["file"]["error"] . "<br>";
		    
			}else{
				$new_filename=date('d_m_Y_H_i_s', strtotime('now'));
				
		  /*  echo "Upload: " . $_FILES["file"]["name"] . "<br>";
		    echo "Type: " . $_FILES["file"]["type"] . "<br>";
		    echo "Size: " . ($_FILES["file"]["size"] / 1024) . " kB<br>";
		    echo "Stored in: " . $_FILES["file"]["tmp_name"]."<br/>";
			echo "Nuevo Nombre: ".$new_filename;
			*/
		    if(file_exists("upload/{$new_filename}")){
		    	echo $new_filename . " already exists. ";
				
		    }else{
				
				$uploadfile = plugin_dir_path( __FILE__ ).'upload/'.$new_filename.'.zip'; // 
				
				  if(move_uploaded_file($_FILES["file"]["tmp_name"], $uploadfile )){
					  echo "<br/>El Archivo se ha subido correctamente.";
					  
					  //Descompresión de archivo zip
					  unzip_facturas($uploadfile);
					  
				  }else{
					  echo "<pre>";
					  echo "Error: No se ha podido subir el archivo, por favor inténte nuevamente.";
					  echo "</pre>";
				  }
				  
		    }
			  
			  
			  
		    }
		  
		  }else{
			  echo "El formato para subir las Facturas debe ser en .zip <br/>";
//			  print_r($allowedExts);
		  }
		  
		  
	
	}else{
		
		echo '<form action="'.admin_url().'admin.php?page=facturas_menu" method="post"	enctype="multipart/form-data">';
		echo '<label for="file">Archivo:</label>';
		echo '<input type="file" name="file" id="file"><br/>';
		echo '<input type="submit" name="submit" value="Envíar">';
		echo '</form>';
		
	}
}

	
	function unzip_facturas($path){
		$array_files= array();
		$master_file = null;
		$header = null;
		$header_data = null;
		$num_rows = 0;
		$info_values = null;
		
		
		//echo "<br/> Descomprimiento las facturas de {$path}";
		// Crear folder para guardar las facturas de forma temporal
		$extract_to = str_replace ('.zip' , '', $path);
		
			
		
		if(!mkdir($extract_to, 0700, true)){
		    die('Fallo al crear carpetas...');
		}else{
			chmod($extract_to, 0700);
			//echo "<br/> Descomprimiento las facturas en {$extract_to}";
			
			$zip = new ZipArchive;
		
			if ($zip->open($path) === TRUE) {
			    $zip->extractTo($extract_to);
			    $zip->close();
			    echo '<br/>La carpeta se descomprimió correctamente';
				
				$dir    = $extract_to;
				$files1 = scandir($dir);
				echo "<pre>";
					end($files1);
					$last_id=key($files1);
					$contenedora = $extract_to.'/'.$files1[$last_id];
				//	echo "Carpeta contenedora: ".$contenedora;
				//	echo "<br/>";
					$files2 = scandir($contenedora);
					echo "<br/>";
					
					
					foreach($files2 as $f){
						$file_parts = pathinfo($contenedora.'/'.$f);
						$base_name = strtolower($file_parts['basename']);

						 if(($base_name =='master.xls')|| ($base_name=='master.xlsx')){
							 $master_file = $contenedora.'/'.$f;	
						 }
							 
						if(	($file_parts['extension']=='xml') 
							|| ($file_parts['extension']=='pdf') 
							||  ($file_parts['extension']=='xls')
							|| ($file_parts['extension']=='xlsx')
							){
							array_push($array_files,$contenedora.'/'.$f);
						}
					
					}	
					
					echo "<br/>";
					if(count($array_files) > 0){
						
						//var_dump($array_files);
						
						if(!is_null($master_file)){
						
							//echo "<br/> <div style='background-color:blue; color: white; width:100%;'> <b>Archivo Maestro Encontrado</b><br/>{$master_file}</div>";
							echo "Archivo maestro encontrado";
						
							//Verificar formato del maestro
							//echo 'Loading file ',pathinfo($inputFileName,PATHINFO_BASENAME),' using IOFactory to identify the format<br />';
							$objPHPExcel = PHPExcel_IOFactory::load($master_file);



							$sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
							
							//var_dump($sheetData);
							$num_rows = count($sheetData);
							
							foreach($sheetData as $key=>$r){
								$values= array_values($r);
								$upper_arr= array_map('strtoupper', $values);
								
								$haystack = array('FOLIO', 'CLIENTE', 'SERIE');

								if(count(array_intersect($haystack, $upper_arr)) > 2){
								    // all of $target is in $haystack
									$header=$key;
									$header_data = $r;
									break;
									
								}
								
							}//end foreach
								
							if(!is_null($key)){
								$info_values= array_slice($sheetData, $header);
							}else
								echo "<br/>No se encontró la cabecera";
							
							

							if(count($header_data)){
								echo "<br/>Header Encontrado<br/>";
								//print_r($header_data);
								
							//	echo "La llave se formará con {$header_data['C']} + {$header_data['D']}";
								
								if(count($info_values) > 0){
									$reg = count($info_values);
									echo "<br/>Se encontraron {$reg} registros<br/>";
									
									foreach($info_values as $key=>$factura){
										//print_r($factura);
										$info_values[$key]['key_files']=$factura['C'].preg_replace('/[^0-9]/', '', $factura['D']);
										//$factura['folio']='llave_files';
										
									}
									
									/**
									Buscar los archivos basados en el formato
									**/
									search_files($info_values, $contenedora);
									/**
									 Crear los post adjuntando los archivos y relacionarlos con los usuario $master_file
									 **/
									//print_r($info_values);
								}else
									echo "No se encotraron relaciones de facturas con clientes.";
							}else{
								echo "No se encotraron los encabezados en el archivo maestro.";
							}
								
							
						
						
						}else{
							echo "<br/> <div style='background-color:red; color: white; width:100%;'> El archivo maestro no se encontró. </div>";
						}
						
					}else
						echo "La carpeta no contiene ningun archivo con el formato esperado.";
					
				echo "</pre>";
				
				
				
								
			} else {
			    echo '<br/>Los archivos no se han podido extraer, inténte nuevamente por favor.';
			}
			
		}
		
		
		
		
	}
	
	function search_files($relacion, $path){
		$counter=1;
		
		echo "<br/>Buscando los archivos necesarios:<br/>";
		echo "<table style ='width:100%;'>";
			echo "<tr>";
				echo "<th>No.</th>";
				echo "<th>Llave</th>";
				echo "<th>Cliente</th>";
				echo "<th>PDF</th>";
				echo "<th>XLS</th>";
			echo "</tr>";
		
		
		foreach($relacion as $factura){
			$pdf =  (file_exists($path.'/'.$factura['key_files'].'.pdf') || file_exists($path.'/'.$factura['key_files'].'.PDF')) ? 'ok' : 'no'; 
			$xml =  (file_exists($path.'/'.$factura['key_files'].'.xml') || file_exists($path.'/'.$factura['key_files'].'.XML'))? 'ok' : 'no'; 
		
			
			echo "<tr>";
				echo "<td style = 'text-align:center; '>".$counter."</td>";
				echo "<td style = 'text-align:center; '>".$factura['key_files']."</td>";
				echo "<td style = 'text-align:center; '>".$factura['E']."</td>";
				echo "<td style = 'text-align:center; '> <img alt='Ok' src='".plugins_url('images/'.$pdf.'.png', __FILE__)."'/></td>";
				echo "<td style = 'text-align:center; '><img alt='Error' src='".plugins_url('images/'.$xml.'.png', __FILE__)."'/></td>";
			echo "</tr>";
			
			$counter++;
		}
		echo "</table>";
	}
?>