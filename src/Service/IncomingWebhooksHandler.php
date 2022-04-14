<?php
/*
 * @copyright   Copyright (C) 2010-2022 Combodo SARL
 * @license     http://opensource.org/licenses/AGPL-3.0
 */

namespace Combodo\iTop\Extension\TawkIntegration\Service;

use AttributeText;
use BinaryExpression;
use Combodo\iTop\Extension\TawkIntegration\Helper\ConfigHelper;
use DBObjectSearch;
use DBObjectSet;
use Exception;
use FieldExpression;
use IssueLog;
use MetaModel;
use utils;
use VariableExpression;

/**
 * Class IncomingWebhooksHandler
 *
 * Handle incoming webhooks sent by the tawk.to platform.
 * @link https://developer.tawk.to/webhooks/
 *
 * @package Combodo\iTop\Extension\TawkIntegration\Service
 * @author  Guillaume Lajarige <guillaume.lajarige@combodo.com>
 * @since 1.1.0
 */
class IncomingWebhooksHandler
{
	/**
	 * @var string Each webhook event is signed via a Hash-based Message Authentication Code (HMAC) using the webhooks secret key. The HMAC-SHA1 algorithm is used to generate the webhook payload signature. The signature is passed along with each request in the headers as ‘X-Tawk-Signature.’
	 */
	protected $sSignature;
	/** @var false|string Raw received payload */
	protected $sPayload;
	/** @var array|false|null Parsed JSON data from the $sPayload */
	protected $data;

	public function __construct()
	{
		$this->sSignature = $_SERVER['HTTP_X_TAWK_SIGNATURE'];
		$this->sPayload = file_get_contents('php://input');
		$this->data = json_decode($this->sPayload, true);

		$this->CheckAccess();
		$this->CheckConsistency();
	}

	/**
	 * Process the operation by calling the right callback (static::OperationXXX) depending on the event code
	 *
	 * @throws \Exception
	 */
	public function HandleOperation()
	{
		// Prepare operation callback name from event code
		$sEventCode = $this->data['event'];
		$sEventCallbackName = 'Operation';
		$aEventParts = explode(':', $sEventCode);
		foreach ($aEventParts as $sEventPart) {
			$sEventCallbackName .= ucfirst($sEventPart);
		}

		// Check if callback exists
		if (false === is_callable([static::class, $sEventCallbackName])) {
			$sErrorMessage = ConfigHelper::GetModuleCode().': Callback method for event not found';
			IssueLog::Error($sErrorMessage, ConfigHelper::GetModuleCode(), [
				'event' => $sEventCode,
				'callback_method' => $sEventCallbackName,
			]);
			throw new Exception($sErrorMessage);
		}

		return $this->$sEventCallbackName();
	}

	/**
	 * @return string The friendlyname of the created ticket
	 *
	 * @throws \ArchivedObjectException
	 * @throws \CoreCannotSaveObjectException
	 * @throws \CoreException
	 * @throws \CoreUnexpectedValue
	 * @throws \CoreWarning
	 * @throws \MySQLException
	 * @throws \OQLException
	 * @used-by \Combodo\iTop\Extension\TawkIntegration\Service\IncomingWebhooksHandler::HandleOperation()
	 */
	protected function OperationTicketCreate()
	{
		/** @var array $aConf */
		$aConf = ConfigHelper::GetModuleSetting('webhooks.create_ticket');
		// Check configuration consistency
		if ((false === isset($aConf['ticket_class']))
		|| (false === is_array($aConf['ticket_default_values']))) {
			$sErrorMessage = ConfigHelper::GetModuleCode().': Wrong configuration for "create_ticket" webhook, check documentation';
			IssueLog::Error($sErrorMessage, ConfigHelper::GetModuleCode(), [
				'configuration' => $aConf,
			]);
			throw new Exception($sErrorMessage);
		}

		// Prepare ticket
		/** @var string $sTicketClass */
		$sTicketClass = $aConf['ticket_class'];
		$oTicket = MetaModel::NewObject($sTicketClass, $aConf['ticket_default_values']);

		// Look for matching caller
		$sCallerFriendlyname = $this->data['requester']['name'];
		$sCallerEmail = (isset($this->data['requester']['email']) && false === is_null($this->data['requester']['email'])) ? $this->data['requester']['email'] : null;
		$oCaller = $this->ContactLookupFromEmail($sCallerFriendlyname, $sCallerEmail);
		if (is_null($oCaller)) {
			$sErrorMessage = ConfigHelper::GetModuleCode().': No match found for the Contact';
			IssueLog::Error($sErrorMessage, ConfigHelper::GetModuleCode(), [
				'caller_friendlyname' => $sCallerFriendlyname,
				'caller_email' => $sCallerEmail,
			]);
			throw new Exception($sErrorMessage);
		}

		// Fill ticket
		// - Caller
		$oTicket->Set('org_id', $oCaller->Get('org_id'));
		$oTicket->Set('caller_id', $oCaller->GetKey());
		// - Origin
		$oTicket->Set('origin', 'chat');
		// - Title
		$oTicket->Set('title', $this->data['ticket']['subject']);
		// - Description
		$sDescription = $this->data['ticket']['message'];
		//   Convert to HTML if necessary
		$oDescriptionAttDef = MetaModel::GetAttributeDef($sTicketClass, 'description');
		if (($oDescriptionAttDef instanceof AttributeText) && ($oDescriptionAttDef->GetFormat() === 'html')) {
			$sDescription = utils::TextToHtml($sDescription);
		}
		$oTicket->Set('description', $sDescription);
		// - Tawk.to ref
		$oTicket->Set('tawkto_ref', $this->data['ticket']['humanId']);

		$oTicket->DBInsert();
		return $oTicket->GetRawName();
	}

