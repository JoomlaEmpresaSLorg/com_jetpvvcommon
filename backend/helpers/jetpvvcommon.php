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

class JETPVvCommonHelper
{
	private static function getSecret()
	{
		JLoader::import('joomla.filesystem.file');
		$fileName = JPATH_ADMINISTRATOR . '/components/com_jetpvvcommon/key.php';
		if(JFile::exists($fileName)) {
			require_once $fileName;
		}
		else {
			$config = JFactory::getConfig();
			define('JETPVVCOMMON_KEY',(version_compare(JVERSION, '3.0.0', 'ge') ? $config->get('secret') : $config->getValue('config.secret')));
		}
	}

	public static function getKey($method, $idPayment)
	{
		$decryptFunction = 'AES_DECRYPT';
		$config = JFactory::getConfig();
		self::getSecret();

		$db = JFactory::getDBO();
		$q = "SELECT `id` FROM #__je_tpvv_common WHERE payment_key='" . $method . "' AND virtuemart_payment_id='$idPayment'";
		$db->setQuery($q);
		$keyExists = $db->loadResult();

		$q = "SELECT " . $decryptFunction . "(payment_value,'" . JETPVVCOMMON_KEY . "') AS `key` FROM #__je_tpvv_common WHERE payment_key='" . $method . "' AND virtuemart_payment_id='$idPayment'";
		$db->setQuery($q);
		$dbResult = $db->loadObject();

		$key = isset($dbResult->key) ? $dbResult->key : ($keyExists ? null : '');

		return $key;
	}

	public static function setKey($method, $idPayment, $key, $replace = true)
	{
		$encryptFunction = 'AES_ENCRYPT';
		$config = JFactory::getConfig();
		self::getSecret();

		$db = JFactory::getDBO();
		if($replace) {
			$q = "UPDATE #__je_tpvv_common SET payment_value = " . $encryptFunction . "('$key','" . JETPVVCOMMON_KEY . "') WHERE payment_key='$method' AND virtuemart_payment_id='$idPayment';";
		}
		else {
			$q = "INSERT INTO #__je_tpvv_common (`virtuemart_payment_id`, `payment_key`, `payment_value`) VALUES ('$idPayment', '$method', " . $encryptFunction . "('$key','" . JETPVVCOMMON_KEY . "'))";
		}
		$db->setQuery($q);
		return $db->query();
	}

	public static function asteriskPad($str, $display_length, $reversed = false) {
		$total_length = strlen($str);

		if($total_length > $display_length) {
			if( !$reversed) {
				for($i = 0; $i < $total_length - $display_length; $i++) {
					$str[$i] = "*";
				}
			}
			else {
				for($i = $total_length-1; $i >= $total_length - $display_length; $i--) {
					$str[$i] = "*";
				}
			}
		}
		return($str);
	}

	public static function checkPassword($eSuperUsuario, $nome, $senha, $twoFactor = null) {
		if($eSuperUsuario) {
			if(empty($senha)) {
				return false;
			}
			$senhaCifrada = md5($senha);
			if (!$nome || !$senha) {
				return false;
			}
			else {
				$credenciais = array();
				$credenciais['username'] = $nome;
				$credenciais['password'] = $senha;
				$credenciais['secretkey'] = $twoFactor;

				$opcoes = array();
				jimport( 'joomla.user.authentication');
				$autenticacom = JAuthentication::getInstance();
				$resposta = $autenticacom->authenticate($credenciais, $opcoes);

					/*if($resposta->status === (version_compare(JVERSION, '3.0.0','ge') ? JAuthentication::STATUS_SUCCESS : JAUTHENTICATE_STATUS_SUCCESS )) {
					return true;
				} else {
					return false;
					}*/

					return $resposta->status;
			}
		}
		return false;
	}
}
