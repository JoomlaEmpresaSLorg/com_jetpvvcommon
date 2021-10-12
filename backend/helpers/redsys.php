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

class JETPVvCommonHelperRedsys
{
	public static function createSendSignature($method, $idPayment, $redsysOrderParamsArray)
	{
		if(phpversion() < 7 && !function_exists('mcrypt_encrypt'))
		{
			throw new RuntimeException('The mcrypt_encrypt function is not available.', 500);
		}
		elseif(!function_exists('openssl_encrypt'))
		{
			throw new RuntimeException('The openssl_encrypt function is not available.', 500);
		}

		if (!function_exists('hash_hmac'))
		{
			throw new RuntimeException('The hash_hmac function is not available.', 500);
		}

		$key = base64_decode(JETPVvCommonHelper::getKey($method, $idPayment));
		$redsysOrderParamsJSon = json_encode($redsysOrderParamsArray);
		$redsysOrderParamsB64 = base64_encode($redsysOrderParamsJSon);

		$iv = "\000\000\000\000\000\000\000\000";
		try
		{
			if(phpversion() < 7)
			{
			$cipherText = mcrypt_encrypt(MCRYPT_3DES, $key, $redsysOrderParamsArray['Ds_Merchant_Order'], MCRYPT_MODE_CBC, $iv);
		}
			else
			{
				$long = ceil(strlen($redsysOrderParamsArray['Ds_Merchant_Order']) / 8) * 8;
				$cipherText = substr(openssl_encrypt($redsysOrderParamsArray['Ds_Merchant_Order'] . str_repeat("\0", $long - strlen($redsysOrderParamsArray['Ds_Merchant_Order'])), 'des-ede3-cbc', $key, OPENSSL_RAW_DATA, $iv), 0, $long);
			}
		}
		catch (Exception $e)
		{
			throw new RuntimeException('Can\'t create cipherText', 500);
		}

		try
		{
			$signature = hash_hmac('sha256', $redsysOrderParamsB64, $cipherText, true);
		}
		catch (Exception $e)
		{
			throw new RuntimeException('Can\'t create signature', 500);
		}

		return base64_encode($signature);
	}

	public static function createNotifySignature($method, $idPayment, $redsysOrderParamsB64)
	{
		$key = base64_decode(JETPVvCommonHelper::getKey($method, $idPayment));
		$redsysOrderParamsJSon = base64_decode(strtr($redsysOrderParamsB64, '-_', '+/'));
		$redsysOrderParamsArray = json_decode($redsysOrderParamsJSon, true);

		$iv = "\000\000\000\000\000\000\000\000";
		try
		{
			if(phpversion() < 7)
			{
		$cipherText = mcrypt_encrypt(MCRYPT_3DES, $key, $redsysOrderParamsArray['Ds_Order'], MCRYPT_MODE_CBC, $iv);
			}
			else
			{
				$long = ceil(strlen($redsysOrderParamsArray['Ds_Order']) / 8) * 8;
				$cipherText = substr(openssl_encrypt($redsysOrderParamsArray['Ds_Order'] . str_repeat("\0", $long - strlen($redsysOrderParamsArray['Ds_Order'])), 'des-ede3-cbc', $key, OPENSSL_RAW_DATA, $iv), 0, $long);
			}
		}
		catch (Exception $e)
		{
			throw new RuntimeException('Can\'t create cipherText', 500);
		}

		try
		{
		$signature = hash_hmac('sha256', $redsysOrderParamsB64, $cipherText, true);
		}
		catch (Exception $e)
		{
			throw new RuntimeException('Can\'t create signature', 500);
		}

		return strtr(base64_encode($signature), '+/', '-_');
	}