	/**
	 * Check if the incoming webhook is legit and consistent, if not an exception is thrown.
	 *
	 * @throws \Exception
	 */
	protected function CheckAccess()
	{
		// Retrieve configured secret key
		$sSecretKey = ConfigHelper::GetModuleSetting('webhooks.secret_key');
		if (strlen($sSecretKey) === 0) {
			$sErrorMessage = ConfigHelper::GetModuleCode().': Parameter "webhooks.secret_key" must be set in the module\'s parameters for incoming webhooks to work';
			IssueLog::Error($sErrorMessage, ConfigHelper::GetModuleCode(), ['webhooks.secret_key' => $sSecretKey]);
			throw new Exception($sErrorMessage);
		}

		// Verify secret key
		$sDigest = hash_hmac('sha1', $this->sPayload, $sSecretKey);
		if ($sDigest !== $this->sSignature) {
			$sErrorMessage = ConfigHelper::GetModuleCode().': Signature does not match payload and secret key';
			IssueLog::Error($sErrorMessage, ConfigHelper::GetModuleCode(), [
				'signature' => $this->sSignature,
				'digest (hash_hmac sha1)' => $sDigest,
				'secret' => $sSecretKey,
				'payload' => $this->sPayload,
			]);
			throw new Exception($sErrorMessage);
		}
	}

	/**
	 * Check if the received payload is well-formed
	 * @throws \Exception
	 */
	protected function CheckConsistency()
	{
		// Verify payload
		if (false === is_array($this->data)) {
			// Redecode on purpose to get json last error message
			$aData = json_decode($this->sPayload, true);

			$sErrorMessage = ConfigHelper::GetModuleCode().': Invalid payload, could not be parsed to JSON';
			IssueLog::Error($sErrorMessage, ConfigHelper::GetModuleCode(), [
				'json_error' => json_last_error_msg(),
				'payload' => $this->sPayload,
			]);
			throw new Exception($sErrorMessage);
		}
	}

	/**
	 * @param string      $sFriendlyname Friendlyname / fullname of the contact to find
	 * @param null|string $sEmail Email of the contact to find
	 *
	 * @return \DBObject|null Return the **first** Contact object matching $sFriendlyname (and $sEmail is provided), null if none found.
	 * @throws \CoreException
	 * @throws \CoreUnexpectedValue
	 * @throws \MissingQueryArgument
	 * @throws \MySQLException
	 * @throws \MySQLHasGoneAwayException
	 * @throws \OQLException
	 */
	protected function ContactLookupFromEmail($sFriendlyname, $sEmail = null)
	{
		$oSearch = DBObjectSearch::FromOQL('SELECT Contact WHERE friendlyname = :friendlyname');
		$oSearch->AllowAllData(true);
		$aParams = ['friendlyname' => $sFriendlyname];

		if (false === is_null($sEmail)) {
			$oSearch->AddConditionExpression(new BinaryExpression(new FieldExpression('email'), '=', new VariableExpression('email')));
			$aParams['email'] = $sEmail;
		}

		$oSet = new DBObjectSet($oSearch, [], $aParams);
		$iCount = $oSet->CountWithLimit(2);

		if ($iCount === 0) {
			return null;
		} else {
			return $oSet->Fetch();
		}
	}
}
