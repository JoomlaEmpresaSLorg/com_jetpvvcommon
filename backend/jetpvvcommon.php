<?php
/*
 *      Joomla Empresa TPVV Common Component
 *      @package Joomla Empresa TPVV Common Component
 *      @subpackage Content
 *      @author José António Cidre Bardelás
 *      @copyright Copyright (C) 2011-2015 José António Cidre Bardelás and Joomla Empresa. All rights reserved
 *      @license GNU/GPL v3 or later
 *      
 *      Contact us at info@joomlaempresa.com (http://www.joomlaempresa.es)
 *      
 *      This file is part of Joomla Empresa TPVV Common Component.
 *      
 *          Joomla Empresa TPVV Common Component is free software: you can redistribute it and/or modify
 *          it under the terms of the GNU General Public License as published by
 *          the Free Software Foundation, either version 3 of the License, or
 *          (at your option) any later version.
 *      
 *          Joomla Empresa TPVV Common Component is distributed in the hope that it will be useful,
 *          but WITHOUT ANY WARRANTY; without even the implied warranty of
 *          MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *          GNU General Public License for more details.
 *      
 *          You should have received a copy of the GNU General Public License
 *          along with Joomla Empresa TPVV Common Component.  If not, see <http://www.gnu.org/licenses/>.
 */
defined('_JEXEC') or die();

JLoader::import('joomla.filesystem.file');
$fileName = JPATH_ADMINISTRATOR . '/components/com_jetpvvcommon/key.php';
if(!JFile::exists($fileName)) {
	$config = JFactory::getConfig();
	$secretKey = version_compare(JVERSION, '3.0.0','ge') ? $config->get('secret') : $config->getValue('config.secret');
	$fileData = "<?php\ndefined('_JEXEC') or die();\n\ndefine('JETPVVCOMMON_KEY','" . $secretKey . "');";
	JFile::write($fileName, $fileData);
}

JLoader::import('helpers.jetpvvcommon', JPATH_COMPONENT_ADMINISTRATOR);
if(version_compare(JVERSION, '3.0.0','lt'))
{
	$doc = JFactory::getDocument();
	$doc->addStyleSheet(JURI::root() . 'administrator/components/com_jetpvvcommon/assets/css/joomla_25.css');
}
else {
	JHtml::_('bootstrap.framework');
}

$eSuperUsuario = false;
$app = JFactory::getApplication();
$JInput  = $app->input;
$method = $JInput->getMethod();
$senha  = $JInput->$method->get('senha', '', 'RAW');

$usuario = JFactory::getUser();
$versom = new JVersion;
if (version_compare(JVERSION, '1.5', 'eq')) {
	if($usuario->usertype == "Super Administrator")
	$eSuperUsuario = true;
}
else {
	$grupos = JAccess::getGroupsByUser($usuario->id);
	if(in_array(8, $grupos)){
		$eSuperUsuario = true;
	}
}
$jeTPVVToken = version_compare(JVERSION, '3.0.0','ge') ? JSession::getFormToken() : JUtility::getToken();
if(!$JInput->getBool($jeTPVVToken) && $eSuperUsuario) {
    echo '<h1>'.JText::_('JETPVV_COMMON_NOME').'</h1>';
    echo '<p>'.JText::_('JETPVV_COMMON_JE').'</p>';
    return false;
    }
elseif(!$JInput->getBool($jeTPVVToken) || !$eSuperUsuario) {
    echo '<h1 class="text-error text-center">'.JText::_('JETPVV_COMMON_ACESSO_RESTRITO').'</h1>';
    return false;
    }
