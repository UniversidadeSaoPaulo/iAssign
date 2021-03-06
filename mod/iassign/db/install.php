<?php
/**
 * Script for install all ilm in iassign table.
 *
 * This file replaces:
 * STATEMENTS section in db/install.xml
 * lib.php/modulename_install() post installation hook partially defaults.php.
 *
 * Release Notes:
 * - v 1.4 2014/02/27
 * 		+ Fix error in install without iLM files.
 * 		+ Not remove iLM files if debug mode is active.
 * 		+ Allow insert one or more tags in iLM config table. 
 * - v 1.3 2013/12/12
 * 		+ Language support in iLM.
 * - v 1.2 2013/09/19
 * 		+ Change path file for ilm, consider version in pathname.
 *
 * @author Patricia Alves Rodrigues
 * @author Leônidas O. Brandão
 * @author Luciano Oliveira Borges
 * @version v 1.4 2014/02/27
 * @package mod_iassign_db
 * @since 2010/09/27
 * @copyright iMatica (<a href="http://www.matematica.br">iMath</a>) - Computer Science Dep. of IME-USP (Brazil)
 *
 * <b>License</b>
 *  - http://opensource.org/licenses/gpl-license.php GNU Public License
 */
require_once ($CFG->dirroot . '/mod/iassign/locallib.php');