	public static function getPOSResponseErrorText($POSResponse)
	{
		$lang = JFactory::getLanguage();
		$lang->load('com_jetpvvcommon', JPATH_ADMINISTRATOR, 'en-GB', true);
		$lang->load('com_jetpvvcommon', JPATH_ADMINISTRATOR, null, true);

		$POSResponse = (int)$POSResponse;
		$errorTexts = array(
			101 => 'JETPVV_REDSYS_EXPIRED_CARD',
			102 => 'JETPVV_REDSYS_TEMPORARILY_SUSPENDED_CARD',
			104 => 'JETPVV_REDSYS_TRANSACTION_NOT_ALLOWED',
			106 => 'JETPVV_REDSYS_PIN_ATTEMPTS_EXCEEDED',
			116 => 'JETPVV_REDSYS_INSUFFICIENT_FUNDS',
			118 => 'JETPVV_REDSYS_UNREGISTERED_CARD',
			125 => 'JETPVV_REDSYS_INEFFECTIVE_CARD',
			129 => 'JETPVV_REDSYS_WRONG_SEC_CODE',
			180 => 'JETPVV_REDSYS_UNKNOW_CARD',
			184 => 'JETPVV_REDSYS_AUTH_ERROR',
			190 => 'JETPVV_REDSYS_DENIED_WITHOUT_EXPLANATION',
			191 => 'JETPVV_REDSYS_WRONG_EXPIRATION_DATE',
			202 => 'JETPVV_REDSYS_FRAUD_SUSPICIOUS_CARD',
			904 => 'JETPVV_REDSYS_COMMERCE_NOT_REGISTERED_IN_FUC',
			909 => 'JETPVV_REDSYS_SYSTEM_ERROR',
			912 => 'JETPVV_REDSYS_BANK_NOT_AVAILABLE',
			913 => 'JETPVV_REDSYS_REPEATED_ORDER',
			944 => 'JETPVV_REDSYS_INCORRECT_SESSION',
			950 => 'JETPVV_REDSYS_REFUND_OPERATION_NOT_ALLOWED',
			9064 => 'JETPVV_REDSYS_CARD_POSITONS_NUMBER_INCORRECT',
			9078 => 'JETPVV_REDSYS_OPERATION_NOT_ALLOWED_FOR_THIS_CARD',
			9093 => 'JETPVV_REDSYS_NONEXISTENT_CARD',
			9094 => 'JETPVV_REDSYS_INTERNATIONAL_SERVERS_REJECT',
			9104 => 'JETPVV_REDSYS_SECURE_COMMERCE_AND_USER_NOT_SECURE',
			9218 => 'JETPVV_REDSYS_COMMERCE_DOESNT_ALLOW_SECURE_OPERATIONS',
			9253 => 'JETPVV_REDSYS_CARD_DOESNT_CHECK_DIGIT',
			9256 => 'JETPVV_REDSYS_COMMERCE_CANT_PREAUTHORIZE',
			9257 => 'JETPVV_REDSYS_CARD_CANT_PREAUTHORIZE',
			9261 => 'JETPVV_REDSYS_STOPPED_OPERATION_FOR_EXCEEDED_RESTRICTIONS',
			9912 => 'JETPVV_REDSYS_BANK_NOT_AVAILABLE',
			9915 => 'JETPVV_REDSYS_CANCELLED_BY_USER',
			9928 => 'JETPVV_REDSYS_REFEREED_AUTH_CANCELLED_BY_SIS',
			9929 => 'JETPVV_REDSYS_REFEREED_AUTH_CANCELLED_BY_COMMERCE',
			9997 => 'JETPVV_REDSYS_PROCESSING_ANOTHER_TRANSACTION_WITH_SAME_CARD',
			9998 => 'JETPVV_REDSYS_PROCESSING_CARD_DATA_REQUEST',
			9999 => 'JETPVV_REDSYS_REDIRECTED_TO_ISSUER_FOR_AUTHENTICATION',
		);

		$errorText = array_key_exists($POSResponse, $errorTexts) ? JText::_($errorTexts[$POSResponse]) : JText::_('JETPVV_REDSYS_RESPONSE_ERROR_UNKNOW');
		return $errorText . ' (' . $POSResponse . ')';
	}
}