?>
<div class="row-fluid">
<div class="span12">
<h1 class="text-info"><?php echo JText::_('JETPVV_COMMON_NOME'); ?></h1>
<div class="alert alert-info"><?php echo JText::_('JETPVV_COMMON_DESC'); ?></div>
<?php
$valorChave = $JInput->getString('valorchave', null );
$metodoPagamento = $JInput->get('key');
$idPagamento = $JInput->getInt('cid');
$tarefa = $JInput->get('tarefa');
$urlCompo = JURI::base().'index.php?option=com_jetpvvcommon&amp;layout=modal&amp;tmpl=component';
if ($metodoPagamento != '') {
	$chaveBD = JETPVvCommonHelper::getKey($metodoPagamento, $idPagamento);
	$forceReplace = is_null($chaveBD) ? true : false;
	$identificado = false;
	$identificationError = '';
	if($JInput->get('submit') != '') {
		$idResponse = JETPVvCommonHelper::checkPassword($eSuperUsuario, $usuario->username, $senha);
		if($idResponse && ($idResponse === (version_compare(JVERSION, '3.0.0','ge') ? JAuthentication::STATUS_SUCCESS : JAUTHENTICATE_STATUS_SUCCESS))) {
			$identificado = true;
		}
		else {
			$identificationError = $idResponse ? $idResponse : 64;
		}
	}
	if($identificado && empty($valorChave)) {
		echo "<form action=\"".$urlCompo."\" method=\"post\" class=\"form-horizontal\">\n";
		echo "<fieldset><legend>" .JText::_('JETPVV_COMMON_FORMULARIO_TROCO_CHAVE'). "</legend>\n";
		echo "<div class=\"alert alert-block\">" . JText::_('JETPVV_COMMON_FORMULARIO_LOGIN_YOURSELF_AND_INSERT_KEY') . "</div>";
		echo "<div class=\"control-group\">\n";
		echo "<label class=\"control-label\" for=\"valorchave\">" . JText::_('JETPVV_COMMON_CHAVE_ATUAL') . ": " . "</label>";
		echo "<div class=\"controls offset4\">";
		echo "<input class=\"input-xlarge\" type=\"text\" name=\"valorchave\" value=\"".$chaveBD."\" />\n";
		echo "</div></div>";
		echo "<div class=\"control-group\">\n";
		echo "<label class=\"control-label\" for=\"userName\">" . JText::_('JETPVV_COMMON_YOUR_USERNAME') . ": " . "</label>";
		echo "<div class=\"controls offset4\">";
		echo "<input type=\"text\" name=\"userName\" value=\"" . $usuario->username . "\" readonly />\n";
		echo "</div></div>";
		echo "<div class=\"control-group\">\n";
		echo "<label class=\"control-label\" for=\"senha\">" . JText::_('JETPVV_COMMON_INSERE_SENHA') . ": " . "</label>";
		echo "<div class=\"controls offset4\">";
		echo "<input type=\"password\" name=\"senha\" value=\"\" />\n";
		echo "</div></div>";
		echo "<div class=\"control-group\">\n";
		echo "<div class=\"controls offset4\">";
		echo "<input class=\"btn\" type=\"submit\" name=\"submit\" value=\"" . JText::_('JETPVV_COMMON_ENVIAR') . "\" />\n";
		echo "</div></div>";
		echo "<input type=\"hidden\" name=\"key\" value=\"$metodoPagamento\" />\n";
		echo "<input type=\"hidden\" name=\"cid\" value=\"$idPagamento\" />\n";
		echo "<input type=\"hidden\" name=\"tarefa\" value=\"".($chaveBD != '' || $forceReplace ? 'mudarchave' : 'adicionarchave')."\" />\n";
		echo "<input type=\"hidden\" name=\"".$jeTPVVToken."\" value=\"1\" />\n";
		echo "</fieldset>\n";
		echo "</form>\n";
	}
	elseif ($identificado && !empty($valorChave) && $tarefa == "adicionarchave") {
		JETPVvCommonHelper::setKey($metodoPagamento, $idPagamento, $valorChave, false);
		echo '<h1 class="text-success text-center">'.JText::_('JETPVV_COMMON_CHAVE_ADICIONADA').'</h1>';
		echo '<script type="text/javascript"> window.setTimeout(\'fechar();\', 800); function fechar() { window.parent.SqueezeBox.close(); }</script>';
	}
	elseif ($identificado && !empty($valorChave) && $tarefa == "mudarchave") {
		JETPVvCommonHelper::setKey($metodoPagamento, $idPagamento, $valorChave, true);
		echo '<h1 class="text-success text-center">'.JText::_('JETPVV_COMMON_CHAVE_MUDADA').'</h1>';
		echo '<script type="text/javascript"> window.setTimeout(\'fechar();\', 1000); function fechar() { window.parent.SqueezeBox.close(); }</script>';
	}
	// not authenticated
	else {
		if($identificationError != '') {
			$statusCodes = array(
					'1' => 'SUCCESS',
					'2' => 'CANCEL',
					'4' => 'FAILURE',
					'8' => 'EXPIRED',
					'16' => 'DENIED',
					'32' => 'UNKNOW',
					'64' => 'EMPTY PASSWORD',
			);

			echo "<span class=\"label label-important\">" . JText::_('JETPVV_COMMON_IDENTIFICATION_ERROR_CODE').' '.$statusCodes[$identificationError] . "</span>";
		}
		echo "<form action=\"".$urlCompo."\" method=\"post\" class=\"form-horizontal\">\n";
		echo "<fieldset><legend>" .JText::_('JETPVV_COMMON_FORMULARIO_TROCO_CHAVE'). "</legend>\n";
		echo "<div class=\"alert alert-block\">" . JText::_('JETPVV_COMMON_FORMULARIO_LOGIN_YOURSELF') . "</div>";
		echo "<div class=\"control-group\">\n";
		echo "<label class=\"control-label\" for=\"currentKey\">" . JText::_('JETPVV_COMMON_CHAVE_ATUAL') . ": " . "</label>";
		echo "<div class=\"controls offset4\">";
		echo "<input class=\"input-xlarge\" type=\"text\" name=\"currentKey\" value=\"" . ($chaveBD === '' ?  JText::_('JETPVV_COMMON_CHAVE_BD_VALEIRA') : ( $forceReplace ? JText::_('JETPVV_COMMON_CANT_DECRYPT_KEY') : JETPVvCommonHelper::asteriskPad($chaveBD, 4 ))) . "\" readonly />\n";
		echo "</div></div>";
		echo "<div class=\"control-group\">\n";
		echo "<label class=\"control-label\" for=\"userName\">" . JText::_('JETPVV_COMMON_YOUR_USERNAME') . ": " . "</label>";
		echo "<div class=\"controls offset4\">";
		echo "<input type=\"text\" name=\"userName\" value=\"" . $usuario->username . "\" readonly />\n";
		echo "</div></div>";
		echo "<div class=\"control-group\">\n";
		echo "<label class=\"control-label\" for=\"senha\">" . JText::_('JETPVV_COMMON_INSERE_SENHA') . ": " . "</label>";
		echo "<div class=\"controls offset4\">";
		echo "<input type=\"password\" name=\"senha\" value=\"\" />\n";
		echo "</div></div>";
		echo "<div class=\"control-group\">\n";
		echo "<div class=\"controls offset4\">";
		echo "<input class=\"btn\" type=\"submit\" name=\"submit\" value=\"" . JText::_('JETPVV_COMMON_ENVIAR') . "\" />\n";
		echo "</div></div>";
		echo "<input type=\"hidden\" name=\"key\" value=\"$metodoPagamento\" />\n";
		echo "<input type=\"hidden\" name=\"cid\" value=\"$idPagamento\" />\n";
		echo "<input type=\"hidden\" name=\"".$jeTPVVToken."\" value=\"1\" />\n";
		echo "</fieldset>\n";
		echo "</form>\n";
	}
}
else echo '<h1 class="text-error text-center">'.JText::_('JETPVV_COMMON_METODO_PAGAMENTO_NOM_INDICADO').'</h1>';
?>
</div>
</div>