function xmldb_iassign_install() {
	global $DB, $USER, $CFG;
	
	if(class_exists('plugin_manager'))
		$pluginman = plugin_manager::instance();
	else
		$pluginman = core_plugin_manager::instance();
	$plugins = $pluginman->get_plugins();
	
	$ilms = array(
			array_combine(	array('name', 'url', 'version', 'description', 'extension', 'file_jar', 'file_class', 'width', 'height', 'enable','timemodified', 'author', 'timecreated', 'evaluate'),
					array('iGeom', 'http://www.matematica.br/igeom', '5.9.12', '{"en":"Interactive Geometry on the Internet.","pt_br":"Geometria Interativa na Internet."}', 'geo', 'iGeom.jar', 'IGeomApplet.class', 800, 600, 1, time(), $USER->id, time(), 1)
			),
			array_combine(	array('name', 'url', 'version', 'description', 'extension', 'file_jar', 'file_class', 'width', 'height', 'enable','timemodified', 'author', 'timecreated', 'evaluate'),
					array('iGraf', 'http://www.matematica.br/igraf', '4.4.0.10', '{"en":"Interactive Graphic on the Internet.","pt_br":"Gráficos Interativos na Internet."}', 'grf', 'iGraf.jar', 'igraf.IGraf.class', 840, 600, 1, time(), $USER->id, time(), 1)
			),
			array_combine(	array('name', 'url', 'version', 'description', 'extension', 'file_jar', 'file_class', 'width', 'height', 'enable','timemodified', 'author', 'timecreated', 'evaluate'),
					array('iComb', 'http://www.matematica.br/icomb', '0.9.0', '{"en":"Combinatorics Interactive on the Internet.","pt_br":"Combinatória Interativa na Internet."}', 'icb,cmb', 'iComb.jar', 'icomb.IComb.class', 750, 685, 1, time(), $USER->id, time(), 1)
			),
			array_combine(	array('name', 'url', 'version', 'description', 'extension', 'file_jar', 'file_class', 'width', 'height', 'enable','timemodified', 'author', 'timecreated', 'evaluate'),
					array('iVProg', 'http://www.matematica.br/ivprog', '0.3.1', '{"en":"Visual Interactive Programming on the Internet.","pt_br":"Programação visual interativa na Internet."}', 'ivp', 'iVprog.jar', 'edu.cmu.cs.stage3.alice.authoringtool.JAlice.class', 800, 600, 1, time(), $USER->id, time(), 0)
			),
			array_combine(	array('name', 'url', 'version', 'description', 'extension', 'file_jar', 'file_class', 'width', 'height', 'enable','timemodified', 'author', 'timecreated', 'evaluate'),
					array('iTangram2', 'http://www.matematica.br/itangram', '0.4.3', '{"en":"The Objective of the game is to reproduce the form of the model using all 7 pieces of iTangram.","pt_br":"O Objetivo do jogo é reproduzir a forma do modelo usando todas as 7 peças do iTangram."}', 'itg2', 'iTangram2.jar', 'ilm.line.itangram2.Tangram', 800, 600, 1, time(), $USER->id, time(), 1)
			),
			array_combine(	array('name', 'url', 'version', 'description', 'extension', 'file_jar', 'file_class', 'width', 'height', 'enable','timemodified', 'author', 'timecreated', 'evaluate'),
					array('Risko', 'http://risko.pcc.usp.br/', '2.1.94', '{"en":"Interactive computational tool for teaching geometry.","pt_br":"Ferramenta computacional interativa para o ensino de geometria."}', 'rsk', 'Risko.jar', 'RiskoApplet.class', 800, 600, 1, time(), $USER->id, time(), 0)
			)
	);
	$tags = array(
		'iGeom' => array(),
		'iGraf' => array(),
		'iComb' => array(),
		'iVProg' => array(),
		'iTangram2' => array(),
		'Risko' => array(
				array('iassign_ilmid' => '', 'param_type' => 'multiple', 'param_name' => 'MA_PARAM_noInstruments', 'param_value' => 'pencil, compass, triangle_45, triangle_60, magnifier, color_pens, player', 'description' => '<p>Selecione o código dos instrumentos que não deseja utilizar na atividade.</p><p>Códigos: <br /><b>pencil</b>(Lápis)<br /><b>compass</b>(Compasso) <br /><b>triangle_45</b>(Esquadro de 45º) <br /><b>triangle_60</b>(Esquadro de 60º) <br /><b>magnifier</b>(Lupa) <br /><b>color_pens</b>(Caixa de Lápis) <br /><b>player</b>(Player)</p>', 'visible' => '1')
		)
	);

	$fs = get_file_storage();
	$is_debug = $CFG->debugdisplay;
	$context = context_system::instance();
	$ilm_path = $CFG->dirroot . "/mod/iassign/ilm/";
	$is_delete = true;
	$is_ilm_path = is_dir($ilm_path);
	
	if($is_ilm_path) {
	
		foreach ($ilms as $ilm) {
	
			$filenames = explode(",", $ilm['file_jar']);
			$file_jar = array();
			foreach($filenames as $filename) {
	
				$file_ilm = array(
						'userid' => $USER->id, // ID of user
						'contextid' => $context->id, // ID of context
						'component' => 'mod_iassign',     // identify of module
						'filearea' => 'ilm',     // file area of iLM files
						'itemid' => 0,               // zero for defaults iLM
						'filepath' => '/iassign/ilm/'.utils::format_pathname($ilm['name']).'/'.utils::format_pathname($ilm['version']).'/', 
						'filename' => $filename);
				$file_ilm = @$fs->create_file_from_string($file_ilm, file_get_contents($ilm_path.$filename));
	
				if($file_ilm && !$is_debug)
					$is_delete &= @unlink($ilm_path.$filename);
	
				array_push($file_jar, $file_ilm->get_id());
			}
			if(!empty($file_jar)){
				$ilm['file_jar'] = implode(",", $file_jar);
				$ilm_id = $DB->insert_record('iassign_ilm', $ilm);
				if(!empty($tags[$ilm['name']])) {
					foreach ($tags[$ilm['name']] as $tag) {
						$tag['iassign_ilmid'] = $ilm_id;
						$DB->insert_record('iassign_ilm_config', $tag);
					}
				}
			}
		}
		
		if(!$is_debug)
			$is_delete &= @unlink($ilm_path . "index.html");
	
		if($is_delete && !$is_debug) {
			@rmdir($ilm_path);
				
			// log event -----------------------------------------------------
			log::add_log('install', 'version: '.$plugins['mod']['iassign']->versiondb);
			// log event -----------------------------------------------------
		}
	}
	
	if(!$is_ilm_path)
		print_error('error_ilm_path', 'iassign');
	else if(!$is_delete)
		print_error('error_ilm_delete', 'iassign');
	
}
